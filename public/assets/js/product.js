/**
 * Product JavaScript
 * Handles product-specific functionality including Image Gallery Synchronization
 */

class ZoomHandler {
    constructor() {
        this.mainImage = document.getElementById('main-image');
        this.zoomWrapper = document.getElementById('zoom-wrapper');
        this.zoomLens = document.getElementById('zoom-lens');
        this.zoomResult = document.getElementById('zoom-result');
        this.zoomResultImg = this.zoomResult ? this.zoomResult.querySelector('img') : null;

        // Configuration
        this.lensSize = 100; // px
        this.cx = 1;
        this.cy = 1;

        // Binds
        this.moveLens = this.moveLens.bind(this);
        this.openZoom = this.openZoom.bind(this);
        this.closeZoom = this.closeZoom.bind(this);

        this.init();
    }

    init() {
        console.log('ZoomHandler: Init started');
        if (!this.zoomWrapper) console.error('ZoomHandler: zoomWrapper missing');
        if (!this.zoomResult) console.error('ZoomHandler: zoomResult missing');
        if (!this.zoomLens) console.error('ZoomHandler: zoomLens missing');
        if (!this.mainImage) console.error('ZoomHandler: mainImage missing');

        if (!this.zoomWrapper || !this.zoomResult || !this.zoomLens || !this.mainImage) {
            console.error('ZoomHandler: Init aborted due to missing elements');
            return;
        }

        // Events
        this.zoomWrapper.addEventListener('mouseenter', this.openZoom);
        this.zoomWrapper.addEventListener('mouseleave', this.closeZoom);
        this.zoomWrapper.addEventListener('mousemove', this.moveLens);

        console.log('ZoomHandler: Events attached');

        // Initial Zoom Image Check
        this.checkZoomAvailability();
    }

    checkZoomAvailability() {
        if (!this.mainImage) return;
        const zoomSrc = this.mainImage.dataset.zoomImage;
        console.log('ZoomHandler: Checking availability, source:', zoomSrc);

        // Enable zoom only if a specific high-res zoom image is available
        if (zoomSrc) {
            this.isZoomEnabled = true;
            console.log('ZoomHandler: Zoom Enabled');
            if (this.zoomResultImg) {
                this.zoomResultImg.src = zoomSrc;
                this.zoomResultImg.onload = () => {
                    console.log('ZoomHandler: Zoom image loaded');
                    this.zoomResultImg.style.width = 'auto';
                    this.zoomResultImg.style.height = 'auto';
                    this.zoomResultImg.style.maxWidth = 'none';
                    this.zoomResultImg.style.maxHeight = 'none';
                };
            }

            this.zoomWrapper.style.cursor = 'crosshair';
        } else {
            console.warn('ZoomHandler: Zoom Disabled (no source)');
            this.isZoomEnabled = false;
            this.zoomWrapper.style.cursor = 'default';
        }
    }

    updateZoomImage(zoomSrc) {
        if (this.zoomResultImg) {
            // If new zoom source is empty, disable zoom
            if (!zoomSrc) {
                this.isZoomEnabled = false;
                this.zoomWrapper.style.cursor = 'default';
                this.zoomResultImg.src = '';
            } else {
                this.isZoomEnabled = true;
                this.zoomWrapper.style.cursor = 'crosshair';
                // Preload image
                const img = new Image();
                img.src = zoomSrc;
                img.onload = () => {
                    if (this.zoomResultImg) {
                        this.zoomResultImg.src = zoomSrc;
                        // Force natural dimensions to prevent CSS squashing
                        this.zoomResultImg.style.width = 'auto';
                        this.zoomResultImg.style.height = 'auto';
                        this.zoomResultImg.style.maxWidth = 'none';
                        this.zoomResultImg.style.maxHeight = 'none';
                    }
                };
                // Set immediately as well to start loading
                this.zoomResultImg.src = zoomSrc;
            }
            // Update data attribute on main image for consistency
            if (this.mainImage) {
                this.mainImage.dataset.zoomImage = zoomSrc || '';
            }
        }
    }

