<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8 text-gray-900">Shopping Cart</h2>
    
    <?php if (!empty($items)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <!-- Cart Items -->
            <div class="lg:col-span-3">
                <div class="space-y-4">
                    <?php foreach ($items as $item): ?>
                        <?php include VIEW_PATH . '/cart/_product_details_box.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 sticky top-20">
                    <h3 class="font-bold text-xl mb-6 text-gray-900">Order Summary</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal (<?php echo $total['item_count'] ?? 0; ?> items)</span>
                            <span class="font-medium">₹<?php echo number_format($total['total'] ?? 0, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Shipping</span>
                            <span class="font-medium">
                                <?php 
                                $shipping = ($total['total'] ?? 0) >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
                                if ($shipping == 0) {
                                    echo '<span class="text-green-600">Free</span>';
                                } else {
                                    echo '₹' . number_format($shipping, 2);
                                }
                                ?>
                            </span>
                        </div>
                        <?php if (($total['total'] ?? 0) < FREE_SHIPPING_THRESHOLD): ?>
                            <div class="text-sm text-gray-600 bg-green-50 p-2 rounded">
                                <span class="font-medium text-green-700">
                                    Add ₹<?php echo number_format(FREE_SHIPPING_THRESHOLD - ($total['total'] ?? 0), 2); ?> more for free shipping!
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-lg text-gray-900">Total</span>
                            <span class="font-bold text-2xl text-pink-600">
                                ₹<?php echo number_format(($total['total'] ?? 0) + $shipping, 2); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Place Order Button -->
                    <a href="<?php echo SITE_URL; ?>/checkout" 
                       class="block w-full bg-pink-600 text-white text-center py-4 rounded-lg hover:bg-pink-700 font-bold text-lg shadow-md hover:shadow-lg transition-all duration-200 mb-3">
                        Place Order
                    </a>
                    
                    <!-- Continue Shopping -->
                    <a href="<?php echo SITE_URL; ?>/product" 
                       class="block w-full bg-white text-gray-700 text-center py-3 rounded-lg border-2 border-gray-300 hover:border-gray-400 font-medium transition-colors">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 text-lg mb-6">Start adding products to your cart!</p>
            <a href="<?php echo SITE_URL; ?>/product" 
               class="inline-block bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-medium transition-colors">
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

