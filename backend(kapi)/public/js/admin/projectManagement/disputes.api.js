/**
 * Disputes API - Handles AJAX calls for disputes management
 */
class DisputesAPI {
    constructor() {
        this.baseUrl = '/admin/project-management';
    }

    /**
     * Fetch disputes with pagination and filters
     */
    async getDisputes(page = 1, search = '', status = '') {
        try {
            const params = new URLSearchParams({
                page: page,
                ...(search && { search: search }),
                ...(status && { status: status })
            });

            const response = await fetch(`${this.baseUrl}/disputes/api?${params}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching disputes:', error);
            throw error;
        }
    }

    /**
     * Get single dispute details
     */
    async getDispute(id) {
        try {
            const response = await fetch(`${this.baseUrl}/disputes/${id}/api`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching dispute details:', error);
            throw error;
        }
    }
}

// Export for use in other files
window.DisputesAPI = DisputesAPI;