    openZoom() {
        if (!this.isZoomEnabled) return;

        console.log('ZoomHandler: Opening Zoom Box');
        this.zoomResult.classList.remove('hidden');

        // NUCLEAR OPTION: Force styles with !important
        this.zoomResult.style.setProperty('display', 'block', 'important');
        this.zoomResult.style.setProperty('visibility', 'visible', 'important');
        this.zoomResult.style.setProperty('opacity', '1', 'important');
        this.zoomResult.style.setProperty('z-index', '1000', 'important');
        this.zoomResult.style.setProperty('width', '550px', 'important');
        this.zoomResult.style.setProperty('height', '550px', 'important');

        // Debug: Check if element actually has size
        const rect = this.zoomResult.getBoundingClientRect();
        console.log('ZoomHandler: Zoom Box Dimensions:', rect.width, 'x', rect.height, 'Top:', rect.top, 'Left:', rect.left);
        console.log('ZoomHandler: Computed Display:', window.getComputedStyle(this.zoomResult).display);

        this.zoomLens.classList.remove('hidden');
        this.zoomLens.style.display = 'block';

        // Calculate Lens Size dynamically based on the ratio
        // Lens covers the area on main image that the zoom window shows of the large image

        const mainRect = this.mainImage.getBoundingClientRect();

        // Ensure accurate zoom box dimensions
        const zoomRect = this.zoomResult.getBoundingClientRect();
        const boxW = zoomRect.width;
        const boxH = zoomRect.height;

        // Use natural dimensions of the high-res image
        const largeW = this.zoomResultImg.naturalWidth;
        const largeH = this.zoomResultImg.naturalHeight;

        if (largeW > 0 && largeH > 0 && mainRect.width > 0 && mainRect.height > 0) {
            const scaleX = mainRect.width / largeW;
            const scaleY = mainRect.height / largeH;

            // Ideal lens size
            let finalLensW = boxW * scaleX;
            let finalLensH = boxH * scaleY;

            // Clamp lens size if it exceeds main image (which means no zoom or zoom out)
            if (finalLensW > mainRect.width) finalLensW = mainRect.width;
            if (finalLensH > mainRect.height) finalLensH = mainRect.height;

            // Override constrained/calculated size with fixed size as per user request
            // This effectively increases the zoom power (magnification)
            this.zoomLens.style.width = '70px';
            this.zoomLens.style.height = '70px';
        } else {
            // Fallback if image not fully loaded
            this.zoomLens.style.width = '70px';
            this.zoomLens.style.height = '70px';
        }

        // Ensure image is styled correctly for zoom
        if (this.zoomResultImg) {
            this.zoomResultImg.style.width = 'auto';
            this.zoomResultImg.style.height = 'auto';
            this.zoomResultImg.style.maxWidth = 'none';
            this.zoomResultImg.style.maxHeight = 'none';
        }
    }

    closeZoom() {
        // console.log('Zoom: Closing');
        this.zoomResult.classList.add('hidden');
        this.zoomResult.style.display = 'none';
        this.zoomLens.classList.add('hidden');
        this.zoomLens.style.display = 'none';
    }

    moveLens(e) {
        if (!this.isZoomEnabled) return;

        const mainRect = this.mainImage.getBoundingClientRect();

        // Cursor position relative to image
        let x = e.clientX - mainRect.left;
        let y = e.clientY - mainRect.top;

        const lensW = this.zoomLens.offsetWidth;
        const lensH = this.zoomLens.offsetHeight;

        // Center lens on cursor
        let lensX = x - (lensW / 2);
        let lensY = y - (lensH / 2);

        // Boundary checks
        if (lensX > mainRect.width - lensW) lensX = mainRect.width - lensW;
        if (lensX < 0) lensX = 0;
        if (lensY > mainRect.height - lensH) lensY = mainRect.height - lensH;
        if (lensY < 0) lensY = 0;

        // Apply Position to Lens (Smoother with transform)
        this.zoomLens.style.transform = `translate3d(${lensX}px, ${lensY}px, 0)`;
        this.zoomLens.style.left = '0';
        this.zoomLens.style.top = '0';

        // Move Zoom Image
        // Percentage position of lens within the available moveable area
        // Available Moveable Area = MainImageWidth - LensWidth
        const maxLensMoveX = mainRect.width - lensW;
        const maxLensMoveY = mainRect.height - lensH;

        // Protect against divide by zero
        const xPercent = maxLensMoveX > 0 ? lensX / maxLensMoveX : 0;
        const yPercent = maxLensMoveY > 0 ? lensY / maxLensMoveY : 0;

        // Calculate translation for High Res Image
        // We move the image in the opposite direction
        // The total moveable distance for the image is ImageWidth - ResultBoxWidth

        const imgW = this.zoomResultImg.offsetWidth || this.zoomResultImg.naturalWidth;
        const imgH = this.zoomResultImg.offsetHeight || this.zoomResultImg.naturalHeight;
        const boxW = this.zoomResult.offsetWidth;
        const boxH = this.zoomResult.offsetHeight;

        const maxTranslateX = imgW - boxW;
        const maxTranslateY = imgH - boxH;

        if (maxTranslateX > 0 && maxTranslateY > 0) {
            const moveX = xPercent * maxTranslateX;
            const moveY = yPercent * maxTranslateY;
            this.zoomResultImg.style.transform = `translate3d(-${moveX}px, -${moveY}px, 0)`;
        } else {
            // If zoom image is smaller than result box (rare but possible), just center it
            // DO NOT SCALE UP - this causes blur
            const centerX = (boxW - imgW) / 2;
            const centerY = (boxH - imgH) / 2;
            this.zoomResultImg.style.transform = `translate3d(${centerX > 0 ? centerX : 0}px, ${centerY > 0 ? centerY : 0}px, 0)`;
        }
    }
}

