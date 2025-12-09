<?php
$productModel = new Product();
$price = $productModel->getPrice($product);
$originalPrice = $product['price'];
$hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$image = $product['primary_image'] ?? '/assets/images/placeholder.jpg';
?>
<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
    <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>">
        <div class="relative">
            <img src="<?php echo SITE_URL . $image; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="w-full h-64 object-cover">
            <?php if ($hasDiscount): ?>
                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm">
                    <?php echo round((($originalPrice - $price) / $originalPrice) * 100); ?>% OFF
                </span>
            <?php endif; ?>
        </div>
    </a>
    
    <div class="p-4">
        <h3 class="font-bold text-lg mb-2">
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" 
               class="hover:text-pink-600">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <div class="flex items-center justify-between mb-3">
            <div>
                <span class="text-2xl font-bold text-pink-600">₹<?php echo number_format($price, 2); ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="text-gray-500 line-through ml-2">₹<?php echo number_format($originalPrice, 2); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">
            Add to Cart
        </button>
    </div>
</div>

