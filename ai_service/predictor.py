import os
from datetime import datetime

import joblib
import numpy as np
import pandas as pd

LEGACY_MODEL_PATH = "model/project_delay_model.pkl"
LEGACY_SCALER_PATH = "model/scaler.pkl"

NEW_MODEL_PATH = "model/project_delay_model_leg.keras"
NEW_SCALER_PATH = "model/scaler_leg.pkl"
NEW_FEATURES_PATH = "model/feature_columns_leg.pkl"

legacy_model = None
legacy_scaler = None
dnn_model = None
dnn_scaler = None
dnn_features = []

LEGACY_FEATURES = [
    "pacing_index",
    "climate_stress",
    "dispute_count",
    "holiday_impact_pct",
    "avg_temp",
    "avg_rain",
    "low_visibility_days",
]


def _load_legacy_artifacts():
    global legacy_model, legacy_scaler
    if os.path.exists(LEGACY_MODEL_PATH):
        try:
            legacy_model = joblib.load(LEGACY_MODEL_PATH)
        except Exception as e:
            print(f"Legacy model load error: {e}")

    if os.path.exists(LEGACY_SCALER_PATH):
        try:
            legacy_scaler = joblib.load(LEGACY_SCALER_PATH)
        except Exception as e:
            print(f"Legacy scaler load error: {e}")


def _load_new_artifacts():
    global dnn_model, dnn_scaler, dnn_features

    if os.path.exists(NEW_SCALER_PATH):
        try:
            dnn_scaler = joblib.load(NEW_SCALER_PATH)
        except Exception as e:
            print(f"DNN scaler load error: {e}")

    if os.path.exists(NEW_FEATURES_PATH):
        try:
            dnn_features = joblib.load(NEW_FEATURES_PATH)
        except Exception as e:
            print(f"DNN feature list load error: {e}")

    if os.path.exists(NEW_MODEL_PATH):
        try:
            import tensorflow as tf

            dnn_model = tf.keras.models.load_model(NEW_MODEL_PATH)
        except Exception as e:
            print(f"DNN model load skipped: {e}")


def _clamp(value, low, high):
    return max(low, min(value, high))


def validate_input_data(input_data: dict) -> dict:
    """Validate and sanitize base runtime signals from live system data."""
    validated = {}

    validated["pacing_index"] = _clamp(float(input_data.get("pacing_index", 1.0)), 0.0, 1.5)
    validated["climate_stress"] = _clamp(float(input_data.get("climate_stress", 0)), 0.0, 15.0)
    validated["dispute_count"] = _clamp(int(input_data.get("dispute_count", 0)), 0, 20)
    validated["holiday_impact_pct"] = _clamp(float(input_data.get("holiday_impact_pct", 0)), 0.0, 1.0)
    validated["avg_temp"] = _clamp(float(input_data.get("avg_temp", 30.0)), 15.0, 45.0)
    validated["avg_rain"] = _clamp(float(input_data.get("avg_rain", 0.0)), 0.0, 500.0)
    validated["low_visibility_days"] = _clamp(int(input_data.get("low_visibility_days", 0)), 0, 30)

    return validated