class ProductGallery {
    constructor() {
        // Elements
        this.desktopMainImage = document.getElementById('main-image');
        this.desktopThumbnails = document.querySelectorAll('.product-thumbnail');
        this.desktopThumbContainer = document.getElementById('thumbnail-gallery-desktop');

        this.mobileMainGallery = document.getElementById('product-main-gallery-mobile');
        this.mobileThumbnails = document.querySelectorAll('.product-thumbnail-mobile');
        this.mobileThumbContainer = document.getElementById('thumbnail-gallery-mobile');

        // State
        this.currentIndex = 0;
        this.totalImages = this.desktopThumbnails.length > 0 ? this.desktopThumbnails.length :
            (this.mobileMainGallery ? this.mobileMainGallery.querySelectorAll('[data-index]').length : 0);
        this.autoSlideInterval = null;
        this.isUserInteracting = false;

        // Zoom Handler
        this.zoomHandler = new ZoomHandler();

        this.init();
    }

    init() {
        if (this.totalImages === 0) return;

        // Desktop Thumbnail Clicks & Hover
        this.desktopThumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', (e) => {
                e.preventDefault();
                this.updateGallery(index, 'click');
            });

            // Optional: Hover support (Amazon style)
            thumb.addEventListener('mouseenter', () => {
                this.updateGallery(index, 'hover');
            });
        });

        // Mobile Thumbnail Clicks
        this.mobileThumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', (e) => {
                e.preventDefault();
                this.updateGallery(index, 'click');
            });
        });

        // Mobile Main Gallery Scroll (Swipe) Detection
        if (this.mobileMainGallery) {
            let isScrolling;
            this.mobileMainGallery.addEventListener('scroll', () => {
                // Clear timeout throughout the scroll
                window.clearTimeout(isScrolling);
                this.isUserInteracting = true;
                this.pauseAutoSlide();

                // Set a timeout to run after scrolling ends
                isScrolling = setTimeout(() => {
                    this.handleMobileScroll();
                    this.isUserInteracting = false;
                    this.startAutoSlide();
                }, 100); // 100ms after scroll stops
            }, { passive: true });
        }

        // Keyboard Navigation (Accessibility)
        document.addEventListener('keydown', (e) => {
            if (document.activeElement.closest('.thumbnail-gallery-vertical') ||
                document.activeElement.closest('#thumbnail-gallery-mobile')) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.nextImage();
                } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.prevImage();
                }
            }
        });

        // Start Auto Slide
        this.startAutoSlide();
    }

    updateGallery(index, source = 'script') {
        if (index < 0 || index >= this.totalImages) return;
        this.currentIndex = index;

        // 1. Update Desktop Main Image
        if (this.desktopMainImage) {
            // Find the image URL from one of the thumbnails or data attributes
            // We can get it from the desktop thumbnail at this index
            const targetThumb = this.desktopThumbnails[index];
            if (targetThumb) {
                const newSrc = targetThumb.dataset.imageUrl;
                const newZoomSrc = targetThumb.dataset.zoomImage;

                if (newSrc && this.desktopMainImage.src !== newSrc) {
                    this.desktopMainImage.src = newSrc;
                    // Update Zoom Source
                    this.zoomHandler.updateZoomImage(newZoomSrc);
                }
            }
        }

        // 2. Update Active State on Desktop Thumbnails
        this.desktopThumbnails.forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.add('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
                thumb.classList.remove('border-gray-300', 'hover:border-pink-400', 'hover:shadow-sm');
                thumb.setAttribute('aria-pressed', 'true');
                // Scroll thumbnail into view if needed
                if (source !== 'desktop-scroll') {
                    this.scrollThumbnailIntoView(thumb, this.desktopThumbContainer);
                }
            } else {
                thumb.classList.remove('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
                thumb.classList.add('border-gray-300', 'hover:border-pink-400', 'hover:shadow-sm');
                thumb.setAttribute('aria-pressed', 'false');
            }
        });

        // 3. Update Mobile Main Gallery Scroll Position
        // If the update didn't come from the mobile scroll itself, scroll to it
        if (source !== 'mobile-scroll' && this.mobileMainGallery) {
            const mobileSlide = this.mobileMainGallery.children[index];
            if (mobileSlide) {
                // Use scrollTo on the container instead of scrollIntoView on the element
                // This prevents the page from jumping if the slider is out of viewport
                this.mobileMainGallery.scrollTo({
                    left: index * this.mobileMainGallery.offsetWidth,
                    behavior: 'smooth'
                });
            }
        }

        // 4. Update Active State on Mobile Thumbnails
        this.mobileThumbnails.forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.add('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
                thumb.classList.remove('border-gray-300');
                // Scroll thumbnail into view
                if (source !== 'mobile-thumb-scroll') {
                    this.scrollThumbnailIntoView(thumb, this.mobileThumbContainer);
                }
            } else {
                thumb.classList.remove('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
                thumb.classList.add('border-gray-300');
            }
        });

        // Update Counter
        const counter = document.getElementById('mobile-gallery-counter');
        if (counter) {
            counter.textContent = `${index + 1}/${this.totalImages}`;
        }
    }

    handleMobileScroll() {
        if (!this.mobileMainGallery) return;

        // Determine which slide is currently most visible
        const scrollLeft = this.mobileMainGallery.scrollLeft;
        const width = this.mobileMainGallery.offsetWidth;
        // Simple calculation: round to nearest index
        const index = Math.round(scrollLeft / width);

        if (index !== this.currentIndex) {
            this.updateGallery(index, 'mobile-scroll');
        }
    }

    scrollThumbnailIntoView(thumbnail, container) {
        if (!thumbnail || !container) return;

        const thumbLeft = thumbnail.offsetLeft;
        const thumbTop = thumbnail.offsetTop;
        const thumbWidth = thumbnail.offsetWidth;
        const thumbHeight = thumbnail.offsetHeight;

        const containerScrollLeft = container.scrollLeft;
        const containerScrollTop = container.scrollTop;
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;

        // Check if out of view (Horizontal)
        if (container.scrollWidth > container.clientWidth) {
            // Horizontal scrolling (Desktop thumbnails)
            const isVisible = (thumbLeft >= containerScrollLeft) &&
                (thumbLeft + thumbWidth <= containerScrollLeft + containerWidth);

            if (!isVisible) {
                // Scroll to center
                const targetScroll = thumbLeft - (containerWidth / 2) + (thumbWidth / 2);
                container.scrollTo({
                    left: targetScroll,
                    behavior: 'smooth'
                });
            }
        }
        // Check if out of view (Vertical)
        else if (container.scrollHeight > container.clientHeight) {
            // Vertical scrolling (Desktop thumbnails)
            const isVisible = (thumbTop >= containerScrollTop) &&
                (thumbTop + thumbHeight <= containerScrollTop + containerHeight);

            if (!isVisible) {
                // Scroll to center
                const targetScroll = thumbTop - (containerHeight / 2) + (thumbHeight / 2);
                container.scrollTo({
                    top: targetScroll,
                    behavior: 'smooth'
                });
            }
        }
    }

    nextImage() {
        const nextIndex = (this.currentIndex + 1) % this.totalImages;
        this.updateGallery(nextIndex, 'auto');
    }

    prevImage() {
        const prevIndex = (this.currentIndex - 1 + this.totalImages) % this.totalImages;
        this.updateGallery(prevIndex, 'auto');
    }

    startAutoSlide() {
        // Clear existing to avoid duplicates
        this.pauseAutoSlide();

        // Auto slide ONLY on mobile (< 768px)
        if (window.innerWidth >= 768) return;

        // Auto slide every 5 seconds
        if (this.totalImages > 1) {
            this.autoSlideInterval = setInterval(() => {
                if (!this.isUserInteracting) {
                    this.nextImage();
                }
            }, 5000);
        }
    }

    pauseAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }
}

