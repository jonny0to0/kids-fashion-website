/**
 * Enterprise Hero Banner Slider
 * Features:
 * - Auto-slide with configurable duration (4-6 seconds)
 * - Pause on hover/user interaction
 * - Swipe gestures for mobile
 * - Manual navigation (arrows/indicators)
 * - Smooth transitions
 * - Lazy loading support
 * - Performance optimized
 */

class HeroBannerSlider {
    constructor(container) {
        if (!container) {
            throw new Error('HeroBannerSlider: Container element is required');
        }
        
        this.container = container;
        this.slides = container.querySelectorAll('.hero-banner-slide');
        this.dots = container.querySelectorAll('.hero-pagination-dot');
        this.prevBtn = container.querySelector('.hero-nav-prev');
        this.nextBtn = container.querySelector('.hero-nav-next');
        
        this.currentSlide = 0;
        this.totalSlides = this.slides ? this.slides.length : 0;
        this.autoSlideEnabled = container.dataset.autoSlide === 'true';
        this.slideDuration = parseInt(container.dataset.slideDuration) || 5000;
        
        // Validate slide duration (must be between 4000-6000ms)
        if (this.slideDuration < 4000) this.slideDuration = 4000;
        if (this.slideDuration > 6000) this.slideDuration = 6000;
        
        this.autoSlideTimer = null;
        this.isPaused = false;
        
        // Touch/swipe handling
        this.touchStartX = 0;
        this.touchEndX = 0;
        this.minSwipeDistance = 50;
        
        this.init();
    }
    
    init() {
        if (this.totalSlides === 0) {
            console.warn('HeroBannerSlider: No slides found');
            return;
        }
        
        // Validate slides exist
        if (!this.slides || this.slides.length === 0) {
            console.error('HeroBannerSlider: Slides container is empty');
            return;
        }
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Start auto-slide if enabled
        if (this.autoSlideEnabled && this.totalSlides > 1) {
            this.startAutoSlide();
        }
        
        // Initialize first slide
        this.showSlide(0);
    }
    
    setupEventListeners() {
        // Navigation buttons
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevSlide());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextSlide());
        }
        
        // Pagination dots
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });
        
        // Pause on hover
        this.container.addEventListener('mouseenter', () => this.pauseAutoSlide());
        this.container.addEventListener('mouseleave', () => {
            if (!this.isPaused) {
                this.startAutoSlide();
            }
        });
        
        // Pause on touch/interaction
        this.container.addEventListener('touchstart', () => this.pauseAutoSlide());
        this.container.addEventListener('touchend', () => {
            // Resume after a delay
            setTimeout(() => {
                if (!this.isPaused) {
                    this.startAutoSlide();
                }
            }, 3000);
        });
        
        // Swipe gestures
        this.container.addEventListener('touchstart', (e) => this.handleTouchStart(e));
        this.container.addEventListener('touchmove', (e) => this.handleTouchMove(e));
        this.container.addEventListener('touchend', () => this.handleTouchEnd());
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.container.contains(document.activeElement) || 
                document.activeElement === document.body) {
                if (e.key === 'ArrowLeft') {
                    this.prevSlide();
                } else if (e.key === 'ArrowRight') {
                    this.nextSlide();
                }
            }
        });
        
        // Visibility API - pause when tab is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoSlide();
            } else if (!this.isPaused) {
                this.startAutoSlide();
            }
        });
    }
    
    showSlide(index) {
        // Validate index
        if (index < 0) index = this.totalSlides - 1;
        if (index >= this.totalSlides) index = 0;
        
        // Hide all slides
        this.slides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.add('active');
                slide.style.opacity = '1';
                slide.style.zIndex = '10';
            } else {
                slide.classList.remove('active');
                slide.style.opacity = '0';
                slide.style.zIndex = '1';
            }
        });
        
        // Update pagination dots
        this.dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('bg-white');
                dot.classList.remove('bg-white/50');
                dot.style.width = '2rem';
            } else {
                dot.classList.remove('bg-white');
                dot.classList.add('bg-white/50');
                dot.style.width = '0.75rem';
            }
        });
        
        this.currentSlide = index;
        
        // Lazy load next slide image if not already loaded
        const nextIndex = (index + 1) % this.totalSlides;
        const nextSlide = this.slides[nextIndex];
        if (nextSlide) {
            const img = nextSlide.querySelector('img');
            if (img && img.getAttribute('loading') === 'lazy') {
                img.loading = 'eager';
            }
        }
    }
    
    nextSlide() {
        this.showSlide(this.currentSlide + 1);
        this.restartAutoSlide();
    }
    
    prevSlide() {
        this.showSlide(this.currentSlide - 1);
        this.restartAutoSlide();
    }
    
    goToSlide(index) {
        this.showSlide(index);
        this.restartAutoSlide();
    }
    
    startAutoSlide() {
        if (!this.autoSlideEnabled || this.totalSlides <= 1) return;
        
        this.pauseAutoSlide(); // Clear any existing timer
        
        this.autoSlideTimer = setInterval(() => {
            if (!this.isPaused) {
                this.nextSlide();
            }
        }, this.slideDuration);
    }
    
    pauseAutoSlide() {
        if (this.autoSlideTimer) {
            clearInterval(this.autoSlideTimer);
            this.autoSlideTimer = null;
        }
    }
    
    restartAutoSlide() {
        this.pauseAutoSlide();
        if (this.autoSlideEnabled && !this.isPaused) {
            this.startAutoSlide();
        }
    }
    
    // Touch/Swipe handling
    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
    }
    
    handleTouchMove(e) {
        // Prevent default to avoid scrolling while swiping
        e.preventDefault();
    }
    
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        this.handleSwipe();
    }
    
    handleSwipe() {
        const swipeDistance = this.touchEndX - this.touchStartX;
        
        if (Math.abs(swipeDistance) > this.minSwipeDistance) {
            if (swipeDistance > 0) {
                // Swipe right - go to previous slide
                this.prevSlide();
            } else {
                // Swipe left - go to next slide
                this.nextSlide();
            }
        }
    }
}

// Initialize slider when DOM is ready
let heroBannerSlider = null;

function initHeroBannerSlider() {
    const heroSection = document.getElementById('hero-banner-slider');
    if (heroSection) {
        try {
            heroBannerSlider = new HeroBannerSlider(heroSection);
            
            // Make slider accessible globally for onclick handlers
            window.heroBannerSlider = heroBannerSlider;
        } catch (error) {
            console.error('HeroBannerSlider initialization error:', error);
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroBannerSlider);
} else {
    // DOM is already loaded
    initHeroBannerSlider();
}

// Export for module systems (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HeroBannerSlider;
}

