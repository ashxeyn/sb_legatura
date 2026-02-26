/**
 * Property Owner Dashboard JavaScript
 * Handles interactivity and dynamic content updates
 */

class PropertyOwnerDashboard {
    constructor() {
        // Read initial stats from HTML elements
        this.stats = {
            total: this.getStatValue('statTotal'),
            pending: this.getStatValue('statPending'),
            active: this.getStatValue('statActive'),
            inProgress: this.getStatValue('statInProgress'),
            completed: 0
        };
        
        this.init();
    }

    getStatValue(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const value = parseInt(element.textContent.trim(), 10);
            return isNaN(value) ? 0 : value;
        }
        return 0;
    }

    init() {
        // Set greeting based on time of day
        this.setGreeting();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Load user data if available
        this.loadUserData();
        
        // Initialize stats - only update if stats have been explicitly changed
        // This preserves the HTML values as the source of truth
        // Call updateStats() only when stats are updated from backend
    }

    setGreeting() {
        const hour = new Date().getHours();
        let greeting = 'Good Morning';
        
        if (hour >= 12 && hour < 17) {
            greeting = 'Good Afternoon';
        } else if (hour >= 17 || hour < 5) {
            greeting = 'Good Evening';
        }
        
        const greetingElement = document.getElementById('greeting');
        if (greetingElement) {
            greetingElement.textContent = greeting;
        }
    }

    loadUserData() {
        // Extract initials from name and set username if needed
        const nameElement = document.getElementById('profileUserName');
        const roleElement = document.getElementById('profileUserRole');
        const avatarElement = document.getElementById('profileAvatar');
        
        if (nameElement && avatarElement) {
            const currentName = nameElement.textContent.trim();
            const initialsElement = avatarElement.querySelector('.profile-initials');
            
            // Extract initials from name
            if (initialsElement && currentName) {
                const words = currentName.split(' ');
                if (words.length >= 2) {
                    initialsElement.textContent = (words[0][0] + words[1][0]).toUpperCase();
                } else if (words.length === 1 && currentName.length >= 2) {
                    initialsElement.textContent = currentName.substring(0, 2).toUpperCase();
                }
            }
            
            // If username/role is not set, extract from name
            if (roleElement && (!roleElement.textContent.trim() || roleElement.textContent.trim() === '@emmanuellesantos')) {
                // Extract username from name (convert to lowercase, replace spaces with nothing, add @)
                const username = '@' + currentName.toLowerCase().replace(/\s+/g, '');
                roleElement.textContent = username;
            }
        }
        
        // If you need to load user data from backend in the future, add it here
        // but make sure to check if name/username already exists before overriding
    }

    updateStats() {
        // Only update if stats have changed (to preserve HTML values if they're already set)
        // Update stat numbers with animation only if values differ
        const currentTotal = this.getStatValue('statTotal');
        if (currentTotal !== this.stats.total) {
            this.animateNumber('statTotal', this.stats.total);
        }
        
        const currentPending = this.getStatValue('statPending');
        if (currentPending !== this.stats.pending) {
            this.animateNumber('statPending', this.stats.pending);
        }
        
        const currentActive = this.getStatValue('statActive');
        if (currentActive !== this.stats.active) {
            this.animateNumber('statActive', this.stats.active);
        }
        
        const currentInProgress = this.getStatValue('statInProgress');
        if (currentInProgress !== this.stats.inProgress) {
            this.animateNumber('statInProgress', this.stats.inProgress);
        }
        
        // Update project counts
        const allProjectsCount = document.getElementById('allProjectsCount');
        if (allProjectsCount) {
            allProjectsCount.textContent = this.stats.total;
        }
        
        const finishedProjectsCount = document.getElementById('finishedProjectsCount');
        if (finishedProjectsCount) {
            finishedProjectsCount.textContent = this.stats.completed;
        }
    }

    animateNumber(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const duration = 1000; // 1 second
        const startValue = 0;
        const increment = targetValue / (duration / 16); // 60fps
        let currentValue = startValue;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                element.textContent = targetValue;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(currentValue);
            }
        }, 16);
    }

    setupEventListeners() {
        // Statistics card click handlers
        this.setupStatsInteractivity();

        // All Projects card click handler
        const allProjectsCard = document.getElementById('allProjectsCard');
        if (allProjectsCard) {
            allProjectsCard.addEventListener('click', (event) => {
                this.handleAllProjectsClick(event);
            });
        }

        // Finished Projects card click handler
        const finishedProjectsCard = document.getElementById('finishedProjectsCard');
        if (finishedProjectsCard) {
            finishedProjectsCard.addEventListener('click', (e) => {
                this.handleFinishedProjectsClick(e);
            });
        }

        // Profile avatar click handler
        const profileAvatar = document.getElementById('profileAvatar');
        if (profileAvatar) {
            profileAvatar.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleProfileClick();
            });
        }
    }

    setupStatsInteractivity() {
        const statItems = document.querySelectorAll('.stat-item');
        
        statItems.forEach((item, index) => {
            // Add click handler
            item.addEventListener('click', () => {
                this.handleStatClick(item, index);
            });

            // Add hover sound effect (optional - can be removed if not needed)
            item.addEventListener('mouseenter', () => {
                this.animateStatItem(item);
            });

            // Add ripple effect on click
            item.addEventListener('click', (e) => {
                this.createRippleEffect(e, item);
            });
        });
    }

    handleStatClick(statItem, index) {
        // Remove active class from all items
        document.querySelectorAll('.stat-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        statItem.classList.add('active');

        // Get stat type from label
        const label = statItem.querySelector('.stat-label').textContent.trim();
        const number = statItem.querySelector('.stat-number').textContent.trim();

        // Show notification
        this.showNotification(`Viewing ${label}: ${number} projects`);

        // Add pulse animation
        this.pulseAnimation(statItem);

        // In a real implementation, you could filter projects based on the stat clicked
        // For example:
        // if (label === 'TOTAL') {
        //     this.filterProjects('all');
        // } else if (label === 'PENDING') {
        //     this.filterProjects('pending');
        // } etc.
    }

    animateStatItem(item) {
        const number = item.querySelector('.stat-number');
        if (number) {
            number.style.transform = 'scale(1.1)';
            setTimeout(() => {
                number.style.transform = '';
            }, 200);
        }
    }

    createRippleEffect(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            left: ${x}px;
            top: ${y}px;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;

        element.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    pulseAnimation(element) {
        element.style.animation = 'pulse 0.5s ease-in-out';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }

    handleAllProjectsClick(event) {
        const card = document.getElementById('allProjectsCard');
        
        // Add click animation
        this.pulseAnimation(card);
        this.createRippleEffect(event, card);
        
        // Navigate to all projects page
        const projectsUrl = window.ownerRoutes?.projects || '/owner/projects';
        window.location.href = projectsUrl;
    }

    handleFinishedProjectsClick(event) {
        const card = document.getElementById('finishedProjectsCard');
        
        // Add click animation
        this.pulseAnimation(card);
        if (event) {
        this.createRippleEffect(event, card);
        }
        
        // Navigate to finished projects page
        const finishedProjectsUrl = window.ownerRoutes?.finishedProjects || '/owner/projects/finished';
        window.location.href = finishedProjectsUrl;
    }

    handleProfileClick() {
        // Navigate to profile page or open profile menu
        this.showNotification('Opening profile...');
        
        // In a real implementation:
        // window.location.href = '/owner/profile';
    }

    showNotification(message) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'dashboard-toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #EEA24B;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInUp 0.3s ease-out;
            font-weight: 500;
        `;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Method to update stats (can be called from backend)
    updateStatsFromBackend(newStats) {
        this.stats = { ...this.stats, ...newStats };
        this.updateStats();
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerDashboard();
});

// Add CSS animations for toast
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

