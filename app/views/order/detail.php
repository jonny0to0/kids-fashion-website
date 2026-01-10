<?php
// Ensure items is always defined
if (!isset($items)) {
    $items = [];
}

// Ensure shippingAddress is always defined
if (!isset($shippingAddress)) {
    $shippingAddress = null;
}

// Ensure billingAddress is always defined
if (!isset($billingAddress)) {
    $billingAddress = null;
}
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="<?php echo SITE_URL; ?>/order" class="text-pink-600 hover:underline flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-6 border-b">
                <h1 class="text-3xl font-bold mb-2">Order Details</h1>
                <p class="text-gray-600">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                <p class="text-sm text-gray-500 mt-1">Placed on <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
            </div>
            
            <!-- Order Status -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold mb-2">Order Status</h3>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-medium capitalize 
                        <?php 
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-purple-100 text-purple-800',
                            'shipped' => 'bg-indigo-100 text-indigo-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        echo $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>">
                        <?php echo $order['order_status']; ?>
                    </span>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                <?php if (empty($items) || count($items) === 0): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">No order items found</p>
                        <p class="text-gray-400 text-sm mt-2">This order does not have any items associated with it.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($items as $item): ?>
                            <div class="flex gap-4 border-b pb-4 last:border-b-0 last:pb-0">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo SITE_URL; ?><?php echo htmlspecialchars($item['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="w-20 h-20 object-cover rounded border border-gray-200">
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center border border-gray-300">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="font-medium text-lg mb-1">
                                        <?php if (!empty($item['slug'])): ?>
                                            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>" 
                                               class="text-pink-600 hover:underline">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2">
                                        <span><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></span>
                                        <span><strong>Unit Price:</strong> ₹<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                    <?php if (!empty($item['discount']) && $item['discount'] > 0): ?>
                                        <p class="text-sm text-green-600 mt-1">
                                            Discount: ₹<?php echo number_format($item['discount'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['tax']) && $item['tax'] > 0): ?>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Tax: ₹<?php echo number_format($item['tax'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-gray-900">₹<?php echo number_format($item['total'], 2); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Item Total</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Addresses -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <?php if ($shippingAddress): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Shipping Address</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium"><?php echo htmlspecialchars($shippingAddress['full_name']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($shippingAddress['address_line1']); ?></p>
                            <?php if (!empty($shippingAddress['address_line2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($shippingAddress['address_line2']); ?></p>
                            <?php endif; ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($shippingAddress['city'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($shippingAddress['state'] ?? ''); ?> - 
                                <?php echo htmlspecialchars($shippingAddress['pincode'] ?? ''); ?>
                                <!-- postal_code -->
                            </p>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($shippingAddress['phone']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($billingAddress): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Billing Address</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium"><?php echo htmlspecialchars($billingAddress['full_name']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($billingAddress['address_line1']); ?></p>
                            <?php if (!empty($billingAddress['address_line2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($billingAddress['address_line2']); ?></p>
                            <?php endif; ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($billingAddress['city'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($billingAddress['state'] ?? ''); ?> - 
                                <?php echo htmlspecialchars($billingAddress['pincode'] ?? ''); ?>
                                <!-- postal_code -->
                            </p>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($billingAddress['phone']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Summary -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                <div class="space-y-2 max-w-md ml-auto">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['tax_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Tax:</span>
                            <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['shipping_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span>₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-xl font-bold pt-2 border-t">
                        <span>Total:</span>
                        <span class="text-pink-600">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Payment Method:</span>
                        <span class="capitalize"><?php echo strtoupper($order['payment_method']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Payment Status:</span>
                        <span class="capitalize 
                            <?php 
                            $paymentStatusColors = [
                                'pending' => 'text-yellow-600',
                                'paid' => 'text-green-600',
                                'failed' => 'text-red-600',
                                'refunded' => 'text-blue-600'
                            ];
                            echo $paymentStatusColors[$order['payment_status']] ?? '';
                            ?>">
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Order Notes -->
            <?php if (!empty($order['notes'])): ?>
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-lg font-semibold mb-2">Order Notes</h3>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t flex gap-4">
                <a href="<?php echo SITE_URL; ?>/order" 
                   class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300">
                    Back to Orders
                </a>
                <?php if ($order['order_status'] === ORDER_STATUS_PENDING || $order['order_status'] === ORDER_STATUS_CONFIRMED): ?>
                    <a href="<?php echo SITE_URL; ?>/order/cancel/<?php echo htmlspecialchars($order['order_number']); ?>" 
                       class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 cancel-order-link"
                       data-order-number="<?php echo htmlspecialchars($order['order_number']); ?>">
                        Cancel Order
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelLink = document.querySelector('.cancel-order-link');
    if (cancelLink) {
        cancelLink.addEventListener('click', async function(e) {
            e.preventDefault();
            const orderNumber = this.getAttribute('data-order-number');
            const result = await showConfirm(
                'Cancel Order',
                'Are you sure you want to cancel this order? This action cannot be undone.',
                'Yes, Cancel',
                'No, Keep Order',
                'warning'
            );
            
            if (result.isConfirmed) {
                window.location.href = this.href;
            }
        });
    }
});
</script>

