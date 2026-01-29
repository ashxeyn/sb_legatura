@extends('layouts.app')

@section('title', 'Milestone Report - Legatura')

@section('content')
    <div class="property-owner-milestone-report min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="milestone-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('owner.projects') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('owner.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('owner.projects') }}" class="breadcrumb-link">My Projects</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Milestone Report</span>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Milestone Report</h1>
                        <p class="text-gray-600 mt-1">Review milestone timeline, payment breakdown, and project duration</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="milestoneReportContainer" class="bg-white rounded-xl shadow-md p-6">
                <!-- Milestone Report content will be dynamically inserted here -->
                <div class="text-center py-16">
                    <i class="fi fi-rr-file-chart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Milestone Report</h3>
                    <p class="text-gray-500">Milestone information will be displayed here</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Modal -->
    @include('owner.propertyOwner_Modals.ownerPaymenthistory_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_MilestoneReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Allprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPaymenthistory_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_MilestoneReport.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerPaymenthistory_Modal.js') }}"></script>
    <script>
        // Set Dashboard link as active when on milestone report page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("owner.dashboard") }}') {
                    link.classList.add('active');
                }
            });
            
            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search milestones...';
            }
        });
    </script>
@endsection
