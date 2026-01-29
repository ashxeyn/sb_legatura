<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A Dispute has been Filed Against You</title>
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
      <h2>A dispute has been filed against you</h2>
      <p>A dispute has been filed against you regarding Project: <strong>{{ $data['project_title'] ?? '-' }}</strong>.</p>

      <hr>
      <h3>Dispute Context</h3>
      <p class="label">Dispute Type</p>
      <p class="value">{{ $data['dispute_type'] ?? '-' }}</p>

      <p class="label">Description</p>
      <p class="value">{{ $data['description'] ?? ($data['dispute_desc'] ?? '-') }}</p>

      <p class="label">Requested Action</p>
      <p class="value">{{ $data['requested_action'] ?? ($data['reason'] ?? 'Please resubmit milestone proofs') }}</p>
    </div>

    <p style="font-size:12px;color:#6b7280">This is an automated message from Legatura.</p>
  </body>
</html>
