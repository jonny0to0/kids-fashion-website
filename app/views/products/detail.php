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

<div class="container mx-auto px-4 pb-24 lg:pb-8 max-w-7xl">
    <!-- py-4 md:py-6 -->
    <!-- Mobile: Product Title (Shown First on Mobile < 1024px) -->
    <div class="lg:hidden mb-4 space-y-2">
        <?php if (!empty($product['brand'])): ?>
            <div class="text-sm text-gray-500 font-medium">
                <?php echo htmlspecialchars($product['brand']); ?>
            </div>
        <?php endif; ?>

        <h1 class="text-xl font-semibold text-gray-900 leading-tight">
            <?php echo htmlspecialchars($product['name']); ?>
        </h1>

        <div class="flex items-center gap-2">
            <?php if (!empty($rating['avg_rating']) && $rating['avg_rating'] > 0): ?>
                <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-md border border-green-100">
                    <span class="font-bold text-sm text-green-700">
                        <?php echo number_format($rating['avg_rating'], 1); ?>
                    </span>
                    <svg width="14" height="14" style="width: 14px; height: 14px;"
                        class="w-3.5 h-3.5 text-green-600 fill-current" viewBox="0 0 20 20">
                        <path
                            d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-500 underline decoration-dotted">
                    <?php echo $rating['total_reviews']; ?> reviews
                </span>
            <?php else: ?>
                <span class="text-sm text-gray-400">No ratings yet</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Product Section: Amazon-Style Responsive Layout -->
    <div class="bg-white lg:rounded-xl lg:shadow-sm lg:border lg:border-gray-100">
        <!-- Responsive Grid: Mobile (1 col) | Tablet/Desktop (3 cols) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 xl:gap-8 p-0 md:p-6">

            <!-- COLUMN 1: Thumbnail Gallery (Left - Tablet & Desktop, ≥768px) -->
            <?php if (!empty($images) && count($images) > 1): ?>
                <div class="hidden lg:block lg:col-span-1 xl:col-span-1 lg:order-1 product-thumbnail-sticky-wrapper">
                    <div class="thumbnail-gallery-vertical max-h-[600px] overflow-y-auto scrollbar-thin"
                        id="thumbnail-gallery-desktop">

                        <?php foreach ($images as $index => $image): ?>
                            <button
                                class="product-thumbnail group w-20 h-20 mb-2.5 rounded-md overflow-hidden border-2 bg-white transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-1 <?php echo $index === 0 ? 'active border-pink-500 shadow-sm bg-pink-50' : 'border-gray-300 hover:border-pink-400 hover:shadow-sm'; ?>"
                                data-index="<?php echo $index; ?>"
                                data-image-url="<?php echo SITE_URL . $image['image_url']; ?>"
                                data-zoom-image="<?php echo !empty($image['zoom_image_url']) ? SITE_URL . $image['zoom_image_url'] : ''; ?>"
                                aria-label="View image <?php echo $index + 1; ?> of <?php echo count($images); ?>"
                                aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>" role="button" tabindex="0">
                                <img src="<?php echo SITE_URL . $image['image_url']; ?>"
                                    alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name'] . ' - Image ' . ($index + 1)); ?>"
                                    class="w-full h-full object-cover transition-opacity duration-200 group-hover:opacity-90"
                                    loading="<?php echo $index < 4 ? 'eager' : 'lazy'; ?>" width="80" height="80"
                                    onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- COLUMN 2: Main Product Image (Center - Tablet & Desktop) / Top (Mobile) -->
            <div class="md:col-span-6 lg:col-span-6 xl:col-span-5 product-media-col lg:order-2"
                style="position: relative;">
                <div class="product-media-sticky-wrapper product-media-sticky" style="overflow: visible;">
                    <div class="relative">
                        <!-- Wishlist Icon (Top Right Corner) -->
                        <?php if (!Session::isAdmin()): ?>
                            <button onclick="toggleWishlist(<?php echo $product['product_id']; ?>)"
                                class="wishlist-btn-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> absolute top-2 right-2 z-40 bg-white/90 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-lg transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>"
                                aria-label="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                <svg class="w-5 h-5 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'text-gray-700'; ?>"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        <?php endif; ?>

                        <!-- Main Product Image with Amazon-Style Zoom -->
                        <div class="relative bg-white lg:rounded-lg overflow-visible product-image-container lg:border lg:border-gray-200 z-30"
                            id="main-image-container">
                            <div class="aspect-square flex items-center justify-center p-0 relative">
                                <?php if (!empty($images)): ?>
                                    <!-- Desktop (≥1024px): Desktop Image View -->
                                    <div class="hidden lg:block w-full h-full relative overflow-hidden product-zoom-wrapper cursor-crosshair"
                                        id="zoom-wrapper">
                                        <img id="main-image" src="<?php echo SITE_URL . $images[0]['image_url']; ?>"
                                            data-zoom-image="<?php echo !empty($images[0]['zoom_image_url']) ? SITE_URL . $images[0]['zoom_image_url'] : ''; ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="w-full h-full object-contain transition-opacity duration-200 product-zoom-image"
                                            loading="eager" draggable="false"
                                            onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                                        <div class="product-zoom-lens hidden absolute pointer-events-none z-20 border border-blue-500 bg-white/20"
                                            id="zoom-lens" style="width: 600px !important; height: 600px !important;"></div>
                                    </div>

                                    <!-- Tablet & Mobile (<1024px): Swipeable Carousel -->
                                    <div class="lg:hidden w-full h-full overflow-x-auto flex snap-x snap-mandatory scrollbar-hide product-mobile-gallery"
                                        id="product-main-gallery-mobile" style="scroll-snap-type: x mandatory;">
                                        <?php foreach ($images as $index => $image): ?>
                                            <div class="w-full h-full flex-shrink-0 snap-center flex items-center justify-center relative bg-white"
                                                style="scroll-snap-align: center;" data-index="<?php echo $index; ?>">
                                                <img src="<?php echo SITE_URL . $image['image_url']; ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                    class="max-w-full max-h-full object-contain p-2"
                                                    onclick="openImageZoomModal()"
                                                    loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                                                    onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Mobile Zoom Indicator (Overlay) -->
                                    <div class="lg:hidden absolute bottom-3 right-3 pointer-events-none">
                                        <div class="bg-black/50 backdrop-blur-sm text-white px-2 py-1 rounded text-xs">
                                            <span id="mobile-gallery-counter">1/
                                                <?php echo count($images); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Zoom Result Container (Flyout - Absolute positioned relative to container) -->
                            <!-- Using inline styles for positioning to be 100% safe against missing Tailwind JIT -->
                            <div class="product-zoom-result absolute top-0 border-2 border-red-500 bg-white shadow-2xl overflow-hidden pointer-events-none rounded-lg"
                                id="zoom-result"
                                style="display: none; left: 105%; width: 550px; height: 550px; z-index: 1000; visibility: visible;">
                                <img src="" alt="Zoomed product view"
                                    class="absolute top-0 left-0 max-w-none origin-top-left will-change-transform"
                                    draggable="false">
                            </div>

                            <!-- Zoom Icon (Mobile only) -->
                            <?php if (!empty($images)): ?>
                                <button onclick="openImageZoomModal()"
                                    class="lg:hidden absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    aria-label="Zoom image">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Mobile: Horizontal Thumbnail Gallery with Swipe (Below Main Image) -->
                        <?php if (!empty($images) && count($images) > 1): ?>
                            <div class="lg:hidden mt-4 px-1">
                                <div class="flex gap-2.5 overflow-x-auto pb-3 scrollbar-hide product-gallery-mobile"
                                    id="thumbnail-gallery-mobile"
                                    style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scroll-padding-left: 0.25rem;">
                                    <?php foreach ($images as $index => $image): ?>
                                        <button
                                            class="product-thumbnail-mobile flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-md overflow-hidden border-2 bg-white transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-1 <?php echo $index === 0 ? 'active border-pink-500 shadow-sm bg-pink-50' : 'border-gray-300 active:border-pink-400'; ?>"
                                            data-index="<?php echo $index; ?>"
                                            data-image-url="<?php echo SITE_URL . $image['image_url']; ?>"
                                            style="scroll-snap-align: start;"
                                            aria-label="View image <?php echo $index + 1; ?> of <?php echo count($images); ?>"
                                            role="button" tabindex="0">
                                            <img src="<?php echo SITE_URL . $image['image_url']; ?>"
                                                alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name'] . ' - Thumbnail ' . ($index + 1)); ?>"
                                                class="w-full h-full object-cover"
                                                loading="<?php echo $index < 6 ? 'lazy' : 'lazy'; ?>" width="64" height="64"
                                                onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- COLUMN 3: Product Information & Actions (Right - Tablet & Desktop) / Below Image (Mobile) -->
            <div
                class="md:col-span-6 lg:col-span-5 xl:col-span-6 px-4 lg:px-0 lg:order-3 flex flex-col md:block lg:flex lg:flex-col">
                <!-- Product Identity -->
                <div class="hidden lg:block lg:order-1">
                    <!-- Brand -->
                    <?php if (!empty($product['brand'])): ?>
                        <div class="text-sm text-gray-500 mb-2">
                            <span class="font-normal">
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- Desktop: Product Name (Hidden on Mobile/Tablet Portrait - Shown at Top) -->
                    <div class="hidden lg:block mb-3">
                        <h1 class="text-xl md:text-2xl font-semibold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>
                    </div>

                    <!-- Product Badges Row with Customer Ratings & Reviews -->
                    <div class="product-badges-ratings-row mb-3">
                        <!-- Product Badges -->
                        <div class="product-badges mb-2 lg:mb-0">
                            <?php if ($product['is_bestseller'] ?? false): ?>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-600 to-pink-700 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200"
                                    aria-label="Bestseller badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    Bestseller
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_featured'] ?? false): ?>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-500 to-pink-600 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200"
                                    aria-label="Featured badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    Featured
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_new_arrival'] ?? false): ?>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-pink-500 to-pink-600 text-white text-xs font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200"
                                    aria-label="New Arrival badge">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    New Arrival
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Customer Ratings & Reviews -->
                        <div class="customer-ratings-reviews">
                            <?php if (!empty($rating['avg_rating']) && $rating['avg_rating'] > 0): ?>
                                <a href="#product-reviews-section" onclick="scrollToReviews(event)" class="ratings-link"
                                    aria-label="View customer reviews: <?php echo number_format($rating['avg_rating'], 1); ?> out of 5 stars with <?php echo $rating['total_reviews']; ?> reviews">
                                    <div class="ratings-stars-container" role="img"
                                        aria-label="<?php echo number_format($rating['avg_rating'], 1); ?> out of 5 stars">
                                        <div class="ratings-stars" aria-hidden="true">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg width="20" height="20" style="width: 20px; height: 20px;"
                                                    class="star-icon <?php echo $i <= round($rating['avg_rating']) ? 'fill-current text-yellow-400' : 'text-gray-300'; ?>"
                                                    viewBox="0 0 20 20" aria-hidden="true">
                                                    <path
                                                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="ratings-value">
                                            <?php echo number_format($rating['avg_rating'], 1); ?>
                                        </span>
                                    </div>
                                    <span class="ratings-count">
                                        (
                                        <?php echo $rating['total_reviews']; ?>
                                        <?php echo $rating['total_reviews'] == 1 ? 'review' : 'reviews'; ?>)
                                    </span>
                                </a>
                            <?php else: ?>
                                <div class="ratings-no-reviews">
                                    <div class="ratings-stars-empty" role="img" aria-label="No ratings yet">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg width="20" height="20" style="width: 20px; height: 20px;"
                                                class="star-icon text-gray-300" fill="currentColor" viewBox="0 0 20 20"
                                                aria-hidden="true">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
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
                <div class="mb-4 pb-4 border-b border-gray-200 lg:order-3 lg:border-none lg:pb-2 lg:mb-2 order-1">
                    <div class="flex items-baseline gap-2 flex-wrap mb-1">
                        <span class="text-3xl font-semibold text-gray-900">₹
                            <?php echo number_format($price, 2); ?>
                        </span>
                        <?php if ($hasDiscount): ?>
                            <span class="text-lg text-gray-400 line-through">₹
                                <?php echo number_format($product['price'], 2); ?>
                            </span>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded-full">
                                <?php echo $discountPercent; ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-xs text-gray-500">Inclusive of all taxes</p>
                        <?php if ($hasDiscount): ?>
                            <p class="text-sm text-green-700 font-medium">You save
                                ₹
                                <?php echo number_format($product['price'] - $price, 2); ?>
                            </p>
                        <?php endif; ?>
                    </div>
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
                            if (count($highlights) >= 5)
                                break;
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

                // Remove the limit to allow "See More" to show everything
                // $highlights = array_slice($highlights, 0, 5); 
                ?>

                <?php if (!empty($highlights)): ?>
                    <div class="mb-4 pb-4 border-b border-gray-200 expandable-wrapper lg:order-8 order-6" data-type="list"
                        data-visible-items="3">
                        <h3 class="text-base font-semibold text-gray-900 mb-2">Key Highlights</h3>
                        <div class="expandable-content overflow-hidden">
                            <ul class="space-y-1.5" id="highlights-list">
                                <?php foreach ($highlights as $highlight): ?>
                                    <li class="flex items-start gap-2 text-sm text-gray-700">
                                        <svg width="16" height="16" style="width: 16px; height: 16px;"
                                            class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="flex-1 min-w-0 break-words break-all">
                                            <?php echo htmlspecialchars($highlight); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php if (count($highlights) > 3): ?>
                            <button class="see-more-btn" onclick="toggleExpansion(this)">
                                <span class="see-more-text">See More</span>
                                <svg class="see-more-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Variant Selection -->
                <?php if (!empty($variants)): ?>
                    <div class="mb-3 space-y-3 lg:order-2 order-2">
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
                                        <button
                                            onclick="selectColor('<?php echo htmlspecialchars($colorName); ?>', <?php echo $firstVariant['variant_id']; ?>)"
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
                                        <button
                                            onclick="selectSize('<?php echo htmlspecialchars($size); ?>', <?php echo $availableVariant ? $availableVariant['variant_id'] : 'null'; ?>)"
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
                                if (
                                    isset($existingAttr['attribute_id']) && isset($attr['attribute_id']) &&
                                    $existingAttr['attribute_id'] == $attr['attribute_id']
                                ) {
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
                usort($allAttributesWithValues, function ($a, $b) {
                    $orderA = isset($a['display_order']) ? (int) $a['display_order'] : 999;
                    $orderB = isset($b['display_order']) ? (int) $b['display_order'] : 999;
                    if ($orderA != $orderB) {
                        return $orderA <=> $orderB;
                    }
                    $nameA = strtolower($a['attribute_name'] ?? '');
                    $nameB = strtolower($b['attribute_name'] ?? '');
                    return strcmp($nameA, $nameB);
                });
                ?>

                <?php if (!empty($allAttributesWithValues)): ?>
                    <div class="mb-3 pb-3 border-gray-200 expandable-wrapper lg:order-7 order-5" data-type="table"
                        data-visible-rows="3">
                        <h3 class="text-base font-semibold text-gray-800 mb-3">Product Attributes</h3>
                        <div class="expandable-content overflow-hidden">
                            <table class="attr-table">
                                <tbody>
                                    <?php foreach ($allAttributesWithValues as $attr): ?>
                                        <tr class="text-sm">
                                            <td>
                                                <?php echo htmlspecialchars($attr['attribute_name']); ?>
                                            </td>
                                            <td>
                                                <?php
                                                // Handle different attribute types
                                                if ($attr['attribute_type'] === 'color' && !empty($attr['attribute_value'])) {
                                                    $colorValue = trim($attr['attribute_value']);
                                                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colorValue)) {
                                                        echo '<span class="inline-flex items-center gap-2">';
                                                        echo '<span class="w-4 h-4 rounded-full border border-gray-300 shadow-sm" style="background-color: ' . htmlspecialchars($colorValue) . ';"></span>';
                                                        echo '<span>' . htmlspecialchars($colorValue) . '</span>';
                                                        echo '</span>';
                                                    } else {
                                                        echo htmlspecialchars($attr['attribute_value']);
                                                    }
                                                } else {
                                                    echo nl2br(htmlspecialchars($attr['attribute_value']));
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($allAttributesWithValues) > 3): ?>
                            <button class="see-more-btn text-gray-500" onclick="toggleExpansion(this)">
                                <span class="see-more-text">See More</span>
                                <svg class="see-more-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Quantity Selector -->
                <!-- Quantity Selector -->
                <!-- Quantity Selector - STRICT ISOLATION -->

                <!-- MOBILE Version (< 1024px) -->
                <!-- Single line: [Decrease] [Input] [Increase] [Max Stock] -->
                <div class="lg:hidden mb-6 order-3 w-full">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">Quantity</label>
                    <div
                        class="flex items-center w-full h-12 border border-gray-300 rounded-lg bg-white overflow-hidden">
                        <!-- Decrease: Fixed width, no shrink -->
                        <button onclick="decreaseProductQuantity()"
                            class="w-12 h-full flex-shrink-0 flex items-center justify-center border-r border-gray-200 active:bg-gray-100 touch-manipulation text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>

                        <!-- Input: Fixed width (enough for 2-3 digits), no shrink, centered -->
                        <input type="number" value="1" min="1" max="<?php echo $product['max_order_quantity'] ?? 10; ?>"
                            class="product-quantity-input w-16 flex-shrink-0 h-full text-center border-none text-lg font-semibold text-gray-900 focus:outline-none bg-transparent appearance-none m-0"
                            readonly>

                        <!-- Increase: Fixed width, no shrink -->
                        <button onclick="increaseProductQuantity()"
                            class="w-12 h-full flex-shrink-0 flex items-center justify-center border-l border-gray-200 active:bg-gray-100 touch-manipulation text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>

                        <!-- Max Stock Label: Takes remaining space, truncates if needed -->
                        <div
                            class="flex-1 min-w-0 h-full flex items-center justify-center px-3 bg-gray-50 border-l border-gray-200">
                            <span class="text-xs text-gray-500 truncate w-full text-center">Max:
                                <?php echo $product['max_order_quantity'] ?? 10; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- DESKTOP Version (>= 1024px) -->
                <!-- Classic Commerce Design: Grouped, Compact, Trust-focused -->
                <div class="hidden lg:block mb-4 lg:order-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center">
                        <div class="flex items-center rounded border border-gray-300 bg-white">
                            <button onclick="decreaseProductQuantity()"
                                class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 border-r border-gray-300 transition-colors focus:outline-none active:bg-gray-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>
                            </button>
                            <input type="number" id="quantity" value="1" min="1"
                                max="<?php echo $product['max_order_quantity'] ?? 10; ?>"
                                class="product-quantity-input w-20 h-8 text-center border-none text-sm text-gray-900 focus:outline-none focus:ring-0 appearance-none p-0">
                            <button onclick="increaseProductQuantity()"
                                class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 border-l border-gray-300 transition-colors focus:outline-none active:bg-gray-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-xs text-gray-500 ml-3">Max:
                            <?php echo $product['max_order_quantity'] ?? 10; ?>
                        </span>
                    </div>
                </div>

                <!-- Primary Actions (CTA Section) -->
                <div class="mb-5 space-y-4 hidden lg:block lg:order-5">
                    <button onclick="handleAddToCart()"
                        class="w-full bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white font-medium py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-[1.01] flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Add to Cart
                    </button>

                    <div class="flex gap-2">
                        <?php if (!Session::isAdmin()): ?>
                            <button onclick="toggleWishlistDetail(<?php echo $product['product_id']; ?>)"
                                class="flex-1 border-2 border-gray-300 hover:border-pink-500 text-gray-700 hover:text-pink-600 font-medium py-2 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5 text-sm">
                                <svg class="w-4 h-4 <?php echo $inWishlist ? 'fill-current text-pink-600' : ''; ?>"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
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
                <div
                    class="border-t border-gray-200 py-5 flex justify-around lg:order-6 lg:border-none lg:pt-3 lg:pb-5 lg:justify-between lg:gap-4 order-4">
                    <!-- Delivery Info -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>
                            <p class="text-xs font-normal text-gray-700">Free Delivery</p>
                            <p class="text-xs text-gray-600">On orders above ₹500</p>
                        </div>
                    </div>

                    <!-- Return Policy -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <div>
                            <p class="text-xs font-normal text-gray-700">Easy Returns</p>
                            <p class="text-xs text-gray-600">7-day return policy</p>
                        </div>
                    </div>

                    <!-- Quality Assurance -->
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
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
        <div class="border-t border-gray-200 px-3 md:px-6 py-3 expandable-wrapper" data-type="text"
            data-visible-lines="3">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Description & Additional Information</h2>
            <div class="expandable-content prose max-w-none break-words overflow-hidden">
                <div class="description-text">
                    <p class="text-sm text-gray-700 mb-3 leading-relaxed break-words">
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? $product['short_description'] ?? '')); ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <?php if (!empty($product['brand'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm flex-shrink-0">Brand:</span>
                            <span class="text-gray-600 text-sm flex-1 break-words">
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm flex-shrink-0">Age Group:</span>
                        <span class="text-gray-600 text-sm flex-1 break-words">
                            <?php echo htmlspecialchars($product['age_group']); ?>
                            years
                        </span>
                    </div>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm">Gender:</span>
                        <span class="text-gray-600 text-sm">
                            <?php echo ucfirst($product['gender']); ?>
                        </span>
                    </div>
                    <?php if (!empty($product['material'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm">Material:</span>
                            <span class="text-gray-600 text-sm">
                                <?php echo htmlspecialchars($product['material']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="flex">
                        <span class="font-medium text-gray-700 w-28 text-sm">SKU:</span>
                        <span class="text-gray-600 text-sm">
                            <?php echo htmlspecialchars($product['sku']); ?>
                        </span>
                    </div>
                    <?php if (!empty($product['stock_quantity'])): ?>
                        <div class="flex">
                            <span class="font-medium text-gray-700 w-28 text-sm">Stock:</span>
                            <span class="text-gray-600 text-sm">
                                <?php echo $product['stock_quantity']; ?> available
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button class="see-more-btn mt-2" onclick="toggleExpansion(this)">
                <span class="see-more-text">See More</span>
                <svg class="see-more-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Reviews Section -->
    <div id="product-reviews-section" tabindex="-1"
        class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden scroll-mt-20">
        <div class="p-6 md:p-8">
            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">

                <!-- Left Column: Summary & Actions -->
                <div class="w-full lg:w-1/3 flex-shrink-0">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex flex-col">
                            <span class="text-5xl font-bold text-gray-900">
                                <?php echo number_format($rating['avg_rating'], 1); ?>
                            </span>
                            <div class="flex text-yellow-400 my-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="20" height="20" style="width: 20px; height: 20px;"
                                        class="w-5 h-5 <?php echo $i <= round($rating['avg_rating']) ? 'fill-current' : 'text-gray-300'; ?>"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="text-sm text-gray-500">
                                <?php echo number_format($rating['total_reviews']); ?>
                                global ratings
                            </span>
                        </div>
                    </div>

                    <!-- Rating Histogram -->
                    <div class="space-y-3 mb-8">
                        <?php
                        $total = max(1, $rating['total_reviews']);
                        for ($star = 5; $star >= 1; $star--):
                            $count = $rating['distribution'][$star] ?? 0;
                            $percent = ($count / $total) * 100;
                            ?>
                            <div class="flex items-center gap-3 text-sm">
                                <span
                                    class="w-12 text-gray-600 font-medium hover:text-pink-600 cursor-pointer hover:underline decoration-dotted">
                                    <?php echo $star; ?>
                                    star
                                </span>
                                <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full" style="width: <?php echo $percent; ?>%">
                                    </div>
                                </div>
                                <span class="w-10 text-right text-gray-500">
                                    <?php echo round($percent); ?>%
                                </span>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Write Review Action -->
                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Review this product</h3>
                        <p class="text-sm text-gray-600 mb-4">Share your thoughts with other customers</p>

                        <!-- Universal Review Button (Amazon Style) -->
                        <button id="writeReviewBtn" data-product-id="<?php echo $product['product_id']; ?>"
                            class="w-full py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-colors">
                            Write a customer review
                        </button>
                    </div>
                </div>

                <!-- Right Column: Filter & List -->
                <div class="flex-1 min-w-0">
                    <!-- Filter/Sort -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Top Reviews from India</h3>
                        <select
                            class="text-sm border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50"
                            onchange="filterReviews(this.value)">
                            <option value="recent">Most Recent</option>
                            <option value="top_rated">Top Rated</option>
                            <option value="lowest_rated">Lowest Rated</option>
                            <option value="verified">Verified Purchase</option>
                        </select>
                    </div>

                    <?php if (!empty($reviews)): ?>
                        <div class="space-y-6">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card mb-6 pb-6 border-b border-gray-100 last:border-0">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-gray-400">
                                            <?php if (!empty($review['profile_image'])): ?>
                                                <img src="<?php echo SITE_URL . $review['profile_image']; ?>"
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                            </p>
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <span>
                                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                                </span>
                                                <?php if (!empty($review['is_verified_purchase'])): ?>
                                                    <span class="text-green-600 font-medium flex items-center gap-0.5"
                                                        title="Verified Purchase">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Verified Purchase
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex text-yellow-400">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg width="16" height="16" style="width: 16px; height: 16px;"
                                                    class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'fill-current' : 'text-gray-300'; ?>"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if (!empty($review['title'])): ?>
                                            <h4 class="font-bold text-gray-900 text-sm">
                                                <?php echo htmlspecialchars($review['title']); ?>
                                            </h4>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-gray-700 text-sm leading-relaxed mb-3">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </div>

                                    <?php
                                    $media = !empty($review['media_json']) ? json_decode($review['media_json'], true) : [];
                                    if (!empty($media) && is_array($media)):
                                        ?>
                                        <div class="flex gap-2 mb-3 overflow-x-auto pb-2 scrollbar-hide">
                                            <?php foreach ($media as $m): ?>
                                                <img src="<?php echo SITE_URL . $m; ?>"
                                                    class="h-16 w-16 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity"
                                                    onclick="window.open(this.src, '_blank')">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($review['admin_reply'])): ?>
                                        <div class="mt-3 p-3 bg-gray-50 border-l-4 border-pink-500 rounded-r text-sm">
                                            <p class="font-semibold text-gray-900 mb-1">Response from Seller:</p>
                                            <p class="text-gray-700">
                                                <?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button onclick="openReportModal(<?php echo $review['review_id']; ?>)"
                                        class="text-xs text-gray-400 hover:text-red-500 font-medium flex items-center gap-1 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Report
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination/Load More (Placeholder) -->
                        <?php if ($rating['total_reviews'] > count($reviews)): ?>
                            <button class="text-sm font-medium text-pink-600 hover:text-pink-700 hover:underline">
                                See all reviews >
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                        <p class="text-gray-500 font-medium">No reviews yet.</p>
                        <p class="text-sm text-gray-400">Share your thoughts with other customers.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Review Submission Modal -->
<div id="review-form-modal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="document.getElementById('review-form-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            <form action="<?php echo SITE_URL; ?>/product/submit_review" method="POST" enctype="multipart/form-data"
                class="p-6">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="order_id" value="<?php echo $reviewEligibility['order_id'] ?? ''; ?>">
                <input type="hidden" name="is_verified_purchase" value="1">

                <!-- 1. Header (Reduced Height) -->
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-bold text-gray-900">Write a Review</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none"
                        onclick="document.getElementById('review-form-modal').classList.add('hidden')">
                        <span class="text-2xl leading-none">&times;</span>
                    </button>
                </div>

                <!-- 2. Product Info (Inline) -->
                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
                    <div class="w-10 h-10 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                        <?php
                        $thumb = !empty($images) ? $images[0]['image_url'] : '';
                        ?>
                        <?php if ($thumb): ?>
                            <img src="<?php echo SITE_URL . $thumb; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-full h-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            <?php
                            $variantParts = [];
                            if (!empty($variants)) {
                                // Just show "select variant" or similar if generic, but usually we know the order variant?
                                // For now, just show generic text or leave empty if dynamic
                            }
                            ?>
                            Share your experience
                        </p>
                    </div>
                </div>

                <!-- 3. Rating Section (Inline) -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-700">Rating:</span>
                    <div class="flex items-center gap-1" id="star-rating-input">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button"
                                class="star-input text-gray-300 hover:text-yellow-400 focus:outline-none transition-colors p-1"
                                data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)">
                                <svg width="24" height="24" style="width: 24px; height: 24px;" class="w-6 h-6 fill-current"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>

                <!-- 4. Review Title & Textarea (Smart Height) -->
                <div class="space-y-4 mb-2">
                    <input type="text" name="title"
                        class="block w-full border-0 border-b border-gray-200 focus:border-pink-500 focus:ring-0 p-3 text-sm font-medium placeholder-gray-400 bg-transparent transition-colors"
                        placeholder="Headline (optional)">

                    <textarea name="review_text" rows="1"
                        class="review-textarea w-full rounded-lg border border-gray-200 focus:border-pink-500 focus:ring-0 text-sm p-3 bg-white placeholder-gray-400 transition-colors"
                        placeholder="Write your review..." required></textarea>
                </div>

                <!-- 5. Image Upload (Hidden by Default) -->
                <div class="mb-4">
                    <div class="flex items-center">
                        <button type="button"
                            class="photo-upload-trigger text-sm font-medium flex items-center gap-2 py-2"
                            onclick="togglePhotoUpload()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Add Photos (optional)
                        </button>
                    </div>

                    <div id="photo-upload-section"
                        class="hidden mt-2 p-3 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                        <input type="file" name="review_images[]" multiple accept="image/*"
                            class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-pink-100 file:text-pink-700 hover:file:bg-pink-200">
                        <p class="text-xs text-gray-400 mt-1 pl-1">Max 3 images.</p>
                    </div>
                </div>

                <!-- 6. Submit Button (Right Aligned & Compact) -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-pink-600 text-white rounded-lg shadow-sm py-2 px-6 text-sm font-semibold hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function setRating(val) {
        document.getElementById('rating-value').value = val;
        document.querySelectorAll('.star-input').forEach((btn, idx) => {
            if (idx < val) {
                btn.classList.add('text-yellow-400');
                btn.classList.remove('text-gray-300');
            } else {
                btn.classList.add('text-gray-300');
                btn.classList.remove('text-yellow-400');
            }
        });
    }

    function filterReviews(sort) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sort);
        // Retain scroll position or hash
        window.location.href = url.toString() + '#product-reviews-section';
    }

    let currentReportReviewId = null;
    function openReportModal(reviewId) {
        if (!<?php echo Session::isLoggedIn() ? 'true' : 'false'; ?>) {
            window.location.href = '<?php echo SITE_URL; ?>/login?redirect=' + encodeURIComponent(window.location.href);
            return;
        }
        currentReportReviewId = reviewId;
        document.getElementById('report-modal').classList.remove('hidden');
    }

    async function submitReport(e) {
        e.preventDefault();
        if (!currentReportReviewId) return;

        const reason = document.getElementById('report-reason').value;
        const formData = new FormData();
        formData.append('review_id', currentReportReviewId);
        formData.append('reason', reason);

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/product/report_review', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            document.getElementById('report-modal').classList.add('hidden');
            if (data.success) {
                alert('Review reported successfully. Thank you.');
            } else {
                if (data.message) alert(data.message);
                else alert('Failed to report review.');
            }
        } catch (err) {
            alert('An error occurred.');
        }
    }
</script>

<!-- Report Modal -->
<div id="report-modal" class="hidden fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            onclick="document.getElementById('report-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full">
            <form onsubmit="submitReport(event)" class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Report Review</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for reporting</label>
                    <select id="report-reason"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm">
                        <option value="Inappropriate Content">Inappropriate Content</option>
                        <option value="Spam">Spam</option>
                        <option value="Fake Review">Fake Review</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mt-5 sm:mt-6 flex gap-3">
                    <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden')"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:text-sm">
                        Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
    <div class="mt-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4 px-4 md:px-0">Related Products</h2>
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-6 px-4 md:px-0 scrollbar-hide -mx-4 md:mx-0">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="flex-shrink-0 w-[45%] md:w-[25%] lg:w-[20%] snap-center pl-1">
                    <?php include VIEW_PATH . '/products/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
</div>

<!-- Mobile Sticky CTA Bar -->
<div
    class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50 px-4 py-3">
    <div class="container mx-auto flex gap-3 items-center">
        <?php if (!Session::isAdmin()): ?>
            <button onclick="toggleWishlistDetail(<?php echo $product['product_id']; ?>)"
                class="wishlist-btn-mobile-<?php echo $product['product_id']; ?> <?php echo $inWishlist ? 'in-wishlist' : ''; ?> flex-shrink-0 w-12 h-12 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:border-pink-500 hover:text-pink-600 transition-colors duration-200"
                aria-label="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                <svg class="w-6 h-6 <?php echo $inWishlist ? 'text-pink-600 fill-current' : 'currentColor'; ?>" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        <?php endif; ?>

        <button onclick="handleAddToCart()"
            class="flex-1 bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white font-semibold py-3 px-4 rounded-lg shadow-sm active:scale-[0.98] transition-all duration-200 flex items-center justify-center">
            Add to Cart
        </button>

        <button onclick="handleBuyNow()"
            class="flex-1 bg-white border border-pink-600 text-pink-600 hover:bg-pink-50 font-semibold py-3 px-4 rounded-lg shadow-sm active:scale-[0.98] transition-all duration-200">
            Buy Now
        </button>
    </div>
</div>

<script>
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
    // Updated to support multiple inputs (Mobile/Desktop Sync)
    function updateQuantityInputs(newQty) {
        // Find max from any input
        const firstInput = document.querySelector('.product-quantity-input');
        const maxQty = firstInput ? (parseInt(firstInput.getAttribute('max')) || 10) : 10;

        if (newQty > maxQty) newQty = maxQty;
        if (newQty < 1) newQty = 1;

        const inputs = document.querySelectorAll('.product-quantity-input');
        inputs.forEach(input => {
            input.value = newQty;
        });
    }

    function increaseProductQuantity() {
        const input = document.querySelector('.product-quantity-input');
        if (!input) return;

        let currentQty = parseInt(input.value) || 1;
        updateQuantityInputs(currentQty + 1);
    }

    function decreaseProductQuantity() {
        const input = document.querySelector('.product-quantity-input');
        if (!input) return;

        let currentQty = parseInt(input.value) || 1;
        if (currentQty > 1) {
            updateQuantityInputs(currentQty - 1);
        }
    }

    // Listen for manual input changes to sync all inputs
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.product-quantity-input');
        inputs.forEach(input => {
            input.addEventListener('change', function () {
                let val = parseInt(this.value) || 1;
                updateQuantityInputs(val);
            });
        });
    });

    // Add to Cart Handler
    async function handleAddToCart() {
        const productId = <?php echo $product['product_id']; ?>;
        const quantityInput = document.querySelector('.product-quantity-input');
        const quantity = quantityInput ? (parseInt(quantityInput.value) || 1) : 1;
        const variantId = selectedVariantId || null;

        await addToCart(productId, variantId, quantity);
    }

    // Buy Now Handler - Direct purchase (clears cart and adds only this product)
    async function handleBuyNow() {
        const productId = <?php echo $product['product_id']; ?>;
        const quantityInput = document.querySelector('.product-quantity-input');
        const quantity = quantityInput ? (parseInt(quantityInput.value) || 1) : 1;
        const variantId = selectedVariantId || null;

        try {
            const siteUrl = window.SITE_URL || (function () {
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

    /* Sticky Positioning for Product Images - Fixed Implementation */
    /* 
 * CRITICAL: Sticky requires:
 * 1. No overflow restrictions on parent containers
 * 2. Sticky element height < container height
 * 3. Proper wrapper structure
 * 4. Correct top offset matching navbar height
 */

    /* Base: Column container - must allow sticky context */
    .product-media-col {
        position: relative;
    }

    /* CRITICAL: Ensure parent containers don't block sticky */
    /* Target product details page containers specifically */
    .product-media-col {
        /* Ensure parent grid allows sticky */
    }

    /* Force visible overflow on product section container to allow sticky */
    .bg-white.rounded-xl {
        overflow: visible !important;
    }

    /* Ensure grid container allows sticky */
    .grid.grid-cols-1.md\:grid-cols-2.xl\:grid-cols-12 {
        overflow: visible !important;
    }

    /* Mobile (< 640px): Disable sticky - single column layout */
    @media (max-width: 639px) {

        .product-media-sticky-wrapper,
        .product-thumbnail-sticky-wrapper {
            position: static !important;
        }
    }

    /* Tablet (640px - 1023px): Disable sticky - natural scrolling */
    @media (min-width: 640px) and (max-width: 1023px) {

        .product-media-sticky-wrapper,
        .product-thumbnail-sticky-wrapper {
            position: sticky !important;
            top: 5rem;
        }
    }

    /* Laptop (1024px - 1279px): Enable sticky with height constraints */
    @media (min-width: 1024px) and (max-width: 1279px) {
        .product-media-sticky-wrapper {
            position: sticky;
            top: 5rem;
            /* 64px - matches navbar height (h-16) */
            align-self: start;
            height: fit-content;
            /* CRITICAL: Limit height to enable sticky - must be < details column height */
            max-height: calc(100vh - 4rem - 2rem);
            /* viewport - navbar - padding */
        }

        .product-thumbnail-sticky-wrapper {
            position: sticky;
            top: 4rem;
            align-self: start;
            height: fit-content;
            max-height: calc(100vh - 4rem - 2rem);
        }
    }

    /* Desktop (≥ 1280px): Enable sticky positioning */
    @media (min-width: 1280px) {
        .product-thumbnail-sticky-wrapper {
            position: sticky;
            top: 4rem;
            /* 64px - matches navbar height (h-16) to prevent overlap */
            align-self: start;
            height: fit-content;
            /* Sticky will naturally stop when bottom of element reaches bottom of scrolling container */
        }

        .product-media-sticky-wrapper {
            position: sticky;
            top: 4rem;
            /* 64px - matches navbar height (h-16) to prevent overlap */
            align-self: start;
            height: fit-content;
            /* Sticky will naturally stop when bottom of element reaches bottom of scrolling container */
        }

        /* Ensure smooth scrolling performance */
        .product-thumbnail-sticky-wrapper,
        .product-media-sticky-wrapper {
            will-change: transform;
        }
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
        min-height: 350px;
        background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
        max-width: 100%;
        overflow: visible;
        /* Changed from hidden to allow sticky positioning */
    }

    /* Tablet (640px - 1023px) */
    @media (min-width: 640px) and (max-width: 1023px) {
        .product-image-container {
            min-height: 400px;
        }
    }

    /* Laptop (1024px - 1279px) */
    @media (min-width: 1024px) and (max-width: 1279px) {
        .product-image-container {
            min-height: 450px;
            max-width: 100%;
        }
    }

    /* XL Desktop (≥1280px) */
    @media (min-width: 1280px) {
        .product-image-container {
            min-height: 500px;
            max-width: 100%;
        }
    }

    /* Ensure images respect grid boundaries and maintain aspect ratio */
    .product-zoom-wrapper,
    .product-zoom-wrapper img,
    #main-image,
    #main-image-mobile {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
    }

    .product-zoom-image,
    #main-image {
        object-fit: contain;
        object-position: center;
    }

    /* Ensure thumbnail gallery respects column width */
    .thumbnail-gallery-vertical {
        max-width: 100%;
    }

    .thumbnail-gallery-vertical .product-thumbnail {
        max-width: 100%;
    }

    .thumbnail-gallery-vertical .product-thumbnail img {
        object-fit: cover;
        max-width: 100%;
        max-height: 100%;
    }

    /* Mobile/Tablet Optimizations */
    @media (max-width: 1023px) {

        /* Touch-friendly thumbnail sizing */
        .product-thumbnail-mobile {
            min-width: 64px;
            min-height: 64px;
            width: 64px;
            height: 64px;
        }
    }

    /* Mobile Only (<640px) */
    @media (max-width: 639px) {
        .product-image-container {
            min-height: 300px;
        }
    }

    /* XL Desktop (≥1280px): Ensure zoom result positioning doesn't overflow */
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

    /* Ensure zoom result doesn't show on mobile/tablet/laptop */
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
        fill: #FACC15;
        /* yellow-400 - default filled stars */
    }

    .star-icon.text-gray-300 {
        fill: #D1D5DB;
        /* gray-300 - empty stars */
    }

    .ratings-stars-empty .star-icon {
        fill: #D1D5DB;
        /* gray-300 - no ratings stars */
    }

    .ratings-value {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        /* gray-700 - WCAG AA compliant */
        min-width: 2.5rem;
    }

    .ratings-count {
        font-size: 12px;
        color: #4B5563;
        /* gray-600 - WCAG AA compliant */
        white-space: nowrap;
        transition: color 0.2s ease-in-out;
    }

    .ratings-link:hover .ratings-count {
        color: #EC4899;
        /* pink-600 */
    }

    .ratings-no-reviews {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ratings-no-text {
        font-size: 12px;
        color: #6B7280;
        /* gray-500 - WCAG AA compliant */
        white-space: nowrap;
    }

    /* Desktop Behavior (≥ 1280px) */
    @media (min-width: 1280px) {
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

    /* Tablet/Laptop Behavior (640px – 1279px) */
    @media (min-width: 640px) and (max-width: 1279px) {
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
    @media (max-width: 639px) {
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

<!-- JSON-LD SEO Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "<?php echo htmlspecialchars($product['name']); ?>",
  "image": "<?php echo SITE_URL . $images[0]['image_url']; ?>",
  "description": "<?php echo htmlspecialchars($product['short_description']); ?>",
  "sku": "<?php echo htmlspecialchars($product['sku']); ?>",
  "brand": {
    "@type": "Brand",
    "name": "<?php echo htmlspecialchars($product['brand'] ?? 'Kid Bazar'); ?>"
  },
  "offers": {
    "@type": "Offer",
    "url": "<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>",
    "priceCurrency": "INR",
    "price": "<?php echo $product['sale_price'] ?? $product['price']; ?>",
    "availability": "<?php echo ($product['stock_quantity'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>"
  }
  <?php if ($rating['total_reviews'] > 0): ?>
                                                                          ,"aggregateRating": {
                                                                            "@type": "AggregateRating",
                                                                            "ratingValue": "<?php echo $rating['avg_rating']; ?>",
                                                                            "reviewCount": "<?php echo $rating['total_reviews']; ?>"
                                                                          },
                                                                          "review": [
                                                                            <?php
                                                                            $schemaReviews = [];
                                                                            foreach ($reviews as $r) {
                                                                                $reviewBody = json_encode(strip_tags($r['review_text']));
                                                                                $reviewTitle = json_encode(strip_tags($r['title']));
                                                                                $author = json_encode($r['first_name'] . ' ' . $r['last_name']);
                                                                                $date = date('Y-m-d', strtotime($r['created_at']));
                                                                                $schemaReviews[] = "{
            \"@type\": \"Review\",
            \"reviewRating\": {
              \"@type\": \"Rating\",
              \"ratingValue\": \"{$r['rating']}\"
            },
            \"author\": {
              \"@type\": \"Person\",
              \"name\": $author
            },
            \"datePublished\": \"$date\",
            \"reviewBody\": $reviewBody,
            \"name\": $reviewTitle
        }";
                                                                            }
                                                                            echo implode(',', $schemaReviews);
                                                                            ?>
                                                                          ]
  <?php endif; ?>
}
</script>