def _build_dnn_feature_row(validated_data: dict, raw_input: dict) -> dict:
    """Map current live runtime signals into the 37-feature DNN schema."""
    now = datetime.now()
    start_month = _clamp(int(raw_input.get("start_month", now.month)), 1, 12)
    start_quarter = ((start_month - 1) // 3) + 1
    rainy_season = 1 if start_month in [6, 7, 8, 9, 10, 11] else 0
    ber_months = 1 if start_month in [9, 10, 11, 12] else 0

    budget_value = float(raw_input.get("budget_value", 50_000_000))
    budget_log = float(raw_input.get("budget_log", 0.0))
    if budget_log <= 0:
        budget_log = float(np.log1p(budget_value))
    budget_tier = int(raw_input.get("budget_tier", 1))

    remaining_days = _clamp(int(raw_input.get("to_finish", 90)), 1, 730)
    holiday_density = validated_data["holiday_impact_pct"]
    holiday_count = int(round(holiday_density * remaining_days))

    oni = float(raw_input.get("oni_value", 0.0))
    enso_strength = abs(oni)
    enso_multiplier = 1.15 if oni >= 0.5 else (1.20 if oni <= -0.5 else 1.00)

    avg_temp = validated_data["avg_temp"]
    avg_humidity = _clamp(float(raw_input.get("avg_humidity", 75.0)), 30.0, 100.0)
    avg_rain = validated_data["avg_rain"]
    heavy_rain_freq = _clamp(float(raw_input.get("heavy_rain_freq", avg_rain / 30.0)), 0.0, 1.0)
    avg_wind_speed = _clamp(float(raw_input.get("avg_wind_speed", 10.0)), 0.0, 150.0)
    low_vis_freq = _clamp(float(raw_input.get("low_vis_freq", validated_data["low_visibility_days"] / 30.0)), 0.0, 1.0)
    heat_stress_freq = _clamp(float(raw_input.get("heat_stress_freq", max(0.0, avg_temp - 30.0) / 15.0)), 0.0, 1.0)
    temp_humidity_idx = avg_temp + (0.33 * avg_humidity) - 4.0

    base_climate_stress = _clamp(float(raw_input.get("base_climate_stress", validated_data["climate_stress"] / 2.0)), 0.0, 10.0)
    climate_stress = base_climate_stress * enso_multiplier
    enso_rain_risk = (avg_rain * 1.20 + heavy_rain_freq * 8.0) if oni <= -0.5 else avg_rain
    enso_heat_risk = (heat_stress_freq * 12.0 + max(0.0, avg_temp - 32.0) * 1.3) if oni >= 0.5 else (heat_stress_freq * 8.0)

    contractor_exp_years = _clamp(float(raw_input.get("contractor_exp_years", 0.0)), 0.0, 60.0)
    contractor_n_prior = _clamp(int(raw_input.get("contractor_n_prior", 0)), 0, 300)
    contractor_success_rate = _clamp(float(raw_input.get("contractor_success_rate", 0.75)), 0.0, 1.0)
    cpr_delay = 1.0 - contractor_success_rate
    cpr_cost = _clamp(float(raw_input.get("cpr_cost", 1.0)), 0.5, 2.0)
    cpr_std = _clamp(float(raw_input.get("cpr_std", 30.0)), 0.0, 365.0)

    norm_std = _clamp(cpr_std / 120.0, 0.0, 1.0)
    experience_efficiency = (0.60 * (1.0 - cpr_delay)) + (0.40 * (1.0 - norm_std))

    location_text = str(raw_input.get("project_location", "")).lower()
    is_mindanao = 1 if any(k in location_text for k in ["mindanao", "davao", "zamboanga", "cotabato", "cagayan de oro"]) else 0
    is_visayas = 1 if any(k in location_text for k in ["visayas", "cebu", "iloilo", "leyte", "samar", "bacolod"]) else 0

    contractor_count = _clamp(int(raw_input.get("ContractorCount", 1)), 1, 20)

    feature_row = {
        "start_month": float(start_month),
        "start_quarter": float(start_quarter),
        "rainy_season": float(rainy_season),
        "ber_months": float(ber_months),
        "budget_log": float(budget_log),
        "budget_tier": float(budget_tier),
        "holiday_count": float(holiday_count),
        "holiday_density": float(holiday_density),
        "oni": float(oni),
        "enso_strength": float(enso_strength),
        "enso_multiplier": float(enso_multiplier),
        "avg_temp": float(avg_temp),
        "avg_humidity": float(avg_humidity),
        "avg_rain": float(avg_rain),
        "heavy_rain_freq": float(heavy_rain_freq),
        "avg_wind_speed": float(avg_wind_speed),
        "low_vis_freq": float(low_vis_freq),
        "heat_stress_freq": float(heat_stress_freq),
        "temp_humidity_idx": float(temp_humidity_idx),
        "base_climate_stress": float(base_climate_stress),
        "climate_stress": float(climate_stress),
        "enso_rain_risk": float(enso_rain_risk),
        "enso_heat_risk": float(enso_heat_risk),
        "contractor_exp_years": float(contractor_exp_years),
        "contractor_n_prior": float(contractor_n_prior),
        "cpr_delay": float(cpr_delay),
        "cpr_cost": float(cpr_cost),
        "cpr_std": float(cpr_std),
        "experience_efficiency": float(experience_efficiency),
        "is_mindanao": float(is_mindanao),
        "is_visayas": float(is_visayas),
        "ContractorCount": float(contractor_count),
    }

    feature_row["climate_x_rainy"] = feature_row["climate_stress"] * feature_row["rainy_season"]
    feature_row["holiday_x_budget"] = feature_row["holiday_density"] * feature_row["budget_log"]
    feature_row["contractor_x_climate"] = feature_row["experience_efficiency"] * feature_row["climate_stress"]
    feature_row["enso_x_rain"] = feature_row["enso_multiplier"] * feature_row["avg_rain"]
    feature_row["heat_x_humidity"] = feature_row["enso_heat_risk"] * feature_row["avg_humidity"]

    return feature_row


def _confidence_from_probability(probability: float) -> str:
    confidence_score = abs(probability - 0.5) * 2.0
    if confidence_score > 0.6:
        return "high"
    if confidence_score > 0.3:
        return "medium"
    return "low"


def _predict_with_new_dnn(validated_data: dict, raw_input: dict):
    if not (dnn_model is not None and dnn_scaler is not None and dnn_features):
        return None

    try:
        feature_row = _build_dnn_feature_row(validated_data, raw_input)
        df = pd.DataFrame([feature_row])
        for col in dnn_features:
            if col not in df.columns:
                df[col] = 0.0

        df = df[dnn_features].astype(float).fillna(0.0)
        x_scaled = dnn_scaler.transform(df)
        prob = float(dnn_model.predict(x_scaled, verbose=0)[0][0])
        confidence = _confidence_from_probability(prob)

        return {
            "delay_probability": round(_clamp(prob, 0.0, 1.0), 4),
            "prediction": "DELAYED" if prob >= 0.5 else "ON-TIME",
            "confidence": confidence,
            "model_version": "2.0-dnn",
        }
    except Exception as e:
        print(f"DNN prediction error: {e}")
        return None


def _predict_with_legacy_model(validated_data: dict):
    if not (legacy_model is not None and legacy_scaler is not None):
        return None

    try:
        df = pd.DataFrame([validated_data])
        for col in LEGACY_FEATURES:
            if col not in df.columns:
                df[col] = 0.0
        df = df[LEGACY_FEATURES].astype(float).fillna(0.0)

        x_scaled = legacy_scaler.transform(df)
        prob = float(legacy_model.predict_proba(x_scaled)[0][1])
        confidence = _confidence_from_probability(prob)

        return {
            "delay_probability": round(_clamp(prob, 0.0, 1.0), 4),
            "prediction": "DELAYED" if prob >= 0.5 else "ON-TIME",
            "confidence": confidence,
            "model_version": "1.0-rf",
        }
    except Exception as e:
        print(f"Legacy prediction error: {e}")
        return None


def _rule_based_fallback(validated_data: dict):
    prob = 0.35

    if validated_data["pacing_index"] < 0.9:
        prob += 0.20
    if validated_data["pacing_index"] < 0.7:
        prob += 0.10

    if validated_data["dispute_count"] > 0:
        prob += min(0.30 + (0.03 * validated_data["dispute_count"]), 0.45)

    if validated_data["climate_stress"] >= 7:
        prob += 0.10

    if validated_data["holiday_impact_pct"] > 0.15:
        prob += 0.06

    prob = _clamp(prob, 0.01, 0.99)

    return {
        "delay_probability": round(prob, 4),
        "prediction": "DELAYED" if prob >= 0.5 else "ON-TIME",
        "reason": "Model unavailable, using validated business fallback rules",
        "confidence": "medium",
        "model_version": "fallback-rules",
    }


def predict_delay(input_data: dict):
    try:
        validated_data = validate_input_data(input_data)
    except Exception as e:
        print(f"Input validation error: {e}")
        validated_data = {
            "pacing_index": 1.0,
            "climate_stress": 0.0,
            "dispute_count": 0,
            "holiday_impact_pct": 0.0,
            "avg_temp": 30.0,
            "avg_rain": 0.0,
            "low_visibility_days": 0,
        }

    dnn_result = _predict_with_new_dnn(validated_data, input_data)
    if dnn_result:
        return dnn_result

    legacy_result = _predict_with_legacy_model(validated_data)
    if legacy_result:
        return legacy_result

    return _rule_based_fallback(validated_data)


_load_legacy_artifacts()
_load_new_artifacts()

model_is_trained = bool((legacy_model is not None) or (dnn_model is not None))
scaler_is_trained = bool((legacy_scaler is not None) or (dnn_scaler is not None))