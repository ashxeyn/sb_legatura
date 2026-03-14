@forelse($activeSubscriptions as $sub)
  @php
    $isBoost = $sub->plan_key === 'boost' || stripos($sub->plan_key, 'boost') !== false;
    $personName = $isBoost ? ($sub->first_name . ' ' . $sub->last_name . ' (' . $sub->project_title . ')') : ($sub->company_name ?? 'Contractor');
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $personName), 0, 2));
    $planName = $sub->plan_name ?? ucwords(str_replace('_', ' ', $sub->plan_key));
    $badgeColor = 'bg-blue-100 text-blue-700 border-blue-200';
    if (stripos($planName, 'gold') !== false) $badgeColor = 'bg-yellow-100 text-yellow-700 border-yellow-200';
    elseif (stripos($planName, 'silver') !== false) $badgeColor = 'bg-gray-100 text-gray-700 border-gray-200';
    elseif (stripos($planName, 'bronze') !== false) $badgeColor = 'bg-orange-100 text-orange-700 border-orange-200';
  @endphp
  <tr class="hover:bg-indigo-50/60 transition-colors">
    <td class="px-2.5 py-2.5 text-[11px] text-gray-700 font-medium">
      {{ str_pad($sub->platform_payment_id, 4, '0', STR_PAD_LEFT) }}
    </td>
    <td class="px-2.5 py-2.5">
      <div class="flex items-center gap-1.5">
        <div class="w-7 h-7 rounded-full {{ $isBoost ? 'bg-gradient-to-br from-emerald-500 to-green-600' : 'bg-gradient-to-br from-indigo-500 to-blue-600' }} flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0 uppercase">
          {{ $initials ?: 'U' }}
        </div>
        <span class="text-xs font-medium text-gray-800 truncate max-w-[200px]" title="{{ $personName }}">{{ $personName }}</span>
      </div>
    </td>
    <td class="px-2.5 py-2.5">
      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeColor }}">
        {{ $planName }}
      </span>
    </td>
    <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">
      {{ \Carbon\Carbon::parse($sub->transaction_date)->format('F j, Y') }}
    </td>
    <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">
      {{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('F j, Y') : 'N/A' }}
    </td>
    <td class="px-2.5 py-2.5 whitespace-nowrap">
      <div class="flex items-center gap-1 justify-center">
        <button class="view-subscription-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95"
          title="View Details" data-id="{{ $sub->platform_payment_id }}"
          data-user="{{ $isBoost ? ($sub->first_name . ' ' . $sub->last_name) : ($sub->company_name ?? 'Contractor') }}"
          data-project="{{ $isBoost ? ($sub->project_title ?? '') : '' }}" data-plan="{{ $planName }}"
          data-plan-key="{{ $sub->plan_key }}" data-amount="{{ number_format($sub->amount, 2) }}"
          data-date="{{ \Carbon\Carbon::parse($sub->transaction_date)->format('F j, Y') }}"
          data-expiry="{{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('F j, Y') : 'N/A' }}"
          data-type="{{ $isBoost ? 'Boost' : 'Subscription' }}"
          data-billing="{{ $sub->billing_cycle ?? 'N/A' }}"
          data-txn="{{ $sub->transaction_number ?? 'N/A' }}"
          data-duration="{{ $sub->duration_days ?? '' }}" data-status="active">
          <i class="fi fi-rr-eye text-[13px] leading-none"></i>
        </button>
        <button class="deactivate-subscription-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95"
          title="Deactivate" data-id="{{ $sub->platform_payment_id }}" data-name="{{ $personName }}">
          <i class="fi fi-rr-ban text-[13px] leading-none"></i>
        </button>
      </div>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
      <i class="fi fi-rr-box-open text-3xl block mb-2"></i>
      <p class="text-base font-medium text-gray-500">No active subscriptions found.</p>
    </td>
  </tr>
@endforelse
