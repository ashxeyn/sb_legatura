@php
    $hasFilters = request('search') || request('status');
@endphp

@if($hasFilters)
<div class="px-4 py-2.5 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2 text-xs text-indigo-700">
    <i class="fi fi-rr-filter"></i>
    <span>
        Showing <strong>{{ $payments->total() }}</strong> result(s)
        @if(request('search')) for "<strong>{{ request('search') }}</strong>"@endif
        @if(request('status')) with status "<strong>{{ request('status') }}</strong>"@endif
    </span>
    <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="clear-payments-filters ml-auto text-[11px] font-semibold text-indigo-600 hover:underline">Clear filters</a>
</div>
@endif

<div class="overflow-hidden">
    <table class="w-full table-fixed">
        <thead>
            <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[28%]">Contractor</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[26%]">Project Details</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Payment</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Date</th>
                <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Status</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200" id="paymentsTable">
            @forelse($payments as $payment)
            @php
                $words = array_values(array_filter(explode(' ', trim($payment->company_name ?? ''))));
                $initials = strtoupper(substr($words[0] ?? '?', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: '?';
                $palette = ['from-blue-500 to-indigo-600', 'from-gray-700 to-gray-900', 'from-emerald-500 to-teal-600', 'from-fuchsia-500 to-purple-600', 'from-rose-500 to-red-600', 'from-amber-500 to-orange-600', 'from-cyan-500 to-sky-600', 'from-lime-600 to-green-700'];
                $avatarColor = $palette[$payment->payment_id % count($palette)];
                $statusConfig = [
                    'submitted' => ['label' => 'Pending', 'cls' => 'bg-amber-100 text-amber-700 border-amber-200'],
                    'approved' => ['label' => 'Completed', 'cls' => 'bg-green-100 text-green-700 border-green-200'],
                    'rejected' => ['label' => 'Invalid', 'cls' => 'bg-red-100 text-red-700 border-red-200'],
                    'deleted' => ['label' => 'Deleted', 'cls' => 'bg-gray-100 text-gray-500 border-gray-200'],
                ];
                $sc = $statusConfig[$payment->payment_status] ?? ['label' => ucfirst($payment->payment_status), 'cls' => 'bg-gray-100 text-gray-600 border-gray-200'];
                $methodLabels = ['cash' => 'Cash', 'check' => 'Check', 'bank_transfer' => 'Bank', 'online_payment' => 'Online'];
                $methodLabel = $methodLabels[$payment->payment_type] ?? ucfirst(str_replace('_', ' ', $payment->payment_type ?? ''));
                $detailLine = collect([
                    '#' . $payment->payment_id,
                    $payment->transaction_number,
                    $payment->milestone_item_title,
                ])->filter()->implode(' • ');
            @endphp
            <tr class="hover:bg-indigo-50/60 transition-colors duration-200 ease-in-out">
                <td class="px-2.5 py-2.5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $avatarColor }} flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[170px]" title="{{ $payment->company_name ?? 'N/A' }}">{{ $payment->company_name ?? 'N/A' }}</div>
                            <div class="text-[11px] text-gray-500 truncate max-w-[170px]" title="{{ $payment->company_email ?? 'N/A' }}">{{ $payment->company_email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-2.5 py-2.5 text-gray-700 text-xs">
                    <div class="font-medium leading-tight truncate max-w-[220px]" title="{{ $payment->project_title ?? 'N/A' }}">{{ $payment->project_title ?? 'N/A' }}</div>
                    <div class="text-[11px] text-gray-500 truncate max-w-[220px]" title="{{ $detailLine ?: 'N/A' }}">{{ $detailLine ?: 'N/A' }}</div>
                </td>
                <td class="px-2.5 py-2.5 text-gray-700 text-xs min-w-0">
                    <div class="font-semibold whitespace-nowrap">₱{{ number_format($payment->amount, 2) }}</div>
                    <div class="text-[11px] text-gray-500 truncate" title="{{ $methodLabel }}">{{ $methodLabel }}</div>
                </td>
                <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/y') }}</td>
                <td class="px-2.5 py-2.5 text-center">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $sc['cls'] }}">
                        {{ $sc['label'] }}
                    </span>
                </td>
                <td class="px-2.5 py-2.5 whitespace-nowrap">
                    <div class="flex items-center gap-1">
                        <button class="action-btn view-btn btn-view w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" title="View" data-id="{{ $payment->payment_id }}" data-status="{{ $payment->payment_status }}">
                            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                        </button>
                        <button class="action-btn edit-btn btn-edit w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Edit" data-id="{{ $payment->payment_id }}" data-project="{{ $payment->project_title }}" data-method="{{ $payment->payment_type }}" data-amount="{{ $payment->amount }}" data-status="{{ $payment->payment_status }}" data-txn="{{ $payment->transaction_number }}" data-reason="{{ $payment->reason }}">
                            <i class="fi fi-rr-pencil text-[13px] leading-none"></i>
                        </button>
                        <button class="action-btn delete-btn btn-delete w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Delete" data-id="{{ $payment->payment_id }}" data-project="{{ $payment->project_title }}" data-contractor="{{ $payment->company_name }}" data-amount="₱{{ number_format($payment->amount, 2) }}">
                            <i class="fi fi-rr-trash text-[13px] leading-none"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                    <i class="fi fi-sr-document text-3xl block mb-2"></i>
                    <p class="text-base font-medium text-gray-500">No payment proofs found</p>
                    <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($payments->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $payments->firstItem() }}</strong>–<strong>{{ $payments->lastItem() }}</strong>
            of <strong>{{ $payments->total() }}</strong> results
        </p>
        <div class="flex items-center gap-1">
            @if($payments->onFirstPage())
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
            @else
                <a href="{{ $payments->previousPageUrl() }}" class="payment-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
            @endif

            @foreach($payments->getUrlRange(max(1, $payments->currentPage() - 2), min($payments->lastPage(), $payments->currentPage() + 2)) as $page => $url)
                @if($page == $payments->currentPage())
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="payment-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
            @endforeach

            @if($payments->hasMorePages())
                <a href="{{ $payments->nextPageUrl() }}" class="payment-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
            @else
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
            @endif
        </div>
    </div>
    @else
    <div class="px-4 py-3 border-t border-gray-200">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $payments->total() }}</strong> result(s)
        </p>
    </div>
    @endif
</div>