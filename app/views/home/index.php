<?php
$productModel = new Product();
?>
<!-- Enterprise Hero Banner Slider -->
<?php if (!empty($heroBanners) && is_array($heroBanners)): ?>
<?php 
    // Filter banners - include if either content or image is enabled
    $validBanners = [];
    foreach ($heroBanners as $index => $banner) {
        // Check if content is enabled (default to true for backward compatibility)
        $contentEnabled = isset($banner['content_enabled']) ? (bool)$banner['content_enabled'] : true;
        // Check if image is enabled (default to true for backward compatibility)
        $imageEnabled = isset($banner['image_enabled']) ? (bool)$banner['image_enabled'] : true;
        
        // Determine which image to use based on device
        $isMobile = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $bannerImage = $isMobile ? ($banner['mobile_image'] ?? $banner['desktop_image'] ?? '') : ($banner['desktop_image'] ?? $banner['mobile_image'] ?? '');
        
        // Include banner if:
        // 1. Content is enabled (banner can show content only), OR
        // 2. Image is enabled AND has a valid image
        $hasValidImage = !empty($bannerImage) && trim($bannerImage) !== '';
        
        if ($contentEnabled || ($imageEnabled && $hasValidImage)) {
            $validBanners[] = $banner;
        } else {
            // Log banner filtering in development mode
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("Home index view - Banner '{$banner['title']}' (ID: {$banner['banner_id']}) filtered out: content disabled and (image disabled or no valid image found)");
            }
        }
    }
    
    // Log if all banners were filtered out
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && !empty($heroBanners) && empty($validBanners)) {
        error_log("Home index view - All " . count($heroBanners) . " banners were filtered out due to missing image paths.");
    }
    
    // Only show slider if we have valid banners
    if (!empty($validBanners)):
        // Get first banner settings safely
        $firstBanner = $validBanners[0] ?? null;
        $autoSlide = !empty($firstBanner['auto_slide_enabled']) ? 'true' : 'false';
        $slideDuration = isset($firstBanner['slide_duration']) ? (int)$firstBanner['slide_duration'] : 5000;
