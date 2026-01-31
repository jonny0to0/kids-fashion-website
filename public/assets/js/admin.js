/**
 * Admin Dashboard JavaScript
 * Replaces Alpine.js with vanilla JavaScript for sidebar, submenu, and dropdown functionality
 */

(function () {
    'use strict';

    // Admin Dashboard State
    const AdminDashboard = {
        activeSubmenu: null,
        activeNestedSubmenu: null,
        profileOpen: false,
        notificationOpen: false,
        mobileMenuOpen: false,

        /**
         * Initialize the admin dashboard
         */
        init: function () {
            // Get initial active submenu from data attribute
            const sidebar = document.getElementById('adminSidebar');
            if (sidebar) {
                if (sidebar.dataset.activeSubmenu) {
                    this.activeSubmenu = sidebar.dataset.activeSubmenu;
                    // Open the active submenu on page load
                    if (this.activeSubmenu) {
                        this.toggleSubmenu(this.activeSubmenu, true);
                    }
                }

                // Get initial active nested submenu
                if (sidebar.dataset.activeNestedSubmenu) {
                    this.activeNestedSubmenu = sidebar.dataset.activeNestedSubmenu;
                    // Open the active nested submenu on page load
                    if (this.activeNestedSubmenu) {
                        this.toggleNestedSubmenu(this.activeNestedSubmenu, true);
                    }
                }
            }

            this.initSubmenuToggles();
            this.initNestedSubmenuToggles();
            this.initMobileMenu();
            this.initNotificationDropdown();
            this.initProfileDropdown();
            this.startNotificationPolling();
        },

        /**
         * Initialize submenu toggle functionality
         */
        initSubmenuToggles: function () {
            const submenuTriggers = document.querySelectorAll('[data-submenu-toggle]');

            submenuTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    // Don't prevent default if clicking on a link inside
                    const isLink = trigger.tagName === 'A' || trigger.closest('a');
                    if (!isLink) {
                        e.preventDefault();
                    }
                    e.stopPropagation();
                    const menuName = trigger.getAttribute('data-submenu-toggle');
                    this.toggleSubmenu(menuName);
                });
            });
        },

        /**
         * Toggle a submenu open/closed
         * @param {string} menuName - The name of the menu to toggle
         * @param {boolean} forceOpen - Force the menu to open (used on initial load)
         */
        toggleSubmenu: function (menuName, forceOpen = false) {
            // Find the submenu container
            const submenuContainer = document.querySelector(`[data-submenu="${menuName}"]`);
            if (!submenuContainer) return;

            // Find the trigger and arrow icon within the same parent
            const trigger = document.querySelector(`[data-submenu-toggle="${menuName}"]`);
            if (!trigger) return;
            const arrowIcon = trigger.querySelector('.submenu-arrow');

            // Toggle active state
            if (forceOpen || this.activeSubmenu !== menuName) {
                // Close other submenus (but keep nested submenus if this is the parent)
                if (!forceOpen) {
                    // Only close other top-level submenus, not nested ones
                    const allTopLevelSubmenus = document.querySelectorAll('.sidebar-submenu');
                    allTopLevelSubmenus.forEach(submenu => {
                        if (submenu !== submenuContainer && !submenuContainer.contains(submenu)) {
                            submenu.classList.remove('open');
                        }
                    });

                    const allTopLevelArrows = document.querySelectorAll('.submenu-arrow');
                    allTopLevelArrows.forEach(arrow => {
                        const parentTrigger = arrow.closest('[data-submenu-toggle]');
                        if (parentTrigger && parentTrigger !== trigger) {
                            arrow.classList.remove('rotate-90');
                        }
                    });
                }

                // Open this submenu
                this.activeSubmenu = menuName;
                submenuContainer.classList.add('open');
                if (arrowIcon) {
                    arrowIcon.classList.add('rotate-90');
                }
            } else {
                // Close this submenu (and its nested submenus)
                this.activeSubmenu = null;
                submenuContainer.classList.remove('open');
                if (arrowIcon) {
                    arrowIcon.classList.remove('rotate-90');
                }
                // Also close any nested submenus
                const nestedSubmenus = submenuContainer.querySelectorAll('.sidebar-nested-submenu');
                nestedSubmenus.forEach(nested => nested.classList.remove('open'));
                const nestedArrows = submenuContainer.querySelectorAll('.nested-submenu-arrow');
                nestedArrows.forEach(arrow => arrow.classList.remove('rotate-90'));
            }
        },

        /**
         * Close all submenus
         */
        closeAllSubmenus: function () {
            const allSubmenus = document.querySelectorAll('.sidebar-submenu');
            const allArrows = document.querySelectorAll('.submenu-arrow');

            allSubmenus.forEach(submenu => {
                submenu.classList.remove('open');
            });

            allArrows.forEach(arrow => {
                arrow.classList.remove('rotate-90');
            });
        },

        /**
         * Initialize nested submenu toggle functionality
         */
        initNestedSubmenuToggles: function () {
            const nestedSubmenuTriggers = document.querySelectorAll('[data-nested-submenu-toggle]');

            nestedSubmenuTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const menuName = trigger.getAttribute('data-nested-submenu-toggle');
                    this.toggleNestedSubmenu(menuName);
                });
            });
        },

        /**
         * Toggle a nested submenu open/closed
         * @param {string} menuName - The name of the nested menu to toggle
         * @param {boolean} forceOpen - Force the menu to open (used on initial load)
         */
        toggleNestedSubmenu: function (menuName, forceOpen = false) {
            // Find the nested submenu container
            const nestedSubmenuContainer = document.querySelector(`[data-nested-submenu="${menuName}"]`);
            if (!nestedSubmenuContainer) return;

            // Find the trigger and arrow icon
            const trigger = document.querySelector(`[data-nested-submenu-toggle="${menuName}"]`);
            if (!trigger) return;
            const arrowIcon = trigger.querySelector('.nested-submenu-arrow');

            // Toggle active state
            if (forceOpen || this.activeNestedSubmenu !== menuName) {
                // Close other nested submenus (if any)
                this.closeAllNestedSubmenus();

                // Open this nested submenu
                this.activeNestedSubmenu = menuName;
                nestedSubmenuContainer.classList.add('open');
                if (arrowIcon) {
                    arrowIcon.classList.add('rotate-90');
                }
            } else {
                // Close this nested submenu
                this.activeNestedSubmenu = null;
                nestedSubmenuContainer.classList.remove('open');
                if (arrowIcon) {
                    arrowIcon.classList.remove('rotate-90');
                }
            }
        },

        /**
         * Close all nested submenus
         */
        closeAllNestedSubmenus: function () {
            const allNestedSubmenus = document.querySelectorAll('.sidebar-nested-submenu');
            const allNestedArrows = document.querySelectorAll('.nested-submenu-arrow');

            allNestedSubmenus.forEach(submenu => {
                submenu.classList.remove('open');
            });

            allNestedArrows.forEach(arrow => {
                arrow.classList.remove('rotate-90');
            });
        },

        /**
         * Initialize mobile menu toggle
         */
        initMobileMenu: function () {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;

            // Mobile menu toggle button in header
            const mobileMenuButton = document.querySelector('[data-mobile-menu-toggle]');
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleMobileMenu();
                });
            }

            // Close button inside sidebar
            const closeMenuButton = document.querySelector('[data-mobile-menu-close]');
            if (closeMenuButton) {
                closeMenuButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closeMobileMenu();
                });
            }

            // Close mobile menu when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    if (this.mobileMenuOpen && sidebar && !sidebar.contains(e.target) &&
                        (!mobileMenuButton || !mobileMenuButton.contains(e.target))) {
                        this.closeMobileMenu();
                    }
                }
            });
        },

        /**
         * Toggle mobile menu
         */
        toggleMobileMenu: function () {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;

            this.mobileMenuOpen = !this.mobileMenuOpen;
            if (this.mobileMenuOpen) {
                sidebar.classList.add('mobile-open');
            } else {
                sidebar.classList.remove('mobile-open');
            }
        },

        /**
         * Close mobile menu
         */
        closeMobileMenu: function () {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;

            this.mobileMenuOpen = false;
            sidebar.classList.remove('mobile-open');
        },

        /**
         * Initialize notification dropdown
         */
        initNotificationDropdown: function () {
            const notificationButton = document.querySelector('[data-notification-toggle]');
            const notificationDropdown = document.querySelector('[data-notification-dropdown]');

            if (!notificationButton || !notificationDropdown) return;

            // Initially hide dropdown
            notificationDropdown.style.display = 'none';

            // Toggle dropdown on button click
            notificationButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationDropdown();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (this.notificationOpen) {
                    const notificationContainer = notificationButton.closest('.relative');
                    if (notificationContainer && !notificationContainer.contains(e.target)) {
                        this.closeNotificationDropdown();
                    }
                }
            });

            // Close dropdown when pressing Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.notificationOpen) {
                    this.closeNotificationDropdown();
                }
            });
        },

        /**
         * Toggle notification dropdown
         */
        toggleNotificationDropdown: function () {
            const notificationDropdown = document.querySelector('[data-notification-dropdown]');
            if (!notificationDropdown) return;

            // Close profile dropdown if open
            if (this.profileOpen) {
                this.closeProfileDropdown();
            }

            this.notificationOpen = !this.notificationOpen;

            if (this.notificationOpen) {
                notificationDropdown.style.display = 'block';
            } else {
                notificationDropdown.style.display = 'none';
            }
        },

        /**
         * Close notification dropdown
         */
        closeNotificationDropdown: function () {
            const notificationDropdown = document.querySelector('[data-notification-dropdown]');
            if (!notificationDropdown) return;

            this.notificationOpen = false;
            notificationDropdown.style.display = 'none';
        },

        /**
         * Initialize profile dropdown
         */
        initProfileDropdown: function () {
            const profileButton = document.querySelector('[data-profile-toggle]');
            const profileDropdown = document.querySelector('[data-profile-dropdown]');

            if (!profileButton || !profileDropdown) return;

            // Initially hide dropdown
            profileDropdown.style.display = 'none';

            // Toggle dropdown on button click
            profileButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleProfileDropdown();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (this.profileOpen) {
                    const profileContainer = profileButton.closest('.relative');
                    if (profileContainer && !profileContainer.contains(e.target)) {
                        this.closeProfileDropdown();
                    }
                }
            });

            // Close dropdown when pressing Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.profileOpen) {
                    this.closeProfileDropdown();
                }
            });
        },

        /**
         * Toggle profile dropdown
         */
        toggleProfileDropdown: function () {
            const profileDropdown = document.querySelector('[data-profile-dropdown]');
            if (!profileDropdown) return;

            this.profileOpen = !this.profileOpen;

            // Close notification dropdown if open
            if (this.notificationOpen) {
                this.closeNotificationDropdown();
            }

            if (this.profileOpen) {
                profileDropdown.style.display = 'block';
            } else {
                profileDropdown.style.display = 'none';
            }
        },

        /**
         * Close profile dropdown
         */
        closeProfileDropdown: function () {
            const profileDropdown = document.querySelector('[data-profile-dropdown]');
            if (!profileDropdown) return;

            this.profileOpen = false;
            profileDropdown.style.display = 'none';
        },

        /**
         * Start notification polling
         */
        startNotificationPolling: function () {
            // Initial poll
            this.pollNotifications();

            // Poll every 30 seconds
            setInterval(() => {
                this.pollNotifications();
            }, 30000);
        },

        /**
         * Poll for new notifications
         */
        pollNotifications: function () {
            fetch(window.SITE_URL + '/admin/notifications/check')
                .then(response => response.json())
                .then(data => {
                    this.updateNotificationUI(data);
                })
                .catch(err => console.error('Notification poll failed', err));
        },

        /**
         * Update notification UI
         */
        updateNotificationUI: function (data) {
            // Update badge
            const badge = document.querySelector('[data-notification-toggle] span');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.style.display = 'block';
                    badge.textContent = ''; // Just a dot, or add count if needed
                } else {
                    badge.style.display = 'none';
                }
            }

            // Update dropdown content
            const listContainer = document.querySelector('[data-notification-dropdown] .overflow-y-auto');
            if (listContainer) {
                if (data.latest_notifications.length === 0) {
                    listContainer.innerHTML = `
                        <div class="px-4 py-6 text-center text-gray-500 text-sm">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            No new notifications
                        </div>
                    `;
                } else {
                    let html = '';
                    data.latest_notifications.forEach(notification => {
                        html += `
                            <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors">
                                <p class="text-sm font-medium text-gray-800">${this.escapeHtml(notification.title)}</p>
                                <p class="text-xs text-gray-500 mt-1 truncate">${this.escapeHtml(notification.message)}</p>
                                <div class="mt-2 flex justify-between items-center">
                                    <span class="text-xs text-gray-400">${new Date(notification.created_at).toLocaleDateString()}</span>
                                    ${notification.link ? `<a href="${notification.link}" class="text-xs text-pink-600 hover:underline">View</a>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    listContainer.innerHTML = html;
                }
            }
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function (text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function (m) { return map[m]; });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            AdminDashboard.init();
        });
    } else {
        // DOM is already loaded
        AdminDashboard.init();
    }

    // Make AdminDashboard available globally for debugging
    window.AdminDashboard = AdminDashboard;
})();

