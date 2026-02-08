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

// Helper for status colors
function getStatusColor($status) {
    $colors = [
        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
        'processing' => 'bg-purple-100 text-purple-800 border-purple-200',
        'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'delivered' => 'bg-green-100 text-green-800 border-green-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
        'returned' => 'bg-orange-100 text-orange-800 border-orange-200',
        'refunded' => 'bg-gray-100 text-gray-800 border-gray-200'
    ];
    return $colors[strtolower($status)] ?? 'bg-gray-100 text-gray-800 border-gray-200';
}
?>

<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <?php
            // Render breadcrumb
            renderBreadcrumb([
                ['label' => 'Home', 'url' => '/admin'],
                ['label' => 'Orders', 'url' => '/admin/orders'],
                ['label' => '#' . $order['order_number']]
            ]);
            ?>
            <div class="flex items-center gap-3 mt-2">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                
                <span class="px-3 py-1 rounded-full text-sm font-medium border <?php echo getStatusColor($order['order_status']); ?> capitalize shadow-sm">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $order['order_status'])); ?>
                </span>
                
                <?php if($order['payment_status'] === 'paid'): ?>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                        Paid
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 capitalize">
                        <?php echo htmlspecialchars($order['payment_status']); ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-gray-500 mt-1 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?>
                <span class="mx-1">•</span>
                <span> via Website</span> 
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Navigation -->
            <div class="flex items-center bg-white rounded-lg border border-gray-200 shadow-sm mr-2">
                <a href="<?php echo $prevOrder ? '/admin/orders/' . $prevOrder : '#'; ?>" 
                   class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-50 border-r border-gray-200 <?php echo !$prevOrder ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>"
                   title="Previous Order">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <a href="<?php echo $nextOrder ? '/admin/orders/' . $nextOrder : '#'; ?>" 
                   class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-50 <?php echo !$nextOrder ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>"
                   title="Next Order">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <?php
            renderQuickActions([
                [
                    'type' => 'secondary',
                    'icon' => 'printer',
                    'label' => 'Invoice',
                    'url' => '/admin/orders/invoice/' . $order['order_number'],
                    'target' => '_blank'
                ],
                [
                    'type' => 'primary',
                    'label' => 'Edit Order', // Placeholder for edit functionality if implemented later
                    'url' => '#', 
                    'onclick' => 'alert("Edit functionality coming soon!"); return false;'
                ]
            ], 'relative');
            ?>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Left Column (Main Details) -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- 4. Ordered Product(s) Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                    <span class="text-sm text-gray-500"><?php echo count($items); ?> Items</span>
                </div>
                
                <div class="divide-y divide-gray-100">
                    <?php if (empty($items)): ?>
                        <div class="p-12 text-center text-gray-500">No items found.</div>
                    <?php else: ?>
                        <?php foreach ($items as $item): 
                            // Try to get current stock and SKU for admin reference
                            $currentStock = 'N/A';
                            $currentSku = $item['sku'] ?? 'N/A';
                            try {
                                $productModel = new Product();
                                $prod = $productModel->find($item['product_id']);
                                if ($prod) {
                                    $currentStock = $prod['stock_quantity'];
                                    // If variant, try to find variant stock?
                                    // Complex logic omitted for speed, showing product stock or N/A
                                }
                            } catch(Exception $e){}
                        ?>
                            <div class="p-4 sm:p-6 flex flex-col sm:flex-row gap-6 hover:bg-gray-50/30 transition-colors group">
                                <!-- Product Image -->
                                <div class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?php echo SITE_URL . htmlspecialchars($item['product_image']); ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 leading-tight mb-1">
                                                <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug'] ?? '#'); ?>" target="_blank" class="hover:text-pink-600 transition-colors">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </a>
                                            </h3>
                                            <div class="text-xs text-gray-500 font-mono mb-2">
                                                SKU: <?php echo htmlspecialchars($currentSku); ?> 
                                                <span class="mx-1">|</span> 
                                                ID: <?php echo $item['product_id']; ?>
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                <?php 
                                                // Variant Display
                                                $attributes = !empty($item['attributes_snapshot']) ? json_decode($item['attributes_snapshot'], true) : [];
                                                // Legacy fallback
                                                if(empty($attributes)) {
                                                    // Try parsing from name or other fields if available, otherwise just hide
                                                }
                                                foreach($attributes as $k => $v): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                        <?php echo htmlspecialchars($k); ?>: <?php echo htmlspecialchars($v); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                
                                                <?php if(!empty($item['variant_id'])): ?>
                                                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                        Var ID: <?php echo $item['variant_id']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-gray-900">₹<?php echo number_format($item['total'], 2); ?></p>
                                            <p class="text-xs text-gray-500">
                                                ₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Admin Inventory Info (Hidden from customer) -->
                                    <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                                        <div class="flex gap-4">
                                            <span>Current Stock: <strong class="<?php echo ($currentStock < 10) ? 'text-red-600' : 'text-gray-700'; ?>"><?php echo $currentStock; ?></strong></span>
                                            <span>Location: <strong>Main Warehouse</strong></span> <!-- Placeholder -->
                                        </div>
                                        <div>
                                            <?php if($item['discount'] > 0): ?>
                                                <span class="text-green-600 font-medium">Discount: ₹<?php echo number_format($item['discount'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- 5. Price Summary -->
                <div class="bg-gray-50/50 p-6 border-t border-gray-100">
                    <div class="flex flex-col sm:items-end w-full">
                        <div class="w-full sm:max-w-sm space-y-3">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Items Total</span>
                                <span class="font-medium text-gray-900">₹<?php echo number_format($order['total_amount'] + $order['discount_amount'], 2); ?></span>
                            </div>
                            
                            <?php if ($order['discount_amount'] > 0): ?>
                                <div class="flex justify-between items-center text-sm text-green-600">
                                    <span>Discount</span>
                                    <span>- ₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Shipping</span>
                                <span class="font-medium text-gray-900">
                                    <?php echo ($order['shipping_amount'] > 0) ? '₹' . number_format($order['shipping_amount'], 2) : 'Free'; ?>
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Tax (GST)</span>
                                <span class="font-medium text-gray-900">₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                            </div>

                             <div class="border-t border-gray-200 border-dashed my-2"></div>

                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-gray-900">Final Payable</span>
                                <span class="text-2xl font-bold text-gray-900">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                            </div>
                            
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <div class="bg-green-50 text-green-700 text-xs px-3 py-2 rounded border border-green-100 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Paid via <?php echo ucfirst($order['payment_method']); ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-yellow-50 text-yellow-700 text-xs px-3 py-2 rounded border border-yellow-100 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Payment Pending (<?php echo ucfirst($order['payment_method']); ?>)
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Method</th>
                                <th class="px-6 py-3">Transaction ID</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($payments)): ?>
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No payment records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-6 py-3 whitespace-nowrap"><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded text-xs font-medium <?php echo $payment['type'] === 'refund' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'; ?>">
                                                <?php echo ucfirst($payment['type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 capitalize"><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td class="px-6 py-3 font-mono text-xs"><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></td>
                                        <td class="px-6 py-3 text-right font-medium <?php echo $payment['type'] === 'refund' ? 'text-red-600' : 'text-gray-900'; ?>">
                                            <?php echo $payment['type'] === 'refund' ? '-' : ''; ?>₹<?php echo number_format($payment['amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded text-xs capitalize <?php echo $payment['status'] === 'paid' || $payment['status'] === 'refunded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $payment['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-xs text-gray-500"><?php echo htmlspecialchars($payment['created_by_name'] ?? 'System'); ?></td>
                                    </tr>
                                    <?php if(!empty($payment['notes'])): ?>
                                        <tr class="bg-gray-50/30"><td colspan="7" class="px-6 py-2 text-xs text-gray-500 italic">Note: <?php echo htmlspecialchars($payment['notes']); ?></td></tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 6. Order Status Timeline (Expanded) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Timeline</h2>
                    <span class="text-xs text-gray-500">Local Time</span>
                </div>

                <div class="relative pl-4">
                    <!-- Vertical Line -->
                    <div class="absolute left-6 top-2 bottom-2 w-0.5 bg-gray-100"></div>

                    <div class="space-y-6 relative">
                        <?php
                        // Standard Flow Definition
                        $standardFlow = [
                            ORDER_STATUS_PENDING => ['label' => 'Order Placed', 'icon' => '📝'],
                            ORDER_STATUS_CONFIRMED => ['label' => 'Confirmed', 'icon' => '✓'],
                            ORDER_STATUS_PROCESSING => ['label' => 'Processing', 'icon' => '⚙️'],
                            ORDER_STATUS_SHIPPED => ['label' => 'Shipped', 'icon' => '🚚'],
                            ORDER_STATUS_DELIVERED => ['label' => 'Delivered', 'icon' => '🎉'],
                        ];
                        
                        // Map logs to statuses. 
                        // Strategy: Iterate through logs. If a log matches a standard step, show it as completed.
                        // If it's a note or non-standard status, show it interleaved.
                        
                        // Simple approach for visual clarity:
                        // Show all logs in reverse chronological order (newest first)
                        
                        foreach ($statusHistory as $log): 
                            $isStatusChange = !empty($log['new_status']) && ($log['new_status'] !== $log['old_status']);
                            $statusLabel = ucfirst(str_replace('_', ' ', $log['new_status']));
                            $statusColor = getStatusColor($log['new_status']);
                        ?>
                        <div class="flex gap-4 relative group">
                            <!-- Icon -->
                            <div class="flex-shrink-0 z-10 bg-white">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 bg-gray-50 mt-1.5 group-hover:border-pink-500 group-hover:bg-pink-50 transition-colors"></div>
                            </div>
                            
                            <div class="flex-1 pb-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">
                                            <?php if($isStatusChange): ?>
                                                Status updated to <span class="font-bold"><?php echo $statusLabel; ?></span>
                                            <?php else: ?>
                                                Update
                                            <?php endif; ?>
                                        </p>
                                        <?php if(!empty($log['notes'])): ?>
                                            <p class="text-sm text-gray-600 mt-1 bg-gray-50 p-2 rounded border border-gray-100">
                                                "<?php echo htmlspecialchars($log['notes']); ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right flex-shrink-0 text-xs text-gray-500">
                                        <p><?php echo date('M d, Y', strtotime($log['created_at'])); ?></p>
                                        <p><?php echo date('h:i A', strtotime($log['created_at'])); ?></p>
                                        <p class="mt-1 opacity-75">by <?php echo htmlspecialchars($log['changed_by_name'] ?? 'System'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                         <!-- Initial Placed Event (Simulated if not in logs) -->
                        <div class="flex gap-4 relative group">
                             <div class="flex-shrink-0 z-10 bg-white">
                                <div class="w-5 h-5 rounded-full border-2 border-pink-500 bg-pink-500 mt-1.5"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Order Placed</p>
                                        <p class="text-xs text-gray-500">Order created via <?php echo ucfirst($order['channel'] ?? 'Website'); ?></p>
                                    </div>
                                    <div class="text-right flex-shrink-0 text-xs text-gray-500">
                                        <p><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                        <p><?php echo date('h:i A', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

             <!-- 3. Shipping & Billing Address -->
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shipping -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Shipping Address</h2>
                    </div>
                    <div class="text-sm text-gray-600 leading-relaxed">
                        <p class="font-bold text-gray-900 text-base mb-1"><?php echo htmlspecialchars($order['shipping_full_name'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($order['shipping_address_line1'] ?? ''); ?></p>
                        <?php if (!empty($order['shipping_address_line2'])): ?>
                            <p><?php echo htmlspecialchars($order['shipping_address_line2']); ?></p>
                        <?php endif; ?>
                        <p>
                            <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?>
                        </p>
                        <p class="mb-2">
                            <?php echo htmlspecialchars($order['shipping_pincode'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?>
                        </p>
                        <p class="flex items-center gap-2 text-gray-900">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?>
                        </p>
                    </div>
                </div>

                <!-- Billing -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Billing Address</h2>
                    </div>
                    <div class="text-sm text-gray-600 leading-relaxed">
                        <p class="font-bold text-gray-900 text-base mb-1"><?php echo htmlspecialchars($order['billing_full_name'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($order['billing_address_line1'] ?? ''); ?></p>
                        <?php if (!empty($order['billing_address_line2'])): ?>
                            <p><?php echo htmlspecialchars($order['billing_address_line2']); ?></p>
                        <?php endif; ?>
                        <p>
                            <?php echo htmlspecialchars($order['billing_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['billing_state'] ?? ''); ?>
                        </p>
                        <p class="mb-2">
                            <?php echo htmlspecialchars($order['billing_pincode'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['billing_country'] ?? ''); ?>
                        </p>
                        <p class="flex items-center gap-2 text-gray-900">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <?php echo htmlspecialchars($order['billing_phone'] ?? 'N/A'); ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (Sidebar) -->
        <div class="space-y-6">
            
            <!-- 9. Admin Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Admin Actions</h2>
                <div class="space-y-4">
                    <!-- Update Status -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">UPDATE STATUS</label>
                        <div class="flex gap-2">
                             <select id="order-status-select" 
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    data-order-id="<?php echo $order['order_id']; ?>"
                                    data-current-status="<?php echo htmlspecialchars($order['order_status']); ?>">
                                <option value="">Select Status</option>
                                <?php
                                $validNextStatuses = [
                                    ORDER_STATUS_PENDING => [ORDER_STATUS_CONFIRMED, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_CONFIRMED => [ORDER_STATUS_PROCESSING, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_PROCESSING => [ORDER_STATUS_SHIPPED, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_SHIPPED => [ORDER_STATUS_DELIVERED, ORDER_STATUS_RETURNED],
                                    ORDER_STATUS_DELIVERED => [ORDER_STATUS_RETURNED],
                                    ORDER_STATUS_CANCELLED => [],
                                    ORDER_STATUS_RETURNED => []
                                ];
                                $nextOptions = $validNextStatuses[$order['order_status']] ?? [];
                                foreach ($nextOptions as $st) {
                                     echo '<option value="' . $st . '">' . ucfirst(str_replace('_', ' ', $st)) . '</option>';
                                }
                                ?>
                            </select>
                            <button id="update-order-status-btn" class="p-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 disabled:opacity-50" disabled>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="/admin/orders/invoice/<?php echo $order['order_number']; ?>" target="_blank"
                            class="col-span-1 py-2 px-3 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium text-center flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Invoice
                        </a>
                        <?php if (in_array($order['order_status'], [ORDER_STATUS_PENDING, ORDER_STATUS_CONFIRMED])): ?>
                        <button id="cancel-order-btn" 
                                data-order-id="<?php echo $order['order_id']; ?>"
                                class="col-span-1 py-2 px-3 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Actions -->
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                        <?php if ($order['payment_status'] !== 'paid' && $order['order_status'] !== ORDER_STATUS_CANCELLED): ?>
                            <button id="mark-paid-btn" data-order-id="<?php echo $order['order_id']; ?>" 
                                    class="col-span-2 py-2 px-3 bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100 text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Mark as Paid
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['payment_status'] === 'paid' && $order['final_amount'] > 0): ?>
                             <button onclick="document.getElementById('refund-dialog').showModal()" 
                                    class="col-span-2 py-2 px-3 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                Issue Refund
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Customer Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Customer</h2>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-100 to-purple-100 flex items-center justify-center text-pink-600 font-bold text-lg border border-white shadow-sm">
                        <?php echo strtoupper(substr($order['customer_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800">
                             Registered Customer
                        </span>
                    </div>
                </div>
                
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" class="hover:text-pink-600 hover:underline"><?php echo htmlspecialchars($order['email']); ?></a>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <a href="tel:<?php echo htmlspecialchars($order['phone']); ?>" class="hover:text-pink-600 hover:underline"><?php echo htmlspecialchars($order['phone']); ?></a>
                    </div>
                </div>

                <?php if (!empty($customerStats)): ?>
                    <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2">
                        <div class="text-center p-2 bg-gray-50 rounded-lg">
                            <span class="block text-xs text-gray-500 uppercase">Orders</span>
                            <span class="block text-lg font-bold text-gray-900"><?php echo $customerStats['total_orders'] ?? 0; ?></span>
                        </div>
                        <div class="text-center p-2 bg-gray-50 rounded-lg">
                            <span class="block text-xs text-gray-500 uppercase">Spent</span>
                            <span class="block text-lg font-bold text-gray-900">₹<?php echo number_format($customerStats['total_spent'] ?? 0); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 7. Shipment & Tracking -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Shipment</h2>
                
                <form id="update-shipping-form" class="space-y-4">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 mb-1">COURIER PARTNER</label>
                         <input type="text" name="courier_partner" 
                                value="<?php echo htmlspecialchars($order['courier_partner'] ?? ''); ?>"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="e.g. BlueDart">
                    </div>
                    
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 mb-1">TRACKING ID / AWB</label>
                         <div class="relative">
                             <input type="text" name="tracking_id" 
                                    value="<?php echo htmlspecialchars($order['tracking_id'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    placeholder="Order tracking #">
                            <?php if(!empty($order['tracking_id']) && !empty($order['courier_partner'])): ?>
                                <a href="https://www.google.com/search?q=<?php echo urlencode($order['courier_partner'] . ' tracking ' . $order['tracking_id']); ?>" 
                                   target="_blank" 
                                   class="absolute right-2 top-2 text-blue-600 hover:text-blue-800"
                                   title="Track Package">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            <?php endif; ?>
                         </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">EST. DELIVERY</label>
                        <input type="date" name="estimated_delivery" 
                               value="<?php echo !empty($order['estimated_delivery']) ? date('Y-m-d', strtotime($order['estimated_delivery'])) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">Update Tracking</button>
                </form>
            </div>

            <!-- 10. Internal Notes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Internal Notes</h2>
                <div class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100 italic">
                    <?php echo !empty($order['notes']) ? htmlspecialchars($order['notes']) : 'No notes added.'; ?>
                </div>
                <button class="text-xs text-pink-600 font-medium mt-2 hover:underline">+ Add Note</button>
            </div>
            
             <!-- 11. Invoice & Compliance -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Compliance</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Invoice No</span>
                        <span class="font-mono text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">GST Status</span>
                        <span class="text-gray-900">B2C</span>
                    </div>
                     <div class="flex justify-between text-sm">
                        <span class="text-gray-500">HSN Code</span>
                        <span class="text-gray-900">N/A</span> <!-- Placeholder -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Refund Dialog -->
<dialog id="refund-dialog" class="p-0 rounded-xl shadow-2xl backdrop:bg-gray-900/50 open:animate-fade-in">
    <div class="w-[400px] max-w-full bg-white p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Issue Refund</h3>
            <button onclick="document.getElementById('refund-dialog').close()" class="text-gray-400 hover:text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="refund-form" class="space-y-4">
            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">REFUND AMOUNT</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">₹</span>
                    <input type="number" step="0.01" name="amount" 
                           max="<?php echo $order['final_amount']; ?>"
                           class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                           placeholder="0.00" required>
                </div>
                <p class="text-xs text-gray-500 mt-1">Max refundable: ₹<?php echo number_format($order['final_amount'], 2); ?></p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">REASON</label>
                <textarea name="reason" rows="3" 
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                          placeholder="Reason for refund..." required></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('refund-dialog').close()" 
                        class="flex-1 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                    Process Refund
                </button>
            </div>
        </form>
    </div>
</dialog>

<script src="<?php echo SITE_URL; ?>/assets/js/admin-orders.js"></script>
<?php // Custom CSS for timelines ?>
<style type="text/tailwindcss">
    .timeline-node {
        @apply relative z-10 w-6 h-6 flex items-center justify-center bg-white rounded-full border-2 border-gray-200;
    }
    .timeline-node.active {
        @apply border-pink-500 bg-pink-50 text-pink-500;
    }
    .timeline-node.completed {
        @apply border-green-500 bg-green-500 text-white;
    }
</style>
