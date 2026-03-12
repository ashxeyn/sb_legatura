@foreach($postings as $post)
<tr class="hover:bg-gray-50 transition-colors duration-150">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow overflow-hidden">
                @if($post->profile_pic)
                    <img src="{{ asset('storage/' . $post->profile_pic) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    {{ substr($post->first_name, 0, 1) . substr($post->last_name, 0, 1) }}
                @endif
            </div>
            <div>
                <div class="font-semibold text-gray-800">{{ $post->first_name }} {{ $post->last_name }}</div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-700">{{ $post->project_title }}</td>
    <td class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($post->created_at)->format('d M, Y') }}</td>
    <td class="px-6 py-4">
        @php
            $statusColors = [
                'under_review' => 'bg-yellow-100 text-yellow-800',
                'approved' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800',
                'deleted' => 'bg-gray-100 text-gray-800',
                'due' => 'bg-orange-100 text-orange-800',
            ];
            $statusLabel = [
                'under_review' => 'Under Review',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'deleted' => 'Deleted',
                'due' => 'Due',
            ];
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$post->project_post_status] ?? 'bg-gray-100 text-gray-800' }}">
            {{ $statusLabel[$post->project_post_status] ?? ucfirst($post->project_post_status) }}
        </span>
    </td>
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.globalManagement.postingManagement', array_merge(request()->query(), ['view' => $post->project_id])) }}" 
                class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </a>
        </div>
    </td>
</tr>
@endforeach
<tr>
    <td colspan="5" class="px-6 py-4">
        {{ $postings->links() }}
    </td>
</tr>
