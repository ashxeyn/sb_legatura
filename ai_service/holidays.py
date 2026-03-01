# holidays.py
import requests
from datetime import datetime, timedelta

HOLIDAY_API_KEY = "6be9709b-a505-4dd9-8771-1918240451c8"

def fetch_ph_holidays(year: int):
    url = url = f"https://holidayapi.com/v1/holidays?key={HOLIDAY_API_KEY}&country=PH&year={year}"

    try:
        r = requests.get(url, timeout=10)
        r.raise_for_status()
        data = r.json()

        holidays = []
        for h in data.get("holidays", []):
            holidays.append(
                datetime.strptime(h["date"], "%Y-%m-%d").date()
            )

        return holidays

    except Exception as e:
        print("Holiday API failed:", e)
        return []


def compute_holiday_impact(start_date, remaining_days):
    if not start_date or not remaining_days:
        return 0.0

    end_date = start_date + timedelta(days=remaining_days)

    holidays = (
        fetch_ph_holidays(start_date.year)
        + fetch_ph_holidays(end_date.year)
    )

    lost_days = sum(
        1 for h in holidays if start_date <= h <= end_date
    )

    impact_pct = min(lost_days / max(1, remaining_days), 1.0)

    return round(impact_pct, 3)