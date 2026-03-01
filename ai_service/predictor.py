import joblib
import pandas as pd
import os

MODEL_PATH = "model/project_delay_model.pkl"
SCALER_PATH = "model/scaler.pkl"

model = None
scaler = None
model_is_trained = False
scaler_is_trained = False

# Load Model
if os.path.exists(MODEL_PATH):
    try:
        model = joblib.load(MODEL_PATH)
        model_is_trained = True
    except Exception as e:
        print(f"Model load error: {e}")

# Load Scaler
if os.path.exists(SCALER_PATH):
    try:
        scaler = joblib.load(SCALER_PATH)
        scaler_is_trained = True
    except Exception as e:
        print(f"Scaler load error: {e}")

FEATURES = [
    "pacing_index", "climate_stress", "dispute_count", 
    "holiday_impact_pct", "avg_temp", "avg_rain", "low_visibility_days"
]

def predict_delay(input_data: dict):
    # Fallback if model is missing
    if not model_is_trained or not scaler_is_trained:
        # Rule-based fallback
        prob = 0.5
        if input_data['pacing_index'] < 0.9: prob += 0.2
        if input_data['dispute_count'] > 0: prob += 0.3 + (0.05 * input_data['dispute_count'])
        return {
            "delay_probability": min(prob, 0.99),
            "prediction": "DELAYED" if prob >= 0.5 else "ON-TIME",
            "reason": "Model not loaded, using fallback rules"
        }

    # Prepare DataFrame
    df = pd.DataFrame([input_data])
    
    # Ensure all columns exist
    for col in FEATURES:
        if col not in df.columns:
            df[col] = 0
            
    df = df[FEATURES].astype(float).fillna(0)

    try:
        X = scaler.transform(df)
        prob = float(model.predict_proba(X)[0][1])
    except Exception as e:
        print(f"Prediction Error: {e}")
        prob = 0.5 # Safe default

    return {
        "delay_probability": round(prob, 4),
        "prediction": "DELAYED" if prob >= 0.5 else "ON-TIME",
    }