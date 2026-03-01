<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Management - Legatura</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    {{-- Same JS as other admin pages so nav toggle works --}}
    <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

    <style>
        .ts-control {
            border-radius: 0.5rem !important;
            padding: 0.625rem !important;
            background-color: #f9fafb !important;
            border: 1px solid #e5e7eb !important;
        }
        .ts-wrapper.focus .ts-control {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5) !important;
            border-color: #3b82f6 !important;
        }
        .modal-scroll::-webkit-scrollbar { width: 8px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

    {{-- ===================== SIDEBAR — identical structure to bidManagement.blade.php ===================== --}}
    <aside class="bg-white shadow-xl flex flex-col">

        <div class="flex justify-center items-center">
            <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">

            <div class="nav-group">
                <button class="nav-btn">
                    <div class="flex items-center gap-3">
                        <i class="fi fi-ss-home" style="font-size: 20px;"></i>
                        <span>Home</span>
                    </div>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-submenu">
                    <a href="{{ route('admin.dashboard') }}" class="submenu-link">Dashboard</a>
                    <div class="submenu-nested">
                        <button class="submenu-link submenu-nested-btn">
                            <span>Analytics</span>
                            <span class="arrow-small">▼</span>
                        </button>
                        <div class="submenu-nested-content">
                            <a href="{{ route('admin.analytics') }}" class="submenu-nested-link">Project Analytics</a>
                            <a href="{{ route('admin.analytics.subscription') }}" class="submenu-nested-link">Subscription Analytics</a>
                            <a href="{{ route('admin.analytics.userActivity') }}" class="submenu-nested-link">User Activity Analytics</a>
                            <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project Performance Analytics</a>
                            <a href="{{ route('admin.analytics.bidCompletion') }}" class="submenu-nested-link">Bid Completion Analytics</a>
                            <a href="{{ route('admin.analytics.reports') }}" class="submenu-nested-link">Reports and Analytics</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-group">
                <button class="nav-btn">
                    <div class="flex items-center gap-3">
                        <i class="fi fi-ss-users-alt" style="font-size: 20px;"></i>
                        <span>User Management</span>
                    </div>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-submenu">
                    <a href="{{ route('admin.userManagement.propertyOwner') }}" class="submenu-link">Property Owner</a>
                    <a href="{{ route('admin.userManagement.contractor') }}" class="submenu-link">Contractor</a>
                    <a href="{{ route('admin.userManagement.verificationRequest') }}" class="submenu-link">Verification Request</a>
                    <a href="{{ route('admin.userManagement.suspendedAccounts') }}" class="submenu-link">Suspended Accounts</a>
                </div>
            </div>

            {{-- Global Management — "AI Management" marked active --}}
            <div class="nav-group">
                <button class="nav-btn">
                    <div class="flex items-center gap-3">
                        <i class="fi fi-ss-globe" style="font-size: 20px;"></i>
                        <span>Global Management</span>
                    </div>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-submenu">
                    <a href="{{ route('admin.globalManagement.bidManagement') }}" class="submenu-link">Bid Management</a>
                    <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="submenu-link">Proof of Payments</a>
                    <a href="{{ route('admin.globalManagement.aiManagement') }}" class="submenu-link active">AI Management</a>
                    <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting Management</a>
                </div>
            </div>

            <div class="nav-group">
                <button class="nav-btn">
                    <div class="flex items-center gap-3">
                        <i class="fi fi-sr-master-plan" style="font-size: 20px;"></i>
                        <span>Project Management</span>
                    </div>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-submenu">
                    <a href="{{ route('admin.projectManagement.listOfProjects') }}" class="submenu-link">List of Projects</a>
                    <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link">Disputes/Reports</a>
                    <a href="{{ route('admin.projectManagement.messages') }}" class="submenu-link">Messages</a>
                    <a href="{{ route('admin.projectManagement.subscriptions') }}" class="submenu-link">Subscriptions & Boosts</a>
                </div>
            </div>

            <div class="nav-group">
                <button class="nav-btn">
                    <div class="flex items-center gap-3">
                        <i class="fi fi-br-settings-sliders" style="font-size: 20px;"></i>
                        <span>Settings</span>
                    </div>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-submenu">
                    <a href="{{ route('admin.settings.notifications') }}" class="submenu-link">Notifications</a>
                    <a href="{{ route('admin.settings.security') }}" class="submenu-link">Security</a>
                </div>
            </div>

        </nav>

        <div class="mt-auto p-4">
            <div class="user-card flex items-center gap-3 p-3 rounded-lg shadow-md text-white">
                <div class="w-10 h-10 rounded-full bg-white text-indigo-900 flex items-center justify-center font-bold shadow flex-shrink-0">
                    ES
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">Emmanuelle Santos</div>
                    <div class="text-xs opacity-80 truncate">santos@Legatura.com</div>
                </div>
                <div class="relative">
                    <button id="userMenuBtn" class="text-white opacity-80 hover:opacity-100 transition text-2xl w-8 h-8 flex items-center justify-center rounded-full">⋮</button>
                    <div id="userMenuDropdown" class="absolute right-0 bottom-full mb-2 w-44 bg-white text-gray-800 rounded-xl shadow-2xl border border-gray-200 hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-sm font-semibold truncate">Emmanuelle Santos</div>
                            <div class="text-xs text-gray-500 truncate">santos@Legatura.com</div>
                        </div>
                        <ul class="py-1">
                            <li>
                                <a href="{{ route('admin.settings.security') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">
                                    <i class="fi fi-br-settings-sliders"></i>
                                    <span>Account settings</span>
                                </a>
                            </li>
                            <li>
                                <button id="logoutBtn" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                                    <i class="fi fi-ss-exit"></i>
                                    <span>Logout</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </aside>
    {{-- ===================== END SIDEBAR ===================== --}}

    {{-- ===================== MAIN CONTENT ===================== --}}
    <main class="flex-1">

        <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">AI Management</h1>
                <p class="text-gray-400 text-sm">Predictive Analytics & Risk Assessment Console</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 rounded-full flex items-center gap-2
                    {{ ($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <span class="w-3 h-3 rounded-full animate-pulse
                        {{ ($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="font-bold text-sm">System: {{ $aiUsage['status'] ?? 'Offline' }}</span>
                </div>

                <div class="relative">
                    <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                        <i class="fi fi-ss-bell-notification-social-media" style="font-size: 20px;"></i>
                    </button>
                    <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">Notifications</span>
                            <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
                        </div>
                        <ul class="max-h-80 overflow-y-auto" id="notificationList">
                            <li class="px-4 py-3 hover:bg-gray-50 transition">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center">
                                        <i class="fi fi-ss-exclamation"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-800 truncate">High-risk flag: Duplex Housing requires review.</p>
                                        <p class="text-xs text-gray-500">Yesterday</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div class="px-4 py-3 border-t border-gray-100">
                            <a href="{{ route('admin.settings.notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Notification settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-8">

            @if(!empty($aiUsage['features']))
            <div class="flex flex-wrap gap-2">
                @foreach($aiUsage['features'] as $feature)
                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">{{ $feature }}</span>
                @endforeach
            </div>
            @endif

            {{-- Run New Analysis --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center gap-2">
                    <i class="fi fi-br-search-alt text-blue-600"></i> Run New Analysis
                </h3>
                <div class="flex gap-4 items-end">
                    <div class="flex-1 max-w-md">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Select Project</label>
                        <select id="projectSelect" name="project_id" placeholder="Start typing project name..." autocomplete="off" class="w-full">
                            <option value=""></option>
                            @foreach($projects as $project)
                                <option value="{{ $project->project_id }}">
                                    {{ $project->project_id }} - {{ $project->project_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="runAnalysis()" id="btnAnalyze"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-2.5 rounded-lg transition-all flex items-center gap-2 shadow-lg shadow-blue-200">
                        Analyze Now
                    </button>
                </div>
                <div id="analysisResult" class="hidden mt-6 p-6 bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-xl">
                    <div id="resultContent"></div>
                </div>
            </div>

            {{-- Prediction History Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Prediction History Logs</h3>
                    <span class="text-xs text-gray-400">Latest 10 records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold">
                            <tr>
                                <th class="px-6 py-4">Analyzed</th>
                                <th class="px-6 py-4">Project</th>
                                <th class="px-6 py-4">Verdict</th>
                                <th class="px-6 py-4">Risk Probability</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($predictionLogs as $log)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $log->project_title }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-black border
                                        {{ $log->prediction === 'DELAYED'
                                            ? 'bg-red-50 text-red-700 border-red-100'
                                            : 'bg-green-50 text-green-700 border-green-100' }}">
                                        {{ $log->prediction }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-gray-600">
                                    {{ number_format($log->delay_probability * 100, 1) }}%
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        onclick="showDetails({{ json_encode(json_decode($log->ai_response_snapshot), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }})"
                                        class="text-blue-600 hover:text-blue-800 font-bold text-xs underline underline-offset-4 transition">
                                        View Full Audit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No analysis history found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($predictionLogs, 'links'))
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $predictionLogs->links() }}
                </div>
                @endif
            </div>

        </div>
    </main>
    {{-- ===================== END MAIN CONTENT ===================== --}}

</div>

{{-- ===================== DETAILS MODAL ===================== --}}
<div id="detailsModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden relative z-10">
            <div class="bg-gray-50 px-8 py-5 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">AI Strategic Project Audit</h3>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mt-1">Project Analysis</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition p-2 hover:bg-red-50 rounded-full">
                    <i class="fi fi-br-cross"></i>
                </button>
            </div>
            <div class="p-8 overflow-y-auto modal-scroll bg-white" id="modalBody"></div>
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button onclick="closeModal()" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition">Close</button>
                <button onclick="window.print()" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fi fi-rr-print"></i> Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        new TomSelect("#projectSelect", {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: "Search for a project...",
            maxOptions: 10
        });
    });

    async function runAnalysis() {
        const projectId = document.getElementById('projectSelect').value;
        const btn        = document.getElementById('btnAnalyze');
        const resultBox  = document.getElementById('analysisResult');
        const contentBox = document.getElementById('resultContent');

        if (!projectId) { alert('Please select a project first.'); return; }

        btn.disabled    = true;
        btn.innerHTML   = '<span class="animate-spin inline-block mr-2">⟳</span> Analyzing...';
        resultBox.classList.remove('hidden');
        contentBox.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-blue-600">
                <div class="animate-spin h-8 w-8 border-4 border-blue-600 border-t-transparent rounded-full mb-3"></div>
                <p class="font-bold animate-pulse">Running Random Forest Model...</p>
                <p class="text-xs text-blue-400">Fetching Weather, Milestone Data & Contractor History</p>
            </div>`;

        try {
            const response = await fetch(`/admin/global-management/ai-management/analyze/${projectId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const res = await response.json();

            if (res.success) {
                const data = res.data;
                contentBox.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-gray-900 font-bold text-lg mb-2">Analysis Complete</h4>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 border-l-4 border-blue-500 pl-3">
                                ${data.analysis_report.conclusion || 'Analysis successful.'}
                            </p>
                            <div class="flex gap-6">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase">Verdict</span>
                                    <p class="text-2xl font-black ${data.prediction.prediction === 'DELAYED' ? 'text-red-600' : 'text-green-600'}">
                                        ${data.prediction.prediction}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase">Confidence</span>
                                    <p class="text-2xl font-black text-gray-800">
                                        ${(data.prediction.delay_probability * 100).toFixed(1)}%
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button onclick="location.reload()"
                            class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition shadow-lg">
                            Save to Logs
                        </button>
                    </div>`;
            } else { throw new Error(res.message); }
        } catch (err) {
            contentBox.innerHTML = `
                <div class="bg-red-50 text-red-700 p-4 rounded-lg flex items-center gap-3">
                    <i class="fi fi-rr-exclamation"></i>
                    <div><p class="font-bold">Analysis Failed</p><p class="text-sm">${err.message}</p></div>
                </div>`;
        } finally {
            btn.disabled  = false;
            btn.innerHTML = 'Analyze Now';
        }
    }

    function closeModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    function showDetails(data) {
        const modal = document.getElementById('detailsModal');
        const body  = document.getElementById('modalBody');
        const d     = (typeof data === 'string') ? JSON.parse(data) : data;

        modal.classList.remove('hidden');

        const isDelayed  = d.prediction.prediction === 'DELAYED';
        const riskColor  = isDelayed ? 'text-red-600'   : 'text-green-600';
        const riskBorder = isDelayed ? 'border-red-100' : 'border-green-100';
        const contractor = d.analysis_report.contractor_audit;
        const isFlagged  = contractor.flagged || (contractor.status && contractor.status.includes('Flagged'));

        const details = d.analysis_report.pacing_status.details;
        let pacingRows = '';
        if (details && details.length > 0) {
            pacingRows = details.map(item => {
                const isRejected = item.status === 'rejected';
                const isLate     = item.days_variance > 0;
                return `
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                        <td class="py-3 px-4 font-medium text-gray-700">${item.title}</td>
                        <td class="py-3 px-4 text-xs font-bold uppercase ${isRejected ? 'text-red-600' : 'text-gray-500'}">${item.status}</td>
                        <td class="py-3 px-4 font-mono text-sm ${isLate ? 'text-amber-600' : 'text-green-600'}">
                            ${item.days_variance > 0 ? '+' + item.days_variance : item.days_variance} days
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs font-bold
                                ${isRejected ? 'bg-red-100 text-red-700' : isLate ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'}">
                                ${item.pacing_label}
                            </span>
                        </td>
                    </tr>`;
            }).join('');
        } else {
            pacingRows = '<tr><td colspan="4" class="text-center py-4 text-gray-400 italic">No milestone data available yet.</td></tr>';
        }

        const recs     = d.dds_recommendations || [];
        const recItems = recs.length > 0
            ? recs.map(rec => `
                <div class="flex gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="flex-shrink-0 mt-1">
                        ${rec.includes('QUALITY') ? '<i class="fi fi-rr-shield-exclamation text-red-500 text-xl"></i>'
                          : rec.includes('WEATHER') || rec.includes('RAIN') ? '<i class="fi fi-rr-cloud-disabled text-orange-500 text-xl"></i>'
                          : '<i class="fi fi-rr-bulb text-purple-500 text-xl"></i>'}
                    </div>
                    <p class="text-gray-700 text-sm font-medium leading-relaxed">${rec}</p>
                </div>`).join('')
            : '<p class="text-gray-400 italic text-sm">No recommendations generated.</p>';

        const dots = [1,2,3,4,5].map(i =>
            `<div class="w-2 h-2 rounded-full ${i <= d.weather_severity ? 'bg-orange-500' : 'bg-gray-200'}"></div>`
        ).join('');

        body.innerHTML = `
            <div class="mb-8 bg-blue-50 border-l-4 border-blue-600 p-6 rounded-r-lg">
                <h4 class="text-blue-900 font-bold text-sm uppercase tracking-wider mb-2">Executive Summary</h4>
                <p class="text-blue-800 text-lg leading-relaxed font-medium">"${d.analysis_report.conclusion}"</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 space-y-6">

                    <div class="bg-white border ${riskBorder} rounded-xl p-5 shadow-sm">
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Risk Assessment</h5>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-3xl font-black ${riskColor}">${d.prediction.prediction}</span>
                            <div class="text-right">
                                <span class="block text-sm text-gray-500">Probability</span>
                                <span class="text-xl font-bold text-gray-900">${(d.prediction.delay_probability * 100).toFixed(1)}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full ${isDelayed ? 'bg-red-500' : 'bg-green-500'}"
                                 style="width:${(d.prediction.delay_probability * 100)}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 italic">${d.prediction.reason || ''}</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden">
                        ${isFlagged ? '<div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-bl">FLAGGED</div>' : ''}
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Contractor Vetting</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Experience</span>
                                <span class="font-bold text-gray-900">${contractor.experience}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Success Rate</span>
                                <span class="font-bold ${isFlagged ? 'text-red-600' : 'text-green-600'}">${contractor.historical_success}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Audit Status</span>
                                <span class="font-bold text-xs uppercase px-2 py-0.5 rounded
                                    ${isFlagged ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50'}">
                                    ${contractor.status || (isFlagged ? 'High Risk' : 'Good Standing')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Environment Context</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <i class="fi fi-rr-cloud-showers-heavy text-blue-500"></i>
                                <p class="text-xs text-gray-500 mt-1">Rainfall</p>
                                <p class="font-bold text-gray-800">${d.weather.total_rain}mm</p>
                            </div>
                            <div class="text-center p-2 bg-gray-50 rounded">
                                <i class="fi fi-rr-temperature-high text-orange-500"></i>
                                <p class="text-xs text-gray-500 mt-1">ENSO</p>
                                <p class="font-bold text-gray-800">${d.enso_state}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs text-gray-500">Weather Severity</span>
                            <div class="flex gap-1">${dots}</div>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-2 space-y-8">

                    <div>
                        <h5 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                            Milestone Pacing Audit
                        </h5>
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Milestone</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Variance</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Verdict</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">${pacingRows}</tbody>
                            </table>
                            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 text-right">
                                <span class="text-xs font-bold text-gray-500">Average Pacing: </span>
                                <span class="text-sm font-bold ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'text-red-600' : 'text-green-600'}">
                                    ${d.analysis_report.pacing_status.avg_delay_days} days
                                    ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'behind' : 'ahead'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h5 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                            AI Strategic Recommendations
                        </h5>
                        <div class="space-y-3">${recItems}</div>
                    </div>

                </div>
            </div>`;
    }
</script>

</body>
</html>