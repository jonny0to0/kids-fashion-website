/**
 * Main JavaScript File
 * Global utilities and initialization
 */

// Unified notification system using SweetAlert2
function showToast(message, type = 'success', options = {}) {
    const defaultOptions = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    };

    const typeConfig = {
        success: {
            icon: 'success',
            iconColor: '#10b981',
            background: '#f0fdf4',
            color: '#166534'
        },
        error: {
            icon: 'error',
            iconColor: '#ef4444',
            background: '#fef2f2',
            color: '#991b1b'
        },
        warning: {
            icon: 'warning',
            iconColor: '#f59e0b',
            background: '#fffbeb',
            color: '#92400e'
        },
        info: {
            icon: 'info',
            iconColor: '#3b82f6',
            background: '#eff6ff',
            color: '#1e40af'
        }
    };

    const config = {
        ...defaultOptions,
        ...typeConfig[type] || typeConfig.success,
        text: message,
        ...options
    };

    Swal.fire(config);
}

// Show confirmation dialog using SweetAlert2
function showConfirm(title, message, confirmText = 'Yes', cancelText = 'Cancel', icon = 'question') {
    return Swal.fire({
        title: title,
        text: message,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#ec4899',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
}

// Show alert dialog using SweetAlert2
function showAlert(title, message, icon = 'info', confirmText = 'OK') {
    return Swal.fire({
        title: title,
        text: message,
        icon: icon,
        confirmButtonColor: '#ec4899',
        confirmButtonText: confirmText
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count
    updateCartCount();
    
    // Update wishlist count
    updateWishlistCount();
    
    // Handle PHP flash messages with SweetAlert
    const flashMessages = document.querySelectorAll('[role="alert"][data-flash-type]');
    flashMessages.forEach(flash => {
        const type = flash.getAttribute('data-flash-type');
        const message = flash.textContent.trim();
        if (message) {
            showToast(message, type);
            // Remove the original flash message after a short delay
            setTimeout(() => {
                flash.remove();
            }, 100);
        }
    });
    
    // Initialize scroll-based header behavior
    initScrollHeader();
});

/**
 * Smooth scroll-based header behavior
 * Hides/shows main nav based on scroll direction
 */
function initScrollHeader() {
    const mainNav = document.getElementById('main-nav');
    
    if (!mainNav) {
        return; // Element not found, skip initialization
    }
    
    let lastScrollTop = 0;
    let mainNavTimeout = null;
    
    // Scroll threshold to avoid jittery behavior at the top
    const SCROLL_THRESHOLD = 10;
    // Delay before hiding main nav when scrolling down
    const HIDE_NAV_DELAY = 250; // ms
    
    function handleScroll() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Clear any pending timeouts
        if (mainNavTimeout) clearTimeout(mainNavTimeout);
        
        // Only activate behavior after scrolling past threshold
        if (currentScrollTop < SCROLL_THRESHOLD) {
            // At top of page, ensure nav is visible
            mainNav.classList.remove('nav-hidden');
            lastScrollTop = currentScrollTop;
            return;
        }
        
        // Determine scroll direction
        const scrollingDown = currentScrollTop > lastScrollTop;
        const scrollDifference = Math.abs(currentScrollTop - lastScrollTop);
        
        // Only trigger if scroll difference is significant enough (avoid micro-scrolls)
        if (scrollDifference < 5) {
            lastScrollTop = currentScrollTop;
            return;
        }
        
        if (scrollingDown) {
            // Scrolling down: Hide main nav
            mainNavTimeout = setTimeout(() => {
                mainNav.classList.add('nav-hidden');
            }, HIDE_NAV_DELAY);
        } else {
            // Scrolling up: Show main nav
            mainNav.classList.remove('nav-hidden');
        }
        
        lastScrollTop = currentScrollTop;
    }
    
    // Throttled scroll handler using requestAnimationFrame
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
    
    // Initialize state at page load
    handleScroll();
}

// Update cart count
async function updateCartCount() {
    try {
        // Use SITE_URL from window (set by PHP) or detect it
        const siteUrl = window.SITE_URL || (function() {
            const path = window.location.pathname;
            if (path.includes('/kid-bazar-ecom/public')) {
                return window.location.origin + '/kid-bazar-ecom/public';
            }
            if (path.includes('/kid-bazar-ecom')) {
                return window.location.origin + '/kid-bazar-ecom/public';
            }
            return window.location.origin;
        })();
        
        const response = await fetch(siteUrl + '/cart/get-count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        if (response.ok) {
            const data = await response.json();
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl && data.success !== false) {
                cartCountEl.textContent = data.count || 0;
                // Show/hide cart count badge based on count
                if (data.count > 0) {
                    cartCountEl.style.display = 'flex';
                } else {
                    cartCountEl.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Update wishlist count
async function updateWishlistCount() {
    try {
        // Only update if user is logged in
        if (!window.SITE_URL) {
            return;
        }
        
        // Use SITE_URL from window (set by PHP) or detect it
        const siteUrl = window.SITE_URL || (function() {
            const path = window.location.pathname;
            if (path.includes('/kid-bazar-ecom/public')) {
                return window.location.origin + '/kid-bazar-ecom/public';
            }
            if (path.includes('/kid-bazar-ecom')) {
                return window.location.origin + '/kid-bazar-ecom/public';
            }
            return window.location.origin;
        })();
        
        const response = await fetch(siteUrl + '/user/wishlist-get-count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        if (response.ok) {
            const data = await response.json();
            const wishlistCountEl = document.getElementById('wishlist-count');
            if (wishlistCountEl && data.success !== false) {
                wishlistCountEl.textContent = data.count || 0;
                // Show/hide wishlist count badge based on count
                if (data.count > 0) {
                    wishlistCountEl.style.display = 'flex';
                } else {
                    wishlistCountEl.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating wishlist count:', error);
    }
}

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Toggle wishlist item
async function toggleWishlist(productId) {
    try {
        if (!window.SITE_URL) {
            showToast('Please login to add items to wishlist', 'error');
            return;
        }
        
        const siteUrl = window.SITE_URL;
        const wishlistBtn = document.querySelector(`.wishlist-btn-${productId}`);
        
        if (!wishlistBtn) {
            console.error('Wishlist button not found for product:', productId);
            showToast('Error: Wishlist button not found', 'error');
            return;
        }
        
        const svg = wishlistBtn.querySelector('svg');
        if (!svg) {
            console.error('SVG element not found in wishlist button');
            showToast('Error: SVG element not found', 'error');
            return;
        }
        
        const isInWishlist = wishlistBtn.classList.contains('in-wishlist');
        
        // If removing from wishlist, proceed normally (requires auth)
        if (isInWishlist) {
            const endpoint = '/user/wishlistRemove';
            const formData = new FormData();
            formData.append('product_id', productId);
            
            const response = await fetch(siteUrl + endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Update button state
                wishlistBtn.classList.remove('in-wishlist');
                svg.classList.remove('text-pink-600', 'fill-current');
                svg.classList.add('text-gray-700');
                svg.setAttribute('fill', 'none');
                wishlistBtn.setAttribute('title', 'Add to Wishlist');
                
                // Update wishlist count if provided
                if (data.count !== undefined) {
                    const wishlistCountEl = document.getElementById('wishlist-count');
                    if (wishlistCountEl) {
                        wishlistCountEl.textContent = data.count || 0;
                        if (data.count > 0) {
                            wishlistCountEl.style.display = 'flex';
                        } else {
                            wishlistCountEl.style.display = 'none';
                        }
                    }
                } else if (typeof updateWishlistCount === 'function') {
                    updateWishlistCount();
                }
                
                showToast(data.message || 'Removed from wishlist', 'success');
            } else {
                showToast(data.message || 'Failed to remove from wishlist', 'error');
            }
            return;
        }
        
        // Adding to wishlist - check authentication first
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('check_auth', '1'); // Flag to check auth without requiring it
        
        const response = await fetch(siteUrl + '/user/wishlistAdd', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // If user is not authenticated, redirect to login with product ID
        if (data.requires_auth === true || data.success === false && (data.message && data.message.toLowerCase().includes('login'))) {
            // Store product ID in session via backend and redirect to login
            const currentUrl = window.location.href;
            window.location.href = siteUrl + '/user/login?wishlist_product=' + productId + '&redirect=' + encodeURIComponent(currentUrl);
            return;
        }
        
        if (data.success) {
            // Update button state
            wishlistBtn.classList.add('in-wishlist');
            svg.classList.add('text-pink-600', 'fill-current');
            svg.classList.remove('text-gray-700');
            svg.setAttribute('fill', 'currentColor');
            wishlistBtn.setAttribute('title', 'Remove from Wishlist');
            
            // Update wishlist count if provided
            if (data.count !== undefined) {
                const wishlistCountEl = document.getElementById('wishlist-count');
                if (wishlistCountEl) {
                    wishlistCountEl.textContent = data.count || 0;
                    if (data.count > 0) {
                        wishlistCountEl.style.display = 'flex';
                    } else {
                        wishlistCountEl.style.display = 'none';
                    }
                }
            } else if (typeof updateWishlistCount === 'function') {
                updateWishlistCount();
            }
            
            showToast(data.message || 'Added to wishlist', 'success');
        } else {
            showToast(data.message || 'Failed to add to wishlist', 'error');
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Product Slider Alpine.js Component
function productSlider() {
    return {
        currentSlide: 0,
        totalSlides: 2,
        autoplayInterval: null,
        touchStartX: 0,
        touchEndX: 0,
        isTransitioning: false,
        
        init() {
            // Count actual slides available
            const slides = document.querySelectorAll('.hero-slide');
            this.totalSlides = slides.length;
            
            // Only start autoplay if there are multiple slides
            if (this.totalSlides > 1) {
                // Delay autoplay start for better initial load experience
                setTimeout(() => {
                    this.startAutoplay();
                }, 3000);
            }
        },
        
        startAutoplay() {
            if (this.totalSlides <= 1 || this.isTransitioning) return;
            
            this.autoplayInterval = setInterval(() => {
                if (!this.isTransitioning) {
                    this.nextSlide();
                }
            }, 6000); // Change slide every 6 seconds
        },
        
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        },
        
        nextSlide() {
            if (this.totalSlides <= 1 || this.isTransitioning) return;
            this.isTransitioning = true;
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
            this.restartAutoplay();
            // Reset transition flag after animation completes
            setTimeout(() => {
                this.isTransitioning = false;
            }, 800);
        },
        
        prevSlide() {
            if (this.totalSlides <= 1 || this.isTransitioning) return;
            this.isTransitioning = true;
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            this.restartAutoplay();
            // Reset transition flag after animation completes
            setTimeout(() => {
                this.isTransitioning = false;
            }, 800);
        },
        
        goToSlide(index) {
            if (this.totalSlides <= 1 || this.isTransitioning) return;
            if (index >= 0 && index < this.totalSlides && index !== this.currentSlide) {
                this.isTransitioning = true;
                this.currentSlide = index;
                this.restartAutoplay();
                // Reset transition flag after animation completes
                setTimeout(() => {
                    this.isTransitioning = false;
                }, 800);
            }
        },
        
        restartAutoplay() {
            this.stopAutoplay();
            if (this.totalSlides > 1) {
                setTimeout(() => {
                    this.startAutoplay();
                }, 100);
            }
        },
        
        // Touch event handlers for mobile swipe support
        handleTouchStart(event) {
            this.touchStartX = event.touches[0].clientX;
        },
        
        handleTouchMove(event) {
            // Prevent default to avoid scrolling while swiping
            if (Math.abs(event.touches[0].clientX - this.touchStartX) > 10) {
                event.preventDefault();
            }
        },
        
        handleTouchEnd(event) {
            if (!this.touchStartX || !event.changedTouches[0]) return;
            
            this.touchEndX = event.changedTouches[0].clientX;
            const swipeDistance = this.touchStartX - this.touchEndX;
            const minSwipeDistance = 50; // Minimum distance for a swipe
            
            if (Math.abs(swipeDistance) > minSwipeDistance) {
                if (swipeDistance > 0) {
                    // Swipe left - next slide
                    this.nextSlide();
                } else {
                    // Swipe right - previous slide
                    this.prevSlide();
                }
            }
            
            // Reset touch values
            this.touchStartX = 0;
            this.touchEndX = 0;
        }
    }
}

// Category Slider Alpine.js Component
function categorySlider(totalCategories) {
    return {
        currentIndex: 0,
        totalCategories: totalCategories || 0,
        itemsPerView: 4, // Default for desktop
        itemWidth: 0,
        touchStartX: 0,
        touchEndX: 0,
        isDragging: false,
        containerWidth: 0,
        resizeTimeout: null,
        dimensionsCalculated: false,
        
        // Pagination dots
        get totalPages() {
            if (!this.dimensionsCalculated || this.itemsPerView === 0 || this.totalCategories === 0) {
                // Fallback: estimate based on total categories
                return Math.ceil(this.totalCategories / 4);
            }
            // Calculate total pages based on items per view
            // Each page shows itemsPerView items, so we need ceil(totalCategories / itemsPerView) pages
            return Math.ceil(this.totalCategories / this.itemsPerView);
        },
        
        get pagesArray() {
            // Create an array of page indices for x-for directive
            const pages = this.totalPages;
            return Array.from({ length: pages }, (_, i) => i);
        },
        
        get currentPage() {
            if (!this.dimensionsCalculated || this.itemsPerView === 0) {
                return 0;
            }
            // Calculate which page we're currently on
            // Since we move one item at a time, we need to determine which "page" we're on
            // We'll use a simple approach: currentIndex / itemsPerView rounded down
            // But we want to show the page that best represents the current view
            return Math.min(
                Math.floor(this.currentIndex / this.itemsPerView),
                this.totalPages - 1
            );
        },
        
        init() {
            // Wait for images to load before calculating dimensions
            const wrapper = this.$el;
            if (wrapper) {
                const images = wrapper.querySelectorAll('img');
                let imagesLoaded = 0;
                const totalImages = images.length;
                
                if (totalImages > 0) {
                    // Wait for all images to load
                    images.forEach(img => {
                        if (img.complete) {
                            imagesLoaded++;
                        } else {
                            img.addEventListener('load', () => {
                                imagesLoaded++;
                                if (imagesLoaded === totalImages) {
                                    // All images loaded, recalculate
                                    setTimeout(() => {
                                        this.calculateDimensions();
                                        this.updateItemsPerView();
                                    }, 50);
                                }
                            });
                            img.addEventListener('error', () => {
                                imagesLoaded++;
                                if (imagesLoaded === totalImages) {
                                    setTimeout(() => {
                                        this.calculateDimensions();
                                        this.updateItemsPerView();
                                    }, 50);
                                }
                            });
                        }
                    });
                    
                    // If all images already loaded
                    if (imagesLoaded === totalImages) {
                        setTimeout(() => {
                            this.calculateDimensions();
                            this.updateItemsPerView();
                        }, 50);
                    }
                }
            }
            
            // Calculate dimensions immediately (fallback)
            this.calculateDimensions();
            
            // Also try after delays to ensure DOM is ready
            setTimeout(() => {
                this.calculateDimensions();
                this.updateItemsPerView();
            }, 200);
            
            setTimeout(() => {
                this.calculateDimensions();
                this.updateItemsPerView();
            }, 500);
            
            // Wait for DOM to be ready
            this.$nextTick(() => {
                this.calculateDimensions();
                this.updateItemsPerView();
            });
            
            // Recalculate on window resize with debounce
            window.addEventListener('resize', () => {
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = setTimeout(() => {
                    this.calculateDimensions();
                    this.updateItemsPerView();
                }, 150);
            });
        },
        
        calculateDimensions() {
            // Find the container within this component's scope
            const wrapper = this.$el;
            if (!wrapper) return;
            
            const container = wrapper.querySelector('.category-slider-container');
            if (!container) return;
            
            // Get the actual visible width (container width, which is inside the padded wrapper)
            this.containerWidth = container.offsetWidth;
            if (this.containerWidth === 0) {
                // Try again after a delay if container not ready
                return;
            }
            
            // Get the track element to check actual content width
            const track = container.querySelector('.category-slider-track');
            
            // Get the first item to calculate actual width
            const firstItem = container.querySelector('.category-slider-item');
            if (firstItem && firstItem.offsetWidth > 0) {
                const itemStyle = window.getComputedStyle(firstItem);
                // Calculate item width including padding
                const itemWidth = firstItem.offsetWidth;
                this.itemWidth = itemWidth;
                
                // Calculate how many items fit in the viewport
                // Be very conservative - subtract 1 to ensure arrows show when needed
                // This accounts for rounding errors and ensures arrows appear
                const calculatedItemsPerView = Math.floor(this.containerWidth / this.itemWidth);
                this.itemsPerView = Math.max(1, calculatedItemsPerView - 1);
                
                // Additional check: if track width exceeds container width, we definitely need arrows
                if (track && track.scrollWidth > this.containerWidth) {
                    // Force itemsPerView to be less than totalCategories to show arrows
                    if (this.itemsPerView >= this.totalCategories) {
                        this.itemsPerView = Math.max(1, this.totalCategories - 1);
                    }
                }
                
                this.dimensionsCalculated = true;
            } else {
                // Fallback calculation based on screen size
                // Use conservative estimates to ensure arrows show when needed
                if (window.innerWidth < 640) {
                    this.itemsPerView = 3; // Show arrows if more than 3 on mobile
                    this.itemWidth = this.containerWidth > 0 ? this.containerWidth / this.itemsPerView : 120;
                } else if (window.innerWidth < 1024) {
                    this.itemsPerView = 4; // Show arrows if more than 4 on tablet
                    this.itemWidth = this.containerWidth > 0 ? this.containerWidth / this.itemsPerView : 140;
                } else {
                    this.itemsPerView = 5; // Show arrows if more than 5 on desktop
                    this.itemWidth = this.containerWidth > 0 ? this.containerWidth / this.itemsPerView : 160;
                }
                this.dimensionsCalculated = true;
            }
        },
        
        updateItemsPerView() {
            // itemsPerView is already an integer (Math.floor applied in calculateDimensions)
            if (this.totalCategories <= this.itemsPerView) {
                // All items fit, no need to slide
                this.currentIndex = 0;
                return;
            }
            
            const maxIndex = Math.max(0, this.totalCategories - this.itemsPerView);
            if (this.currentIndex > maxIndex) {
                this.currentIndex = maxIndex;
            }
        },
        
        slideNext() {
            // itemsPerView is already an integer (Math.floor applied in calculateDimensions)
            if (this.totalCategories <= this.itemsPerView) return;
            
            const maxIndex = Math.max(0, this.totalCategories - this.itemsPerView);
            if (this.currentIndex < maxIndex) {
                this.currentIndex = Math.min(this.currentIndex + 1, maxIndex);
            }
        },
        
        slidePrev() {
            if (this.currentIndex > 0) {
                this.currentIndex = Math.max(0, this.currentIndex - 1);
            }
        },
        
        goToPage(pageIndex) {
            if (!this.dimensionsCalculated || this.itemsPerView === 0) {
                return;
            }
            // Calculate the index for the given page
            // Each page starts at pageIndex * itemsPerView
            const targetIndex = pageIndex * this.itemsPerView;
            const maxIndex = Math.max(0, this.totalCategories - this.itemsPerView);
            this.currentIndex = Math.min(targetIndex, maxIndex);
        },
        
        get showPrevArrow() {
            // Show if we can go back and there are more items than can fit
            if (!this.dimensionsCalculated) {
                // Before dimensions are calculated, show if we have many categories and we're not at start
                return this.totalCategories > 5 && this.currentIndex > 0;
            }
            // Ensure we have valid data
            if (this.totalCategories === 0 || this.itemsPerView === 0) {
                // Fallback: show if we have many categories
                return this.totalCategories > 5 && this.currentIndex > 0;
            }
            // itemsPerView is already conservative (subtracted 1), so use it directly
            // Show if we're not at the start and there are more items than can fit
            return this.currentIndex > 0 && this.totalCategories > this.itemsPerView;
        },
        
        get showNextArrow() {
            // Always show if we have significantly more categories than a reasonable fit
            // This is a persistent safety check
            if (this.totalCategories > 8) {
                // If we have more than 8 categories, almost certainly need arrows
                const maxIndex = this.dimensionsCalculated 
                    ? Math.max(0, this.totalCategories - this.itemsPerView)
                    : this.totalCategories - 5;
                return this.currentIndex < maxIndex;
            }
            
            // Show if there are more items to show
            if (!this.dimensionsCalculated) {
                // Before dimensions are calculated, show if we have many categories
                // This ensures arrows appear immediately for users
                return this.totalCategories > 5;
            }
            // Ensure we have valid data
            if (this.totalCategories === 0 || this.itemsPerView === 0) {
                // Fallback: show if we have many categories
                return this.totalCategories > 5;
            }
            
            // Check actual DOM overflow as a definitive test
            const wrapper = this.$el;
            if (wrapper) {
                const container = wrapper.querySelector('.category-slider-container');
                const track = container ? container.querySelector('.category-slider-track') : null;
                if (track && container) {
                    // If track is wider than container, we definitely need arrows
                    if (track.scrollWidth > container.offsetWidth + 10) { // Add 10px buffer
                        const maxIndex = Math.max(0, this.totalCategories - this.itemsPerView);
                        return this.currentIndex < maxIndex;
                    }
                }
            }
            
            // itemsPerView is already conservative (subtracted 1), so use it directly
            // If all items fit, don't show arrow
            if (this.totalCategories <= this.itemsPerView) {
                return false;
            }
            // Calculate max index and check if we can go further
            const maxIndex = Math.max(0, this.totalCategories - this.itemsPerView);
            return this.currentIndex < maxIndex;
        },
        
        // Touch event handlers
        handleTouchStart(event) {
            this.touchStartX = event.touches[0].clientX;
            this.isDragging = true;
        },
        
        handleTouchMove(event) {
            if (!this.isDragging) return;
            this.touchEndX = event.touches[0].clientX;
        },
        
        handleTouchEnd(event) {
            if (!this.isDragging) return;
            this.isDragging = false;
            
            const swipeThreshold = 50; // Minimum distance for swipe
            const diff = this.touchStartX - this.touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swiped left - go to next
                    this.slideNext();
                } else {
                    // Swiped right - go to previous
                    this.slidePrev();
                }
            }
            
            // Reset touch positions
            this.touchStartX = 0;
            this.touchEndX = 0;
        }
    }
}


