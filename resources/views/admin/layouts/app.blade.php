<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a href="{{ route('admin.projects.index') }}" class="nav-link">Projects</a></li>
        <li class="nav-item"><a href="{{ route('admin.disputes.index') }}" class="nav-link">Disputes</a></li>
      </ul>
      <form method="post" action="/accounts/logout">@csrf<button class="btn btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>

<div class="container">
    @yield('content')
</div>
</body>
</html>
