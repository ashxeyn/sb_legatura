{{-- resources/views/admin/home/partials/subscriberTable.blade.php
     Used by: subscriptionAnalytics.blade.php (initial render)
     Also returned as HTML by getSubscribersJson when X-Requested-With is set
--}}

@php
  $gradients = [
    'from-indigo-400 to-indigo-600',
    'from-violet-400 to-violet-600',
    'from-emerald-400 to-emerald-600',
    'from-amber-400 to-amber-600',
    'from-rose-400 to-rose-600',
    'from-cyan-400 to-cyan-600',
    'from-fuchsia-400 to-fuchsia-600',
  ];

  $tierBadge = [
    'gold'   => 'tier-gold',
    'silver' => 'tier-silver',
    'bronze' => 'tier-bronze',
    'boost'  => 'tier-boost',
  ];

  $statusBadge = [
    'active'    => ['class' => 'status-active',    'dot' => 'bg-emerald-500', 'label' => 'Active'],
    'expired'   => ['class' => 'status-expired',   'dot' => 'bg-red-500',     'label' => 'Expired'],
    'pending'   => ['class' => 'status-pending',   'dot' => 'bg-yellow-500',  'label' => 'Pending'],
    'cancelled' => ['class' => 'status-cancelled', 'dot' => 'bg-gray-400',    'label' => 'Cancelled'],
  ];
@endphp

@if($subscribers->isEmpty())
  <div class="py-20 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <p class="text-gray-400 font-medium">No subscribers match your filters</p>
    <p class="text-gray-300 text-sm mt-1">Try adjusting the search or clearing filters</p>
  </div>
