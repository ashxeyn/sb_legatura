# enso.py
import pandas as pd
import requests

ONI_URL = "https://www.cpc.ncep.noaa.gov/data/indices/oni.ascii.txt"

def fetch_latest_enso_state():
    """
    Fetch latest ONI values from NOAA and return ENSO state,
    interpreted for Philippine climate context:

    - ElNino  -> drought, heat stress, water shortage in PH
    - LaNina  -> heavy rainfall, flooding, typhoon risk in PH
    - Neutral -> normal conditions
    """
    try:
        r = requests.get(ONI_URL, timeout=15)
        r.raise_for_status()
        lines = r.text.splitlines()

        records = []

        for line in lines:
            line = line.strip()

            # Skip headers, comments, labels, blanks
            if (
                not line
                or line.startswith("#")
                or "SEAS" in line
                or "YEAR" in line
            ):
                continue

            parts = line.split()

            if len(parts) < 3:
                continue

            try:
                year = int(parts[0])
                month = int(parts[1])
                oni = float(parts[2])
                records.append({"year": year, "month": month, "oni": oni})
            except ValueError:
                continue

        if not records:
            return "Neutral", 0.0

        df = pd.DataFrame(records)
        latest = df.iloc[-1]
        oni_value = float(latest["oni"])

        if oni_value >= 0.5:
            return "ElNino", oni_value
        elif oni_value <= -0.5:
            return "LaNina", oni_value
        else:
            return "Neutral", oni_value

    except Exception as e:
        print("Failed to fetch ENSO:", e)
        return "Neutral", 0.0