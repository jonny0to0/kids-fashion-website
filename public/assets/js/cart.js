/**
 * Cart JavaScript
 * Handles cart operations
 */

// Add to cart
async function addToCart(productId, variantId = null, quantity = 1) {
    try {
        const siteUrl = window.location.origin + '/kid-bazar-ecom/public';
        const formData = new FormData();
        formData.append('product_id', productId);
        if (variantId) {
            formData.append('variant_id', variantId);
        }
        formData.append('quantity', quantity);
        
        const response = await fetch(siteUrl + '/cart/add', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Item added to cart successfully!', 'success');
            // Update cart count after adding item
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        } else {
            showToast(data.message || 'Failed to add item to cart', 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Update cart item quantity
async function updateCartItem(cartItemId, quantity) {
    try {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', quantity);
        
        const siteUrl = window.location.origin + '/kid-bazar-ecom/public';
        const response = await fetch(siteUrl + '/cart/update', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update cart count before reloading
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            location.reload(); // Reload to update totals
        } else {
            showToast('Failed to update cart item', 'error');
        }
    } catch (error) {
        console.error('Error updating cart item:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Remove cart item
async function removeCartItem(cartItemId) {
    if (!confirm('Are you sure you want to remove this item from cart?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        
        const siteUrl = window.location.origin + '/kid-bazar-ecom/public';
        const response = await fetch(siteUrl + '/cart/remove', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Item removed from cart', 'success');
            // Update cart count before reloading
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            location.reload();
        } else {
            showToast('Failed to remove item', 'error');
        }
    } catch (error) {
        console.error('Error removing cart item:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Get cart count (AJAX endpoint handler)
if (window.location.pathname.includes('/cart/get-count')) {
    // This would typically be handled by a PHP controller
    // For now, it's a placeholder
}

