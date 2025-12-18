<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Dispute Submission has been Verified</title>
    <style>
      body { font-family: Arial, sans-serif; color: #1f2937; }
      .header { background: linear-gradient(90deg,#f59e0b,#f97316); padding: 24px; color: #fff; }
      .card { background: #ffffff; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
      .label { font-size: 12px; color: #6b7280; }
      .value { font-weight: 600; color: #111827; }
    </style>
  </head>
  <body>
    <div class="header">
      <h1>Legatura</h1>
    </div>

    <div class="card">
      <h2>Your dispute submission has been verified</h2>
      <p>Check out for updates in your project now.</p>

      <hr>
      <h3>Dispute Details</h3>
      <p class="label">Subject</p>
      <p class="value">{{ $data['title'] ?? ($data['subject'] ?? '-') }}</p>

      <p class="label">Description</p>
      <p class="value">{{ $data['description'] ?? ($data['dispute_desc'] ?? '-') }}</p>

      <p class="label">Requested Action</p>
      <p class="value">{{ $data['requested_action'] ?? ($data['reason'] ?? '-') }}</p>
    </div>

    <p style="font-size:12px;color:#6b7280">This is an automated message from Legatura.</p>
  </body>
</html>
