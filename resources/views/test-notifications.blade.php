<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Test Panel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 2rem; }
        h1 { color: #f8fafc; font-size: 1.6rem; margin-bottom: 0.25rem; }
        .subtitle { color: #94a3b8; font-size: 0.875rem; margin-bottom: 2rem; }
        .panel { background: #1e293b; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid #334155; }
        .panel h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; margin-bottom: 1rem; }
        .time-row { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
        .field { display: flex; flex-direction: column; gap: 0.35rem; flex: 1; min-width: 220px; }
        label { font-size: 0.8rem; color: #94a3b8; }
        input[type="datetime-local"] {
            background: #0f172a; border: 1px solid #475569; border-radius: 8px;
            color: #f1f5f9; padding: 0.6rem 0.9rem; font-size: 0.9rem; width: 100%;
        }
        input[type="datetime-local"]:focus { outline: none; border-color: #6366f1; }
        .hint { font-size: 0.75rem; color: #64748b; }
        .btn-clear {
            background: #334155; border: none; border-radius: 8px; color: #94a3b8;
            padding: 0.6rem 1rem; cursor: pointer; font-size: 0.85rem; white-space: nowrap;
        }
        .btn-clear:hover { background: #475569; color: #f1f5f9; }
        .btn-danger {
            background: #450a0a; border: 1px solid #7f1d1d; border-radius: 8px; color: #fca5a5;
            padding: 0.6rem 1.1rem; cursor: pointer; font-size: 0.85rem; white-space: nowrap; font-weight: 600;
        }
        .btn-danger:hover { background: #7f1d1d; color: #fff; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 0.75rem; }
        .check-btn {
            background: #1e3a5f; border: 1px solid #2563eb44; border-radius: 10px;
            padding: 0.9rem 1rem; cursor: pointer; text-align: left; transition: all 0.15s;
            display: flex; flex-direction: column; gap: 0.3rem;
        }
        .check-btn:hover { background: #1d4ed8; border-color: #60a5fa; transform: translateY(-1px); }
        .check-btn.all { background: #312e81; border-color: #7c3aed44; }
        .check-btn.all:hover { background: #4f46e5; border-color: #a78bfa; }
        .check-btn .icon { font-size: 1.2rem; }
        .check-btn .label { font-size: 0.9rem; font-weight: 600; color: #e2e8f0; }
        .check-btn .windows { font-size: 0.72rem; color: #7dd3fc; }
        .check-btn.loading { opacity: 0.6; pointer-events: none; }

        #result {
            background: #0f172a; border: 1px solid #334155; border-radius: 10px;
            padding: 1rem 1.25rem; font-size: 0.875rem; min-height: 60px;
            line-height: 1.6; display: none;
        }
        #result.visible { display: block; }
        #result.success { border-color: #16a34a44; }
        #result.error { border-color: #dc262644; }
        .tag { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .tag.ok { background: #14532d; color: #86efac; }
        .tag.err { background: #450a0a; color: #fca5a5; }
        .time-used { color: #facc15; font-size: 0.8rem; margin-top: 0.35rem; }
        .msg { margin-top: 0.35rem; color: #cbd5e1; }
    </style>
</head>
<body>

<h1>🔔 Notification Test Panel</h1>
<p class="subtitle">Runs server-side notification checks with a fake time. Refreshing this page resets everything.</p>

<div class="panel">
    <h2>Fake Time (optional)</h2>
    <div class="time-row">
        <div class="field">
            <label for="fake_now">Set system time to:</label>
            <input type="datetime-local" id="fake_now" />
            <span class="hint">Leave blank to use real server time.</span>
        </div>
        <button class="btn-clear" onclick="document.getElementById('fake_now').value=''">✕ Clear</button>
    </div>
</div>

<div class="panel" style="border-color: #7f1d1d44;">
    <h2>⚠️ Dedup Control</h2>
    <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:1rem;">
        Most notifications fire only once per item (or once per day). If a check ran before, the dedup key blocks it from sending again.
        Use this to reset so checks can fire again during testing.
    </p>
    <button class="btn-danger" onclick="clearDedup()">🗑 Clear All Dedup Keys</button>
</div>

<div class="panel">
    <h2>Checks</h2>
    <div class="grid">
        <button class="check-btn all" onclick="run('all')">
            <span class="icon">🚀</span>
            <span class="label">Run ALL Checks</span>
            <span class="windows">Runs every check at once</span>
        </button>

        @foreach($checks as $key => $label)
            @if($key !== 'all')
            <button class="check-btn" onclick="run('{{ $key }}')">
                <span class="icon">
                    @if(str_contains($key, 'Bidding')) 🏷️
                    @elseif(str_contains($key, 'Milestone')) 📋
                    @elseif(str_contains($key, 'Overdue') && str_contains($key, 'Settlement')) ⚠️
                    @elseif(str_contains($key, 'Overdue')) 🔴
                    @elseif(str_contains($key, 'Payment')) 💳
                    @elseif(str_contains($key, 'Settlement')) 🗓️
                    @elseif(str_contains($key, 'Dispute')) ⚖️
                    @elseif(str_contains($key, 'Subscription')) 🎟️
                    @elseif(str_contains($key, 'Boost')) 🚀
                    @else 🔔
                    @endif
                </span>
                <span class="label">{{ $label }}</span>
            </button>
            @endif
        @endforeach
    </div>
</div>

<div class="panel">
    <h2>Result</h2>
    <div id="result"></div>
</div>

<script>
    // Pre-fill with current local datetime
    (function() {
        const now = new Date();
        now.setSeconds(0, 0);
        const pad = n => String(n).padStart(2, '0');
        const val = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        document.getElementById('fake_now').value = val;
    })();

    async function run(checkKey) {
        const fakeNow = document.getElementById('fake_now').value;
        const resultEl = document.getElementById('result');

        // Disable all buttons while running
        document.querySelectorAll('.check-btn').forEach(b => b.classList.add('loading'));
        resultEl.className = 'visible';
        resultEl.innerHTML = '<span style="color:#94a3b8">⏳ Running...</span>';

        try {
            const resp = await fetch('{{ route("test.notifications.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ fake_now: fakeNow || null, check: checkKey })
            });
            const data = await resp.json();

            if (data.success) {
                resultEl.className = 'visible success';
                resultEl.innerHTML = `
                    <span class="tag ok">✓ Success</span>
                    <div class="time-used">🕐 ${data.time_used}</div>
                    <div class="msg">${data.message}</div>
                    ${data.logs ? '<div class="msg" style="margin-top:0.5rem">' + data.logs.join('<br>') + '</div>' : ''}
                `;
            } else {
                resultEl.className = 'visible error';
                resultEl.innerHTML = `
                    <span class="tag err">✕ Error</span>
                    ${data.time_used ? '<div class="time-used">🕐 ' + data.time_used + '</div>' : ''}
                    <div class="msg">${data.message}</div>
                `;
            }
        } catch (e) {
            resultEl.className = 'visible error';
            resultEl.innerHTML = `<span class="tag err">✕ Network error</span><div class="msg">${e.message}</div>`;
        }

        document.querySelectorAll('.check-btn').forEach(b => b.classList.remove('loading'));
    }

    async function clearDedup() {
        const resultEl = document.getElementById('result');
        resultEl.className = 'visible';
        resultEl.innerHTML = '<span style="color:#94a3b8">⏳ Clearing...</span>';

        try {
            const resp = await fetch('{{ route("test.notifications.clear-dedup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await resp.json();
            resultEl.className = 'visible success';
            resultEl.innerHTML = `<span class="tag ok">✓ Done</span><div class="msg">${data.message}</div>`;
        } catch (e) {
            resultEl.className = 'visible error';
            resultEl.innerHTML = `<span class="tag err">✕ Error</span><div class="msg">${e.message}</div>`;
        }
    }
</script>
</body>
</html>
