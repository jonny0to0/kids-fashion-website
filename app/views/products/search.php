<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">
        Search Results for: "<?php echo htmlspecialchars($query); ?>"
    </h2>
    
    <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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
            <p class="text-gray-500 text-lg">No products found matching your search.</p>
        </div>
    <?php endif; ?>
</div>

