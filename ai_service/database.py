import mysql.connector
import os
from datetime import datetime, timedelta
from holidays import compute_holiday_impact

# Load environment variables (for production deployment)
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass  # dotenv not required in development

def get_db_connection():
    """Get database connection using environment variables or defaults."""
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=os.getenv("DB_NAME", "legatura"),
        port=int(os.getenv("DB_PORT", 3306)),
    )

# --------- NEW: Detailed Pacing Logic ---------
def get_detailed_pacing_stats(conn, project_id):
    """
    Calculates pacing based on:
    1. Deadline (date_to_finish) vs Actual Submission (submitted_at)
    2. Quality (progress_status: approved vs rejected)
    """
    cursor = conn.cursor(dictionary=True)

    # Fetch all items, their deadlines, and the LATEST progress submission
    query = """
        SELECT
            mi.item_id,
            mi.milestone_item_title,
            mi.date_to_finish,
            p.submitted_at,
            p.progress_status
        FROM milestone_items mi
        LEFT JOIN (
            SELECT milestone_item_id, submitted_at, progress_status
            FROM progress
            WHERE progress_id IN (
                SELECT MAX(progress_id) FROM progress GROUP BY milestone_item_id
            )
        ) p ON mi.item_id = p.milestone_item_id
        WHERE mi.milestone_id IN (SELECT milestone_id FROM milestones WHERE project_id = %s)
    """
    cursor.execute(query, (project_id,))
    items = cursor.fetchall()

    if not items:
        return {"pacing_index": 1.0, "avg_delay_days": 0, "rejected_count": 0, "details": []}

    total_items = len(items)
    total_days_variance = 0
    rejected_count = 0
    today = datetime.now().date()

    item_details = []

    for item in items:
        deadline = item["date_to_finish"]
        submitted = item["submitted_at"]
        status = item["progress_status"]

            # --- NORMALIZE DATE TYPES ---
        if isinstance(deadline, datetime):
            deadline = deadline.date()

        if isinstance(submitted, datetime):
            submitted = submitted.date()

        variance = 0
        pacing_status = "Pending"

        # Logic: If submitted, check date diff
        if submitted:
            # If rejected, we penalize it as 'late' even if submitted early because it needs rework
            if status == 'rejected':
                rejected_count += 1
                variance = 5 # Penalty: Treat as 5 days late automatically
                pacing_status = "REWORK NEEDED"
            else:
                # Calculate difference: submitted - deadline
                # Negative = Early, Positive = Late
                if isinstance(submitted, datetime): submitted = submitted.date()
                if isinstance(deadline, datetime): deadline = deadline.date()

                delta = (submitted - deadline).days
                variance = delta
                pacing_status = "LATE" if delta > 0 else "ON-TIME/EARLY"

        # Logic: If NOT submitted but deadline passed
        elif deadline is not None and deadline < today:
             delta = (today - deadline).days
             variance = delta # Days overdue
             pacing_status = "OVERDUE (Missing)"

        total_days_variance += variance

        item_details.append({
            "title": item["milestone_item_title"],
            "status": status if status else "No Submission",
            "days_variance": variance,
            "pacing_label": pacing_status
        })

    # Calculate Normalized Pacing Index for the AI Model
    # Formula: Start at 1.0. Subtract 0.05 for every day late on average.
    avg_variance = total_days_variance / max(1, total_items)
    pacing_index = 1.0 - (avg_variance * 0.05)

    # Penalize for rejections explicitly (Reduce index further)
    if rejected_count > 0:
        pacing_index -= (rejected_count * 0.1)

    # Additional penalty if disputes exist
    cursor.execute(
        "SELECT COUNT(*) as cnt FROM disputes WHERE project_id = %s AND dispute_status IN ('open','under_review')",
        (project_id,)
    )
    dispute_count = cursor.fetchone()["cnt"]

    if dispute_count > 0:
        pacing_index -= (dispute_count * 0.05)
        pacing_index = max(0.1, min(pacing_index, 1.2))

    return {
        "pacing_index": round(pacing_index, 2),
        "avg_delay_days": round(avg_variance, 1),
        "rejected_count": rejected_count,
        "details": item_details
    }

# --------- NEW: Contractor History Logic ---------

