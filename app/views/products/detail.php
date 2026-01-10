<?php
// Check if product is in wishlist
$inWishlist = false;
if (Session::isLoggedIn() && !Session::isAdmin()) {
    $wishlistModel = new Wishlist();
    $inWishlist = $wishlistModel->isInWishlist(Session::getUserId(), $product['product_id']);
}

// Get product pricing
$productModel = new Product();
$price = $productModel->getPrice($product);
$hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$discountPercent = $hasDiscount ? round((($product['price'] - $price) / $product['price']) * 100) : 0;

// Organize variants by color and size
$colors = [];
$sizes = [];
$variantMap = [];
if (!empty($variants)) {
    foreach ($variants as $variant) {
        if (!empty($variant['color'])) {
            if (!isset($colors[$variant['color']])) {
                $colors[$variant['color']] = [];
            }
            $colors[$variant['color']][] = $variant;
        }
        if (!empty($variant['size'])) {
            if (!in_array($variant['size'], $sizes)) {
                $sizes[] = $variant['size'];
            }
        }
        $variantMap[$variant['variant_id']] = $variant;
    }
}
sort($sizes); // Sort sizes logically
?>

<div class="container mx-auto px-4 py-4 md:py-6 pb-24 lg:pb-6 max-w-7xl">
    <!-- Mobile: Product Title (Shown First on Mobile) -->
    <div class="lg:hidden mb-4">
        <h1 class="text-xl font-semibold text-gray-900 leading-tight mb-2">
            <?php echo htmlspecialchars($product['name']); ?>
        </h1>
    </div>

    <!-- Main Product Section: Amazon-Style 3-Column Layout (Desktop) / Single Column (Mobile) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 lg:gap-6 p-3 md:p-6">
            
            <!-- COLUMN 1: Thumbnail Gallery (Left - Desktop Only) - Amazon-Style Compact -->
            <?php if (!empty($images) && count($images) > 1): ?>
                <div class="hidden xl:block xl:col-span-2 order-1">
                    <div class="thumbnail-gallery-vertical sticky top-4 max-h-[600px] overflow-y-auto pr-3 scrollbar-thin">
                        <?php foreach ($images as $index => $image): ?>
                            <button onmouseenter="changeMainImageOnHover('<?php echo SITE_URL . $image['image_url']; ?>', <?php echo $index; ?>)" 
                                    onclick="changeMainImage('<?php echo SITE_URL . $image['image_url']; ?>', <?php echo $index; ?>)" 
                                    onkeydown="handleThumbnailKeydown(event, <?php echo $index; ?>)" 
                                    class="product-thumbnail group w-20 h-20 mb-2.5 rounded-md overflow-hidden border-2 bg-white transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-1 <?php echo $index === 0 ? 'active border-pink-500 shadow-sm bg-pink-50' : 'border-gray-300 hover:border-pink-400 hover:shadow-sm'; ?>"
                                    data-index="<?php echo $index; ?>"
                                    data-image-url="<?php echo SITE_URL . $image['image_url']; ?>"
                                    aria-label="View image <?php echo $index + 1; ?> of <?php echo count($images); ?>"
                                    aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                    role="button"
                                    tabindex="0">
                                <img src="<?php echo SITE_URL . $image['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name'] . ' - Image ' . ($index + 1)); ?>"
                                     class="w-full h-full object-cover transition-opacity duration-200 group-hover:opacity-90"
                                     loading="<?php echo $index < 4 ? 'eager' : 'lazy'; ?>"
                                     width="80"
                                     height="80">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- COLUMN 2: Main Product Image (Center - Desktop) / Top (Mobile) -->
            <div class="xl:col-span-5 order-2 lg:order-1">
                <div class="relative">
                    <!-- Wishlist Icon (Top Right Corner) -->
                    <?php if (!Session::isAdmin()): ?>
                        <button onclick="toggleWishlist(<?php echo $product['product_id']; ?>)" 
                                class="wishlist-btn-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> absolute top-2 right-2 z-20 bg-white/90 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-lg transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>"
                                aria-label="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                            <svg class="w-5 h-5 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'text-gray-700'; ?>" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    <?php endif; ?>
                    
                    <!-- Main Product Image with Amazon-Style Zoom -->
                    <div class="relative bg-white rounded-lg overflow-visible product-image-container border border-gray-200" id="main-image-container">
                        <div class="aspect-square flex items-center justify-center p-4 lg:p-8 relative">
                            <?php if (!empty($images)): ?>
                                <!-- Desktop: Amazon-Style Hover Zoom with Lens -->
                                <div class="hidden xl:block w-full h-full relative overflow-hidden product-zoom-wrapper cursor-zoom-in" id="zoom-wrapper">
                                    <img id="main-image" 
                                         src="<?php echo SITE_URL . $images[0]['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="w-full h-full object-contain transition-opacity duration-200 product-zoom-image"
                                         loading="eager"
                                         draggable="false">
                                    <div class="product-zoom-lens hidden absolute pointer-events-none z-20 rounded-full border-2 border-pink-500 bg-white/30 backdrop-blur-sm shadow-xl" id="zoom-lens"></div>
                                </div>
                                
                                <!-- Mobile/Tablet: Tap to Zoom Modal -->
                                <img id="main-image-mobile" 
                                     src="<?php echo SITE_URL . $images[0]['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="xl:hidden w-full h-full object-contain cursor-zoom-in transition-transform duration-200 active:scale-95"
                                     onclick="openImageZoomModal()"
                                     loading="eager"
                                     draggable="false"
                                     aria-label="Tap to zoom">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Zoom Result Container (Positioned as sibling, outside overflow-hidden) -->
                        <div class="product-zoom-result hidden absolute top-0 left-full ml-4 w-[500px] h-[500px] border-2 border-gray-300 bg-white rounded-lg shadow-2xl overflow-hidden z-30 pointer-events-none" id="zoom-result" style="display: none;">
                            <img src="" alt="Zoomed product view" class="w-full h-full object-contain" draggable="false">
                        </div>
                        
                        <!-- Zoom Icon (Mobile) -->
                        <?php if (!empty($images)): ?>
                            <button onclick="openImageZoomModal()" 
                                    class="xl:hidden absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    aria-label="Zoom image">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mobile: Horizontal Thumbnail Gallery with Swipe (Amazon-Style Compact) -->
                    <?php if (!empty($images) && count($images) > 1): ?>
                        <div class="xl:hidden mt-4 px-1">
                            <div class="flex gap-2.5 overflow-x-auto pb-3 scrollbar-hide product-gallery-mobile" 
                                 id="thumbnail-gallery-mobile"
                                 style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scroll-padding-left: 0.25rem;">
                                <?php foreach ($images as $index => $image): ?>
                                    <button onclick="changeMainImageMobile('<?php echo SITE_URL . $image['image_url']; ?>', <?php echo $index; ?>)" 
                                            class="product-thumbnail-mobile flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 bg-white transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-1 <?php echo $index === 0 ? 'active border-pink-500 shadow-sm bg-pink-50' : 'border-gray-300 active:border-pink-400'; ?>"
                                            data-index="<?php echo $index; ?>"
                                            data-image-url="<?php echo SITE_URL . $image['image_url']; ?>"
                                            style="scroll-snap-align: start;"
                                            aria-label="View image <?php echo $index + 1; ?> of <?php echo count($images); ?>"
                                            role="button"
                                            tabindex="0">
                                        <img src="<?php echo SITE_URL . $image['image_url']; ?>" 
                                             alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name'] . ' - Thumbnail ' . ($index + 1)); ?>"
                                             class="w-full h-full object-cover"
                                             loading="<?php echo $index < 6 ? 'lazy' : 'lazy'; ?>"
                                             width="64"
                                             height="64">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- COLUMN 3: Product Information & Actions (Right - Desktop) / Below Image (Mobile) -->
            <div class="xl:col-span-5 order-1 lg:order-2">
                <!-- Product Identity -->
                <div class="mb-4">
                    <!-- Brand -->
                    <?php if (!empty($product['brand'])): ?>
                        <div class="text-sm text-gray-500 mb-2">
                            <span class="font-normal"><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Desktop: Product Name (Hidden on Mobile - Shown at Top) -->
                    <div class="hidden lg:block mb-3">
                        <h1 class="text-2xl font-semibold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>
                    </div>
                    
                    <!-- Product Badges Row with Customer Ratings & Reviews -->
                    <div class="product-badges-ratings-row mb-3">
                        <!-- Product Badges -->
                        <div class="product-badges mb-2 lg:mb-0">
                            <?php if ($product['is_bestseller'] ?? false): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-600 to-pink-700 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200" aria-label="Bestseller badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Bestseller
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_featured'] ?? false): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-500 to-pink-600 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200" aria-label="Featured badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Featured
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_new_arrival'] ?? false): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-500 to-pink-600 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200" aria-label="New Arrival badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    New Arrival
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Customer Ratings & Reviews -->
                        <div class="customer-ratings-reviews">
                            <?php if (!empty($rating['avg_rating']) && $rating['avg_rating'] > 0): ?>
                                <a href="#product-reviews-section" onclick="scrollToReviews(event)" class="ratings-link" aria-label="View customer reviews: <?php echo number_format($rating['avg_rating'], 1); ?> out of 5 stars with <?php echo $rating['total_reviews']; ?> reviews">
                                    <div class="ratings-stars-container" role="img" aria-label="<?php echo number_format($rating['avg_rating'], 1); ?> out of 5 stars">
                                        <div class="ratings-stars" aria-hidden="true">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="star-icon <?php echo $i <= round($rating['avg_rating']) ? 'fill-current text-yellow-400' : 'text-gray-300'; ?>" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="ratings-value"><?php echo number_format($rating['avg_rating'], 1); ?></span>
                                    </div>
                                    <span class="ratings-count">
                                        (<?php echo $rating['total_reviews']; ?> <?php echo $rating['total_reviews'] == 1 ? 'review' : 'reviews'; ?>)
                                    </span>
                                </a>
                            <?php else: ?>
                                <div class="ratings-no-reviews">
                                    <div class="ratings-stars-empty" role="img" aria-label="No ratings yet">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="star-icon text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ratings-no-text">No ratings yet</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Area -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-baseline gap-2 flex-wrap mb-2">
                        <span class="text-3xl font-semibold text-gray-900">₹<?php echo number_format($price, 2); ?></span>
                        <?php if ($hasDiscount): ?>
                            <span class="text-lg text-gray-400 line-through">₹<?php echo number_format($product['price'], 2); ?></span>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded-full">
                                <?php echo $discountPercent; ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasDiscount): ?>
                        <p class="text-sm text-green-700 font-medium">You save ₹<?php echo number_format($product['price'] - $price, 2); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Key Highlights (Amazon-Style Bullet Points) -->
                <?php 
                // Extract key highlights from description or product fields
                $highlights = [];
                if (!empty($product['short_description'])) {
                    $descLines = explode("\n", $product['short_description']);
                    foreach ($descLines as $line) {
                        $line = trim($line);
                        if (!empty($line) && strlen($line) < 150 && strlen($line) > 10) {
                            $highlights[] = $line;
                            if (count($highlights) >= 5) break;
                        }
                    }
                }
                
                // If no highlights from description, use product fields
                if (empty($highlights)) {
                    if (!empty($product['brand'])) {
                        $highlights[] = 'Brand: ' . htmlspecialchars($product['brand']);
                    }
                    if (!empty($product['age_group'])) {
                        $highlights[] = 'Age Group: ' . htmlspecialchars($product['age_group']) . ' years';
                    }
                    if (!empty($product['gender'])) {
                        $highlights[] = 'Gender: ' . ucfirst($product['gender']);
                    }
                    if (!empty($product['material'])) {
                        $highlights[] = 'Material: ' . htmlspecialchars($product['material']);
                    }
                    if ($hasDiscount) {
                        $highlights[] = 'Save ' . $discountPercent . '% with this offer';
                    }
                }
                
                // Limit to 5 highlights
                $highlights = array_slice($highlights, 0, 5);
                ?>
                
                <?php if (!empty($highlights)): ?>
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-2">Key Highlights</h3>
                        <ul class="space-y-1.5">
                            <?php foreach ($highlights as $highlight): ?>
                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                    <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><?php echo htmlspecialchars($highlight); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Variant Selection -->
                <?php if (!empty($variants)): ?>
                    <div class="mb-3 space-y-3">
                        <!-- Color Selection -->
                        <?php if (!empty($colors)): ?>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-normal text-gray-700">Color</label>
                                    <span id="selected-color" class="text-xs text-gray-500"></span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $colorIndex = 0;
                                    foreach ($colors as $colorName => $colorVariants): 
                                        $firstVariant = $colorVariants[0];
                                    ?>
                        <button onclick="selectColor('<?php echo htmlspecialchars($colorName); ?>', <?php echo $firstVariant['variant_id']; ?>)" 
                                class="color-option color-<?php echo $colorIndex; ?> px-3 py-1.5 rounded-lg border-2 border-gray-300 hover:border-pink-500 transition-all duration-200 text-xs font-normal <?php echo $colorIndex === 0 ? 'selected border-pink-500 bg-pink-50' : 'bg-white'; ?>"
                                                data-color="<?php echo htmlspecialchars($colorName); ?>"
                                                data-variant-id="<?php echo $firstVariant['variant_id']; ?>">
                                            <?php echo htmlspecialchars($colorName); ?>
                                        </button>
                                    <?php 
                                        $colorIndex++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Size Selection -->
                        <?php if (!empty($sizes)): ?>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-normal text-gray-700">Size</label>
                                    <a href="#" class="text-xs text-pink-600 hover:text-pink-700 underline">Size Guide</a>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $sizeIndex = 0;
                                    foreach ($sizes as $size): 
                                        // Find variant with this size (and selected color if applicable)
                                        $availableVariant = null;
                                        foreach ($variants as $variant) {
                                            if ($variant['size'] === $size) {
                                                $availableVariant = $variant;
                                                break;
                                            }
                                        }
                                        $isDisabled = !$availableVariant || ($availableVariant['stock_quantity'] ?? 0) <= 0;
                                    ?>
                        <button onclick="selectSize('<?php echo htmlspecialchars($size); ?>', <?php echo $availableVariant ? $availableVariant['variant_id'] : 'null'; ?>)" 
                                class="size-option px-4 py-2 rounded-lg border-2 font-normal text-xs transition-all duration-200 <?php echo $isDisabled ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed' : ($sizeIndex === 0 ? 'selected border-pink-500 bg-pink-50 text-pink-700' : 'border-gray-300 bg-white text-gray-700 hover:border-pink-500'); ?>"
                                                data-size="<?php echo htmlspecialchars($size); ?>"
                                                data-variant-id="<?php echo $availableVariant ? $availableVariant['variant_id'] : ''; ?>"
                                                <?php echo $isDisabled ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($size); ?>
                                        </button>
                                    <?php 
                                        $sizeIndex++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Variant Info Display -->
                        <div id="variant-info" class="hidden p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <span id="variant-stock-text"></span>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Product Attributes (Flat List - All Attributes) -->
                <?php 
                // Collect all attributes with values from all sources (grouped or flat)
                $allAttributesWithValues = [];
                
                // First, collect from grouped attributes if available
                if (!empty($attributesByGroup)) {
                    foreach ($attributesByGroup as $groupId => $groupAttributes) {
                        foreach ($groupAttributes as $attr) {
                            // Only include attributes that have values
                            if (!empty($attr['attribute_value'])) {
                                // Skip variant attributes (Color, Size are handled by variants)
                                if (!empty($attr['is_variant'])) {
                                    continue;
                                }
                                // Skip Color and Size by name (case-insensitive) as they're handled by variants
                                $attrName = strtolower(trim($attr['attribute_name'] ?? ''));
                                if ($attrName === 'color' || $attrName === 'size') {
                                    continue;
                                }
                                // Add to flat list (ignore group information)
                                $allAttributesWithValues[] = $attr;
                            }
                        }
                    }
                }
                
                // Also collect from flat productAttributes if available (for backward compatibility)
                if (!empty($productAttributes)) {
                    foreach ($productAttributes as $attr) {
                        // Only include attributes that have values and aren't already in the list
                        if (!empty($attr['attribute_value'])) {
                            // Skip variant attributes
                            if (!empty($attr['is_variant'])) {
                                continue;
                            }
                            // Skip Color and Size by name
                            $attrName = strtolower(trim($attr['attribute_name'] ?? ''));
                            if ($attrName === 'color' || $attrName === 'size') {
                                continue;
                            }
                            
                            // Check if already added (by attribute_id to avoid duplicates)
                            $alreadyAdded = false;
                            foreach ($allAttributesWithValues as $existingAttr) {
                                if (isset($existingAttr['attribute_id']) && isset($attr['attribute_id']) && 
                                    $existingAttr['attribute_id'] == $attr['attribute_id']) {
                                    $alreadyAdded = true;
                                    break;
                                }
                            }
                            
                            if (!$alreadyAdded) {
                                $allAttributesWithValues[] = $attr;
                            }
                        }
                    }
                }
                
                // Sort by display_order if available, otherwise by attribute name
                usort($allAttributesWithValues, function($a, $b) {
                    $orderA = isset($a['display_order']) ? (int)$a['display_order'] : 999;
                    $orderB = isset($b['display_order']) ? (int)$b['display_order'] : 999;
                    if ($orderA != $orderB) {
                        return $orderA <=> $orderB;
                    }
                    $nameA = strtolower($a['attribute_name'] ?? '');
                    $nameB = strtolower($b['attribute_name'] ?? '');
                    return strcmp($nameA, $nameB);
                });
                ?>
                
                <?php if (!empty($allAttributesWithValues)): ?>
                    <div class="mb-3 pb-3 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-800 mb-2">Product Details</h3>
                        <div class="overflow-hidden rounded-lg">
                            <table class="w-full border-collapse bg-white">
                                <tbody>
                                    <?php 
                                    $rowIndex = 0;
                                    foreach ($allAttributesWithValues as $attr): 
                                        $rowIndex++;
                                        $isEven = $rowIndex % 2 === 0;
                                    ?>
                                        <tr class="<?php echo $isEven ? 'bg-gray-50' : 'bg-white'; ?> hover:bg-gray-100 transition-colors duration-150">
                                            <td class="py-1 align-top w-[35%] md:w-[40%]">
                                                <span class="text-sm font-semibold text-gray-900 leading-relaxed">
                                                    <?php echo htmlspecialchars($attr['attribute_name']); ?>
                                                </span>
                                            </td>
                                            <td class="py-1 align-top w-[65%] md:w-[60%]">
                                                <span class="text-sm text-gray-700 leading-relaxed">
                                                    <?php 
                                                    // Handle different attribute types
                                                    if ($attr['attribute_type'] === 'color' && !empty($attr['attribute_value'])) {
                                                        // Display color with a color swatch if it's a hex code
                                                        $colorValue = trim($attr['attribute_value']);
                                                        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colorValue)) {
                                                            echo '<span class="inline-flex items-center gap-2.5">';
                                                            echo '<span class="w-5 h-5 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: ' . htmlspecialchars($colorValue) . ';"></span>';
                                                            echo '<span>' . htmlspecialchars($colorValue) . '</span>';
                                                            echo '</span>';
                                                        } else {
                                                            echo htmlspecialchars($attr['attribute_value']);
                                                        }
                                                    } else {
                                                        echo nl2br(htmlspecialchars($attr['attribute_value']));
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quantity Selector -->
                <div class="mb-3">
                    <label class="block text-xs font-normal text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center gap-2">
                        <button onclick="decreaseProductQuantity()" 
                                class="w-8 h-8 rounded-lg border-2 border-gray-300 hover:border-pink-500 hover:bg-pink-50 flex items-center justify-center transition-all duration-200">
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <input type="number" 
                               id="quantity" 
                               value="1" 
                               min="1" 
                               max="<?php echo $product['max_order_quantity'] ?? 10; ?>"
                               class="w-16 text-center border-2 border-gray-300 rounded-lg px-2 py-1.5 text-sm font-normal focus:border-pink-500 focus:outline-none">
                        <button onclick="increaseProductQuantity()" 
                                class="w-8 h-8 rounded-lg border-2 border-gray-300 hover:border-pink-500 hover:bg-pink-50 flex items-center justify-center transition-all duration-200">
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <span class="text-xs text-gray-500 ml-2">Max: <?php echo $product['max_order_quantity'] ?? 10; ?></span>
                    </div>
                </div>
                
                <!-- Primary Actions (CTA Section) -->
                <div class="mb-3 space-y-2">
                    <button onclick="handleAddToCart()" 
                            class="w-full bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white font-medium py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.01] flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Add to Cart
                    </button>
                    
                    <div class="flex gap-2">
                        <?php if (!Session::isAdmin()): ?>
                            <button onclick="toggleWishlistDetail(<?php echo $product['product_id']; ?>)" 
                                    class="flex-1 border-2 border-gray-300 hover:border-pink-500 text-gray-700 hover:text-pink-600 font-medium py-2 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 text-sm">
                                <svg class="w-4 h-4 <?php echo $inWishlist ? 'fill-current text-pink-600' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <?php echo $inWishlist ? 'Saved' : 'Save for Later'; ?>
                            </button>
                        <?php endif; ?>
                        <button onclick="handleBuyNow()" 
                                class="flex-1 bg-white border-2 border-pink-600 text-pink-600 hover:bg-pink-50 font-medium py-2 px-3 rounded-lg transition-all duration-200 text-sm">
                            Buy Now
                        </button>
                    </div>
                </div>
                
                <!-- Extra Offers & Info -->
                <div class="border-t border-gray-200 pt-5 flex justify-around">
                    <!-- Delivery Info -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-xs font-normal text-gray-700">Free Delivery</p>
                            <p class="text-xs text-gray-600">On orders above ₹500</p>
                        </div>
                    </div>
                    
                    <!-- Return Policy -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <div>
                            <p class="text-xs font-normal text-gray-700">Easy Returns</p>
                            <p class="text-xs text-gray-600">7-day return policy</p>
                        </div>
                    </div>
                    
                    <!-- Quality Assurance -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-normal text-gray-700">Quality Assured</p>
                            <p class="text-xs text-gray-600">100% authentic products</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Description & Additional Information Section -->
        <div class="border-t border-gray-200 px-3 md:px-6 py-3">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Description & Additional Information</h2>
            <div class="prose max-w-none">
                <p class="text-sm text-gray-700 mb-3 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'] ?? $product['short_description'] ?? '')); ?></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <?php if (!empty($product['brand'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm">Brand:</span>
                            <span class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm">Age Group:</span>
                        <span class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['age_group']); ?> years</span>
                    </div>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm">Gender:</span>
                        <span class="text-gray-600 text-sm"><?php echo ucfirst($product['gender']); ?></span>
                    </div>
                    <?php if (!empty($product['material'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm">Material:</span>
                            <span class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['material']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm">SKU:</span>
                        <span class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['sku']); ?></span>
                    </div>
                    <?php if (!empty($product['stock_quantity'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm">Stock:</span>
                            <span class="text-gray-600 text-sm"><?php echo $product['stock_quantity']; ?> available</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div id="product-reviews-section" tabindex="-1" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Reviews</h2>
        <?php if (!empty($reviews)): ?>
            <div class="space-y-6">
                <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex text-yellow-400">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'fill-current' : 'text-gray-300'; ?>" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <?php if ($review['title']): ?>
                            <h4 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($review['title']); ?></h4>
                        <?php endif; ?>
                        <p class="text-gray-700 leading-relaxed"><?php echo htmlspecialchars($review['review_text']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-gray-500 text-lg">No reviews yet.</p>
                <p class="text-gray-400 text-sm mt-2">Be the first to review this product!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <?php include VIEW_PATH . '/products/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile Sticky CTA Bar -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 p-4">
    <div class="container mx-auto flex gap-3">
                    <?php if (!Session::isAdmin()): ?>
            <button onclick="toggleWishlistDetail(<?php echo $product['product_id']; ?>)" 
                    class="wishlist-btn-mobile-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> p-3 border-2 border-gray-300 rounded-xl hover:border-pink-500 transition-all duration-200">
                <svg class="w-6 h-6 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'text-gray-700'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        <?php endif; ?>
        <button onclick="handleAddToCart()" 
                class="flex-1 bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-all duration-200">
            Add to Cart
        </button>
        <button onclick="handleBuyNow()" 
                class="flex-1 bg-white border-2 border-pink-600 text-pink-600 hover:bg-pink-50 font-bold py-3 px-4 rounded-xl transition-all duration-200">
            Buy Now
        </button>
    </div>
</div>

<script>
// Product Gallery Functions
let currentImageIndex = 0;
const images = <?php echo json_encode(array_map(function($img) { return SITE_URL . $img['image_url']; }, $images ?? [])); ?>;
let zoomLens = null;
let zoomResult = null;
let touchStartX = 0;
let touchEndX = 0;
let swipeThreshold = 50;

// Preload product images for smooth transitions (Performance Optimization)
function preloadProductImages() {
    if (images && images.length > 0) {
        // Preload first 4 images immediately
        images.slice(0, 4).forEach(function(imageUrl) {
            const img = new Image();
            img.src = imageUrl;
        });
        
        // Preload remaining images after a short delay (non-blocking)
        if (images.length > 4) {
            setTimeout(function() {
                images.slice(4).forEach(function(imageUrl) {
                    const img = new Image();
                    img.src = imageUrl;
                });
            }, 1000);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Preload images for better performance
    preloadProductImages();
    
    // Initialize zoom, swipe, and keyboard navigation
    initImageZoom();
    initSwipeSupport();
    initKeyboardNavigation();
    
    // Select first color and size if available
    const firstColor = document.querySelector('.color-option');
    if (firstColor) {
        const colorName = firstColor.getAttribute('data-color');
        const variantId = firstColor.getAttribute('data-variant-id');
        if (colorName && variantId) {
            selectColor(colorName, parseInt(variantId));
        }
    }
    
    const firstSize = document.querySelector('.size-option:not([disabled])');
    if (firstSize) {
        const size = firstSize.getAttribute('data-size');
        const variantId = firstSize.getAttribute('data-variant-id');
        if (size && variantId) {
            selectSize(size, parseInt(variantId));
        }
    }
});

// Desktop: Change main image on hover (Amazon-style instant update)
function changeMainImageOnHover(imageUrl, index) {
    const mainImage = document.getElementById('main-image');
    if (mainImage && mainImage.complete) {
        // Instant update with fade transition
        mainImage.style.transition = 'opacity 0.15s ease-in-out';
        mainImage.style.opacity = '0.7';
        
        // Preload image for smooth transition
        const img = new Image();
        img.onload = function() {
            mainImage.src = imageUrl;
            mainImage.style.opacity = '1';
            // Update zoom image source as well
            updateZoomImageSource(imageUrl);
        };
        img.src = imageUrl;
    }
    
    // Update active thumbnail state (visual feedback)
    updateThumbnailActiveState(index);
    currentImageIndex = index;
}

// Desktop: Change main image on click (for accessibility and touch)
function changeMainImage(imageUrl, index) {
    const mainImage = document.getElementById('main-image') || document.getElementById('main-image-mobile');
    if (mainImage) {
        // Smooth transition for click
        mainImage.style.transition = 'opacity 0.2s ease-in-out';
        mainImage.style.opacity = '0.6';
        
        const img = new Image();
        img.onload = function() {
            mainImage.src = imageUrl;
            mainImage.style.opacity = '1';
            if (document.getElementById('main-image')) {
                updateZoomImageSource(imageUrl);
            }
        };
        img.src = imageUrl;
    }
    
    // Update active thumbnail (desktop vertical)
    updateThumbnailActiveState(index);
    
    // Sync mobile thumbnails if they exist
    document.querySelectorAll('.product-thumbnail-mobile').forEach(thumb => {
        const isActive = parseInt(thumb.getAttribute('data-index')) === index;
        if (isActive) {
            thumb.classList.add('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.remove('border-gray-300');
            thumb.setAttribute('aria-pressed', 'true');
        } else {
            thumb.classList.remove('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.add('border-gray-300');
            thumb.setAttribute('aria-pressed', 'false');
        }
    });
    
    currentImageIndex = index;
}

// Helper function to update thumbnail active state
function updateThumbnailActiveState(index) {
    document.querySelectorAll('.product-thumbnail').forEach(thumb => {
        const isActive = parseInt(thumb.getAttribute('data-index')) === index;
        if (isActive) {
            thumb.classList.add('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.remove('border-gray-300', 'hover:border-pink-400');
            thumb.setAttribute('aria-pressed', 'true');
        } else {
            thumb.classList.remove('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.add('border-gray-300');
            thumb.setAttribute('aria-pressed', 'false');
        }
    });
}

// Update zoom result image source
function updateZoomImageSource(imageUrl) {
    const zoomResult = document.getElementById('zoom-result');
    if (zoomResult) {
        let zoomImg = zoomResult.querySelector('img');
        if (!zoomImg) {
            // Create zoom image if it doesn't exist
            zoomImg = document.createElement('img');
            zoomImg.className = 'w-full h-full object-contain transition-transform duration-100 ease-out';
            zoomImg.alt = 'Zoomed product image';
            zoomImg.draggable = false;
            zoomResult.appendChild(zoomImg);
        }
        // Update source with smooth loading
        if (zoomImg.src !== imageUrl) {
            zoomImg.src = imageUrl;
        }
    }
}

// Mobile: Change main image with smooth transition
function changeMainImageMobile(imageUrl, index) {
    const mainImageMobile = document.getElementById('main-image-mobile');
    if (mainImageMobile) {
        // Smooth fade transition
        mainImageMobile.style.transition = 'opacity 0.2s ease-in-out';
        mainImageMobile.style.opacity = '0.6';
        
        // Preload image for smooth transition
        const img = new Image();
        img.onload = function() {
            mainImageMobile.src = imageUrl;
            mainImageMobile.style.opacity = '1';
        };
        img.src = imageUrl;
    }
    
    // Update mobile thumbnails with active state
    document.querySelectorAll('.product-thumbnail-mobile').forEach(thumb => {
        const isActive = parseInt(thumb.getAttribute('data-index')) === index;
        if (isActive) {
            thumb.classList.add('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.remove('border-gray-300');
            thumb.setAttribute('aria-pressed', 'true');
            // Scroll thumbnail into view if needed
            setTimeout(() => {
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }, 100);
        } else {
            thumb.classList.remove('active', 'border-pink-500', 'shadow-sm', 'bg-pink-50');
            thumb.classList.add('border-gray-300');
            thumb.setAttribute('aria-pressed', 'false');
        }
    });
    
    currentImageIndex = index;
}

// Amazon-Style Desktop Image Zoom Functionality
function initImageZoom() {
    const zoomWrapper = document.getElementById('zoom-wrapper');
    const zoomImage = document.getElementById('main-image');
    
    if (!zoomWrapper || !zoomImage) return;
    
    zoomLens = document.getElementById('zoom-lens');
    zoomResult = document.getElementById('zoom-result');
    
    // Ensure zoom image is loaded before enabling zoom
    if (!zoomImage.complete) {
        zoomImage.addEventListener('load', function() {
            setupZoomEventListeners();
        });
    } else {
        setupZoomEventListeners();
    }
    
        function setupZoomEventListeners() {
        if (!zoomImage.naturalWidth || !zoomImage.naturalHeight) {
            // Wait for image to load
            const checkImage = setInterval(function() {
                if (zoomImage.naturalWidth > 0 && zoomImage.naturalHeight > 0) {
                    clearInterval(checkImage);
                    setupZoomEventListeners();
                }
            }, 100);
            return;
        }
        
        const zoomFactor = 2.5; // Amazon-style zoom factor
        const lensSize = 180; // Lens size in pixels (Amazon-style compact)
        
        // Ensure zoom result has the current image
        updateZoomImageSource(zoomImage.src);
        
        // Mouse enter: Show zoom
        zoomWrapper.addEventListener('mouseenter', function() {
            if (zoomLens && zoomImage.complete && zoomImage.naturalWidth > 0) {
                zoomLens.classList.remove('hidden');
                zoomLens.style.display = 'block';
                if (zoomResult) {
                    zoomResult.classList.remove('hidden');
                    zoomResult.style.display = 'block';
                    // Ensure zoom result has the current image
                    updateZoomImageSource(zoomImage.src);
                    // Update zoom result position to match cursor initially
                    const rect = zoomWrapper.getBoundingClientRect();
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    zoomLens.style.width = lensSize + 'px';
                    zoomLens.style.height = lensSize + 'px';
                    zoomLens.style.left = (centerX - lensSize / 2) + 'px';
                    zoomLens.style.top = (centerY - lensSize / 2) + 'px';
                }
            }
        }, { passive: true });
        
        // Mouse move: Update zoom position (Amazon-style smooth tracking)
        zoomWrapper.addEventListener('mousemove', function(e) {
            if (!zoomLens || !zoomImage.complete || !zoomResult) return;
            
            const rect = zoomWrapper.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate lens position (centered on cursor)
            let lensX = x - lensSize / 2;
            let lensY = y - lensSize / 2;
            
            // Constrain lens within image bounds
            const maxX = rect.width - lensSize;
            const maxY = rect.height - lensSize;
            lensX = Math.max(0, Math.min(lensX, maxX));
            lensY = Math.max(0, Math.min(lensY, maxY));
            
            // Update lens position with smooth transition
            zoomLens.style.width = lensSize + 'px';
            zoomLens.style.height = lensSize + 'px';
            zoomLens.style.left = lensX + 'px';
            zoomLens.style.top = lensY + 'px';
            
            // Calculate zoomed image position (Amazon-style background position)
            if (zoomImage.naturalWidth > 0 && zoomImage.naturalHeight > 0) {
                // Get actual displayed image dimensions (object-contain may not fill container)
                const imgRect = zoomImage.getBoundingClientRect();
                const displayedWidth = imgRect.width;
                const displayedHeight = imgRect.height;
                
                // Calculate scale ratios between natural and displayed size
                const scaleX = zoomImage.naturalWidth / displayedWidth;
                const scaleY = zoomImage.naturalHeight / displayedHeight;
                
                // Calculate cursor position relative to the displayed image center
                const containerCenterX = rect.width / 2;
                const containerCenterY = rect.height / 2;
                
                // Offset from image center
                const offsetX = (x - containerCenterX) * scaleX;
                const offsetY = (y - containerCenterY) * scaleY;
                
                // Calculate zoom result image transform (Amazon-style: center on cursor position)
                const zoomResultWidth = zoomResult.offsetWidth || 500;
                const zoomResultHeight = zoomResult.offsetHeight || 500;
                
                // Position zoom image to show magnified portion centered on cursor
                const bgX = -(offsetX * zoomFactor - zoomResultWidth / 2);
                const bgY = -(offsetY * zoomFactor - zoomResultHeight / 2);
                
                const zoomImg = zoomResult.querySelector('img');
                if (zoomImg && zoomImg.complete) {
                    requestAnimationFrame(function() {
                        zoomImg.style.transform = `translate(${bgX}px, ${bgY}px) scale(${zoomFactor})`;
                        zoomImg.style.transformOrigin = 'center center';
                    });
                } else if (!zoomImg) {
                    // Create zoom image if it doesn't exist
                    updateZoomImageSource(zoomImage.src);
                }
            }
        }, { passive: true });
        
        // Mouse leave: Hide zoom
        zoomWrapper.addEventListener('mouseleave', function() {
            if (zoomLens) {
                zoomLens.classList.add('hidden');
                zoomLens.style.display = 'none';
            }
            if (zoomResult) {
                zoomResult.classList.add('hidden');
                zoomResult.style.display = 'none';
            }
        }, { passive: true });
    }
}

// Mobile: Open full-screen zoom modal
function openImageZoomModal() {
    const zoomResult = document.getElementById('zoom-result');
    const zoomedImg = document.getElementById('zoomed-image');
    const mainImg = document.getElementById('main-image-mobile') || document.getElementById('main-image');
    
    if (zoomResult && zoomedImg && mainImg) {
        zoomedImg.src = mainImg.src;
        zoomedImg.alt = mainImg.alt;
        zoomResult.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

// Close zoom modal
function closeImageZoomModal() {
    const zoomResult = document.getElementById('zoom-result');
    if (zoomResult) {
        zoomResult.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Enhanced Swipe support for mobile gallery (Amazon-style smooth)
function initSwipeSupport() {
    const gallery = document.getElementById('thumbnail-gallery-mobile');
    const mainImageMobile = document.getElementById('main-image-mobile');
    
    if (!mainImageMobile || images.length <= 1) return;
    
    const imageContainer = mainImageMobile.closest('.product-image-container');
    if (!imageContainer) return;
    
    let touchStartTime = 0;
    let touchStartY = 0;
    
    imageContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
        touchStartTime = Date.now();
    }, { passive: true });
    
    imageContainer.addEventListener('touchend', function(e) {
        if (!touchStartX) return;
        
        touchEndX = e.changedTouches[0].screenX;
        const touchEndY = e.changedTouches[0].screenY;
        const touchDuration = Date.now() - touchStartTime;
        
        const diffX = touchStartX - touchEndX;
        const diffY = Math.abs(touchStartY - touchEndY);
        
        // Only handle swipe if horizontal movement is greater than vertical (prevent scroll interference)
        if (Math.abs(diffX) > diffY && Math.abs(diffX) > swipeThreshold && touchDuration < 300) {
            e.preventDefault();
            
            if (diffX > 0 && currentImageIndex < images.length - 1) {
                // Swipe left - next image
                currentImageIndex++;
                changeMainImageMobile(images[currentImageIndex], currentImageIndex);
            } else if (diffX < 0 && currentImageIndex > 0) {
                // Swipe right - previous image
                currentImageIndex--;
                changeMainImageMobile(images[currentImageIndex], currentImageIndex);
            }
        }
        
        // Reset
        touchStartX = 0;
        touchStartY = 0;
    }, { passive: false });
}

// Keyboard navigation for thumbnails
function handleThumbnailKeydown(event, index) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        const thumbnail = event.target.closest('.product-thumbnail');
        if (thumbnail) {
            const imageUrl = thumbnail.getAttribute('data-image-url');
            changeMainImage(imageUrl, index);
        }
    } else if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
        event.preventDefault();
        const thumbnails = Array.from(document.querySelectorAll('.product-thumbnail'));
        const currentIndex = thumbnails.indexOf(event.target.closest('.product-thumbnail'));
        let newIndex = currentIndex;
        
        if (event.key === 'ArrowUp' && currentIndex > 0) {
            newIndex = currentIndex - 1;
        } else if (event.key === 'ArrowDown' && currentIndex < thumbnails.length - 1) {
            newIndex = currentIndex + 1;
        }
        
        if (newIndex !== currentIndex) {
            thumbnails[newIndex].focus();
            const imageUrl = thumbnails[newIndex].getAttribute('data-image-url');
            changeMainImage(imageUrl, newIndex);
        }
    }
}

function initKeyboardNavigation() {
    // Allow closing zoom modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageZoomModal();
        }
    });
}

// Variant Selection
let selectedColor = null;
let selectedSize = null;
let selectedVariantId = null;

function selectColor(colorName, variantId) {
    selectedColor = colorName;
    selectedVariantId = variantId;
    
    // Update UI
    document.querySelectorAll('.color-option').forEach(btn => {
        btn.classList.remove('selected', 'border-pink-500', 'bg-pink-50');
        btn.classList.add('border-gray-300', 'bg-white');
    });
    
    const selectedBtn = document.querySelector(`.color-option[data-color="${colorName}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('selected', 'border-pink-500', 'bg-pink-50');
        selectedBtn.classList.remove('border-gray-300', 'bg-white');
    }
    
    document.getElementById('selected-color').textContent = colorName;
    updateVariantInfo();
}

function selectSize(size, variantId) {
    if (!variantId) return;
    
    selectedSize = size;
    selectedVariantId = variantId;
    
    // Update UI
    document.querySelectorAll('.size-option').forEach(btn => {
        btn.classList.remove('selected', 'border-pink-500', 'bg-pink-50', 'text-pink-700');
        if (!btn.disabled) {
            btn.classList.add('border-gray-300', 'bg-white', 'text-gray-700');
        }
    });
    
    const selectedBtn = document.querySelector(`.size-option[data-size="${size}"]`);
    if (selectedBtn && !selectedBtn.disabled) {
        selectedBtn.classList.add('selected', 'border-pink-500', 'bg-pink-50', 'text-pink-700');
        selectedBtn.classList.remove('border-gray-300', 'bg-white', 'text-gray-700');
    }
    
    updateVariantInfo();
}

function updateVariantInfo() {
    const variantInfo = document.getElementById('variant-info');
    if (!variantInfo) return;
    
    if (selectedVariantId) {
        // You can fetch variant details via AJAX or use data attributes
        variantInfo.classList.remove('hidden');
        // Update stock info if available
    } else {
        variantInfo.classList.add('hidden');
    }
}

// Quantity Controls (renamed to avoid conflict with cart.js)
function increaseProductQuantity() {
    const qtyInput = document.getElementById('quantity');
    if (!qtyInput) {
        console.error('Quantity input not found');
        return;
    }
    const maxQty = parseInt(qtyInput.getAttribute('max')) || 10;
    let currentQty = parseInt(qtyInput.value) || 1;
    
    if (currentQty < maxQty) {
        qtyInput.value = currentQty + 1;
    }
}

function decreaseProductQuantity() {
    const qtyInput = document.getElementById('quantity');
    if (!qtyInput) {
        console.error('Quantity input not found');
        return;
    }
    let currentQty = parseInt(qtyInput.value) || 1;
    
    if (currentQty > 1) {
        qtyInput.value = currentQty - 1;
    }
}

// Add to Cart Handler
async function handleAddToCart() {
    const productId = <?php echo $product['product_id']; ?>;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const variantId = selectedVariantId || null;
    
    await addToCart(productId, variantId, quantity);
}

// Buy Now Handler - Direct purchase (clears cart and adds only this product)
async function handleBuyNow() {
    const productId = <?php echo $product['product_id']; ?>;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const variantId = selectedVariantId || null;
    
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
        
        // Add product with buy_now flag (backend will clear cart first)
        const formData = new FormData();
        formData.append('product_id', productId);
        if (variantId) {
            formData.append('variant_id', variantId);
        }
        formData.append('quantity', quantity);
        formData.append('buy_now', '1'); // Flag to indicate buy now - clears existing cart
        
        const response = await fetch(siteUrl + '/cart/add', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Immediately redirect to checkout without showing "added to cart" message
            window.location.href = siteUrl + '/checkout';
        } else {
            showToast(data.message || 'Failed to proceed with purchase', 'error');
        }
    } catch (error) {
        console.error('Error in Buy Now:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

// Custom wishlist toggle that updates both buttons
async function toggleWishlistDetail(productId) {
    const mainBtn = document.querySelector(`.wishlist-btn-${productId}`);
    const mobileBtn = document.querySelector(`.wishlist-btn-mobile-${productId}`);
    
    // Use the main toggleWishlist function
    await toggleWishlist(productId);
    
    // Sync mobile button with main button
    if (mainBtn && mobileBtn) {
        const isInWishlist = mainBtn.classList.contains('in-wishlist');
        const mobileSvg = mobileBtn.querySelector('svg');
        
        if (isInWishlist) {
            mobileBtn.classList.add('in-wishlist');
            if (mobileSvg) {
                mobileSvg.classList.add('text-pink-600', 'fill-current');
                mobileSvg.classList.remove('text-gray-700');
            }
        } else {
            mobileBtn.classList.remove('in-wishlist');
            if (mobileSvg) {
                mobileSvg.classList.remove('text-pink-600', 'fill-current');
                mobileSvg.classList.add('text-gray-700');
            }
        }
    }
}

// Scroll to reviews section smoothly
function scrollToReviews(event) {
    event.preventDefault();
    const reviewsSection = document.getElementById('product-reviews-section');
    if (reviewsSection) {
        reviewsSection.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        // Focus on reviews section for accessibility
        reviewsSection.focus();
        reviewsSection.setAttribute('tabindex', '-1');
    }
}

</script>

<style>
/* Scrollbar Utilities */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-thin {
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb #f9fafb;
}
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #f9fafb;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

/* Amazon-Style Vertical Thumbnail Gallery (Desktop) */
.thumbnail-gallery-vertical {
    max-height: calc(100vh - 120px);
    padding-right: 0.5rem;
}

.thumbnail-gallery-vertical::-webkit-scrollbar {
    width: 6px;
}

.thumbnail-gallery-vertical::-webkit-scrollbar-track {
    background: #f9fafb;
    border-radius: 10px;
}

.thumbnail-gallery-vertical::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}

.thumbnail-gallery-vertical::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

/* Amazon-Style Product Thumbnail (Desktop) */
.product-thumbnail {
    position: relative;
    cursor: pointer;
}

.product-thumbnail::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 0.375rem;
    background-color: rgba(236, 72, 153, 0.05);
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    pointer-events: none;
    z-index: 1;
}

.product-thumbnail:hover::before {
    opacity: 1;
}

.product-thumbnail.active::before {
    opacity: 1;
    background-color: rgba(236, 72, 153, 0.1);
}

.product-thumbnail img {
    position: relative;
    z-index: 0;
}

/* Amazon-Style Product Image Zoom */
.product-zoom-wrapper {
    cursor: zoom-in;
    position: relative;
    user-select: none;
    -webkit-user-select: none;
}

.product-zoom-wrapper:hover {
    cursor: crosshair;
}

.product-zoom-image {
    transition: opacity 0.2s ease-in-out;
    pointer-events: none;
}

.product-zoom-lens {
    background-color: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(2px);
    box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.6), 
                0 0 0 1px rgba(255, 255, 255, 0.8) inset,
                0 4px 12px rgba(0, 0, 0, 0.15);
    pointer-events: none;
    transition: transform 0.05s ease-out;
    will-change: transform;
}

.product-zoom-result {
    pointer-events: none;
    will-change: transform;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 
                0 0 0 1px rgba(0, 0, 0, 0.05);
}

.product-zoom-result img {
    will-change: transform;
    transition: transform 0.1s ease-out;
}

/* Prevent image drag */
.product-zoom-image,
.product-zoom-result img,
#main-image-mobile {
    -webkit-user-drag: none;
    user-select: none;
    -webkit-user-select: none;
}

/* Mobile Gallery Swipe Support (Amazon-Style) */
.product-gallery-mobile {
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    scroll-padding: 0.5rem;
}

.product-thumbnail-mobile {
    position: relative;
    cursor: pointer;
    touch-action: manipulation;
}

.product-thumbnail-mobile::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 0.375rem;
    background-color: rgba(236, 72, 153, 0.05);
    opacity: 0;
    transition: opacity 0.15s ease-in-out;
    pointer-events: none;
    z-index: 1;
}

.product-thumbnail-mobile:active::before {
    opacity: 1;
}

.product-thumbnail-mobile.active::before {
    opacity: 1;
    background-color: rgba(236, 72, 153, 0.1);
}

.product-thumbnail-mobile img {
    position: relative;
    z-index: 0;
}

/* Image Zoom Modal */
#zoom-result {
    animation: fadeIn 0.3s ease-in-out;
}

#zoom-result.hidden {
    display: none !important;
}

#zoomed-image {
    animation: zoomIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes zoomIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Responsive Image Container (Amazon-Style) */
.product-image-container {
    min-height: 400px;
    background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
}

@media (min-width: 1280px) {
    .product-image-container {
        min-height: 500px;
    }
}

/* Mobile Optimizations */
@media (max-width: 1279px) {
    .product-image-container {
        min-height: 350px;
    }
    
    /* Touch-friendly thumbnail sizing */
    .product-thumbnail-mobile {
        min-width: 64px;
        min-height: 64px;
        width: 64px;
        height: 64px;
    }
}

/* Desktop: Ensure zoom result positioning doesn't overflow */
@media (min-width: 1280px) {
    .product-image-container {
        position: relative;
        overflow: visible;
    }
    
    .product-zoom-result {
        position: absolute;
        top: 0;
        left: calc(100% + 1rem);
        max-width: 500px;
        max-height: 500px;
        will-change: transform, opacity;
    }
    
    /* Adjust if zoom result would overflow viewport (show on left side instead) */
    @media (max-width: 1600px) {
        .product-zoom-result {
            left: auto;
            right: calc(100% + 1rem);
        }
    }
    
    /* Hide zoom result on smaller desktop screens if space is limited */
    @media (max-width: 1400px) {
        .product-zoom-result {
            display: none !important;
        }
    }
}

/* Ensure zoom result doesn't show on mobile/tablet */
@media (max-width: 1279px) {
    .product-zoom-result {
        display: none !important;
    }
}

/* Product Badges and Ratings Row - Base Styles */
.product-badges-ratings-row {
    min-height: 2.5rem;
}

.product-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.customer-ratings-reviews {
    display: flex;
    align-items: center;
}

/* Ratings Link Styles */
.ratings-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.2s ease-in-out;
}

.ratings-link:hover {
    opacity: 0.8;
}

.ratings-stars-container {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.ratings-stars {
    display: flex;
    margin-right: 0.375rem;
}

.ratings-stars-empty {
    display: flex;
}

.star-icon {
    width: 1rem;
    height: 1rem;
    fill: #FACC15; /* yellow-400 - default filled stars */
}

.star-icon.text-gray-300 {
    fill: #D1D5DB; /* gray-300 - empty stars */
}

.ratings-stars-empty .star-icon {
    fill: #D1D5DB; /* gray-300 - no ratings stars */
}

.ratings-value {
    font-size: 14px;
    font-weight: 600;
    color: #374151; /* gray-700 - WCAG AA compliant */
    min-width: 2.5rem;
}

.ratings-count {
    font-size: 12px;
    color: #4B5563; /* gray-600 - WCAG AA compliant */
    white-space: nowrap;
    transition: color 0.2s ease-in-out;
}

.ratings-link:hover .ratings-count {
    color: #EC4899; /* pink-600 */
}

.ratings-no-reviews {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ratings-no-text {
    font-size: 12px;
    color: #6B7280; /* gray-500 - WCAG AA compliant */
    white-space: nowrap;
}

/* Desktop Behavior (≥ 1024px) */
@media (min-width: 1024px) {
    .product-badges-ratings-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: nowrap;
        gap: 1rem;
    }
    
    .product-badges {
        flex: 0 1 auto;
    }
    
    .customer-ratings-reviews {
        flex: 0 0 auto;
        margin-left: auto;
    }
    
    .ratings-link {
        gap: 0.75rem;
    }
    
    .star-icon {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    .ratings-value {
        font-size: 16px;
    }
    
    .ratings-count {
        font-size: 14px;
    }
    
    .ratings-no-text {
        font-size: 14px;
    }
}

/* Tablet Behavior (768px – 1023px) */
@media (min-width: 768px) and (max-width: 1023px) {
    .product-badges-ratings-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .product-badges {
        flex: 1 1 auto;
        width: 100%;
    }
    
    .customer-ratings-reviews {
        flex: 1 1 auto;
        margin-left: auto;
        margin-top: 0.5rem;
        justify-content: flex-end;
    }
    
    /* If space allows, keep in same row */
    @media (min-width: 900px) {
        .product-badges {
            width: auto;
            flex: 1 1 auto;
        }
        
        .customer-ratings-reviews {
            margin-top: 0;
        }
    }
    
    /* Touch-friendly spacing */
    .ratings-link {
        padding: 0.25rem 0.5rem;
        min-height: 2.5rem;
    }
    
    .star-icon {
        width: 1rem;
        height: 1rem;
    }
    
    .ratings-value {
        font-size: 14px;
    }
    
    .ratings-count {
        font-size: 12px;
    }
}

/* Mobile Behavior (≤ 767px) */
@media (max-width: 767px) {
    .product-badges-ratings-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .product-badges {
        width: 100%;
        order: 1;
    }
    
    .customer-ratings-reviews {
        width: 100%;
        order: 2;
        margin-left: 0;
        margin-top: 0;
        justify-content: flex-start;
    }
    
    .ratings-link,
    .ratings-no-reviews {
        width: 100%;
        justify-content: flex-start;
        padding: 0.5rem 0;
        min-height: 3rem;
    }
    
    .star-icon {
        width: 1.25rem;
        height: 1.25rem;
        min-width: 1.25rem;
        min-height: 1.25rem;
    }
    
    .ratings-value {
        font-size: 14px;
    }
    
    .ratings-count,
    .ratings-no-text {
        font-size: 14px;
    }
}

/* Smooth transitions for layout changes */
.product-badges-ratings-row,
.customer-ratings-reviews,
.product-badges {
    transition: all 0.3s ease-in-out;
}

/* Prevent horizontal scrolling on mobile */
@media (max-width: 767px) {
    .product-badges-ratings-row {
        overflow-x: hidden;
    }
}

/* Focus styles for accessibility */
.ratings-link:focus {
    outline: 2px solid #EC4899;
    outline-offset: 2px;
    border-radius: 0.25rem;
}

/* Ensure reviews section can receive focus */
#product-reviews-section:focus {
    outline: 2px solid #EC4899;
    outline-offset: 4px;
    border-radius: 0.5rem;
}

#product-reviews-section {
    scroll-margin-top: 1rem;
}
</style>
