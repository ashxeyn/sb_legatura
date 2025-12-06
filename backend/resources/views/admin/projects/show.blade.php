@extends('admin.layouts.app')
@section('title','Project')
@section('content')
<h2>{{ $project->project_title }}</h2>
<p>{{ $project->project_description }}</p>
<p><strong>Status:</strong> {{ $project->project_status }}</p>

<form method="post" action="{{ route('admin.projects.approve', $project->project_id) }}">@csrf<button class="btn btn-success">Approve</button></form>
<form method="post" action="{{ route('admin.projects.reject', $project->project_id) }}">@csrf
  <input name="reason" placeholder="Reject reason" class="form-control" />
  <button class="btn btn-danger mt-2">Reject</button>
</form>

<h4 class="mt-4">Bids</h4>
<table class="table">
  <thead><tr><th>Bid ID</th><th>Contractor</th><th>Amount</th><th>Timeline</th><th>Action</th></tr></thead>
  <tbody>
    @foreach($bids as $b)
      <tr>
        <td>{{ $b->bid_id }}</td>
        <td>{{ $b->company_name }}</td>
        <td>{{ $b->proposed_cost}}</td>
        <td>{{ $b->estimated_timeline }} days</td>
        <td>
          <form method="post" action="{{ route('admin.projects.assign', $project->project_id) }}">
            @csrf
            <input type="hidden" name="contractor_id" value="{{ $b->contractor_id }}">
            <button class="btn btn-primary btn-sm">Assign</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
