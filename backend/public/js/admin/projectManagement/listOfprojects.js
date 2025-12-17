// ============================================================================
// DEPRECATED FILE - COMPLETELY REPLACED
// ============================================================================
// This file previously contained hardcoded mock/template data.
// ALL HARDCODED DATA HAS BEEN COMPLETELY REMOVED.
//
// Current Status:
// - This file is NO LONGER loaded by listOfprojects.blade.php
// - The page now uses ONLY listOfprojects.api.js
// - All data comes directly from the REST API and database
// - No template/mock data is used anywhere
//
// NOTE: This file can be safely deleted. It serves no purpose anymore.
// ============================================================================

console.error('ERROR: Old listOfprojects.js is loaded! All data should come from listOfprojects.api.js');

// Prevent errors from old code trying to reference these
window.projectsData = [];
window.MILESTONE_DATA = [];
window.HALTED_MILESTONE_DATA = [];
window.CANCELLED_MILESTONE_DATA = [];
window.CANCELLED_PAYMENT_DATA = [];

// Empty function stubs
window.renderHaltedMilestoneTimeline = () => console.warn('Deprecated');
window.selectHaltedMilestone = () => console.warn('Deprecated');
window.renderMilestoneTimeline = () => console.warn('Deprecated');
window.selectMilestone = () => console.warn('Deprecated');
window.showOngoingProjectModal = () => console.warn('Deprecated');
window.populateOngoingProjectModal = () => console.warn('Deprecated');
