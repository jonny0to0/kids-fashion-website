<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Product Images -->
            <div>
                <?php if (!empty($images)): ?>
                    <div class="mb-4">
                        <img id="main-image" src="<?php echo SITE_URL . $images[0]['image_url']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-96 object-contain rounded-lg">
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="grid grid-cols-4 gap-2">
                            <?php foreach ($images as $image): ?>
                                <img src="<?php echo SITE_URL . $image['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name']); ?>"
                                     onclick="document.getElementById('main-image').src = this.src"
                                     class="w-full h-24 object-cover rounded border-2 border-gray-200 hover:border-pink-500 cursor-pointer">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Product Details -->
            <div>
                <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Rating -->
                <?php if (!empty($rating['avg_rating'])): ?>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-5 h-5 <?php echo $i <= round($rating['avg_rating']) ? 'fill-current' : 'text-gray-300'; ?>" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <span class="ml-2 text-gray-600"><?php echo number_format($rating['avg_rating'], 1); ?> (<?php echo $rating['total_reviews']; ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <!-- Price -->
                <div class="mb-6">
                    <?php
                    $productModel = new Product();
                    $price = $productModel->getPrice($product);
                    $hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
                    ?>
                    <span class="text-4xl font-bold text-pink-600">₹<?php echo number_format($price, 2); ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="text-2xl text-gray-500 line-through ml-2">₹<?php echo number_format($product['price'], 2); ?></span>
                        <span class="ml-2 text-green-600 font-medium">
                            <?php echo round((($product['price'] - $price) / $product['price']) * 100); ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Variants -->
                <?php if (!empty($variants)): ?>
                    <div class="mb-6">
                        <label class="block font-medium mb-2">Size</label>
                        <select id="variant-select" class="border border-gray-300 rounded px-3 py-2">
                            <?php foreach ($variants as $variant): ?>
                                <option value="<?php echo $variant['variant_id']; ?>" 
                                        data-price="<?php echo $price + $variant['additional_price']; ?>"
                                        data-stock="<?php echo $variant['stock_quantity']; ?>">
                                    <?php echo htmlspecialchars($variant['size']); ?>
                                    <?php if ($variant['color']): ?>
                                        - <?php echo htmlspecialchars($variant['color']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <!-- Add to Cart -->
                <div class="flex gap-4 mb-6">
                    <input type="number" id="quantity" value="1" min="1" 
                           max="<?php echo $product['max_order_quantity']; ?>"
                           class="w-20 border border-gray-300 rounded px-3 py-2">
                    <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                            class="flex-1 bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-bold">
                        Add to Cart
                    </button>
                </div>
                
                <!-- Product Info -->
                <div class="border-t pt-6">
                    <h3 class="font-bold mb-2">Product Details</h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['short_description'] ?? $product['description']); ?></p>
                    
                    <ul class="space-y-2 text-sm">
                        <li><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></li>
                        <li><strong>Age Group:</strong> <?php echo htmlspecialchars($product['age_group']); ?> years</li>
                        <li><strong>Gender:</strong> <?php echo ucfirst($product['gender']); ?></li>
                        <li><strong>Material:</strong> <?php echo htmlspecialchars($product['material'] ?? 'N/A'); ?></li>
                        <li><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="mt-12 border-t pt-8">
            <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
            <?php if (!empty($reviews)): ?>
                <div class="space-y-6">
                    <?php foreach ($reviews as $review): ?>
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'fill-current' : 'text-gray-300'; ?>" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 font-medium"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></span>
                                <span class="ml-2 text-gray-500 text-sm"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <?php if ($review['title']): ?>
                                <h4 class="font-bold mb-1"><?php echo htmlspecialchars($review['title']); ?></h4>
                            <?php endif; ?>
                            <p class="text-gray-600"><?php echo htmlspecialchars($review['review_text']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="mt-12 border-t pt-8">
                <h2 class="text-2xl font-bold mb-6">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php include VIEW_PATH . '/products/_product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

