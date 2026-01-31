<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 bg-white p-6 rounded-lg shadow-md h-fit">
            <h3 class="font-bold text-lg mb-4">Filters</h3>
            
            <form method="GET" action="<?php echo SITE_URL; ?>/product">
                <!-- Gender -->
                <div class="mb-4">
                    <label class="block font-medium mb-2">Gender</label>
                    <select name="gender" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">All</option>
                        <option value="boy" <?php echo ($filters['gender'] == 'boy') ? 'selected' : ''; ?>>Boy</option>
                        <option value="girl" <?php echo ($filters['gender'] == 'girl') ? 'selected' : ''; ?>>Girl</option>
                        <option value="unisex" <?php echo ($filters['gender'] == 'unisex') ? 'selected' : ''; ?>>Unisex</option>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="mb-4">
                    <label class="block font-medium mb-2">Price Range</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" placeholder="Min" 
                               value="<?php echo $filters['min_price']; ?>"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                        <input type="number" name="max_price" placeholder="Max" 
                               value="<?php echo $filters['max_price']; ?>"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">
                    Apply Filters
                </button>
            </form>
        </aside>
        
        <!-- Products Grid -->
        <main class="flex-1">
            <!-- Sort Options -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Products</h2>
                <select id="sort-select" class="border border-gray-300 rounded px-3 py-2" 
                        onchange="window.location.href = '<?php echo SITE_URL; ?>/product?<?php echo http_build_query(array_merge($filters, ['sort' => ''])); ?>&sort=' + this.value">
                    <option value="">Sort by</option>
                    <option value="price_low" <?php echo ($filters['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo ($filters['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name" <?php echo ($filters['sort'] == 'name') ? 'selected' : ''; ?>>Name</option>
                    <option value="popularity" <?php echo ($filters['sort'] == 'popularity') ? 'selected' : ''; ?>>Popularity</option>
                </select>
            </div>
            
            <!-- Products -->
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($products as $product): ?>
                        <?php include VIEW_PATH . '/products/_product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="mt-8">
                    <?php echo $pagination->render(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No products found.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

