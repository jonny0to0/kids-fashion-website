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

// Quantity controls
function increaseQuantity(input) {
    const max = parseInt(input.getAttribute('max')) || 999;
    const current = parseInt(input.value) || 1;
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity(input) {
    const min = parseInt(input.getAttribute('min')) || 1;
    const current = parseInt(input.value) || 1;
    if (current > min) {
        input.value = current - 1;
    }
}

