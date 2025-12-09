/**
 * Main JavaScript File
 * Global utilities and initialization
 */

// Show toast notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count
    updateCartCount();
    
    // Auto-hide flash messages
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
});

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
        
        const response = await fetch(siteUrl + '/cart/get-count');
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

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Product Slider Alpine.js Component
function productSlider() {
    return {
        currentSlide: 0,
        totalSlides: 2,
        autoplayInterval: null,
        
        init() {
            // Count actual slides available
            const slides = document.querySelectorAll('.slide');
            this.totalSlides = slides.length;
            
            // Only start autoplay if there are multiple slides
            if (this.totalSlides > 1) {
                this.startAutoplay();
            }
        },
        
        startAutoplay() {
            if (this.totalSlides <= 1) return;
            
            this.autoplayInterval = setInterval(() => {
                this.nextSlide();
            }, 5000); // Change slide every 5 seconds
        },
        
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        },
        
        nextSlide() {
            if (this.totalSlides <= 1) return;
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
            this.restartAutoplay();
        },
        
        prevSlide() {
            if (this.totalSlides <= 1) return;
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            this.restartAutoplay();
        },
        
        goToSlide(index) {
            if (this.totalSlides <= 1) return;
            if (index >= 0 && index < this.totalSlides) {
                this.currentSlide = index;
                this.restartAutoplay();
            }
        },
        
        restartAutoplay() {
            this.stopAutoplay();
            if (this.totalSlides > 1) {
                this.startAutoplay();
            }
        }
    }
}

