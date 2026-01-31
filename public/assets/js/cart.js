/**
 * Cart JavaScript
 * Handles cart operations
 */

// Add to cart
async function addToCart(productId, variantId = null, quantity = 1) {
    try {
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
            // Update cart count immediately from response
            if (typeof updateCartCount === 'function') {
                if (data.count !== undefined) {
                    // Update directly from response
                    const cartCountEl = document.getElementById('cart-count');
                    if (cartCountEl) {
                        cartCountEl.textContent = data.count;
                        cartCountEl.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                } else {
                    // Fallback to fetching count
                    updateCartCount();
                }
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
    // Validate quantity
    quantity = parseInt(quantity);
    if (isNaN(quantity) || quantity < 1) {
        showToast('Quantity must be at least 1', 'error');
        // Reset input to 1
        const inputEl = document.getElementById('quantity-' + cartItemId);
        if (inputEl) {
            inputEl.value = 1;
        }
        return;
    }
    
    // Show loading state
    const inputEl = document.getElementById('quantity-' + cartItemId);
    if (inputEl) {
        inputEl.disabled = true;
    }
    
    try {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', quantity);
        
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
        
        const response = await fetch(siteUrl + '/cart/update', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Update cart count from response
            if (data.count !== undefined) {
                const cartCountEl = document.getElementById('cart-count');
                if (cartCountEl) {
                    cartCountEl.textContent = data.count;
                    cartCountEl.style.display = data.count > 0 ? 'flex' : 'none';
                }
            } else if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            // Reload to update totals and item prices
            location.reload();
        } else {
            showToast(data.message || 'Failed to update cart item', 'error');
            // Reset input on error
            if (inputEl) {
                location.reload();
            }
        }
    } catch (error) {
        console.error('Error updating cart item:', error);
        showToast('An error occurred. Please try again.', 'error');
        // Reset input on error
        if (inputEl) {
            location.reload();
        }
    } finally {
        // Re-enable input
        if (inputEl) {
            inputEl.disabled = false;
        }
    }
}

// Update quantity (alias for updateCartItem for consistency)
function updateQuantity(cartItemId, quantity, maxQuantity = null) {
    // Validate quantity
    quantity = parseInt(quantity);
    if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
    }
    
    // Check max quantity if provided
    if (maxQuantity !== null && quantity > maxQuantity) {
        quantity = maxQuantity;
        // Update the input field to reflect the max
        const inputEl = document.getElementById('quantity-' + cartItemId);
        if (inputEl) {
            inputEl.value = maxQuantity;
        }
        showToast('Maximum quantity reached', 'warning');
    }
    
    updateCartItem(cartItemId, quantity);
}

// Decrease quantity
function decreaseQuantity(cartItemId, maxQuantity = null) {
    const inputEl = document.getElementById('quantity-' + cartItemId);
    if (!inputEl) {
        showToast('Quantity input not found', 'error');
        return;
    }
    
    let currentQty = parseInt(inputEl.value) || 1;
    if (currentQty > 1) {
        currentQty--;
        inputEl.value = currentQty;
        updateCartItem(cartItemId, currentQty);
    }
}

// Increase quantity
function increaseQuantity(cartItemId, maxQuantity = null) {
    const inputEl = document.getElementById('quantity-' + cartItemId);
    if (!inputEl) {
        showToast('Quantity input not found', 'error');
        return;
    }
    
    let currentQty = parseInt(inputEl.value) || 1;
    const maxQty = maxQuantity !== null ? parseInt(maxQuantity) : 999;
    
    if (currentQty < maxQty) {
        currentQty++;
        inputEl.value = currentQty;
        updateCartItem(cartItemId, currentQty);
    } else {
        showToast('Maximum quantity reached', 'warning');
    }
}

// Remove cart item
async function removeCartItem(cartItemId) {
    const result = await showConfirm(
        'Remove Item',
        'Are you sure you want to remove this item from cart?',
        'Yes, Remove',
        'Cancel',
        'warning'
    );
    
    if (!result.isConfirmed) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        
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
        const response = await fetch(siteUrl + '/cart/remove', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Item removed from cart', 'success');
            // Update cart count from response
            if (data.count !== undefined) {
                const cartCountEl = document.getElementById('cart-count');
                if (cartCountEl) {
                    cartCountEl.textContent = data.count;
                    cartCountEl.style.display = data.count > 0 ? 'flex' : 'none';
                }
            } else if (typeof updateCartCount === 'function') {
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

// Save item for later (move to wishlist)
async function saveForLater(cartItemId) {
    if (!cartItemId) {
        console.error('Cart item ID is required');
        showToast('Invalid cart item', 'error');
        return;
    }
    
    console.log('Saving item for later:', cartItemId);
    
    try {
        const formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        
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
        
        const url = siteUrl + '/cart/save-for-later';
        console.log('Fetching URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            // Try to parse error response
            let errorMessage = 'Failed to save item for later';
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorMessage;
                console.error('Error response:', errorData);
            } catch (e) {
                errorMessage = `Server error: ${response.status}`;
                console.error('Failed to parse error response:', e);
            }
            showToast(errorMessage, 'error');
            return;
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showToast('Item saved for later', 'success');
            // Update cart count from response
            if (data.count !== undefined) {
                const cartCountEl = document.getElementById('cart-count');
                if (cartCountEl) {
                    cartCountEl.textContent = data.count;
                    cartCountEl.style.display = data.count > 0 ? 'flex' : 'none';
                }
            } else if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            // Reload page to remove item from cart display
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to save item for later', 'error');
        }
    } catch (error) {
        console.error('Error saving item for later:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Get cart count (AJAX endpoint handler)
if (window.location.pathname.includes('/cart/get-count')) {
    // This would typically be handled by a PHP controller
    // For now, it's a placeholder
}

