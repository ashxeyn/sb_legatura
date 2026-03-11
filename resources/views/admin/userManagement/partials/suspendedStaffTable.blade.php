<table class="w-full">
  <thead>
    <tr class="bg-gray-50 border-b border-gray-200">
      <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Company</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Registered</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Suspension Until</th>
      <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200">
    @if($suspendedStaff && $suspendedStaff->count() > 0)
      @foreach($suspendedStaff as $staff)
        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                {{ strtoupper(substr($staff->name ?? 'S', 0, 1)) }}
              </div>
              <span class="font-medium text-gray-900">{{ $staff->name ?? 'N/A' }}</span>
            </div>
          </td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $staff->email ?? 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $staff->company_name ?? 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $staff->role ?? 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $staff->date_registered ? \Carbon\Carbon::parse($staff->date_registered)->format('d M, Y') : 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-red-600 font-medium">{{ $staff->suspension_until ? \Carbon\Carbon::parse($staff->suspension_until)->format('d M, Y') : 'N/A' }}</div></td>
          <td class="px-6 py-4"><div class="text-sm text-gray-700">{{ Str::limit($staff->reason ?? 'No reason provided', 50) }}</div></td>
          <td class="px-6 py-4">
            <div class="flex items-center justify-center gap-2">
              <button class="reactivate-btn p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                      data-id="{{ $staff->staff_id }}"
                      data-user-type="staff"
                      data-name="{{ $staff->name }}"
                      title="Reactivate">
                <i class="fi fi-rr-refresh"></i>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td colspan="8" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <i class="fi fi-rr-users text-5xl mb-3"></i>
            <p class="text-lg font-medium">No suspended staff found</p>
            <p class="text-sm mt-1">All staff members are currently active</p>
          </div>
        </td>
      </tr>
    @endif
  </tbody>
</table>
