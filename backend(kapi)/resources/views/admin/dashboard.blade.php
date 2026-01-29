@extends('admin.layouts.app')

@section('title','Dashboard')

@section('content')
<h1>Admin Dashboard</h1>
<div class="row">
  <div class="col-md-3"><div class="card p-3">Total Projects: {{ $totalProjects }}</div></div>
  <div class="col-md-3"><div class="card p-3">Open Projects: {{ $openProjects }}</div></div>
  <div class="col-md-3"><div class="card p-3">Pending Verifications: {{ $pendingVerifications }}</div></div>
  <div class="col-md-3"><div class="card p-3">Open Disputes: {{ $openDisputes }}</div></div>
</div>
@endsection
