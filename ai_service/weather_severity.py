def compute_weather_severity(
    rain_events,
    flooding,
    work_suspension_hours,
    low_visibility,
    storm_warning,
):
    severity = 0

    if rain_events >= 4:
        severity += 3
    elif rain_events >= 2:
        severity += 2
    elif rain_events >= 1:
        severity += 1

    if flooding:
        severity += 4

    if work_suspension_hours >= 6:
        severity += 4
    elif work_suspension_hours >= 3:
        severity += 2
    elif work_suspension_hours >= 1:
        severity += 1

    if low_visibility:
        severity += 1

    if storm_warning:
        severity += 2

    return severity