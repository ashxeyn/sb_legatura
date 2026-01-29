@extends('layouts.appContractor')

@section('title', 'Milestone Progress Report - Legatura')

@section('content')
    <div class="contractor-milestone-progress-report min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="milestone-progress-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('contractor.projects.milestone-report') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('contractor.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('contractor.myprojects') }}" class="breadcrumb-link">My Projects</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('contractor.projects.milestone-report') }}" class="breadcrumb-link">Milestone Report</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Milestone Progress</span>
                        </div>
                    </div>
                </div>

                <!-- Title and Action Button -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Milestone Progress Report</h1>
                        <p class="text-gray-600 mt-1">View and submit detailed progress reports for this milestone</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="milestoneProgressContainer" class="bg-white rounded-xl shadow-md p-6">
                <!-- Milestone Progress content will be dynamically inserted here -->
                <div class="text-center py-16">
                    <i class="fi fi-rr-file-chart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Milestone Progress Report</h3>
                    <p class="text-gray-500">Milestone progress information will be displayed here</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Report Modal (for submitting) -->
    @include('contractor.contractor_Modals.contractorProgressreport_Modal')
    
    <!-- View Progress Report Modal (for viewing existing reports) -->
    @include('contractor.contractor_Modals.contractorMilestoneprogressReport_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_MilestoneprogressReport.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Myprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorProgressreport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorMilestoneprogressReport_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_MilestoneprogressReport.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorProgressreport_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorMilestoneprogressReport_Modal.js') }}"></script>
    <script>
        // Set Dashboard link as active when on milestone progress report page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("contractor.dashboard") }}') {
                    link.classList.add('active');
                }
            });
            
            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search milestone progress...';
            }
        });
    </script>
@endsection
