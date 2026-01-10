<?php
$pageTitle = 'My Wishlist';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8">My Wishlist</h2>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Your wishlist is empty</h3>
            <p class="text-gray-600 mb-6">Start adding products to your wishlist!</p>
            <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium inline-block">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>">
                        <?php if (!empty($item['primary_image'])): ?>
                            <img src="<?php echo SITE_URL . $item['primary_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-full h-64 object-cover">
                        <?php else: ?>
                            <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">
                            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>" 
                               class="hover:text-pink-600">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        </h3>
                        <div class="flex justify-between items-center">
                            <div>
                                <?php if (!empty($item['sale_price']) && $item['sale_price'] < $item['price']): ?>
                                    <span class="text-pink-600 font-bold">₹<?php echo number_format($item['sale_price'], 2); ?></span>
                                    <span class="text-gray-400 line-through text-sm ml-2">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-pink-600 font-bold">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <button onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)" 
                                    class="text-red-600 hover:text-red-800" title="Remove from wishlist">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
async function removeFromWishlist(productId) {
    const result = await showConfirm(
        'Remove from Wishlist',
        'Remove this item from your wishlist?',
        'Yes, Remove',
        'Cancel',
        'question'
    );
    
    if (!result.isConfirmed) {
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    
    const siteUrl = window.SITE_URL || '<?php echo SITE_URL; ?>';
    
    fetch(siteUrl + '/user/wishlistRemove', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
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
            
            showToast(data.message || 'Item removed from wishlist', 'success');
            // Reload after a short delay to show the toast
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to remove item from wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing from wishlist:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}
</script>

