@forelse($cancelledSubscriptions as $sub)
  @php
    $isBoost = $sub->plan_key === 'boost' || stripos($sub->plan_key, 'boost') !== false;
    $personName = $isBoost ? ($sub->first_name . ' ' . $sub->last_name . ' (' . $sub->project_title . ')') : ($sub->company_name ?? 'Contractor');
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $personName), 0, 2));
    $planName = $sub->plan_name ?? ucwords(str_replace('_', ' ', $sub->plan_key));
  @endphp
  <tr class="hover:bg-indigo-50/60 transition-colors">
    <td class="px-2.5 py-2.5 text-[11px] text-gray-700 font-medium">
      {{ str_pad($sub->platform_payment_id, 4, '0', STR_PAD_LEFT) }}
    </td>
    <td class="px-2.5 py-2.5">
      <div class="flex items-center gap-1.5">
        @if($isBoost && $sub->profile_pic)
          <img src="{{ asset('storage/' . $sub->profile_pic) }}" alt="{{ $personName }}" class="w-7 h-7 rounded-full object-cover shadow flex-shrink-0">
        @elseif(!$isBoost && $sub->company_logo)
          <img src="{{ asset('storage/' . $sub->company_logo) }}" alt="{{ $personName }}" class="w-7 h-7 rounded-full object-cover shadow flex-shrink-0">
        @else
          <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0 uppercase">
            {{ $initials ?: 'U' }}
          </div>
        @endif
        <span class="text-xs font-medium text-gray-800 truncate max-w-[220px]" title="{{ $personName }}">{{ $personName }}</span>
      </div>
    </td>
    <td class="px-2.5 py-2.5">
      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-gray-100 text-gray-700 border-gray-200">
        {{ $planName }}
      </span>
    </td>
    <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">
      {{ \Carbon\Carbon::parse($sub->transaction_date)->format('F j, Y') }}
    </td>
    <td class="px-2.5 py-2.5 whitespace-nowrap">
      <div class="flex items-center gap-1 justify-center">
        <button class="reactivate-subscription-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-emerald-100 hover:shadow-sm hover:border-emerald-300 hover:-translate-y-0.5 transition-all active:scale-95"
          title="Reactivate" data-id="{{ $sub->platform_payment_id }}" data-name="{{ $personName }}">
          <i class="fi fi-rr-undo text-[13px] leading-none"></i>
        </button>
      </div>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
      <i class="fi fi-rr-box-open text-3xl block mb-2"></i>
      <p class="text-base font-medium text-gray-500">No cancelled subscriptions found.</p>
    </td>
  </tr>
@endforelse