@else
  <div class="overflow-x-auto">
    <table class="w-full text-sm" id="subscriberTable">
      <thead>
        <tr class="border-b-2 border-gray-100">
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Subscriber</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Plan</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Subscribed</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Expires</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">TXN #</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @foreach($subscribers as $i => $sub)
          @php
            $grad  = $gradients[$i % count($gradients)];
            $badge = $tierBadge[$sub->plan_key]               ?? 'tier-other';
            $st    = $statusBadge[$sub->subscription_status]  ?? $statusBadge['cancelled'];
            $expCar = $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date) : null;
          @endphp
          <tr class="sub-row" data-id="{{ $sub->platform_payment_id }}">

            {{-- Subscriber identity --}}
            <td class="px-4 py-4">
              <div class="flex items-center gap-3">
                @if($sub->avatar)
                  <img src="{{ asset('storage/' . $sub->avatar) }}"
                       alt="{{ $sub->subscriber_name }}"
                       class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow shrink-0">
                @else
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm font-bold shadow ring-2 ring-white shrink-0">
                    {{ $sub->initials }}
                  </div>
                @endif
                <div class="min-w-0">
                  <div class="font-semibold text-gray-800 leading-tight truncate max-w-[180px]">
                    {{ $sub->subscriber_name }}
                  </div>
                  @if($sub->rep_name && trim($sub->rep_name) !== ' ')
                    <div class="text-xs text-gray-400 leading-tight truncate max-w-[180px]">
                      {{ $sub->rep_name }}
                    </div>
                  @endif
                  @if($sub->subscriber_email)
                    <div class="text-xs text-gray-300 truncate max-w-[180px]">{{ $sub->subscriber_email }}</div>
                  @endif
                  <span class="inline-flex items-center mt-0.5 px-1.5 py-px rounded text-[10px] font-semibold
                    {{ $sub->subscriber_type === 'Contractor' ? 'bg-indigo-50 text-indigo-600' : 'bg-teal-50 text-teal-700' }}">
                    {{ $sub->subscriber_type }}
                  </span>
                </div>
              </div>
            </td>

            {{-- Plan --}}
            <td class="px-4 py-4">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                {{ ucfirst($sub->plan_key) }}
              </span>
              <div class="text-xs text-gray-400 mt-1 leading-tight">{{ $sub->plan_name }}</div>
              <div class="text-xs text-gray-300 capitalize">{{ $sub->billing_cycle }}</div>
            </td>

            {{-- Amount --}}
            <td class="px-4 py-4">
              <div class="font-bold text-gray-800">₱{{ number_format($sub->amount, 2) }}</div>
              <div class="text-xs text-gray-400">{{ $sub->payment_type ?? '' }}</div>
            </td>

            {{-- Status --}}
            <td class="px-4 py-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $st['class'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }} inline-block shrink-0"></span>
                {{ $st['label'] }}
              </span>
              @if($sub->subscription_status === 'active' && $expCar && !$expCar->isPast() && $expCar->diffInDays(now()) <= 7)
                <div class="text-xs text-amber-600 font-medium mt-1 flex items-center gap-1">
                  <span>⚠</span> Expiring soon
                </div>
              @endif
              @if($sub->deactivation_reason)
                <div class="text-xs text-gray-400 mt-1 max-w-[130px] truncate" title="{{ $sub->deactivation_reason }}">
                  {{ Str::limit($sub->deactivation_reason, 25) }}
                </div>
              @endif
            </td>

            {{-- Subscribed --}}
            <td class="px-4 py-4">
              <div class="text-gray-800 font-medium">
                {{ \Carbon\Carbon::parse($sub->transaction_date)->format('M j, Y') }}
              </div>
              <div class="text-xs text-gray-400">
                {{ \Carbon\Carbon::parse($sub->transaction_date)->diffForHumans() }}
              </div>
            </td>

            {{-- Expires --}}
            <td class="px-4 py-4">
              @if($expCar)
                <div class="font-medium {{ $expCar->isPast() ? 'text-red-500' : 'text-gray-800' }}">
                  {{ $expCar->format('M j, Y') }}
                </div>
                <div class="text-xs {{ $expCar->isPast() ? 'text-red-400' : 'text-gray-400' }}">
                  {{ $expCar->isPast() ? 'Expired ' : 'Expires ' }}{{ $expCar->diffForHumans() }}
                </div>
              @else
                <span class="text-xs text-gray-300 italic">No expiry</span>
              @endif
            </td>

            {{-- TXN --}}
            <td class="px-4 py-4">
              <div class="text-xs font-mono text-gray-400 max-w-[130px] truncate" title="{{ $sub->transaction_number }}">
                {{ $sub->transaction_number ?? '—' }}
              </div>
            </td>

          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($subscribers->lastPage() > 1)
    <div class="mt-6 flex items-center justify-between px-1" id="paginationRow">
      <div class="text-sm text-gray-500">
        Showing <span class="font-semibold text-gray-700">{{ $subscribers->firstItem() }}</span>
        – <span class="font-semibold text-gray-700">{{ $subscribers->lastItem() }}</span>
        of <span class="font-semibold text-gray-700">{{ $subscribers->total() }}</span>
      </div>
      <div class="flex items-center gap-1 flex-wrap">
        {{-- Prev --}}
        @if($subscribers->onFirstPage())
          <span class="px-3 py-2 rounded-lg text-sm text-gray-300 select-none">← Prev</span>
        @else
          <button class="page-btn px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  data-page="{{ $subscribers->currentPage() - 1 }}">← Prev</button>
        @endif

        {{-- Page numbers --}}
        @php
          $cur   = $subscribers->currentPage();
          $last  = $subscribers->lastPage();
          $start = max(1, $cur - 2);
          $end   = min($last, $cur + 2);
        @endphp

        @if($start > 1)
          <button class="page-btn px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="1">1</button>
          @if($start > 2)<span class="px-2 text-gray-300 text-sm">…</span>@endif
        @endif

        @for($p = $start; $p <= $end; $p++)
          @if($p === $cur)
            <span class="px-3 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white">{{ $p }}</span>
          @else
            <button class="page-btn px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="{{ $p }}">{{ $p }}</button>
          @endif
        @endfor

        @if($end < $last)
          @if($end < $last - 1)<span class="px-2 text-gray-300 text-sm">…</span>@endif
          <button class="page-btn px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="{{ $last }}">{{ $last }}</button>
        @endif

        {{-- Next --}}
        @if($subscribers->hasMorePages())
          <button class="page-btn px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  data-page="{{ $subscribers->currentPage() + 1 }}">Next →</button>
        @else
          <span class="px-3 py-2 rounded-lg text-sm text-gray-300 select-none">Next →</span>
        @endif
      </div>
    </div>
  @endif
@endif