@forelse($contractor->team_members ?? [] as $index => $member)
  <?php
    // Generate color based on index
    $colors = ['purple', 'blue', 'green', 'red', 'yellow', 'pink', 'indigo', 'orange'];
    $color = $colors[$index % count($colors)];

    // Generate initials
    $fname = $member->first_name ?? '';
    $lname = $member->last_name ?? '';
    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));

    // Determine status based on user requirements:
    // - active: (default logic if none of the below match, assumed is_active=1)
    // - pending: is_active = 0 AND deletion_reason is NULL
    // - cancelled: is_active = 0 AND deletion_reason IS NOT NULL
    // - deactivated: is_active = 0 AND is_suspended = 1 AND suspension_until IS NOT NULL AND suspension_reason IS NOT NULL

    if ($member->is_active == 0 && $member->is_suspended == 1 && !empty($member->suspension_until) && !empty($member->suspension_reason)) {
      $statusClass = 'team-member-row hidden';
      $dataStatus = 'deactivated';
    } elseif ($member->is_active == 0 && !empty($member->deletion_reason)) {
      $statusClass = 'team-member-row hidden';
      $dataStatus = 'cancelled';
    } elseif ($member->is_active == 0 && empty($member->deletion_reason)) {
      $statusClass = 'team-member-row hidden';
      $dataStatus = 'pending';
    } else {
      $statusClass = 'team-member-row';
      $dataStatus = 'active';
    }
      ?>
  <tr class="hover:bg-gray-50 transition-all duration-200 group {{ $statusClass }}" data-status="{{ $dataStatus }}"
    data-email="{{ $member->email ?? '' }}" data-contact="">
    <td class="px-6 py-4">
      <div class="flex items-center gap-3 {{ $member->is_suspended ? 'opacity-60' : '' }}">
        <div
          class="w-10 h-10 rounded-full bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
          @if($member->profile_pic)
            <img src="{{ asset('storage/' . $member->profile_pic) }}" alt="{{ $fname . ' ' . $lname }}"
              class="w-full h-full object-cover">
          @else
            <span class="text-white font-bold text-sm">{{ $initials }}</span>
          @endif
        </div>
        <span
          class="font-medium {{ $member->is_suspended ? 'text-gray-600' : 'text-gray-800 group-hover:text-orange-600' }} transition">
          {{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}
        </span>
      </div>
    </td>
    <td class="px-6 py-4 text-center text-sm text-gray-600">
      @php
        $displayRole = ucfirst($member->company_role ?? 'N/A');
        if ($member->company_role === 'others' && !empty($member->role_if_others)) {
          $displayRole = ucfirst($member->role_if_others);
        }
      @endphp
      {{ $displayRole }}
    </td>
    <td class="px-6 py-4 text-center text-sm text-gray-600">
      {{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : 'N/A' }}
    </td>
    <td class="px-6 py-4 text-center status-cell">
      @if($dataStatus == 'deactivated')
        <span
          class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hidden">
          Suspended
        </span>
        <span class="deletion-reason text-sm text-gray-700">{{ $member->suspension_reason ?? 'No reason provided' }}</span>
      @elseif($dataStatus == 'active')
        <span
          class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
          Active
        </span>
      @elseif($dataStatus == 'cancelled')
        <span
          class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 hidden">
          Cancelled Invitation
        </span>
      @else
        <span
          class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 hidden">
          Pending Invitation
        </span>
      @endif
    </td>
    <td class="px-6 py-4">
      <div class="flex items-center justify-center gap-2">
        @if($dataStatus == 'deactivated')
          <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn"
            title="Reactivate Account" data-member-id="{{ $member->staff_id }}"
            data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
        @elseif($dataStatus == 'active')
          <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member"
            data-member-id="{{ $member->staff_id }}">
            <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
          <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn"
            title="Suspend Account" data-member-id="{{ $member->staff_id }}"
            data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
        @elseif($dataStatus == 'pending')
          <button class="team-cancel-invitation-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn"
            title="Cancel Invitation" data-member-id="{{ $member->staff_id }}"
            data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-cross-circle text-red-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
        @elseif($dataStatus == 'cancelled')
          <button class="team-reapply-invitation-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn"
            title="Reapply Invitation" data-member-id="{{ $member->staff_id }}"
            data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-rotate-right text-green-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
        @endif
      </div>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
      No team members found
    </td>
  </tr>
@endforelse