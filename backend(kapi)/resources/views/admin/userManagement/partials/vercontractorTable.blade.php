            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Owner Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Registered</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                @foreach($contractorRequests as $request)
                  <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group">
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center text-white font-semibold shadow-md flex-shrink-0">
                          {{ strtoupper(substr($request->company_name ?? 'C', 0, 2)) }}
                        </div>
                        <span class="font-medium text-gray-900">
                            {{ $request->company_name }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="text-sm text-gray-900 font-medium">
                            {{ $request->authorized_rep_fname ? $request->authorized_rep_fname . ' ' . $request->authorized_rep_lname : $request->username }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-left"><div class="text-sm text-gray-600">{{ $request->email }}</div></td>
                    <td class="px-6 py-4 text-center"><div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($request->request_date)->format('d M, Y') }}</div></td>
                    <td class="px-6 py-4">
                      <div class="flex items-center justify-center gap-2">
                        <button class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition vr-view-btn" data-key="{{ $request->user_id }}" title="View">
                          <i class="fi fi-rr-eye"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $contractorRequests->appends(request()->query())->links() }}
            </div>
