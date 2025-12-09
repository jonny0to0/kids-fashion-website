<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mb-6">
            <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold mb-4">Order Confirmed!</h1>
        <p class="text-gray-600 mb-6">Thank you for your order. We've sent a confirmation email to your registered email address.</p>
        
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <p class="text-lg mb-2">Order Number</p>
            <p class="text-2xl font-bold text-pink-600"><?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>
        
        <!-- Order Details -->
        <div class="text-left mb-6">
            <h3 class="font-bold mb-4">Order Details</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Total Amount:</span>
                    <span class="font-bold">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Payment Method:</span>
                    <span><?php echo strtoupper($order['payment_method']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Order Status:</span>
                    <span class="capitalize"><?php echo $order['order_status']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="text-left mb-6">
            <h3 class="font-bold mb-4">Items Ordered</h3>
            <div class="space-y-3">
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between border-b pb-3">
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></p>
                            <p class="text-sm text-gray-600">Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <span>₹<?php echo number_format($item['total'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="flex gap-4 justify-center">
            <a href="<?php echo SITE_URL; ?>/order" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300">
                View Orders
            </a>
            <a href="<?php echo SITE_URL; ?>/product" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

