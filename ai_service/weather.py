import requests
from datetime import datetime

API_KEY = "2a5164eb9d184652817162655261202"

def get_weather(location: str):
    # Use Forecast API for "Yesterday/Today/Tomorrow" simulation
    url = "http://api.weatherapi.com/v1/forecast.json"
    params = {
        "key": API_KEY,
        "q": location,
        "days": 3,
        "aqi": "no",
        "alerts": "no"
    }

    try:
        r = requests.get(url, params=params, timeout=5)
        if r.status_code != 200: return default_weather()
            
        data = r.json()
        current = data["current"]
        forecast_days = data["forecast"]["forecastday"]

        forecast_list = []
        for day in forecast_days:
            date_obj = datetime.strptime(day["date"], "%Y-%m-%d")
            forecast_list.append({
                "date": date_obj.strftime("%a, %b %d"),
                "temp_avg": day["day"]["avgtemp_c"],
                "condition": day["day"]["condition"]["text"],
                "icon": "https:" + day["day"]["condition"]["icon"],
                "rain_chance": day["day"]["daily_chance_of_rain"]
            })

        return {
            "avg_temp": current["temp_c"],
            "avg_humidity": current["humidity"],
            "avg_wind": current["wind_kph"],
            "total_rain": current["precip_mm"],
            "condition_text": current["condition"]["text"],
            "condition_icon": "https:" + current["condition"]["icon"],
            "forecast": forecast_list
        }
    except Exception:
        return default_weather()

def default_weather():
    return {
        "avg_temp": 30.0, "avg_humidity": 70, "avg_wind": 10.0, "total_rain": 0.0,
        "condition_text": "Unavailable", "condition_icon": "", "forecast": []
    }