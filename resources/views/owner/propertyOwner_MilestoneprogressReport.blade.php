@extends('layouts.app')

@section('title', 'Milestone Progress Report - Legatura')

@section('content')
    <div class="property-owner-milestone-progress-report min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="milestone-progress-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('owner.projects.milestone-report') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('owner.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('owner.projects') }}" class="breadcrumb-link">My Projects</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('owner.projects.milestone-report') }}" class="breadcrumb-link">Milestone Report</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Milestone Progress</span>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Milestone Progress Report</h1>
                        <p class="text-gray-600 mt-1">View detailed progress and reports for this milestone</p>
                    </div>
                    <div class="relative">
                        <button id="reportMenuBtn" class="report-menu-btn" type="button">
                            <i class="fi fi-rr-menu-dots-vertical"></i>
                        </button>
                        <div id="reportDropdown" class="report-dropdown hidden">
                            <button class="report-dropdown-item" data-action="send-report">
                                <i class="fi fi-rr-paper-plane"></i>
                                <span>Send Report</span>
                            </button>
                            <button class="report-dropdown-item" data-action="report-history">
                                <i class="fi fi-rr-time-past"></i>
                                <span>Report History</span>
                            </button>
                        </div>
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

    <!-- Progress Report Modal -->
    @include('owner.propertyOwner_Modals.ownerProgressreport_Modal')

    <!-- Send Report Modal -->
    @include('owner.propertyOwner_Modals.disputesSendreport_Modal')

    <!-- Report History Modal -->
    @include('owner.propertyOwner_Modals.disputesReporthistory_Modal')

    <!-- Report Details Modal -->
    @include('owner.propertyOwner_Modals.disputesReportdetails_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_MilestoneprogressReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Allprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerProgressreport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/disputesSendreport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/disputesReporthistory_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/disputesReportdetails_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_MilestoneprogressReport.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerProgressreport_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/disputesSendreport_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/disputesReporthistory_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/disputesReportdetails_Modal.js') }}"></script>
    <script>
        // Set Dashboard link as active when on milestone progress report page
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
                navbarSearchInput.placeholder = 'Search milestone progress...';
            }

            // Report Menu Dropdown Functionality
            const reportMenuBtn = document.getElementById('reportMenuBtn');
            const reportDropdown = document.getElementById('reportDropdown');

            if (reportMenuBtn && reportDropdown) {
                // Toggle dropdown
                reportMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    reportDropdown.classList.toggle('hidden');
                    reportMenuBtn.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!reportMenuBtn.contains(e.target) && !reportDropdown.contains(e.target)) {
                        reportDropdown.classList.add('hidden');
                        reportMenuBtn.classList.remove('active');
                    }
                });

                // Handle dropdown item clicks
                const dropdownItems = reportDropdown.querySelectorAll('.report-dropdown-item');
                dropdownItems.forEach(item => {
                    item.addEventListener('click', (e) => {
                        const action = item.getAttribute('data-action');
                        reportDropdown.classList.add('hidden');
                        reportMenuBtn.classList.remove('active');

                        if (action === 'send-report') {
                            console.log('Send Report clicked');
                            openSendReportModal();
                        } else if (action === 'report-history') {
                            console.log('Report History clicked');
                            openReportHistoryModal();
                        }
                    });
                });
            }
        });
    </script>
@endsection
