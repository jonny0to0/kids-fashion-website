<?php
$productModel = new Product();
$price = $productModel->getPrice($product);
$originalPrice = $product['price'];
$hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$image = $product['primary_image'] ?? '/assets/images/placeholder.jpg';
$discountPercent = $hasDiscount ? round((($originalPrice - $price) / $originalPrice) * 100) : 0;

// Check if product is in wishlist
$inWishlist = false;
if (Session::isLoggedIn() && !Session::isAdmin()) {
    $wishlistModel = new Wishlist();
    $inWishlist = $wishlistModel->isInWishlist(Session::getUserId(), $product['product_id']);
}
?>
<div class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
    <!-- Image Container with Overlay Effects -->
    <div class="relative overflow-hidden bg-gray-100">
        <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>">
            <div class="relative aspect-square overflow-hidden">
                <img src="<?php echo SITE_URL . $image; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <!-- Gradient Overlay on Hover -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
        </a>
        
        <!-- Discount Badge -->
        <?php if ($hasDiscount): ?>
            <div class="absolute top-3 left-3 z-10">
                <span class="bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-lg">
                    -<?php echo $discountPercent; ?>%
                </span>
            </div>
        <?php endif; ?>
        
        <!-- Quick Action Buttons (Visible on Hover) -->
        <div class="absolute top-3 right-3 z-10 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <?php if (!Session::isAdmin()): ?>
                <button onclick="toggleWishlist(<?php echo $product['product_id']; ?>)" 
                        class="wishlist-btn-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> bg-white/90 backdrop-blur-sm hover:bg-white p-2.5 rounded-full shadow-lg transition-all duration-200 hover:scale-110"
                        title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                    <svg class="w-5 h-5 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'text-gray-700'; ?>" 
                         fill="<?php echo $inWishlist ? 'currentColor' : 'none'; ?>" 
                         stroke="currentColor" 
                         stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" 
               class="bg-white/90 backdrop-blur-sm hover:bg-white p-2.5 rounded-full shadow-lg transition-all duration-200 hover:scale-110"
               title="Quick View">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </a>
        </div>
        
        <!-- Add to Cart Button Overlay (Visible on Hover) -->
        <div class="absolute bottom-0 left-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
            <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                    class="w-full bg-gradient-to-r from-pink-600 to-rose-600 text-white py-2.5 rounded-lg font-semibold hover:from-pink-700 hover:to-rose-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Add to Cart
                </span>
            </button>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="p-4">
        <!-- Product Name -->
        <h3 class="font-semibold text-base mb-2 line-clamp-2 min-h-[3rem]">
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" 
               class="text-gray-800 hover:text-pink-600 transition-colors duration-200">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <!-- Price Section -->
        <div class="flex items-baseline gap-2 mb-3">
            <span class="text-2xl font-bold text-gray-900">₹<?php echo number_format($price, 0); ?></span>
            <?php if ($hasDiscount): ?>
                <span class="text-sm text-gray-500 line-through">₹<?php echo number_format($originalPrice, 0); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Customer Ratings & Stars -->
        <?php 
        $avgRating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 0;
        $reviewCount = isset($product['review_count']) ? intval($product['review_count']) : 0;
        $roundedRating = round($avgRating);
        ?>
        <div class="flex items-center gap-1.5 mb-3">
            <div class="flex items-center">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($avgRating > 0 && $i <= $roundedRating): ?>
                        <!-- Filled star -->
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php else: ?>
                        <!-- Empty star -->
                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php if ($avgRating > 0): ?>
                <span class="text-xs font-medium text-gray-700"><?php echo number_format($avgRating, 1); ?></span>
                <?php if ($reviewCount > 0): ?>
                    <span class="text-xs text-gray-500">(<?php echo $reviewCount; ?>)</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-xs text-gray-400">No ratings</span>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Add to Cart Button (Visible on small screens) -->
        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                class="w-full md:hidden bg-gradient-to-r from-pink-600 to-rose-600 text-white py-2.5 rounded-lg font-semibold hover:from-pink-700 hover:to-rose-700 transition-all duration-200 shadow-md hover:shadow-lg mt-2">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Add to Cart
            </span>
        </button>
    </div>
</div>

