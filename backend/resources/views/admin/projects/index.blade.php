@extends('admin.layouts.app')
@section('title','Projects')
@section('content')
<h2>Projects</h2>
<table class="table">
  <thead><tr><th>ID</th><th>Title</th><th>Owner</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($q as $p)
      <tr>
        <td>{{ $p->project_id }}</td>
        <td>{{ $p->project_title }}</td>
        <td>{{ $p->first_name }} {{ $p->last_name }}</td>
        <td>{{ $p->project_status }}</td>
        <td>
          <a class="btn btn-sm btn-primary" href="{{ route('admin.projects.show', $p->project_id) }}">View</a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{ $q->links() }}
@endsection
