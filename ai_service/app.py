from fastapi import FastAPI, HTTPException
import logging
import traceback
import os

app = FastAPI()

# Logging setup
os.makedirs("logs", exist_ok=True)
_logger = logging.getLogger("ai_service")
if not _logger.handlers:
    file_handler = logging.FileHandler("logs/ai_service.log")
    file_handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
    _logger.addHandler(file_handler)

    console_handler = logging.StreamHandler()
    console_handler.setFormatter(logging.Formatter("%(asctime)s %(levelname)s %(message)s"))
    _logger.addHandler(console_handler)

    _logger.setLevel(logging.INFO)

# Import model-dependent modules AFTER logging setup
from database import get_project_and_contractor
from weather import get_weather
from weather_severity import compute_weather_severity
from predictor import predict_delay, model_is_trained, scaler_is_trained
from recommender import generate_dds_recommendation
from enso import fetch_latest_enso_state
from risk_policy import apply_risk_adjustments

# Startup event
@app.on_event("startup")
async def startup_event():
    _logger.info(f"AI Service started - Model loaded: {model_is_trained}")

# Health check endpoint
@app.get("/")
def root():
    return {"status": "ok", "service": "Legatura AI Service", "version": "1.0"}

@app.get("/predict/{project_id}")
def predict(project_id: int):
    _logger.info(f"Prediction request received for project_id={project_id}")
    
    try:
        # 1. Fetch Data
        _logger.info(f"Fetching project data for project_id={project_id}")
        data = get_project_and_contractor(project_id)
        if not data:
            _logger.warning(f"Project not found: project_id={project_id}")
            raise HTTPException(status_code=404, detail="Project not found")

        # 2. Fetch Weather
        _logger.info(f"Fetching weather for location: {data['project_location']}")
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
        _logger.info(f"Weather severity computed: {weather_severity}")

        enso_state, oni_value = fetch_latest_enso_state()
        _logger.info(f"ENSO state: {enso_state}, ONI: {oni_value}")

        # 4. Prepare AI Input
        input_data = {
            "pacing_index": data["pacing_index"],
            "climate_stress": weather_severity,
            "dispute_count": data["dispute_count"],
            "holiday_impact_pct": data["holiday_impact_pct"],
            "avg_temp": weather["avg_temp"],
            "avg_rain": weather["total_rain"],
            "low_visibility_days": low_visibility,
            "avg_humidity": weather.get("avg_humidity", 70),
            "avg_wind_speed": weather.get("avg_wind", 10),
            "oni_value": oni_value,
            "project_location": data.get("project_location", ""),
            "to_finish": data.get("to_finish", 30),
            "contractor_exp_years": data.get("contractor_experience_years", 0),
            "contractor_success_rate": data.get("contractor_history", {}).get("success_rate", 0.75),
            "contractor_n_prior": data.get("contractor_history", {}).get("total_projects", 0),
            "ContractorCount": 1,
        }
        _logger.info(f"AI input prepared: {input_data}")

        # 5. Run Base Prediction
        prediction = predict_delay(input_data)
        final_prob = prediction["delay_probability"]
        final_verdict = prediction["prediction"]
        confidence = prediction.get("confidence", "medium")
        logic_reason = "Standard AI analysis based on current metrics."

        # -----------------------------------------------------------
        # 6. RULE POLICY: Bounded, configurable risk adjustments
        # -----------------------------------------------------------

        c_exp = data["contractor_experience_years"]
        c_success = data["contractor_history"]["success_rate"]
        rejected = data["pacing_data"]["rejected_count"]

        final_prob, rule_adjustment, adjustment_notes = apply_risk_adjustments(
            base_prob=float(final_prob),
            contractor_exp=int(c_exp or 0),
            contractor_success=float(c_success or 0.0),
            rejected_count=int(rejected or 0),
            dispute_count=int(data["dispute_count"] or 0),
        )

        final_verdict = "DELAYED" if final_prob >= 0.5 else "ON-TIME"

        if adjustment_notes:
            logic_reason = (
                f"Model-driven risk with bounded policy adjustment (+{rule_adjustment:.2f}) "
                f"from: {', '.join(adjustment_notes)}."
            )
            _logger.warning(
                f"Risk policy adjustments applied for project_id={project_id}: "
                f"adj={rule_adjustment}, notes={adjustment_notes}"
            )
        else:
            logic_reason = "Model-driven risk with no policy adjustment."

        # Update Prediction Object
        prediction["delay_probability"] = round(final_prob, 4)
        prediction["prediction"] = final_verdict
        prediction["reason"] = logic_reason
        prediction["confidence"] = confidence

        # 7. Generate Recommendations
        _logger.info("Generating recommendations")
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

        _logger.info(f"Prediction completed successfully for project_id={project_id}: {final_verdict} ({final_prob*100:.1f}%)")

        return {
            "prediction": prediction,
            "analysis_report": {
                "conclusion": conclusion,
                "pacing_status": data["pacing_data"],
                "contractor_audit": {
                    "experience": f"{c_exp} Years",
                    "historical_success": f"{c_success*100:.0f}%",
                    "flagged": (c_exp > 10 and c_success < 0.5),
                    "status": "High Risk" if (c_exp > 10 and c_success < 0.5) else "Good Standing"
                }
            },
            "weather": weather,
            "weather_severity": weather_severity,
            "dds_recommendations": dds_recommendations,
            "enso_state": enso_state,
        }

    except HTTPException:
        raise
    except Exception as e:
        _logger.error(f"Error in predict for project_id={project_id}: {str(e)}\n{traceback.format_exc()}")
        return {"error": str(e), "project_id": project_id}

@app.get("/health")
def health():
    return {"ok": True, "model_loaded": bool(model_is_trained)}

@app.get("/system-status")
def system_status():
    return {
        "service_status": "Online",
        "active_features": ["Bounded Risk Policy", "Real-Time Pacing", "Weather Context"]
    }
