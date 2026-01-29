@foreach($disputes as $dispute)
<tr class="hover:bg-gray-50 transition-colors duration-150"
  data-id="{{ $dispute->dispute_id }}"
  data-status="{{ $dispute->status }}"
  data-date="{{ $dispute->created_at }}"
  data-reporter="{{ trim(($reporterFirst ?? '') . ' ' . ($reporterLast ?? '')) }}"
  data-type="{{ $dispute->dispute_type }}"
  data-project="{{ $dispute->project_title ?? '' }}"
  data-subject="{{ e($dispute->subject ?? '') }}"
  data-description="{{ e($dispute->subject ?? '') }}">
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $dispute->dispute_id }}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow overflow-hidden">
        @php
          $reporterFirst = $dispute->reporter_first_name ?? $dispute->first_name ?? null;
          $reporterLast = $dispute->reporter_last_name ?? $dispute->last_name ?? null;
          $reporterPic = $dispute->reporter_profile_pic ?? $dispute->profile_pic ?? null;
          if (empty($reporterFirst) && !empty($dispute->complainant_id)) {
              $u = \Illuminate\Support\Facades\DB::table('users')->where('user_id', $dispute->complainant_id)->first();
              if ($u) {
                  $reporterFirst = $u->first_name ?? $reporterFirst;
                  $reporterLast = $u->last_name ?? $reporterLast;
                  $reporterPic = $u->profile_pic ?? $reporterPic;
              }
          }
        @endphp
        @if(!empty($reporterPic))
          <img src="{{ asset('storage/' . $reporterPic) }}" alt="avatar" class="w-full h-full object-cover">
        @else
          {{ strtoupper(substr($reporterFirst ?? '', 0, 1) . substr($reporterLast ?? '', 0, 1)) }}
        @endif
      </div>
      <div class="min-w-0">
        <div class="text-sm font-medium text-gray-800 truncate">{{ ($reporterFirst ?? '') }} {{ ($reporterLast ?? '') }}</div>
        <div class="text-xs text-gray-500 truncate">{{ $dispute->project_title ?? '' }}</div>
      </div>
    </div>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ ucfirst($dispute->dispute_type) }}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $dispute->subject }}</td>
  <td class="px-6 py-4 whitespace-nowrap text-sm">
    @php
      $status = $dispute->status;
      $badgeClass = 'bg-gray-100 text-gray-700';
      // Mapping: Yellow for under review, original (gray) for open, red for cancelled/escalated, green for resolved
      if ($status === 'under_review' || strpos($status, 'under') !== false || strpos($status, 'review') !== false) { $badgeClass = 'bg-amber-100 text-amber-700'; }
      if ($status === 'open') { $badgeClass = 'bg-gray-100 text-gray-700'; }
      if (in_array($status, ['cancelled','rejected','escalated'])) { $badgeClass = 'bg-red-100 text-red-700'; }
      if ($status === 'resolved') { $badgeClass = 'bg-emerald-100 text-emerald-700'; }
    @endphp
    <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold {{ $badgeClass }}">{{ ucfirst(str_replace('_',' ',$status)) }}</span>
  </td>
  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 
    \Carbon\Carbon::parse($dispute->created_at)->format('d M, Y')
  }}</td>
  <td class="px-6 py-4 whitespace-nowrap text-center">
    <div class="inline-flex gap-2">
      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-id="{{ $dispute->dispute_id }}" title="View">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
      </button>
      <!-- <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-emerald-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 resolve-btn" data-id="{{ $dispute->dispute_id }}" title="Resolve">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"></path></svg>
      </button> -->
    </div>
  </td>
</tr>
@endforeach

@if($disputes->isEmpty())
<tr>
  <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No disputes found.</td>
</tr>
@endif
