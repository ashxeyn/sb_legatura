OTP verification requirements

- For web/browser flows: keep session cookies; server expects `contractor_step2` in session with `otp_hash`.
- For stateless/mobile clients: include either `company_email` (body or header `X-Company-Email`) or `otp_token` (returned after step2) when calling the verify endpoint.
- Request fields:
  - `otp` (required)
  - `company_email` or `otp_token` (one required if no session cookie)
- Responses:
  - 200: success
  - 400: missing identifier (company_email or otp_token)
  - 422: invalid or expired OTP

Notes:
- TTL is 15 minutes with a 30s grace window.
- Server removes cached/session OTP on successful verification to prevent reuse.
