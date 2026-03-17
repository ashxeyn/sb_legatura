import os


def _clamp(value: float, low: float, high: float) -> float:
	return max(low, min(value, high))


def apply_risk_adjustments(
	base_prob: float,
	contractor_exp: int,
	contractor_success: float,
	rejected_count: int,
	dispute_count: int,
):
	"""
	Apply bounded, explainable policy adjustments on top of model output.

	Returns:
		final_prob (float): adjusted probability in [0.01, 0.99]
		adjustment_applied (float): net increase applied after capping
		notes (list[str]): human-readable reasons
	"""
	max_adjustment = float(os.getenv("RISK_POLICY_MAX_ADJUSTMENT", "0.25"))
	max_adjustment = _clamp(max_adjustment, 0.0, 0.5)

	raw_adjustment = 0.0
	notes = []

	# Experience trap: very experienced contractor but weak historical outcome.
	if contractor_exp >= 10 and contractor_success < 0.50:
		raw_adjustment += 0.12
		notes.append("experience trap")

	# Quality/rework pressure from rejected milestone items.
	if rejected_count > 0:
		rework_adj = min(0.08 + (0.03 * max(0, rejected_count - 1)), 0.18)
		raw_adjustment += rework_adj
		notes.append(f"rework risk ({rejected_count} rejected)")

	# Escalating dispute risk.
	if dispute_count > 0:
		dispute_adj = min(0.05 * dispute_count, 0.15)
		raw_adjustment += dispute_adj
		notes.append(f"dispute risk ({dispute_count} active)")

	applied_adjustment = min(raw_adjustment, max_adjustment)
	final_prob = _clamp(float(base_prob) + applied_adjustment, 0.01, 0.99)

	return final_prob, round(applied_adjustment, 4), notes
