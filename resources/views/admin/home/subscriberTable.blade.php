{{-- resources/views/admin/home/partials/subscriberTable.blade.php
     Used by: subscriptionAnalytics.blade.php (initial render)
     Also returned as HTML by getSubscribersJson when X-Requested-With is set
--}}

@php
  $avatarColors = [
    ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700'],
    ['bg' => 'bg-violet-100',  'text' => 'text-violet-700'],
    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
    ['bg' => 'bg-amber-100',   'text' => 'text-amber-700'],
    ['bg' => 'bg-rose-100',    'text' => 'text-rose-700'],
    ['bg' => 'bg-cyan-100',    'text' => 'text-cyan-700'],
    ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-700'],
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
  <div class="py-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
      <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <p class="text-sm text-gray-400 font-medium">No subscribers match your filters</p>
    <p class="text-xs text-gray-300 mt-1">Try adjusting the search or clearing filters</p>
  </div>
@else
  <div class="overflow-x-auto">
    <table class="w-full text-sm sa-sub-table" id="subscriberTable">
      <thead>
        <tr class="border-b border-gray-100 bg-gray-50">
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Subscriber</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Plan</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Subscribed</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Expires</th>
          <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">TXN #</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @foreach($subscribers as $i => $sub)
          @php
            $ac     = $avatarColors[$i % count($avatarColors)];
            $badge  = $tierBadge[$sub->plan_key]              ?? 'tier-other';
            $st     = $statusBadge[$sub->subscription_status] ?? $statusBadge['cancelled'];
            $expCar = $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date) : null;
          @endphp
          <tr class="sub-row" data-id="{{ $sub->platform_payment_id }}">

            {{-- Subscriber identity --}}
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                @if($sub->avatar)
                  <img src="{{ asset('storage/' . $sub->avatar) }}"
                       alt="{{ $sub->subscriber_name }}"
                       class="w-7 h-7 rounded-full object-cover ring-1 ring-gray-100 shrink-0">
                @else
                  <div class="w-7 h-7 rounded-full {{ $ac['bg'] }} {{ $ac['text'] }} flex items-center justify-center text-[10px] font-bold shrink-0">
                    {{ $sub->initials }}
                  </div>
                @endif
                <div class="min-w-0">
                  <div class="font-semibold text-gray-800 leading-tight truncate max-w-[160px] text-xs">
                    {{ $sub->subscriber_name }}
                  </div>
                  @if($sub->subscriber_email)
                    <div class="text-[10px] text-gray-400 truncate max-w-[160px]">{{ $sub->subscriber_email }}</div>
                  @endif
                  <span class="inline-flex items-center px-1.5 py-px rounded text-[10px] font-semibold
                    {{ $sub->subscriber_type === 'Contractor' ? 'bg-indigo-50 text-indigo-600' : 'bg-teal-50 text-teal-700' }}">
                    {{ $sub->subscriber_type }}
                  </span>
                </div>
              </div>
            </td>

            {{-- Plan --}}
            <td class="px-3 py-2">
              <span class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold {{ $badge }}">
                {{ ucfirst($sub->plan_key) }}
              </span>
              <div class="text-[10px] text-gray-400 capitalize mt-0.5">{{ $sub->billing_cycle }}</div>
            </td>

            {{-- Amount --}}
            <td class="px-3 py-2">
              <div class="font-bold text-gray-800 text-xs">₱{{ number_format($sub->amount, 2) }}</div>
              <div class="text-[10px] text-gray-400">{{ $sub->payment_type ?? '' }}</div>
            </td>

            {{-- Status --}}
            <td class="px-3 py-2">
              <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full text-[10px] font-semibold {{ $st['class'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }} inline-block shrink-0"></span>
                {{ $st['label'] }}
              </span>
              @if($sub->subscription_status === 'active' && $expCar && !$expCar->isPast() && $expCar->diffInDays(now()) <= 7)
                <div class="text-[10px] text-amber-600 font-medium mt-0.5">⚠ Expiring soon</div>
              @endif
            </td>

            {{-- Subscribed --}}
            <td class="px-3 py-2">
              <div class="text-[11px] text-gray-800 font-medium">
                {{ \Carbon\Carbon::parse($sub->transaction_date)->format('M j, Y') }}
              </div>
              <div class="text-[10px] text-gray-400">
                {{ \Carbon\Carbon::parse($sub->transaction_date)->diffForHumans() }}
              </div>
            </td>

            {{-- Expires --}}
            <td class="px-3 py-2">
              @if($expCar)
                <div class="text-[11px] font-medium {{ $expCar->isPast() ? 'text-red-500' : 'text-gray-800' }}">
                  {{ $expCar->format('M j, Y') }}
                </div>
                <div class="text-[10px] {{ $expCar->isPast() ? 'text-red-400' : 'text-gray-400' }}">
                  {{ $expCar->isPast() ? 'Expired ' : 'Expires ' }}{{ $expCar->diffForHumans() }}
                </div>
              @else
                <span class="text-[10px] text-gray-300 italic">No expiry</span>
              @endif
            </td>

            {{-- TXN --}}
            <td class="px-3 py-2">
              <div class="text-[10px] font-mono text-gray-400 max-w-[120px] truncate" title="{{ $sub->transaction_number }}">
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
    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100" id="paginationRow">
      <div class="text-xs text-gray-500">
        Showing <span class="font-semibold text-gray-700">{{ $subscribers->firstItem() }}</span>
        &ndash; <span class="font-semibold text-gray-700">{{ $subscribers->lastItem() }}</span>
        of <span class="font-semibold text-gray-700">{{ $subscribers->total() }}</span>
      </div>
      <div class="flex items-center gap-1 flex-wrap">
        {{-- Prev --}}
        @if($subscribers->onFirstPage())
          <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 select-none">← Prev</span>
        @else
          <button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
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
          <button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="1">1</button>
          @if($start > 2)<span class="px-1.5 text-gray-300 text-xs">…</span>@endif
        @endif

        @for($p = $start; $p <= $end; $p++)
          @if($p === $cur)
            <span class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white">{{ $p }}</span>
          @else
            <button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="{{ $p }}">{{ $p }}</button>
          @endif
        @endfor

        @if($end < $last)
          @if($end < $last - 1)<span class="px-1.5 text-gray-300 text-xs">…</span>@endif
          <button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="{{ $last }}">{{ $last }}</button>
        @endif

        {{-- Next --}}
        @if($subscribers->hasMorePages())
          <button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  data-page="{{ $subscribers->currentPage() + 1 }}">Next →</button>
        @else
          <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 select-none">Next →</span>
        @endif
      </div>
    </div>
  @endif
@endif