/* Expandable Content Logic */
class ContentExpander {
    constructor() {
        this.expanders = document.querySelectorAll('.expandable-wrapper');
        this.init();
    }

    init() {
        this.expanders.forEach(wrapper => {
            const content = wrapper.querySelector('.expandable-content');
            const button = wrapper.querySelector('.see-more-btn');
            const type = wrapper.dataset.type; // list, table, text
            const count = parseInt(wrapper.dataset.visibleItems || wrapper.dataset.visibleRows || wrapper.dataset.visibleLines || 3);

            if (!content || !button) return;

            // Wait for images or content to load if necessary, but typically text is ready
            // For robust height calculation, we should ensure the element is in the DOM and rendered

            // Calculate collapsed height
            let collapsedHeight = 0;

            if (type === 'list') {
                const items = content.querySelectorAll('li');
                if (items.length <= count) {
                    button.style.display = 'none';
                    return;
                }
                for (let i = 0; i < Math.min(items.length, count); i++) {
                    collapsedHeight += items[i].offsetHeight;
                }
                // Add margins/gaps if needed (space-y-1.5 = 6px)
                if (count > 1) collapsedHeight += (count - 1) * 6;
                collapsedHeight += 4; // Buffer
            } else if (type === 'table') {
                const rows = content.querySelectorAll('tr');
                if (rows.length <= count) {
                    button.style.display = 'none';
                    return;
                }
                for (let i = 0; i < Math.min(rows.length, count); i++) {
                    collapsedHeight += rows[i].offsetHeight;
                }
            } else if (type === 'text') {
                // Approximate line height for sm text (14px) ~21-24px
                const lineHeight = 24;
                collapsedHeight = lineHeight * count;

                if (content.scrollHeight <= collapsedHeight + 10) { // +10 buffer
                    button.style.display = 'none';
                    return;
                }
            }

            // Set initial state
            wrapper.classList.add('collapsed');
            content.style.maxHeight = collapsedHeight + 'px';
            wrapper.dataset.collapsedHeight = collapsedHeight;
        });
    }

