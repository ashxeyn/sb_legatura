@forelse($contractor->team_members ?? [] as $index => $member)
  <?php
    // Generate color based on index
    $colors = ['purple', 'blue', 'green', 'red', 'yellow', 'pink', 'indigo', 'orange'];
    $color = $colors[$index % count($colors)];

    // Generate initials
    $fname = $member->authorized_rep_fname ?? '';
    $lname = $member->authorized_rep_lname ?? '';
    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));

    // Determine status visibility
    $statusClass = $member->is_active ? 'team-member-row' : 'team-member-row hidden';
    $dataStatus = $member->is_active ? 'active' : 'deactivated';
  ?>
  <tr class="hover:bg-gray-50 transition-all duration-200 group {{ $statusClass }}"
      data-status="{{ $dataStatus }}"
      data-email="{{ $member->email ?? '' }}"
      data-contact="{{ $member->phone_number ?? '' }}">
    <td class="px-6 py-4">
      <div class="flex items-center gap-3 {{ !$member->is_active ? 'opacity-60' : '' }}">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
          @if($member->profile_pic)
            <img src="{{ asset($member->profile_pic) }}" alt="{{ $fname . ' ' . $lname }}" class="w-full h-full object-cover">
          @else
            <span class="text-white font-bold text-sm">{{ $initials }}</span>
          @endif
        </div>
        <span class="font-medium {{ $member->is_active ? 'text-gray-800 group-hover:text-orange-600' : 'text-gray-600' }} transition">
          {{ $fname . ' ' . ($member->authorized_rep_mname ?? '') . ' ' . $lname }}
        </span>
      </div>
    </td>
    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ ucfirst($member->role ?? 'N/A') }}</td>
    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : 'N/A' }}</td>
    <td class="px-6 py-4 text-center status-cell">
      @if($member->is_active)
        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
          Active
        </span>
      @else
        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hidden">
          Deactivated
        </span>
        <span class="deletion-reason text-sm text-gray-700">{{ $member->deletion_reason ?? 'No reason provided' }}</span>
      @endif
    </td>
    <td class="px-6 py-4">
      <div class="flex items-center justify-center gap-2">
        @if($member->is_active)
          <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member" data-member-id="{{ $member->contractor_user_id }}">
            <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
          <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account"
                  data-member-id="{{ $member->contractor_user_id }}"
                  data-member-name="{{ $fname . ' ' . ($member->authorized_rep_mname ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
          </button>
        @else
          <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reactivate Account"
                  data-member-id="{{ $member->contractor_user_id }}"
                  data-member-name="{{ $fname . ' ' . ($member->authorized_rep_mname ?? '') . ' ' . $lname }}">
            <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
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
