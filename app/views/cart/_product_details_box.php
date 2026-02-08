<?php
/**
 * Cart Product Details Box Component
 * Displays comprehensive product information in the cart
 * 
 * @var array $item - Cart item data with all product details
 */
?>

<div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-4 hover:shadow-lg transition-shadow duration-200" 
     data-cart-item-id="<?php echo $item['cart_item_id']; ?>">
    
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Product Image -->
        <div class="flex-shrink-0">
            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $item['slug']; ?>" 
               class="block w-32 md:w-40">
                <div class="relative w-full overflow-hidden bg-gray-50 rounded-lg border border-gray-200 hover:border-pink-300 transition-colors" style="aspect-ratio: 4/5;">
                    <img src="<?php echo SITE_URL . ($item['image'] ?? '/assets/images/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         class="absolute inset-0 w-full h-full object-cover object-center"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                </div>
            </a>
        </div>
        
        <!-- Product Information -->
        <div class="flex-1 min-w-0">
            <!-- Product Name -->
            <h3 class="font-bold text-lg md:text-xl mb-2 text-gray-900">
                <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $item['slug']; ?>" 
                   class="hover:text-pink-600 transition-colors">
                    <?php echo htmlspecialchars($item['name']); ?>
                </a>
            </h3>
            
            <!-- Variant Details -->
            <?php if ($item['size'] || $item['color']): ?>
                <div class="flex flex-wrap gap-3 mb-3">
                    <?php if ($item['size']): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            Size: <?php echo htmlspecialchars($item['size']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($item['color']): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <?php if ($item['color_code']): ?>
                                <span class="inline-block w-4 h-4 rounded-full mr-2 border border-gray-300" 
                                      style="background-color: <?php echo htmlspecialchars($item['color_code']); ?>"></span>
                            <?php endif; ?>
                            Color: <?php echo htmlspecialchars($item['color']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Seller Information -->
            <?php if (!empty($item['seller_name'])): ?>
                <div class="mb-2">
                    <span class="text-sm text-gray-600">
                        Sold by: <span class="font-medium text-gray-800"><?php echo htmlspecialchars($item['seller_name']); ?></span>
                    </span>
                </div>
            <?php endif; ?>
            
            <!-- Estimated Delivery -->
            <div class="mb-3">
                <span class="text-sm text-green-600 font-medium">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Delivery by <?php echo $item['estimated_delivery_formatted']; ?>
                </span>
            </div>
            
            <!-- Pricing Details -->
            <div class="mb-4">
                <div class="flex items-baseline gap-3 flex-wrap">
                    <!-- Current Price -->
                    <span class="text-2xl font-bold text-pink-600">
                        ₹<?php echo number_format($item['price'], 2); ?>
                    </span>
                    
                    <!-- Original Price (if discounted) -->
                    <?php if ($item['discount_percentage'] > 0): ?>
                        <span class="text-lg text-gray-500 line-through">
                            ₹<?php echo number_format($item['original_price'], 2); ?>
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800">
                            <?php echo $item['discount_percentage']; ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Price per unit -->
                <p class="text-sm text-gray-600 mt-1">
                    ₹<?php echo number_format($item['price'], 2); ?> per item
                </p>
            </div>
            
            <!-- Stock Status -->
            <?php if ($item['available_stock'] <= 5 && $item['available_stock'] > 0): ?>
                <div class="mb-3">
                    <span class="text-sm text-orange-600 font-medium">
                        Only <?php echo $item['available_stock']; ?> left in stock!
                    </span>
                </div>
            <?php elseif ($item['available_stock'] <= 0): ?>
                <div class="mb-3">
                    <span class="text-sm text-red-600 font-medium">
                        Out of Stock
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quantity Controller & Actions -->
        <div class="flex flex-col md:items-end gap-4">
            <!-- Quantity Controller -->
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Qty:</label>
                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                    <button onclick="decreaseQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['max_quantity']; ?>)" 
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            aria-label="Decrease quantity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    
                    <input type="number" 
                           id="quantity-<?php echo $item['cart_item_id']; ?>"
                           value="<?php echo $item['quantity']; ?>" 
                           min="1" 
                           max="<?php echo $item['max_quantity']; ?>"
                           onchange="updateQuantity(<?php echo $item['cart_item_id']; ?>, this.value, <?php echo $item['max_quantity']; ?>)"
                           class="w-16 text-center border-0 focus:ring-0 focus:outline-none py-1"
                           aria-label="Quantity">
                    
                    <button onclick="increaseQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['max_quantity']; ?>)" 
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            aria-label="Increase quantity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Item Total -->
            <div class="text-right">
                <p class="text-sm text-gray-600">Item Total:</p>
                <p class="text-xl font-bold text-gray-900" id="item-total-<?php echo $item['cart_item_id']; ?>">
                    ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </p>
            </div>
            
            <!-- Cart Actions -->
            <div class="flex flex-col gap-2 w-full md:w-auto">
                <!-- Save for Later -->
                <?php if (Session::isLoggedIn()): ?>
                    <button onclick="saveForLater(<?php echo $item['cart_item_id']; ?>)" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Save for Later
                    </button>
                <?php endif; ?>
                
                <!-- Remove -->
                <button onclick="removeCartItem(<?php echo $item['cart_item_id']; ?>)" 
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Remove
                </button>
            </div>
        </div>
    </div>
</div>