    toggle(button) {
        const wrapper = button.closest('.expandable-wrapper');
        const content = wrapper.querySelector('.expandable-content');
        const textSpan = button.querySelector('.see-more-text');
        const isCollapsed = wrapper.classList.contains('collapsed');

        if (isCollapsed) {
            // Expand
            wrapper.classList.remove('collapsed');
            content.style.maxHeight = content.scrollHeight + 'px';
            button.classList.add('expanded');
            if (textSpan) textSpan.innerText = 'See Less';
        } else {
            // Collapse
            wrapper.classList.add('collapsed');
            content.style.maxHeight = wrapper.dataset.collapsedHeight + 'px';
            button.classList.remove('expanded');
            if (textSpan) textSpan.innerText = 'See More';

            // Scroll handling: reset view if top of section is out of viewport
            const rect = wrapper.getBoundingClientRect();
            // 100px buffer for header
            if (rect.top < 100) {
                const scrollTop = window.pageYOffset + rect.top - 100;
                window.scrollTo({ top: scrollTop, behavior: 'smooth' });
            }
        }
    }
}

// Global function for onclick
window.toggleExpansion = function (btn) {
    if (window.contentExpander) {
        window.contentExpander.toggle(btn);
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    window.productGallery = new ProductGallery();
    window.contentExpander = new ContentExpander();
});

/**
 * Mobile Image Zoom Modal
 */
