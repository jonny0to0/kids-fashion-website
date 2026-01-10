/**
 * Admin Dashboard JavaScript
 * Replaces Alpine.js with vanilla JavaScript for sidebar, submenu, and dropdown functionality
 */

(function() {
    'use strict';

    // Admin Dashboard State
    const AdminDashboard = {
        activeSubmenu: null,
        activeNestedSubmenu: null,
        profileOpen: false,
        mobileMenuOpen: false,
        
        /**
         * Initialize the admin dashboard
         */
        init: function() {
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
            this.initProfileDropdown();
        },
        
        /**
         * Initialize submenu toggle functionality
         */
        initSubmenuToggles: function() {
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
        toggleSubmenu: function(menuName, forceOpen = false) {
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
        closeAllSubmenus: function() {
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
        initNestedSubmenuToggles: function() {
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
        toggleNestedSubmenu: function(menuName, forceOpen = false) {
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
        closeAllNestedSubmenus: function() {
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
        initMobileMenu: function() {
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
        toggleMobileMenu: function() {
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
        closeMobileMenu: function() {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;
            
            this.mobileMenuOpen = false;
            sidebar.classList.remove('mobile-open');
        },
        
        /**
         * Initialize profile dropdown
         */
        initProfileDropdown: function() {
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
        toggleProfileDropdown: function() {
            const profileDropdown = document.querySelector('[data-profile-dropdown]');
            if (!profileDropdown) return;
            
            this.profileOpen = !this.profileOpen;
            
            if (this.profileOpen) {
                profileDropdown.style.display = 'block';
            } else {
                profileDropdown.style.display = 'none';
            }
        },
        
        /**
         * Close profile dropdown
         */
        closeProfileDropdown: function() {
            const profileDropdown = document.querySelector('[data-profile-dropdown]');
            if (!profileDropdown) return;
            
            this.profileOpen = false;
            profileDropdown.style.display = 'none';
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            AdminDashboard.init();
        });
    } else {
        // DOM is already loaded
        AdminDashboard.init();
    }
    
    // Make AdminDashboard available globally for debugging
    window.AdminDashboard = AdminDashboard;
})();

