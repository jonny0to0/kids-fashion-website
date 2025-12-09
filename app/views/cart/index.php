<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8">Shopping Cart</h2>
    
    <?php if (!empty($items)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center border-b pb-6 mb-6 last:border-b-0">
                            <img src="<?php echo SITE_URL . ($item['image'] ?? '/assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-24 h-24 object-cover rounded">
                            
                            <div class="flex-1 ml-4">
                                <h3 class="font-bold text-lg">
                                    <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $item['slug']; ?>" 
                                       class="hover:text-pink-600">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h3>
                                <?php if ($item['size']): ?>
                                    <p class="text-gray-600 text-sm">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                <?php endif; ?>
                                <p class="text-pink-600 font-bold mt-2">₹<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" 
                                       onchange="updateCartItem(<?php echo $item['cart_item_id']; ?>, this.value)"
                                       class="w-16 border border-gray-300 rounded px-2 py-1">
                                
                                <button onclick="removeCartItem(<?php echo $item['cart_item_id']; ?>)" 
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h3 class="font-bold text-xl mb-4">Order Summary</h3>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($total['total'] ?? 0, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping</span>
                            <span>
                                <?php 
                                $shipping = ($total['total'] ?? 0) >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
                                echo $shipping == 0 ? 'Free' : '₹' . number_format($shipping, 2);
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>₹<?php echo number_format(($total['total'] ?? 0) + $shipping, 2); ?></span>
                        </div>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/checkout" 
                       class="block w-full bg-pink-600 text-white text-center py-3 rounded-lg hover:bg-pink-700 font-bold">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-gray-500 text-lg mb-4">Your cart is empty.</p>
            <a href="<?php echo SITE_URL; ?>/product" 
               class="inline-block bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

