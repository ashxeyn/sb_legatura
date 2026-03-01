from enso import fetch_latest_enso_state

def generate_dds_recommendation(
    delay_probability: float,
    weather_severity: int,
    dispute_count: int,
    pacing_index: float,
    holiday_impact_pct: float,
    rejected_count: int = 0,
    weather_data: dict = {}
):
    """
    Advanced DSS Recommender for Philippine Construction
    """
    enso_state, oni_value = fetch_latest_enso_state()
    recs = []

    # 1. QUALITY / REWORK (Highest Priority)
    if rejected_count > 0:
        recs.append(
            f"🔴 QUALITY ALERT: {rejected_count} milestone items were rejected. "
            "Immediately halt new phases until rework is approved."
        )

    # 2. PACING
    if pacing_index < 0.8: 
        recs.append(
            "🟠 SCHEDULE SLIP: Contractor is falling behind. Request a 'Catch-Up Plan' for critical path items."
        )
    elif pacing_index > 1.1: 
        recs.append(
            "🟢 PACING GOOD: Work is ahead of schedule. Ensure quality isn't being sacrificed for speed."
        )

    # 3. WEATHER
    rain = weather_data.get("total_rain", 0)
    if rain > 10:
        recs.append(
            f"🌧️ HEAVY RAIN ({rain}mm): Suspend exterior concrete pouring. Focus on indoor electrical/plumbing."
        )
    elif weather_severity > 1:
        recs.append("⚠️ WEATHER ADVISORY: Site conditions are poor. Ensure safety gear is used.")

    # 4. HOLIDAYS
    if holiday_impact_pct > 0.15:
        recs.append(
            "📅 CALENDAR RISK: High holiday density upcoming. Negotiate overtime or double shifts now."
        )

    # 5. DISPUTES
    if dispute_count > 0:
        recs.append(
            "⚖️ LEGAL RISK: Active disputes detected. Assign a mediator immediately."
        )

    # 6. GENERAL RISK
    if delay_probability > 0.7:
        recs.append(
            "📢 MANAGEMENT ACTION: High Risk of Delay. Convene emergency meeting with contractor."
        )

    # 7. ENSO CONTEXT
    if enso_state == "ElNino":
        recs.append("☀️ EL NIÑO: Expect drought. Plan water trucking for concrete works.")
    elif enso_state == "LaNina":
        recs.append("⛈️ LA NIÑA: Expect frequent storms. Clear site drainage systems.")

    return recs, enso_state, oni_value