<table class="w-full">
  <thead>
    <tr class="bg-gray-50 border-b border-gray-200">
      <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Registered</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Suspension Until</th>
      <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Projects</th>
      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200">
    @if($suspendedOwners && $suspendedOwners->count() > 0)
      @foreach($suspendedOwners as $owner)
        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                {{ strtoupper(substr($owner->name ?? 'P', 0, 1)) }}
              </div>
              <span class="font-medium text-gray-900">{{ $owner->name ?? 'N/A' }}</span>
            </div>
          </td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $owner->email ?? 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ $owner->date_registered ? \Carbon\Carbon::parse($owner->date_registered)->format('d M, Y') : 'N/A' }}</div></td>
          <td class="px-6 py-4 text-center"><div class="text-sm text-red-600 font-medium">{{ $owner->suspension_until ? \Carbon\Carbon::parse($owner->suspension_until)->format('d M, Y') : 'N/A' }}</div></td>
          <td class="px-6 py-4"><div class="text-sm text-gray-700">{{ Str::limit($owner->reason ?? 'No reason provided', 50) }}</div></td>
          <td class="px-6 py-4 text-center"><span class="text-sm text-gray-700">{{ $owner->total_projects ?? 0 }}</span></td>
          <td class="px-6 py-4">
            <div class="flex items-center justify-center gap-2">
              <button class="reactivate-btn p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                      data-id="{{ $owner->owner_id }}"
                      data-user-type="property_owner"
                      data-name="{{ $owner->name }}"
                      title="Reactivate">
                <i class="fi fi-rr-refresh"></i>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td colspan="7" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <i class="fi fi-rr-users text-5xl mb-3"></i>
            <p class="text-lg font-medium">No suspended property owners found</p>
            <p class="text-sm mt-1">All property owner accounts are currently active</p>
          </div>
        </td>
      </tr>
    @endif
  </tbody>
</table>