?>
<section class="hero-banner-section relative overflow-hidden w-full" 
         id="hero-banner-slider"
         data-banners='<?php echo htmlspecialchars(json_encode($validBanners), ENT_QUOTES, 'UTF-8'); ?>'
         data-auto-slide="<?php echo $autoSlide; ?>"
         data-slide-duration="<?php echo $slideDuration; ?>">
    <div class="hero-banner-container relative w-full">
        <?php foreach ($validBanners as $index => $banner): ?>
            <?php 
            // Determine which image to use based on device
            $isMobile = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $bannerImage = $isMobile ? ($banner['mobile_image'] ?? $banner['desktop_image'] ?? '') : ($banner['desktop_image'] ?? $banner['mobile_image'] ?? '');
            ?>
            <div class="hero-banner-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                 data-slide-index="<?php echo $index; ?>">
                <div class="relative w-full h-full">
                    <?php 
                    // Check if image is enabled (default to true if not set for backward compatibility)
                    $imageEnabled = isset($banner['image_enabled']) ? (bool)$banner['image_enabled'] : true;
                    // Check if content is enabled (default to true if not set for backward compatibility)
                    $contentEnabled = isset($banner['content_enabled']) ? (bool)$banner['content_enabled'] : true;
                    ?>
                    
                    <?php if ($imageEnabled && !empty($bannerImage)): ?>
                        <!-- Banner Image with Lazy Loading -->
                        <picture class="w-full h-full block">
                            <source srcset="<?php echo SITE_URL . $bannerImage; ?>" type="image/webp">
                            <img src="<?php echo SITE_URL . $bannerImage; ?>" 
                                 alt="<?php echo htmlspecialchars($banner['title'] ?? 'Banner'); ?>"
                                 class="w-full h-full object-cover object-center"
                                 <?php echo $index === 0 ? '' : 'loading="lazy"'; ?>
                                 fetchpriority="<?php echo $index === 0 ? 'high' : 'low'; ?>">
                        </picture>
                    <?php endif; ?>
                    
                    <?php if ($contentEnabled): ?>
                        <!-- Overlay for better text readability (only if image is enabled) -->
                        <?php if ($imageEnabled && !empty($bannerImage)): ?>
                            <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-black/20 to-transparent"></div>
                        <?php endif; ?>
                        
                        <!-- Banner Content -->
                        <div class="<?php echo $imageEnabled && !empty($bannerImage) ? 'absolute' : 'relative'; ?> inset-0 flex items-center <?php echo !$imageEnabled ? 'bg-gradient-to-r from-pink-500 to-purple-600 min-h-[400px]' : ''; ?>">
                            <div class="container mx-auto px-4 sm:px-6 lg:px-8 w-full">
                                <div class="max-w-2xl">
                                    <?php if (!empty($banner['title'])): ?>
                                        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold <?php echo $imageEnabled && !empty($bannerImage) ? 'text-white' : 'text-white'; ?> mb-4 leading-tight">
                                            <?php echo htmlspecialchars($banner['title']); ?>
                                        </h2>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($banner['description'])): ?>
                                        <p class="text-lg sm:text-xl md:text-2xl <?php echo $imageEnabled && !empty($bannerImage) ? 'text-gray-100' : 'text-white'; ?> mb-6 font-light">
                                            <?php echo htmlspecialchars($banner['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($banner['cta_text']) && !empty($banner['cta_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($banner['cta_url']); ?>" 
                                           class="inline-flex items-center gap-2 bg-white text-pink-600 px-8 py-4 rounded-xl font-bold text-base sm:text-lg hover:bg-pink-50 transition-all duration-300 shadow-2xl hover:shadow-pink-500/50 hover:scale-105 active:scale-95">
                                            <span><?php echo htmlspecialchars($banner['cta_text']); ?></span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Navigation Arrows -->
    <?php if (count($validBanners) > 1): ?>
        <button class="hero-nav-arrow hero-nav-prev absolute left-3 sm:left-4 md:left-6 top-1/2 transform -translate-y-1/2 z-30 bg-white/80 hover:bg-white text-gray-800 rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110"
                aria-label="Previous slide"
                onclick="heroBannerSlider.prevSlide()">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button class="hero-nav-arrow hero-nav-next absolute right-3 sm:right-4 md:right-6 top-1/2 transform -translate-y-1/2 z-30 bg-white/80 hover:bg-white text-gray-800 rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110"
                aria-label="Next slide"
                onclick="heroBannerSlider.nextSlide()">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    <?php endif; ?>
    
    <!-- Pagination Dots -->
    <?php if (count($validBanners) > 1): ?>
        <div class="absolute bottom-6 sm:bottom-8 left-1/2 transform -translate-x-1/2 z-30 flex items-center gap-2">
            <?php foreach ($validBanners as $index => $banner): ?>
                <button class="hero-pagination-dot w-3 h-3 rounded-full transition-all duration-300 <?php echo $index === 0 ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'; ?>"
                        aria-label="Go to slide <?php echo $index + 1; ?>"
                        onclick="heroBannerSlider.goToSlide(<?php echo $index; ?>)"
                        data-slide-index="<?php echo $index; ?>">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php endif; // End if validBanners ?>
<?php endif; // End if heroBanners ?>

<!-- Category Slider Section -->
<?php if (!empty($categories)): ?>
<section class="py-8 md:py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-6 md:mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Shop by Category</h2>
            <p class="text-gray-600 text-sm md:text-base">Browse our wide range of categories</p>
        </div>
        
        <div class="category-slider-wrapper relative px-12 md:px-16" 
             x-data="categorySlider(<?php echo count($categories); ?>)"
             x-init="init()"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd($event)">
            
            <!-- Slider Container -->
            <div class="category-slider-container overflow-hidden">
                <div class="category-slider-track flex transition-transform duration-300 ease-out"
                     :style="`transform: translateX(-${currentIndex * itemWidth}px)`">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-slider-item flex-shrink-0 px-2 md:px-3">
                            <a href="<?php echo SITE_URL; ?>/product?category=<?php echo urlencode($category['slug']); ?>" 
                               class="category-card flex flex-col items-center group cursor-pointer">
                                <div class="category-image-wrapper mb-3 md:mb-4">
                                    <?php 
                                    $categoryImage = !empty($category['image']) 
                                        ? SITE_URL . $category['image']
                                        : SITE_URL . '/assets/images/placeholder.jpg';
                                    ?>
                                    <img src="<?php echo $categoryImage; ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>"
                                         class="category-image rounded-full w-20 h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 object-cover border-4 border-gray-100 group-hover:border-pink-300 transition-all duration-300 shadow-md group-hover:shadow-lg transform group-hover:scale-105"
                                         loading="lazy">
                                </div>
                                <div class="category-name text-center">
                                    <h3 class="text-xs md:text-sm font-semibold text-gray-800 group-hover:text-pink-600 transition-colors duration-300 line-clamp-2 max-w-[100px] md:max-w-[120px]">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h3>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-slider-item flex-shrink-0 px-2 md:px-3">
                            <a href="<?php echo SITE_URL; ?>/product?category=<?php echo urlencode($category['slug']); ?>" 
                               class="category-card flex flex-col items-center group cursor-pointer">
                                <div class="category-image-wrapper mb-3 md:mb-4">
                                    <?php 
                                    $categoryImage = !empty($category['image']) 
                                        ? SITE_URL . $category['image']
                                        : SITE_URL . '/assets/images/placeholder.jpg';
                                    ?>
                                    <img src="<?php echo $categoryImage; ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>"
                                         class="category-image rounded-full w-20 h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 object-cover border-4 border-gray-100 group-hover:border-pink-300 transition-all duration-300 shadow-md group-hover:shadow-lg transform group-hover:scale-105"
                                         loading="lazy">
                                </div>
                                <div class="category-name text-center">
                                    <h3 class="text-xs md:text-sm font-semibold text-gray-800 group-hover:text-pink-600 transition-colors duration-300 line-clamp-2 max-w-[100px] md:max-w-[120px]">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h3>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
            
            <!-- Navigation Arrows -->
            <button x-show="showPrevArrow" 
                    @click="slidePrev()"
                    class="category-slider-nav category-slider-nav-prev absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow-lg rounded-full p-2 md:p-3 hover:bg-pink-50 transition-all duration-300 z-20 border border-gray-200 hover:border-pink-300 hover:shadow-xl"
                    aria-label="Previous categories">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <button x-show="showNextArrow" 
                    @click="slideNext()"
                    class="category-slider-nav category-slider-nav-next absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow-lg rounded-full p-2 md:p-3 hover:bg-pink-50 transition-all duration-300 z-20 border border-gray-200 hover:border-pink-300 hover:shadow-xl"
                    aria-label="Next categories">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <!-- Pagination Dots -->
            <div x-show="totalPages > 1" 
                 class="flex justify-center items-center space-x-2 mt-6 md:mt-8">
                <template x-for="pageIndex in pagesArray" :key="pageIndex">
                    <button @click="goToPage(pageIndex)"
                            class="pagination-dot transition-all duration-300 rounded-full"
                            :class="currentPage === pageIndex ? 'bg-pink-600 w-8 h-3' : 'bg-gray-300 w-3 h-3 hover:bg-gray-400'"
                            :aria-label="`Go to page ${pageIndex + 1}`"
                            :aria-current="currentPage === pageIndex ? 'true' : 'false'">
                    </button>
                </template>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>





<!-- Best Deals Section -->
<?php if (!empty($bestDeals)): ?>
<section class="py-12 bg-gradient-to-br from-red-50 to-pink-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-2 text-gray-800">Best Deals</h2>
            <p class="text-gray-600">Don't miss out on these amazing offers!</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($bestDeals as $product): ?>
                <?php include VIEW_PATH . '/products/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-pink-700 inline-block transition">
                View All Deals
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Top Selling Products Section -->
<?php if (!empty($topSelling)): ?>
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-2 text-gray-800">Top Selling Products</h2>
            <p class="text-gray-600">Our customers' favorites</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($topSelling as $product): ?>
                <?php include VIEW_PATH . '/products/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-pink-700 inline-block transition">
                View All Products
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Featured Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <?php include VIEW_PATH . '/products/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
