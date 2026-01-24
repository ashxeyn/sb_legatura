@php
  $teamMembers = collect($contractor->team_members ?? [])->filter(function($member) {
    return $member->role !== 'representative' && $member->is_deleted == 0 && $member->is_active == 1;
  });
@endphp

@if($teamMembers->count() > 0)
  @foreach($teamMembers as $member)
    @php
      $initials = strtoupper(substr($member->authorized_rep_fname ?? '', 0, 1) . substr($member->authorized_rep_lname ?? '', 0, 1));
      $fullName = trim(($member->authorized_rep_fname ?? '') . ' ' . ($member->authorized_rep_mname ?? '') . ' ' . ($member->authorized_rep_lname ?? ''));
      $role = $member->role === 'others' ? ($member->if_others ?? 'Staff') : ucfirst($member->role ?? 'Staff');
      $colors = ['from-purple-500 to-purple-600', 'from-blue-500 to-blue-600', 'from-green-500 to-green-600', 'from-red-500 to-red-600', 'from-yellow-500 to-yellow-600'];
      $colorIndex = ord($initials[0]) % count($colors);
    @endphp
    <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group"
         data-member-id="{{ $member->contractor_user_id }}"
         data-member-name="{{ $fullName }}"
         data-member-position="{{ $role }}"
         data-member-phone="{{ $member->phone_number ?? 'N/A' }}">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-gradient-to-br {{ $colors[$colorIndex] }} flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
          {{ $initials }}
        </div>
        <div>
          <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $fullName }}</p>
          <p class="text-sm text-gray-600">{{ $role }} • {{ $member->phone_number ?? 'N/A' }}</p>
        </div>
      </div>
      <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
    </div>
  @endforeach
@else
  <div class="text-center py-6">
    <i class="fi fi-rr-users text-gray-300 text-3xl mb-2"></i>
    <p class="text-gray-500">No team members available</p>
  </div>
@endif
