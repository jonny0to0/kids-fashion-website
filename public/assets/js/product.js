/**
 * Product JavaScript
 * Handles product-specific functionality
 */

// Product image gallery
function initProductGallery() {
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.getElementById('main-image');
    
    if (thumbnails && mainImage) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImage.src = this.src;
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initProductGallery();
});

