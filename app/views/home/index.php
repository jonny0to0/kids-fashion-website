<?php
$productModel = new Product();
?>
<!-- Hero Slider -->
<?php if (!empty($topProducts) || !empty($newProducts)): ?>
<section class="relative bg-gradient-to-br from-pink-600 via-purple-600 to-indigo-700 overflow-hidden" x-data="productSlider()" x-init="init()" @mouseenter="stopAutoplay()" @mouseleave="startAutoplay()">
    <div class="slider-container relative h-[500px] md:h-[600px]">
        <!-- Top Products Slide -->
        <?php if (!empty($topProducts)): ?>
        <div class="slide absolute inset-0 transition-opacity duration-1000" :class="currentSlide === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'">
            <div class="absolute inset-0 bg-gradient-to-r from-black/40 to-transparent"></div>
            <div class="container mx-auto px-4 h-full flex items-center relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center w-full">
                    <div class="text-white">
                        <h2 class="text-3xl md:text-5xl font-bold mb-4 animate-fade-in">Top Products</h2>
                        <p class="text-xl mb-6 text-gray-200">Discover our most popular items</p>
                        <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-pink-700 inline-block transition transform hover:scale-105 shadow-lg">
                            Shop Now
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach (array_slice($topProducts, 0, 4) as $product): ?>
                            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" class="bg-white rounded-lg overflow-hidden shadow-xl hover:shadow-2xl transition transform hover:scale-105">
                                <img src="<?php echo SITE_URL . ($product['primary_image'] ?? '/assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-40 object-cover">
                                <div class="p-3">
                                    <h3 class="font-bold text-sm mb-1 text-gray-800 line-clamp-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-pink-600 font-bold">₹<?php echo number_format($productModel->getPrice($product), 2); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- New Products Slide -->
        <?php if (!empty($newProducts)): ?>
        <div class="slide absolute inset-0 transition-opacity duration-1000" :class="currentSlide === 1 ? 'opacity-100 z-10' : 'opacity-0 z-0'">
            <div class="absolute inset-0 bg-gradient-to-r from-black/40 to-transparent"></div>
            <div class="container mx-auto px-4 h-full flex items-center relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center w-full">
                    <div class="text-white">
                        <h2 class="text-3xl md:text-5xl font-bold mb-4 animate-fade-in">New Arrivals</h2>
                        <p class="text-xl mb-6 text-gray-200">Fresh styles just for you</p>
                        <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-pink-700 inline-block transition transform hover:scale-105 shadow-lg">
                            Explore Now
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach (array_slice($newProducts, 0, 4) as $product): ?>
                            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" class="bg-white rounded-lg overflow-hidden shadow-xl hover:shadow-2xl transition transform hover:scale-105">
                                <img src="<?php echo SITE_URL . ($product['primary_image'] ?? '/assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-40 object-cover">
                                <div class="p-3">
                                    <h3 class="font-bold text-sm mb-1 text-gray-800 line-clamp-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-pink-600 font-bold">₹<?php echo number_format($productModel->getPrice($product), 2); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Slider Controls -->
    <?php if (!empty($topProducts) && !empty($newProducts)): ?>
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-20 flex space-x-2">
        <button @click="goToSlide(0)" 
                class="w-3 h-3 rounded-full transition-all duration-300" 
                :class="currentSlide === 0 ? 'bg-pink-600 w-8' : 'bg-white/50 hover:bg-white/70'"></button>
        <button @click="goToSlide(1)" 
                class="w-3 h-3 rounded-full transition-all duration-300" 
                :class="currentSlide === 1 ? 'bg-pink-600 w-8' : 'bg-white/50 hover:bg-white/70'"></button>
    </div>
    <?php endif; ?>
    
    <!-- Navigation Arrows -->
    <?php if (!empty($topProducts) && !empty($newProducts)): ?>
    <button @click="prevSlide()" class="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white p-3 rounded-full transition-all duration-300 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button @click="nextSlide()" class="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white p-3 rounded-full transition-all duration-300 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
    <?php endif; ?>
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


<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/product?category=<?php echo $category['category_id']; ?>" 
                   class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="text-gray-600"><?php echo $category['product_count'] ?? 0; ?> products</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

