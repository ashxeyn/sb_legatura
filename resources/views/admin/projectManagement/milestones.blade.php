<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/listOfProjects.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/projectManagement/milestones.api.js') }}" defer></script>


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Milestones Management'])

      <div class="p-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
              <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">ID</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">Title</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">Project</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">Amount</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">Due Date</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700">Status</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-700 text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="milestonesTableBody">
                <tr>
                  <td class="px-6 py-4" colspan="7" class="text-center text-gray-500">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>

  </div>
</body>

</html>
