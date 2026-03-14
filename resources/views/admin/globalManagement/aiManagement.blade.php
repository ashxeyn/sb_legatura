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
        .modal-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .modal-scroll::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

    {{-- ===================== SIDEBAR — identical structure to bidManagement.blade.php ===================== --}}
        @include('admin.layouts.sidebar')
    {{-- ===================== END SIDEBAR ===================== --}}

    {{-- ===================== MAIN CONTENT ===================== --}}
    <main class="flex-1">

              @php
                $aiStatusBadge = '<div class="px-3 py-1.5 rounded-full flex items-center gap-1.5 '
            . (($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')
                        . '"><span class="w-2.5 h-2.5 rounded-full animate-pulse '
            . (($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-500' : 'bg-red-500')
                        . '"></span><span class="font-semibold text-xs">System: ' . e($aiUsage['status'] ?? 'Offline') . '</span></div>';
      @endphp
      @include('admin.layouts.topnav', [
          'pageTitle' => 'AI Management',
          'pageSubtitle' => 'Predictive Analytics & Risk Assessment Console',
          'hideSearch' => true,
          'beforeNotifications' => $aiStatusBadge,
      ])

        <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 space-y-4">

            @if(!empty($aiUsage['features']))
            <div class="flex flex-wrap gap-1.5">
                @foreach($aiUsage['features'] as $feature)
                    <span class="bg-blue-100 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-full">{{ $feature }}</span>
                @endforeach
            </div>
            @endif

            {{-- Run New Analysis --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-xl shadow-md border border-blue-400/30 p-6 relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-y-1/2 translate-x-1/2"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-white text-base mb-1">Run New Analysis</h3>
                            <p class="text-blue-50 text-sm">Execute a comprehensive delay risk assessment</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-blue-50 uppercase mb-2 tracking-wide">Select Project</label>
                            <select id="projectSelect" name="project_id" placeholder="Start typing project name..." autocomplete="off" class="w-full px-4 py-2.5 bg-white text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 font-medium text-sm border border-blue-200">
                                <option value=""></option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}">
                                        {{ $project->project_id }} - {{ $project->project_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button onclick="runAnalysis()" id="btnAnalyze"
                            class="bg-white text-blue-500 text-sm font-semibold px-6 py-2.5 rounded-lg transition-all flex items-center gap-2 shadow-md hover:shadow-lg hover:bg-blue-50 active:scale-95 duration-200 whitespace-nowrap">
                            <i class="fi fi-br-play text-xs"></i>
                            Analyze Now
                        </button>
                    </div>
                    <div id="analysisResult" class="hidden mt-4 p-4 bg-white/15 backdrop-blur-sm border border-white/25 rounded-lg">
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>

            {{-- Prediction History Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-sm text-gray-800">Prediction History Logs</h3>
                        <p class="text-xs text-gray-500 mt-1">Analysis records and project risk assessments</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg">Latest 10</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-[11px]">Analyzed</th>
                                <th class="px-4 py-3 text-[11px]">Project</th>
                                <th class="px-4 py-3 text-[11px]">Verdict</th>
                                <th class="px-4 py-3 text-[11px]">Probability</th>
                                <th class="px-4 py-3 text-center text-[11px]">Confidence</th>
                                <th class="px-4 py-3 text-right text-[11px]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($predictionLogs as $log)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-gray-600 font-medium">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $log->project_title }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border
                                        {{ $log->prediction === 'DELAYED'
                                            ? 'bg-red-50 text-red-700 border-red-200'
                                            : 'bg-green-50 text-green-700 border-green-200' }}">
                                        {{ $log->prediction }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full {{ $log->prediction === 'DELAYED' ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $log->delay_probability * 100 }}%"></div>
                                        </div>
                                        <span class="font-mono font-bold text-gray-700 min-w-[40px]">{{ number_format($log->delay_probability * 100, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <div class="w-1.5 h-1.5 rounded-full {{ $i <= round($log->delay_probability * 5) ? ($log->prediction === 'DELAYED' ? 'bg-red-500' : 'bg-green-500') : 'bg-gray-300' }}"></div>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        onclick="showDetails({{ json_encode(json_decode($log->ai_response_snapshot), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-gray-100 text-gray-600 hover:bg-gray-200 hover:border-gray-400 hover:text-gray-700 transition-all active:scale-95"
                                        title="View Details">
                                        <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <p class="text-gray-400 text-sm">No analysis history found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($predictionLogs, 'links'))
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
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
    <div class="flex items-center justify-center min-h-screen p-2">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] flex flex-col overflow-hidden relative z-10">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-3 sm:px-5 py-4 border-b border-gray-100 flex justify-between items-start">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white">
                            <i class="fi fi-rr-chart-histogram text-sm"></i>
                        </div>
                        AI Strategic Project Audit
                    </h3>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mt-1">Comprehensive Risk Analysis Report</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition p-1.5 hover:bg-red-50 rounded-full flex-shrink-0">
                    <i class="fi fi-br-cross text-lg"></i>
                </button>
            </div>
            <div class="p-3 sm:p-4 overflow-y-auto modal-scroll bg-white" id="modalBody"></div>
            <div class="bg-gradient-to-r from-gray-50 to-white px-3 sm:px-4 py-3 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-[11px] font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">Close</button>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white text-[11px] font-semibold rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center gap-2 hover:-translate-y-0.5 active:scale-95">
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
        btn.innerHTML   = '<span class="animate-spin inline-block mr-2 text-sm">⟳</span> Analyzing...';
        resultBox.classList.remove('hidden');
        contentBox.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-blue-600">
                <div class="animate-spin h-10 w-10 border-4 border-blue-600 border-t-transparent rounded-full mb-4"></div>
                <p class="text-sm font-bold animate-pulse">Running Random Forest Model...</p>
                <p class="text-xs text-blue-400 mt-1">Fetching Weather, Milestone Data & Contractor History</p>
                <div class="mt-4 text-xs text-blue-500 space-y-1">
                    <p>✓ Loading project data</p>
                    <p>✓ Computing risk factors</p>
                    <p>Processing environment variables...</p>
                </div>
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
                    <div class="space-y-3">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-100 rounded-xl p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                                    <i class="fi fi-rr-check-circle"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-gray-900 font-bold text-base">Analysis Complete</h4>
                                    <p class="text-gray-600 text-xs mt-1">${data.analysis_report.conclusion || 'Analysis successful.'}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 pt-3 border-t border-green-200">
                                <div class="text-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase block mb-1">Verdict</span>
                                    <p class="text-lg font-black ${data.prediction.prediction === 'DELAYED' ? 'text-red-600' : 'text-green-600'}">
                                        ${data.prediction.prediction}
                                    </p>
                                </div>
                                <div class="text-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase block mb-1">Risk Level</span>
                                    <p class="text-lg font-black text-gray-800">
                                        ${(data.prediction.delay_probability * 100).toFixed(1)}%
                                    </p>
                                </div>
                                <div class="text-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase block mb-1">Status</span>
                                    <p class="text-lg font-black text-blue-600">Active</p>
                                </div>
                            </div>
                        </div>
                        <button onclick="location.reload()"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fi fi-rr-disk"></i>
                            Save to Logs
                        </button>
                    </div>`;
            } else { throw new Error(res.message); }
        } catch (err) {
            contentBox.innerHTML = `
                <div class="bg-red-50 border border-red-100 text-red-700 p-4 rounded-lg flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <i class="fi fi-rr-exclamation text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-sm">Analysis Failed</p>
                        <p class="text-xs mt-0.5 text-red-600">${err.message}</p>
                    </div>
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
                        <td class="py-2 px-3 text-xs font-medium text-gray-700">${item.title}</td>
                        <td class="py-2 px-3 text-[11px] font-bold uppercase ${isRejected ? 'text-red-600' : 'text-gray-500'}">${item.status}</td>
                        <td class="py-2 px-3 font-mono text-xs ${isLate ? 'text-amber-600' : 'text-green-600'}">
                            ${item.days_variance > 0 ? '+' + item.days_variance : item.days_variance} days
                        </td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold
                                ${isRejected ? 'bg-red-100 text-red-700' : isLate ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'}">
                                ${item.pacing_label}
                            </span>
                        </td>
                    </tr>`;
            }).join('');
        } else {
            pacingRows = '<tr><td colspan="4" class="text-center py-3 text-xs text-gray-400 italic">No milestone data available yet.</td></tr>';
        }

        const recs     = d.dds_recommendations || [];
        const recItems = recs.length > 0
            ? recs.map(rec => `
                <div class="flex gap-3 bg-white p-3 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="flex-shrink-0 mt-1">
                        ${rec.includes('QUALITY') ? '<i class="fi fi-rr-shield-exclamation text-red-500 text-lg"></i>'
                          : rec.includes('WEATHER') || rec.includes('RAIN') ? '<i class="fi fi-rr-cloud-disabled text-orange-500 text-lg"></i>'
                          : '<i class="fi fi-rr-bulb text-purple-500 text-lg"></i>'}
                    </div>
                    <p class="text-gray-700 text-xs font-medium leading-relaxed">${rec}</p>
                </div>`).join('')
            : '<p class="text-gray-400 italic text-sm">No recommendations generated.</p>';

        const dots = [1,2,3,4,5].map(i =>
            `<div class="w-2 h-2 rounded-full ${i <= d.weather_severity ? 'bg-orange-500' : 'bg-gray-200'}"></div>`
        ).join('');

        body.innerHTML = `
            <div class="mb-4 bg-blue-50 border-l-4 border-blue-600 p-4 rounded-r-lg">
                <h4 class="text-blue-900 font-bold text-xs uppercase tracking-wider mb-1.5">Executive Summary</h4>
                <p class="text-blue-800 text-sm leading-relaxed font-medium">"${d.analysis_report.conclusion}"</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-1 space-y-4">

                    <div class="bg-white border ${riskBorder} rounded-xl p-3.5 shadow-sm">
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2.5">Risk Assessment</h5>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl font-black ${riskColor}">${d.prediction.prediction}</span>
                            <div class="text-right">
                                <span class="block text-xs text-gray-500">Probability</span>
                                <span class="text-lg font-bold text-gray-900">${(d.prediction.delay_probability * 100).toFixed(1)}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full ${isDelayed ? 'bg-red-500' : 'bg-green-500'}"
                                 style="width:${(d.prediction.delay_probability * 100)}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 italic">${d.prediction.reason || ''}</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-3.5 shadow-sm relative overflow-hidden">
                        ${isFlagged ? '<div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-bl">FLAGGED</div>' : ''}
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2.5">Contractor Vetting</h5>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Experience</span>
                                <span class="text-xs font-bold text-gray-900">${contractor.experience}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Success Rate</span>
                                <span class="text-xs font-bold ${isFlagged ? 'text-red-600' : 'text-green-600'}">${contractor.historical_success}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600">Audit Status</span>
                                <span class="font-bold text-xs uppercase px-2 py-0.5 rounded
                                    ${isFlagged ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50'}">
                                    ${contractor.status || (isFlagged ? 'High Risk' : 'Good Standing')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-3.5 shadow-sm">
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2.5">Environment Context</h5>
                        <div class="grid grid-cols-2 gap-2.5">
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
                        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs text-gray-500">Weather Severity</span>
                            <div class="flex gap-1">${dots}</div>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-2 space-y-4">

                    <div>
                        <h5 class="text-xs sm:text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <span class="w-2 h-5 bg-blue-600 rounded-full"></span>
                            Milestone Pacing Audit
                        </h5>
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-[11px] font-bold text-gray-500 uppercase">Milestone</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-gray-500 uppercase">Status</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-gray-500 uppercase">Variance</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-gray-500 uppercase">Verdict</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs">${pacingRows}</tbody>
                            </table>
                            <div class="bg-gray-50 px-3 py-2 border-t border-gray-200 text-right">
                                <span class="text-xs font-bold text-gray-500">Average Pacing: </span>
                                <span class="text-xs font-bold ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'text-red-600' : 'text-green-600'}">
                                    ${d.analysis_report.pacing_status.avg_delay_days} days
                                    ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'behind' : 'ahead'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h5 class="text-xs sm:text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <span class="w-2 h-5 bg-purple-600 rounded-full"></span>
                            AI Strategic Recommendations
                        </h5>
                        <div class="space-y-2">${recItems}</div>
                    </div>

                </div>
            </div>`;
    }
</script>

</body>
</html>