<?php
// Use cached service if available to avoid re-instantiation in loops
global $badgeService;
if (!isset($badgeService)) {
    require_once APP_PATH . '/services/BadgeService.php';
    $badgeService = new BadgeService();
}

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

// Get badges using the new service
$badges = $badgeService->getBadges($product);
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
        <div class="absolute top-2 left-2 z-10 flex flex-col gap-2 pointer-events-none">
            <?php foreach ($badges as $badge): ?>
                <span class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold shadow-md tracking-wide <?php echo $badge['class']; ?>">
                    <?php if ($badge['id'] === 'rating'): ?>
                         <svg class="w-3 h-3 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                         </svg>
                    <?php endif; ?>
                    <?php echo $badge['label']; ?>
                </span>
            <?php endforeach; ?>
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
                    class="w-full bg-pink-600 text-white py-2.5 rounded-lg font-semibold hover:bg-white hover:text-pink-600 transition-all duration-200 shadow-lg border border-pink-600 hover:border-pink-600 flex items-center justify-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Add to Cart
            </button>
        </div>
    </div>
    
    <!-- Content Section -->
    <div class="p-3 flex flex-col flex-grow bg-white relative">
        <!-- Brand (Classic & Clean) -->
        <?php if (!empty($product['brand'])): ?>
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1 font-sans">
                <?php echo htmlspecialchars($product['brand']); ?>
            </p>
        <?php else: ?>
             <!-- <div class="h-3"></div> -->
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1 font-sans">
                Kids bazaar
            </p>
        <?php endif; ?>
        
        <!-- Product Name (Modern & Readable) -->
        <h3 class="font-medium text-gray-900 text-[15px] leading-tight mb-2 line-clamp-2 h-[38px] group-hover:text-pink-600 transition-colors duration-200">
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <!-- Price & Rating Row (Trendy & Compact) -->
        <div class="mt-auto border-t border-dashed border-gray-100 flex items-center justify-between">
            <!-- Price Section -->
            <div class="flex flex-col relative top-0.5">
                 <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-gray-900 font-sans tracking-tight">₹<?php echo number_format($price, 0); ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="text-sm text-gray-500 line-through decoration-gray-400 font-semibold tracking-widest">₹<?php echo number_format($originalPrice, 0); ?></span>
                    <?php endif; ?>
                </div>
            </div>

             <!-- Rating Section (Pill Style) -->
             <?php 
            $avgRating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 0;
            ?>
            <div class="flex items-center gap-1.5 bg-white text-gray-700 px-2.5 py-1 rounded-full text-xs font-bold border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                 <svg class="w-3.5 h-3.5 text-yellow-400 fill-current drop-shadow-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                 </svg>
                 <span class="relative top-px"><?php echo number_format($avgRating, 1); ?></span>
            </div>
            
            <!-- Mobile Add Cart Button (Overlay) -->
            <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                    class="md:hidden absolute bottom-3 right-3 w-8 h-8 flex items-center justify-center bg-gray-900 rounded-full text-white shadow-lg hover:bg-pink-600 transition-colors z-20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