window.openImageZoomModal = function () {
    // Check if modal exists
    let modal = document.getElementById('image-zoom-modal');
    const gallery = window.productGallery;

    if (!gallery) return;

    const index = gallery.currentIndex || 0;
    let imageUrl = '';

    // Try mobile gallery first (find active slide's image)
    if (gallery.mobileMainGallery) {
        // Mobile gallery uses scroll position, but we track currentIndex
        // Better to get the image from the data source to get the highest res possible? 
        // Actually, usually `zoom_image_url` is best, but for mobile full screen, the `image_url` (Main) is often enough 
        // unless we want to load the super-high-res zoom image. 
        // Let's us the zoom image if available, else standard image.

        // Check thumbnails for data
        if (gallery.desktopThumbnails && gallery.desktopThumbnails[index]) {
            imageUrl = gallery.desktopThumbnails[index].dataset.zoomImage || gallery.desktopThumbnails[index].dataset.imageUrl;
        }
    }

    // Fallback
    if (!imageUrl) {
        // Try getting it from the active active slide directly
        const slides = document.querySelectorAll('#product-main-gallery-mobile [data-index]');
        if (slides[index]) {
            const img = slides[index].querySelector('img');
            if (img) imageUrl = img.src;
        }
    }

    if (!imageUrl) return;

    if (!modal) {
        // Create modal
        modal = document.createElement('div');
        modal.id = 'image-zoom-modal';
        modal.className = 'fixed inset-0 z-[100] bg-black bg-opacity-95 flex items-center justify-center p-2 hidden opacity-0 transition-opacity duration-300';
        modal.innerHTML = `
                <div class="relative w-full h-full flex items-center justify-center overflow-hidden">
                    <img src="" id="modal-zoom-image" class="max-w-full max-h-full object-contain transform transition-transform duration-300 scale-100">
                    <button id="modal-close-btn" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 focus:outline-none z-10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
        document.body.appendChild(modal);

        // Close event
        modal.querySelector('#modal-close-btn').addEventListener('click', window.closeImageZoomModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('.relative') === modal.firstElementChild) {
                window.closeImageZoomModal();
            }
        });

        // Basic Pan/Zoom for Mobile could be added here (Pinch to zoom)
        // For now, simpler is better.
    }

    const modalImg = modal.querySelector('#modal-zoom-image');
    modalImg.src = imageUrl;

    modal.classList.remove('hidden');
    // small delay to allow display:block to apply before opacity transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
    }, 10);
};

window.closeImageZoomModal = function () {
    const modal = document.getElementById('image-zoom-modal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        // Clear source to save memory
        const modalImg = modal.querySelector('#modal-zoom-image');
        if (modalImg) modalImg.src = '';
    }, 300);
};

/**
 * Review Eligibility Check
 */
document.addEventListener('DOMContentLoaded', function () {
    const reviewBtn = document.getElementById('writeReviewBtn');
    if (reviewBtn) {
        reviewBtn.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const siteUrl = window.SITE_URL || '';

            // Show loading state
            const originalText = this.innerText;
            const originalOpacity = this.style.opacity;
            this.innerText = 'Checking...';
            this.style.opacity = '0.7';
            this.disabled = true;

            fetch(`${siteUrl}/product/check_eligibility?product_id=${productId}`)
                .then(res => res.json())
                .then(data => {
                    this.innerText = originalText;
                    this.style.opacity = originalOpacity || '1';
                    this.disabled = false;

                    if (data.already_reviewed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Already Reviewed',
                            text: 'You have already submitted a review for this product.',
                            confirmButtonColor: '#EC4899'
                        });
                        return;
                    }

                    if (!data.logged_in) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Login Required',
                            text: 'Please login to write a review.',
                            showCancelButton: true,
                            confirmButtonText: 'Login Now',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#EC4899',
                            cancelButtonColor: '#6B7280'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `${siteUrl}/user/login?redirect=${encodeURIComponent(window.location.href)}`;
                            }
                        });
                        return;
                    }

                    if (!data.purchased) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Purchase Required',
                            text: 'You must purchase this product to write a review.',
                            confirmButtonColor: '#EC4899'
                        });
                        return;
                    }

                    if (!data.delivered) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Order Not Delivered',
                            text: 'You can only review this product after it has been delivered.',
                            confirmButtonColor: '#EC4899'
                        });
                        return;
                    }

                    // Eligible - Show Modal
                    const modal = document.getElementById('review-form-modal');
                    if (modal) {
                        if (data.order_id) {
                            const orderInput = modal.querySelector('input[name="order_id"]');
                            if (orderInput) orderInput.value = data.order_id;
                        }
                        modal.classList.remove('hidden');
                    } else {
                        console.error('Review modal not found');
                    }
                })
                .catch(err => {
                    console.error('Error checking eligibility:', err);
                    this.innerText = originalText;
                    this.style.opacity = originalOpacity || '1';
                    this.disabled = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again later.',
                        confirmButtonColor: '#EC4899'
                    });
                });
        });
    }
});
