@extends('layouts.appContractor')

@section('title', 'AI Analytics - Legatura')

@section('content')
    <div class="ai-analytics-page p-6">
        <div class="content-container">
            <h1 class="text-2xl font-bold mb-4">AI Analytics</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="text-gray-600">BASTA AI TO PARTE DITO.</p>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        // Set AI Analytics link as active when on this page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'AI Analytics' || link.getAttribute('href') === '{{ route("contractor.ai-analytics") }}') {
                    link.classList.add('active');
                }
            });
        });
    </script>
@endsection