def get_contractor_history(conn, contractor_id):
    """
    Calculates a 'Composite Reputation Score' based on:
    1. Project Completion Rate (Bayesian smoothed) - 70% weight
    2. Owner Ratings/Reviews (Bayesian smoothed) - 30% weight
    """
    cursor = conn.cursor(dictionary=True)

    # ---------------------------------------------------------
    # PART A: Technical Success (Projects Completed vs Total)
    # ---------------------------------------------------------
    cursor.execute("""
        SELECT
            COUNT(project_id) as total_projects,
            SUM(CASE WHEN project_status = 'completed' THEN 1 ELSE 0 END) as successful_projects
        FROM projects
        WHERE selected_contractor_id = %s
    """, (contractor_id,))
    proj_stats = cursor.fetchone()

    # Bayesian Math for Projects (Default to 75% success if new)
    C_PROJ = 2
    AVG_PROJ_SUCCESS = 0.75

    real_success_count = float(proj_stats['successful_projects'] or 0)
    real_total_proj = float(proj_stats['total_projects'] or 0)

    technical_score = (real_success_count + (C_PROJ * AVG_PROJ_SUCCESS)) / (real_total_proj + C_PROJ)

    # ---------------------------------------------------------
    # PART B: Social Success (Star Ratings from Property Owners)
    # ---------------------------------------------------------
    # We join reviews to projects to ensure we get reviews for this contractor's specific jobs
    # Assuming 'rating' column is 1-5
    cursor.execute("""
        SELECT
            COUNT(r.review_id) as total_reviews,
            AVG(r.rating) as avg_stars
        FROM reviews r
        JOIN projects p ON r.project_id = p.project_id
        WHERE p.selected_contractor_id = %s
    """, (contractor_id,))
    review_stats = cursor.fetchone()

    # Bayesian Math for Ratings (Default to 4.0 stars if new)
    C_REV = 3 # We need 3 reviews to start shifting the score significantly
    AVG_STARS = 4.0

    real_review_count = float(review_stats['total_reviews'] or 0)
    real_avg_stars = float(review_stats['avg_stars'] or AVG_STARS) # Use default if NULL

    # Calculate Bayesian Average Rating
    # Formula: ( (TotalReviews * RealAvg) + (C * GlobalAvg) ) / (TotalReviews + C)
    smoothed_rating = ( (real_review_count * real_avg_stars) + (C_REV * AVG_STARS) ) / (real_review_count + C_REV)

    # Normalize to 0.0 - 1.0 scale (e.g., 4.0 stars becomes 0.8)
    social_score = smoothed_rating / 5.0

    # ---------------------------------------------------------
    # PART C: Weighted Composite Score
    # ---------------------------------------------------------
    # We prioritize Technical (0.7) over Social (0.3)
    composite_score = (technical_score * 0.7) + (social_score * 0.3)

    return {
        "success_rate": round(composite_score, 2), # This is what the AI uses
        "technical_score": round(technical_score, 2),
        "social_score": round(social_score, 2),
        "star_rating": round(smoothed_rating, 1),
        "total_projects": int(real_total_proj),
        "total_reviews": int(real_review_count)
    }
# --------- MAIN FUNCTION USED BY FASTAPI ---------
def get_project_and_contractor(project_id: int):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    # 1. Get Project Basics
    cursor.execute("""
        SELECT project_location, to_finish, selected_contractor_id, project_title
        FROM projects
        WHERE project_id = %s
    """, (project_id,))
    project = cursor.fetchone()

    if not project:
        cursor.close()
        conn.close()
        return None

    # 2. Get Contractor Stats
    contractor_exp = 0
    contractor_history = {"success_rate": 1.0, "total": 0}

    if project["selected_contractor_id"]:
        cursor.execute("SELECT years_of_experience FROM contractors WHERE contractor_id = %s", (project["selected_contractor_id"],))
        res = cursor.fetchone()
        if res:
            contractor_exp = res["years_of_experience"]

        # Fetch Success/Fail History
        contractor_history = get_contractor_history(conn, project["selected_contractor_id"])

    # 3. Get Real-Time Pacing
    pacing_data = get_detailed_pacing_stats(conn, project_id)

    # 4. Get Disputes
    cursor.execute("SELECT COUNT(*) as cnt FROM disputes WHERE project_id = %s AND dispute_status IN ('open', 'under_review')", (project_id,))
    dispute_count = cursor.fetchone()['cnt']

    # 5. Get Holiday Impact
    today = datetime.now().date()
    holiday_impact_pct = compute_holiday_impact(
        start_date=today,
        remaining_days=project["to_finish"] or 30
    )

    cursor.close()
    conn.close()

    return {
        "project_title": project["project_title"],
        "project_location": project["project_location"],
        "to_finish": project["to_finish"],
        "contractor_experience_years": contractor_exp,
        "contractor_history": contractor_history,
        "pacing_data": pacing_data,
        "pacing_index": pacing_data["pacing_index"],
        "dispute_count": dispute_count,
        "holiday_impact_pct": holiday_impact_pct,
        "climate_stress": 0,
    }
