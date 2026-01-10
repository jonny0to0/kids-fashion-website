<?php
$pageTitle = 'Order Details - ' . htmlspecialchars($order['order_number']);

// Ensure items is always defined
if (!isset($items)) {
    $items = [];
}

// Ensure statusHistory is always defined
if (!isset($statusHistory)) {
    $statusHistory = [];
}

// Include breadcrumb and quick actions helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_quick_actions.php';
?>

<?php echo getQuickActionsStyles(); ?>

<style>
.status-timeline {
    position: relative;
    padding-left: 2rem;
}

.status-timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.status-timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.status-timeline-item::before {
    content: '';
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: #e5e7eb;
    border: 2px solid white;
    z-index: 1;
}

.status-timeline-item.active::before {
    background: #ec4899;
    border-color: #ec4899;
}

.status-timeline-item.completed::before {
    background: #10b981;
    border-color: #10b981;
}
</style>

<div class="w-full px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
        ['label' => 'Orders', 'url' => '/admin/orders'],
        ['label' => 'Order Details']
    ]);
    ?>
    
    <div class="flex justify-between items-start mb-8">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
            <p class="text-gray-600 mt-2">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>
        <div>
            <?php
            renderQuickActions([
                [
                    'type' => 'secondary',
                    'icon' => 'refresh',
                    'label' => 'Back to Orders',
                    'url' => '/admin/orders',
                    'tooltip' => 'Return to orders list'
                ]
            ], 'top-right');
            ?>
        </div>
    </div>
    
    <!-- Order Summary Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-600">Order ID</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Order Date</p>
                <p class="text-lg font-semibold text-gray-900">
                    <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                <p class="text-lg font-semibold text-gray-900">$<?php echo number_format($order['final_amount'], 2); ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <p class="text-sm text-gray-600">Payment Method</p>
                <p class="text-base font-medium text-gray-900">
                    <?php echo strtoupper($order['payment_method'] ?? 'N/A'); ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Payment Status</p>
                <div class="mt-1">
                    <?php
                    $paymentStatus = strtolower($order['payment_status'] ?? 'pending');
                    $paymentBadgeClass = 'bg-yellow-100 text-yellow-800';
                    if ($paymentStatus === 'paid') {
                        $paymentBadgeClass = 'bg-green-100 text-green-800';
                    } elseif ($paymentStatus === 'failed') {
                        $paymentBadgeClass = 'bg-red-100 text-red-800';
                    } elseif ($paymentStatus === 'refunded') {
                        $paymentBadgeClass = 'bg-purple-100 text-purple-800';
                    }
                    ?>
                    <span class="px-3 py-1 text-sm font-medium rounded-full <?php echo $paymentBadgeClass; ?>">
                        <?php echo ucfirst($paymentStatus); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($order['transaction_id'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600">Transaction ID</p>
            <p class="text-base font-medium text-gray-900 font-mono">
                <?php echo htmlspecialchars($order['transaction_id']); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Shipping Details Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-600">Delivery Type</p>
                <p class="text-base font-medium text-gray-900 uppercase">
                    <?php echo htmlspecialchars($order['delivery_type'] ?? 'standard'); ?>
                </p>
            </div>
            
            <?php if (!empty($order['courier_partner'])): ?>
            <div>
                <p class="text-sm text-gray-600">Courier Partner</p>
                <p class="text-base font-medium text-gray-900">
                    <?php echo htmlspecialchars($order['courier_partner']); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($order['tracking_id'])): ?>
            <div>
                <p class="text-sm text-gray-600">Tracking ID</p>
                <p class="text-base font-medium text-gray-900 font-mono">
                    <?php echo htmlspecialchars($order['tracking_id']); ?>
                </p>
                <?php if (!empty($order['courier_partner'])): ?>
                    <a href="https://www.google.com/search?q=<?php echo urlencode($order['courier_partner'] . ' tracking ' . $order['tracking_id']); ?>" 
                       target="_blank" 
                       class="text-sm text-pink-600 hover:text-pink-700 mt-1 inline-block">
                        Track Package →
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($order['estimated_delivery'])): ?>
            <div>
                <p class="text-sm text-gray-600">Estimated Delivery</p>
                <p class="text-base font-medium text-gray-900">
                    <?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <div>
                <p class="text-sm text-gray-600">Shipping Charges</p>
                <p class="text-base font-medium text-gray-900">
                    $<?php echo number_format($order['shipping_amount'] ?? 0, 2); ?>
                </p>
            </div>
        </div>
        
        <!-- Update Shipping Details Form -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Shipping Information</h3>
            <form id="update-shipping-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Courier Partner</label>
                    <input type="text" name="courier_partner" 
                           value="<?php echo htmlspecialchars($order['courier_partner'] ?? ''); ?>"
                           placeholder="e.g., FedEx, DHL, UPS"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tracking ID</label>
                    <input type="text" name="tracking_id" 
                           value="<?php echo htmlspecialchars($order['tracking_id'] ?? ''); ?>"
                           placeholder="Tracking number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Delivery</label>
                    <input type="date" name="estimated_delivery" 
                           value="<?php echo !empty($order['estimated_delivery']) ? date('Y-m-d', strtotime($order['estimated_delivery'])) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Type</label>
                    <select name="delivery_type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="standard" <?php echo ($order['delivery_type'] ?? 'standard') === 'standard' ? 'selected' : ''; ?>>Standard</option>
                        <option value="express" <?php echo ($order['delivery_type'] ?? 'standard') === 'express' ? 'selected' : ''; ?>>Express</option>
                    </select>
                </div>
                
                <div class="lg:col-span-4">
                    <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition">
                        Update Shipping Details
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Customer Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Details</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="text-base font-medium text-gray-900">
                        <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping Address</h2>
            <div class="text-sm text-gray-700">
                <p class="font-medium"><?php echo htmlspecialchars($order['shipping_full_name'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars($order['shipping_address_line1'] ?? ''); ?></p>
                <?php if (!empty($order['shipping_address_line2'])): ?>
                    <p><?php echo htmlspecialchars($order['shipping_address_line2']); ?></p>
                <?php endif; ?>
                <p>
                    <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>, 
                    <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?> 
                    <?php echo htmlspecialchars($order['shipping_pincode'] ?? ''); ?>
                </p>
                <p><?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?></p>
                <p class="mt-2">Phone: <?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <!-- Billing Address -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Billing Address</h2>
            <div class="text-sm text-gray-700">
                <p class="font-medium"><?php echo htmlspecialchars($order['billing_full_name'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars($order['billing_address_line1'] ?? ''); ?></p>
                <?php if (!empty($order['billing_address_line2'])): ?>
                    <p><?php echo htmlspecialchars($order['billing_address_line2']); ?></p>
                <?php endif; ?>
                <p>
                    <?php echo htmlspecialchars($order['billing_city'] ?? ''); ?>, 
                    <?php echo htmlspecialchars($order['billing_state'] ?? ''); ?> 
                    <?php echo htmlspecialchars($order['billing_pincode'] ?? ''); ?>
                </p>
                <p><?php echo htmlspecialchars($order['billing_country'] ?? ''); ?></p>
                <p class="mt-2">Phone: <?php echo htmlspecialchars($order['billing_phone'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Ordered Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Items</h2>
            <?php if (empty($items) || count($items) === 0): ?>
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">No order items found</p>
                    <p class="text-gray-400 text-sm mt-2">This order does not have any items associated with it.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($items as $index => $item): ?>
                        <div class="flex gap-4 pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?php echo SITE_URL . htmlspecialchars($item['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     class="w-20 h-20 object-cover rounded border border-gray-200">
                            <?php else: ?>
                                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center border border-gray-300">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 mb-1">
                                    <?php if (!empty($item['slug'])): ?>
                                        <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>" 
                                           class="text-pink-600 hover:text-pink-700 hover:underline">
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    <?php endif; ?>
                                </h3>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2">
                                    <span><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></span>
                                    <span><strong>Unit Price:</strong> $<?php echo number_format($item['price'], 2); ?></span>
                                    <?php if (!empty($item['product_id'])): ?>
                                        <?php
                                        // Try to get SKU from product
                                        try {
                                            $productModel = new Product();
                                            $product = $productModel->find($item['product_id']);
                                            if (!empty($product['sku'])): ?>
                                                <span class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                            <?php endif;
                                        } catch (Exception $e) {
                                            // Skip if product not found
                                        }
                                        ?>
                                    <?php endif; ?>
                                    <?php if (!empty($item['variant_id'])): ?>
                                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">Variant ID: <?php echo htmlspecialchars($item['variant_id']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['discount'] > 0): ?>
                                    <p class="text-sm text-green-600 mt-1">
                                        Discount: $<?php echo number_format($item['discount'], 2); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($item['tax'] > 0): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Tax: $<?php echo number_format($item['tax'], 2); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">
                                    $<?php echo number_format($item['total'], 2); ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Item Total</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Order Totals -->
            <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount:</span>
                        <span class="font-medium text-green-600">-$<?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['shipping_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Shipping:</span>
                        <span class="font-medium">$<?php echo number_format($order['shipping_amount'], 2); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['tax_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax:</span>
                        <span class="font-medium">$<?php echo number_format($order['tax_amount'], 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['final_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Order Status Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Status Timeline</h2>
            <div class="status-timeline">
                <?php
                $statuses = [
                    ORDER_STATUS_PENDING => ['label' => 'Pending', 'icon' => '⏳'],
                    ORDER_STATUS_CONFIRMED => ['label' => 'Confirmed', 'icon' => '✓'],
                    ORDER_STATUS_PROCESSING => ['label' => 'Processing', 'icon' => '⚙️'],
                    ORDER_STATUS_SHIPPED => ['label' => 'Shipped', 'icon' => '📦'],
                    ORDER_STATUS_DELIVERED => ['label' => 'Delivered', 'icon' => '✅'],
                ];
                
                $currentStatus = strtolower($order['order_status'] ?? 'pending');
                $statusOrder = [ORDER_STATUS_PENDING, ORDER_STATUS_CONFIRMED, ORDER_STATUS_PROCESSING, ORDER_STATUS_SHIPPED, ORDER_STATUS_DELIVERED];
                $currentIndex = array_search($order['order_status'], $statusOrder);
                
                foreach ($statusOrder as $index => $status) {
                    $statusLower = strtolower($status);
                    $isActive = ($statusLower === $currentStatus);
                    $isCompleted = ($currentIndex !== false && $index < $currentIndex);
                    $isCancelled = ($order['order_status'] === ORDER_STATUS_CANCELLED);
                    
                    $class = 'status-timeline-item';
                    if ($isCompleted) {
                        $class .= ' completed';
                    } elseif ($isActive && !$isCancelled) {
                        $class .= ' active';
                    }
                    
                    // Find status history entry
                    $historyEntry = null;
                    foreach ($statusHistory as $entry) {
                        if ($entry['new_status'] === $status) {
                            $historyEntry = $entry;
                            break;
                        }
                    }
                    ?>
                    <div class="<?php echo $class; ?>">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?php echo $statuses[$status]['icon'] ?? ''; ?> 
                                    <?php echo $statuses[$status]['label']; ?>
                                </p>
                                <?php if ($historyEntry): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y H:i', strtotime($historyEntry['created_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if ($isActive && !$isCancelled): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-800">
                                    Current
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                
                // Show cancelled status if applicable
                if ($isCancelled): ?>
                    <div class="status-timeline-item active">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-red-900">❌ Cancelled</p>
                                <?php if (!empty($order['cancelled_at'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y H:i', strtotime($order['cancelled_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Admin Actions -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Admin Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Update Order Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Order Status</label>
                <div class="flex gap-2">
                    <select id="order-status-select" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                            data-order-id="<?php echo $order['order_id']; ?>"
                            data-current-status="<?php echo htmlspecialchars($order['order_status']); ?>">
                        <option value="">Select Status</option>
                        <?php
                        $currentStatus = $order['order_status'];
                        $validNextStatuses = [];
                        
                        if ($currentStatus === ORDER_STATUS_PENDING) {
                            $validNextStatuses = [ORDER_STATUS_CONFIRMED, ORDER_STATUS_CANCELLED];
                        } elseif ($currentStatus === ORDER_STATUS_CONFIRMED) {
                            $validNextStatuses = [ORDER_STATUS_PROCESSING, ORDER_STATUS_CANCELLED];
                        } elseif ($currentStatus === ORDER_STATUS_PROCESSING) {
                            $validNextStatuses = [ORDER_STATUS_SHIPPED, ORDER_STATUS_CANCELLED];
                        } elseif ($currentStatus === ORDER_STATUS_SHIPPED) {
                            $validNextStatuses = [ORDER_STATUS_DELIVERED, ORDER_STATUS_RETURNED];
                        } elseif ($currentStatus === ORDER_STATUS_DELIVERED) {
                            $validNextStatuses = [ORDER_STATUS_RETURNED];
                        }
                        
                        foreach ($validNextStatuses as $status):
                            $statusLabel = ucfirst(str_replace('_', ' ', $status));
                        ?>
                            <option value="<?php echo $status; ?>"><?php echo $statusLabel; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="update-order-status-btn" 
                            class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                            disabled>
                        Update
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Current: <strong><?php echo ucfirst($order['order_status']); ?></strong></p>
            </div>
            
            <!-- Update Payment Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Payment Status</label>
                <div class="flex gap-2">
                    <select id="payment-status-select" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                            data-order-id="<?php echo $order['order_id']; ?>"
                            data-current-status="<?php echo htmlspecialchars($order['payment_status']); ?>">
                        <option value="">Select Status</option>
                        <option value="<?php echo PAYMENT_STATUS_PENDING; ?>" <?php echo ($order['payment_status'] === PAYMENT_STATUS_PENDING) ? 'selected' : ''; ?>>Pending</option>
                        <option value="<?php echo PAYMENT_STATUS_PAID; ?>" <?php echo ($order['payment_status'] === PAYMENT_STATUS_PAID) ? 'selected' : ''; ?>>Paid</option>
                        <option value="<?php echo PAYMENT_STATUS_FAILED; ?>" <?php echo ($order['payment_status'] === PAYMENT_STATUS_FAILED) ? 'selected' : ''; ?>>Failed</option>
                        <option value="<?php echo PAYMENT_STATUS_REFUNDED; ?>" <?php echo ($order['payment_status'] === PAYMENT_STATUS_REFUNDED) ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                    <button id="update-payment-status-btn" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                            disabled>
                        Update
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Current: <strong><?php echo ucfirst($order['payment_status']); ?></strong></p>
            </div>
        </div>
        
        <!-- Cancel Order Button -->
        <?php
        $cancellableStatuses = [ORDER_STATUS_PENDING, ORDER_STATUS_CONFIRMED, ORDER_STATUS_PROCESSING];
        $canCancel = in_array($order['order_status'], $cancellableStatuses);
        ?>
        <?php if ($canCancel): ?>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button id="cancel-order-btn" 
                        class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition"
                        data-order-id="<?php echo $order['order_id']; ?>">
                    Cancel Order
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/admin-orders.js"></script>

