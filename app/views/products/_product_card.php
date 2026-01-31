<?php
$productModel = new Product();
$price = $productModel->getPrice($product);
$originalPrice = $product['price'];
$hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$image = $product['primary_image'] ?? '/assets/images/no-image.png';
$discountPercent = $hasDiscount ? round((($originalPrice - $price) / $originalPrice) * 100) : 0;

// Check if product is in wishlist
$inWishlist = false;
if (Session::isLoggedIn() && !Session::isAdmin()) {
    $wishlistModel = new Wishlist();
    $inWishlist = $wishlistModel->isInWishlist(Session::getUserId(), $product['product_id']);
}
?>
<div class="product-card group bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden flex flex-col h-full w-full">
    <!-- Image Container (Fixed Aspect Ratio 4:5) -->
    <div class="relative w-full overflow-hidden bg-gray-50 product-card-image-container" style="padding-bottom: 125%;">
        <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" class="absolute inset-0 w-full h-full block">
            <img src="<?php echo SITE_URL . $image; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="w-full h-full object-cover object-center transition-transform duration-700 ease-in-out group-hover:scale-110"
                 loading="lazy"
                 onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                     <!-- w-full h-full  -->
            
            <!-- Gradient Overlay on Hover for better contrast -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </a>
        
        <!-- Badges Container (Safe Zone: Top Left) -->
        <div class="absolute top-3 left-3 z-10 flex flex-col gap-2 pointer-events-none">
            <?php if ($hasDiscount): ?>
                <span class="inline-flex items-center justify-center bg-gradient-to-r from-red-500 to-pink-500 text-white px-2.5 py-1 rounded-md text-xs font-bold shadow-md tracking-wide">
                    -<?php echo $discountPercent; ?>%
                </span>
            <?php endif; ?>
            
            <?php if (isset($product['is_new']) && $product['is_new']): ?>
                <span class="inline-flex items-center justify-center bg-blue-600 text-white px-2.5 py-1 rounded-md text-xs font-bold shadow-md tracking-wide">
                    NEW
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions (Safe Zone: Top Right - Visible on Hover) -->
        <div class="absolute top-3 right-3 z-10 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <?php if (!Session::isAdmin()): ?>
                <button onclick="toggleWishlist(<?php echo $product['product_id']; ?>)" 
                        class="wishlist-btn-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> w-9 h-9 flex items-center justify-center bg-white rounded-full shadow-lg hover:bg-pink-50 active:scale-95 transition-all duration-200"
                        title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                    <svg class="w-5 h-5 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'text-gray-700'; ?>" 
                         fill="<?php echo $inWishlist ? 'currentColor' : 'none'; ?>" 
                         stroke="currentColor" 
                         stroke-width="1.5"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            <?php endif; ?>
            
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" 
               class="w-9 h-9 flex items-center justify-center bg-white rounded-full shadow-lg hover:bg-pink-50 active:scale-95 transition-all duration-200"
               title="Quick View">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </a>
        </div>
        
        <!-- Add to Cart (Bottom Overlay - Slide Up on Hover) -->
        <div class="absolute bottom-0 left-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-full group-hover:translate-y-0 z-10">
            <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                    class="w-full bg-white/95 backdrop-blur-sm text-gray-900 py-2.5 rounded-lg font-semibold hover:bg-pink-600 hover:text-white transition-all duration-200 shadow-lg border border-gray-100 flex items-center justify-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Add to Cart
            </button>
        </div>
    </div>
    
    <!-- Content Section -->
    <div class="p-4 flex flex-col flex-grow">
        <!-- Brand Name (Optional) -->
        <?php if (!empty($product['brand'])): ?>
            <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wider truncate">
                <?php echo htmlspecialchars($product['brand']); ?>
            </p>
        <?php endif; ?>
        
        <!-- Product Name -->
        <h3 class="font-medium text-gray-900 text-[15px] leading-snug mb-2 line-clamp-2 min-h-[42px] group-hover:text-pink-600 transition-colors duration-200">
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <!-- Rating Stars -->
        <?php 
        $avgRating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 0;
        $reviewCount = isset($product['review_count']) ? intval($product['review_count']) : 0;
        ?>
        <div class="flex items-center gap-1 mb-3">
             <div class="flex text-yellow-400">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg class="w-3.5 h-3.5 <?php echo $i <= round($avgRating) ? 'fill-current' : 'text-gray-200'; ?>" 
                         viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                <?php endfor; ?>
            </div>
            <span class="text-xs text-gray-400 ml-1"><?php echo $reviewsCount ?? ""; ?></span>
        </div>
        
        <!-- Price & Actions Spacer -->
        <div class="mt-auto pt-2 border-t border-gray-50 flex items-center justify-between">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-gray-900">₹<?php echo number_format($price, 0); ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="text-xs text-gray-400 line-through">₹<?php echo number_format($originalPrice, 0); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mobile Add Cart Button (Visible only on mobile) -->
            <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                    class="md:hidden w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full text-gray-900 hover:bg-pink-600 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
