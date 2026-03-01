from fastapi import FastAPI, HTTPException
import logging
import traceback
import os

from database import get_project_and_contractor
from weather import get_weather
from weather_severity import compute_weather_severity
from predictor import predict_delay, model_is_trained, scaler_is_trained
from recommender import generate_dds_recommendation
from enso import fetch_latest_enso_state

app = FastAPI()

# Logging
os.makedirs("logs", exist_ok=True)
_logger = logging.getLogger("ai_service")
if not _logger.handlers:
    handler = logging.FileHandler("logs/ai_service.log")
    handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
    _logger.addHandler(handler)
    _logger.setLevel(logging.INFO)

@app.get("/predict/{project_id}")
def predict(project_id: int):
    try:
        # 1. Fetch Data
        data = get_project_and_contractor(project_id)
        if not data:
            raise HTTPException(status_code=404, detail="Project not found")

        # 2. Fetch Weather
        weather = get_weather(data["project_location"])
        rain_mm = float(weather.get("total_rain", 0) or 0)
        avg_wind = float(weather.get("avg_wind", 0) or 0)

        # 3. Compute Severity
        rain_events = 4 if rain_mm > 10 else 2 if rain_mm > 5 else 1 if rain_mm > 1 else 0
        work_suspension_hours = 6 if rain_mm > 15 else 3 if rain_mm > 6 or avg_wind > 60 else 1 if rain_mm > 1 else 0
        flooding = 1 if rain_mm > 25 else 0
        low_visibility = 1 if weather["avg_humidity"] > 90 else 0
        storm_warning = 1 if avg_wind >= 70 else 0

        weather_severity = compute_weather_severity(
            rain_events=rain_events,
            flooding=flooding,
            work_suspension_hours=work_suspension_hours,
            low_visibility=low_visibility,
            storm_warning=storm_warning,
        )

        enso_state, oni_value = fetch_latest_enso_state()

        # 4. Prepare AI Input
        input_data = {
            "pacing_index": data["pacing_index"],
            "climate_stress": weather_severity,
            "dispute_count": data["dispute_count"],
            "holiday_impact_pct": data["holiday_impact_pct"],
            "avg_temp": weather["avg_temp"],
            "avg_rain": weather["total_rain"],
            "low_visibility_days": low_visibility,
        }

        # 5. Run Base Prediction
        prediction = predict_delay(input_data)
        final_prob = prediction["delay_probability"]
        final_verdict = prediction["prediction"]
        logic_reason = "Standard AI analysis based on current metrics."

        # -----------------------------------------------------------
        # 6. LOGIC OVERRIDE: The "Experience Trap" & "Rework" Logic
        # -----------------------------------------------------------
        
        c_exp = data["contractor_experience_years"]
        c_success = data["contractor_history"]["success_rate"]
        rejected = data["pacing_data"]["rejected_count"]

        # A. Experience Trap (Veteran but Failing)
        if c_exp >= 10 and c_success < 0.5:
            if final_prob < 0.70:
                final_prob = 0.85 # Force High Risk
                final_verdict = "DELAYED"
                logic_reason = f"OVERRIDE: Contractor has {c_exp} years experience but low success rate ({c_success*100}%). Statistical risk boost applied."

        # B. Rework Penalty
        if rejected >= 1:
            final_prob = max(final_prob, 0.90)
            final_verdict = "DELAYED"
            logic_reason = f"CRITICAL: {rejected} milestone items rejected. Rework is causing significant delays."

        # C. Dispute Risk Override
        if data["dispute_count"] >= 1:
            final_prob = max(final_prob, 0.75)
            final_verdict = "DELAYED"
            logic_reason = (
                f"CRITICAL: {data['dispute_count']} active dispute(s) detected. "
                "Construction disputes typically slow or halt work progress."
            )

        # If disputes are multiple, escalate risk
        if data["dispute_count"] >= 3:
            final_prob = max(final_prob, 0.90)
            final_verdict = "DELAYED"
            logic_reason = (
                f"SEVERE: {data['dispute_count']} disputes detected. "
                "High probability of schedule disruption."
            )

    # D. Enso Risk Override
        # Update Prediction Object
        prediction["delay_probability"] = round(final_prob, 4)
        prediction["prediction"] = final_verdict
        prediction["reason"] = logic_reason

        # 7. Generate Recommendations
        dds_recommendations, enso_state, oni_value = generate_dds_recommendation(
            delay_probability=final_prob,
            weather_severity=weather_severity,
            dispute_count=data["dispute_count"],
            pacing_index=data["pacing_index"],
            holiday_impact_pct=data["holiday_impact_pct"],
            rejected_count=rejected,
            weather_data=weather
        )

        # 8. Construct Report Conclusion
        avg_delay = data["pacing_data"]["avg_delay_days"]
        pacing_text = "ahead of schedule" if avg_delay <= 0 else f"{avg_delay} days behind schedule"
        
        conclusion = (
            f"The project '{data['project_title']}' is currently {pacing_text}. "
            f"AI predicts a {final_prob*100:.1f}% probability of delay. "
            f"Weather impact is {'Severe' if weather_severity > 2 else 'Minimal'} ({weather['total_rain']}mm rain). "
            f"Holidays impact {data['holiday_impact_pct']*100:.0f}% of remaining time. "
            f"{logic_reason}"
        )

        return {
            "prediction": prediction,
            "analysis_report": {
                "conclusion": conclusion,
                "pacing_status": data["pacing_data"],
                "contractor_audit": {
                    "experience": f"{c_exp} Years",
                    "historical_success": f"{c_success*100:.0f}%",
                    "flagged": (c_exp > 10 and c_success < 0.5)
                }
            },
            "weather": weather,
            "weather_severity": weather_severity,
            "dds_recommendations": dds_recommendations,
            "enso_state": enso_state,
        }

    except Exception as e:
        _logger.error(f"Error in predict: {str(e)}\n{traceback.format_exc()}")
        return {"error": str(e)}

@app.get("/health")
def health():
    return {"ok": True, "model_loaded": bool(model_is_trained)}

@app.get("/system-status")
def system_status():
    return {
        "service_status": "Online",
        "active_features": ["Heuristic Logic Override", "Real-Time Pacing", "Weather Context"]
    }