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



<div class="w-full px-6 py-8 bg-gray-50 min-h-screen">
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
                <?php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'processing' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'delivered' => 'bg-green-100 text-green-800 border-green-200',
                    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                    'returned' => 'bg-gray-100 text-gray-800 border-gray-200'
                ];
                $statusColor = $statusColors[strtolower($order['order_status'])] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium border <?php echo $statusColor; ?> capitalize shadow-sm">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </span>
            </div>
            <p class="text-gray-500 mt-1 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?>
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <?php
            renderQuickActions([
                [
                    'type' => 'secondary',
                    'icon' => 'arrow-left',
                    'label' => 'Back to List',
                    'url' => '/admin/orders',
                ],
                [
                    'type' => 'secondary',
                    'icon' => 'printer',
                    'label' => 'Print Invoice',
                    'url' => '/admin/orders/invoice/' . $order['order_number'],
                    'target' => '_blank'
                ]
            ], 'relative');
            ?>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Left Column (Main Details) -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Order Items Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                    <span class="text-sm text-gray-500"><?php echo count($items); ?> Items</span>
                </div>
                
                <?php if (empty($items)): ?>
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                             <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No Items Found</h3>
                        <p class="text-gray-500 mt-1">This order appears to be empty.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($items as $item): ?>
                            <div class="p-6 flex flex-col sm:flex-row gap-6 hover:bg-gray-50/50 transition-colors">
                                <!-- Product Image -->
                                <div class="w-full sm:w-24 h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?php echo SITE_URL . htmlspecialchars($item['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
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
                                                <?php if (!empty($item['slug'])): ?>
                                                    <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>" class="hover:text-pink-600 transition-colors">
                                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                <?php endif; ?>
                                            </h3>
                                            
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                <?php if (!empty($item['product_id'])): 
                                                      try {
                                                          $productModel = new Product();
                                                          $product = $productModel->find($item['product_id']);
                                                          if (!empty($product['sku'])): ?>
                                                              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 font-mono">
                                                                  SKU: <?php echo htmlspecialchars($product['sku']); ?>
                                                              </span>
                                                          <?php endif; 
                                                      } catch (Exception $e) {}
                                                endif; ?>
                                                
                                                <?php if (!empty($item['variant_id'])): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        Var: <?php echo htmlspecialchars($item['variant_id']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900">
                                            ₹<?php echo number_format($item['total'], 2); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
                                        <div class="flex items-center gap-6">
                                            <div class="flex flex-col">
                                                <span class="text-xs uppercase tracking-wider text-gray-400 font-medium">Price</span>
                                                <span class="font-medium">₹<?php echo number_format($item['price'], 2); ?></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs uppercase tracking-wider text-gray-400 font-medium">Qty</span>
                                                <span class="font-medium">x<?php echo htmlspecialchars($item['quantity']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <?php if ($item['discount'] > 0): ?>
                                                <span class="text-green-600 bg-green-50 px-2 py-1 rounded text-xs font-medium">
                                                    Saved ₹<?php echo number_format($item['discount'], 2); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($item['tax'] > 0): ?>
                                                <span class="text-gray-500 text-xs">
                                                    Tax: ₹<?php echo number_format($item['tax'], 2); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Totals Footer -->
                    <div class="bg-gray-50/50 p-8 rounded-b-xl border-t border-gray-100">
                        <div class="flex flex-col sm:items-end w-full">
                            <div class="w-full sm:max-w-xs space-y-4">
                                <!-- Subtotal -->
                                <div class="flex justify-between items-center text-gray-600">
                                    <span class="text-sm font-medium">Subtotal</span>
                                    <span class="text-base font-semibold text-gray-900">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>

                                <!-- Discount -->
                                <?php if ($order['discount_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-green-600 bg-green-50 px-3 py-1.5 rounded-lg border border-green-100 border-dashed">
                                        <span class="text-sm font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            Discount
                                        </span>
                                        <span class="text-base font-bold">- ₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Shipping -->
                                <?php if ($order['shipping_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span class="text-sm font-medium">Shipping</span>
                                        <span class="text-base font-semibold text-gray-900">₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Tax -->
                                <?php if ($order['tax_amount'] > 0): ?>
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span class="text-sm font-medium">Tax</span>
                                        <span class="text-base font-semibold text-gray-900">₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Divider -->
                                <div class="border-b border-gray-200 border-dashed my-2"></div>

                                <!-- Total -->
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-lg font-bold text-gray-900">Total</span>
                                    <div class="text-right">
                                        <span class="block text-3xl font-bold text-gray-900 tracking-tight">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                                        <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Including Taxes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Order Activity</h2>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium"><?php echo count($statusHistory); ?> Events</span>
                </div>

                <div class="relative">
                    <!-- Vertical Line -->
                    <div class="absolute left-6 top-4 bottom-4 w-0.5 bg-gray-100"></div>

                    <div class="space-y-8 relative">
                        <?php
                        // Consolidated Timeline Logic
                        // We want to show the standard flow "Placed -> ... -> Delivered" as milestones
                        // BUT we also want to show the specific history logs interwoven or as the primary source.
                        // Given the request for "Advanced", let's use the actual History Logs as the source of truth, 
                        // but enhance them with "Future/Pending" steps if the order isn't complete yet.

                        // 1. Define standard flow
                        $standardFlow = [
                            ORDER_STATUS_PENDING => ['label' => 'Order Placed', 'icon' => '📝', 'desc' => 'Order has been received'],
                            ORDER_STATUS_CONFIRMED => ['label' => 'Order Confirmed', 'icon' => '✓', 'desc' => 'Order details verified'],
                            ORDER_STATUS_PROCESSING => ['label' => 'Processing', 'icon' => '⚙️', 'desc' => 'Being prepared for shipping'],
                            ORDER_STATUS_SHIPPED => ['label' => 'Shipped', 'icon' => '🚚', 'desc' => 'Package is on the way'],
                            ORDER_STATUS_DELIVERED => ['label' => 'Delivered', 'icon' => '🎉', 'desc' => 'Package has arrived'],
                        ];

                        // 2. Map existing history to specific steps
                        $historyByStatus = [];
                        foreach ($statusHistory as $log) {
                            $historyByStatus[$log['new_status']] = $log;
                        }
                        // Also add "placed" entry if missing (often initial creation doesn't log status change depending on implementation)
                        if (!isset($historyByStatus[ORDER_STATUS_PENDING])) {
                             $historyByStatus[ORDER_STATUS_PENDING] = [
                                 'created_at' => $order['created_at'],
                                 'changed_by_name' => 'System', // Or customer name
                                 'notes' => 'Order placed by customer'
                             ];
                        }

                        // 3. Determine flow based on current status
                        $currentStatus = $order['order_status'];
                        $isCancelled = ($currentStatus === ORDER_STATUS_CANCELLED);
                        $isReturned = ($currentStatus === ORDER_STATUS_RETURNED);

                        // If cancelled, show Placed -> Cancelled
                        if ($isCancelled) {
                             $steps = [ORDER_STATUS_PENDING, ORDER_STATUS_CANCELLED];
                        } elseif ($isReturned) {
                             // Show full flow up to delivered, then returned? Or just Placed -> ... -> Returned
                             $steps = array_keys($standardFlow);
                             $steps[] = ORDER_STATUS_RETURNED;
                        } else {
                             $steps = array_keys($standardFlow);
                        }
                        
                        // Find index of current status in the standard flow to determine "completed" vs "future"
                        $currentStatusIndex = -1;
                        foreach($steps as $idx => $s) {
                            if($s === $currentStatus) {
                                $currentStatusIndex = $idx;
                                break;
                            }
                        }
                        // If status not in standard flow (e.g. some custom status), assume all pending? 
                        // Logic fallback: check if we have history for it.
                        
                        foreach ($steps as $index => $stepStatus): 
                            // Determine state
                            $hasLog = isset($historyByStatus[$stepStatus]);
                            $logEntry = $hasLog ? $historyByStatus[$stepStatus] : null;
                            
                            $isCompleted = ($currentStatusIndex !== -1 && $index <= $currentStatusIndex) || $hasLog;
                            $isCurrent = ($stepStatus === $currentStatus);
                            $isFuture = !$isCompleted;
                            
                            // Custom Icon/Label for Cancelled/Returned
                            if ($stepStatus === ORDER_STATUS_CANCELLED) {
                                $label = 'Order Cancelled';
                                $icon = '✕';
                                $desc = 'Order has been cancelled';
                                $colorClass = 'red';
                            } elseif ($stepStatus === ORDER_STATUS_RETURNED) {
                                $label = 'Returned';
                                $icon = '↩️';
                                $desc = 'Order was returned';
                                $colorClass = 'orange';
                            } else {
                                $flowData = $standardFlow[$stepStatus] ?? ['label' => ucfirst($stepStatus), 'icon' => '•', 'desc' => ''];
                                $label = $flowData['label'];
                                $icon = $flowData['icon'];
                                $desc = $flowData['desc'];
                                $colorClass = $isCompleted ? 'green' : 'gray';
                                if ($isCurrent) $colorClass = 'pink';
                            }
                            
                            // Styling
                            $circleBg = $isFuture ? 'bg-white border-2 border-gray-200 text-gray-300' : 
                                       ($colorClass === 'pink' ? 'bg-pink-600 text-white ring-4 ring-pink-50' : 
                                       ($colorClass === 'red' ? 'bg-red-600 text-white' : 
                                       ('bg-green-600 text-white'))); // Default completed

                            $textClass = $isFuture ? 'text-gray-400' : 'text-gray-900';
                            $descClass = $isFuture ? 'text-gray-300' : 'text-gray-500';
                        ?>
                        <div class="flex gap-6 relative group">
                            <!-- Icon/Circle -->
                            <div class="flex-shrink-0 z-10">
                                <div class="w-12 h-12 flex items-center justify-center rounded-full <?php echo $circleBg; ?> transition-all duration-300 shadow-sm">
                                    <span class="text-lg font-bold"><?php echo $icon; ?></span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 pt-1 pb-4">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1">
                                    <div>
                                        <h3 class="font-bold text-base <?php echo $textClass; ?>"><?php echo $label; ?></h3>
                                        <p class="text-sm <?php echo $descClass; ?>"><?php echo $desc; ?></p>
                                        
                                        <?php if ($logEntry && !empty($logEntry['notes'])): ?>
                                            <div class="mt-2 bg-yellow-50 border border-yellow-100 rounded-lg p-3 text-sm text-yellow-800 flex gap-2 items-start max-w-md">
                                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                                <span><?php echo htmlspecialchars($logEntry['notes']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="text-right flex-shrink-0">
                                        <?php if ($logEntry): ?>
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-semibold text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($logEntry['created_at'])); ?>
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    <?php echo date('h:i A', strtotime($logEntry['created_at'])); ?>
                                                </span>
                                                <?php if (!empty($logEntry['changed_by_name'])): ?>
                                                    <span class="text-xs text-gray-400 mt-1 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-100">
                                                        by <?php echo htmlspecialchars($logEntry['changed_by_name']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($isCurrent): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                In Progress
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (Sidebar) -->
        <div class="space-y-8">
            
             <!-- Admin Actions Card -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Admin Actions</h2>
                <div class="space-y-6">
                    <!-- Update Order Status -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 sticky top-0 bg-white mb-2">UPDATE ORDER STATUS</label>
                        <div class="flex gap-2">
                             <select id="order-status-select" 
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white transition-colors"
                                    data-order-id="<?php echo $order['order_id']; ?>"
                                    data-current-status="<?php echo htmlspecialchars($order['order_status']); ?>">
                                <option value="">Choose Status...</option>
                                <?php
                                $validNextStatuses = [
                                    ORDER_STATUS_PENDING => [ORDER_STATUS_CONFIRMED, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_CONFIRMED => [ORDER_STATUS_PROCESSING, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_PROCESSING => [ORDER_STATUS_SHIPPED, ORDER_STATUS_CANCELLED],
                                    ORDER_STATUS_SHIPPED => [ORDER_STATUS_DELIVERED, ORDER_STATUS_RETURNED],
                                    ORDER_STATUS_DELIVERED => [ORDER_STATUS_RETURNED]
                                ];
                                $nextOptions = $validNextStatuses[$order['order_status']] ?? [];
                                
                                foreach ($nextOptions as $st) {
                                     echo '<option value="' . $st . '">' . ucfirst(str_replace('_', ' ', $st)) . '</option>';
                                }
                                ?>
                            </select>
                            <button id="update-order-status-btn" class="p-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Update Payment Status -->
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 sticky top-0 bg-white mb-2">UPDATE PAYMENT STATUS</label>
                         <div class="flex gap-2">
                            <select id="payment-status-select" 
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:bg-white transition-colors"
                                    data-order-id="<?php echo $order['order_id']; ?>"
                                    data-current-status="<?php echo htmlspecialchars($order['payment_status']); ?>">
                                <option value="">Choose Status...</option>
                                <?php
                                $pStatuses = [PAYMENT_STATUS_PENDING, PAYMENT_STATUS_PAID, PAYMENT_STATUS_FAILED, PAYMENT_STATUS_REFUNDED];
                                foreach ($pStatuses as $pst) {
                                    $sel = ($order['payment_status'] === $pst) ? 'selected' : '';
                                    echo '<option value="' . $pst . '" ' . $sel . '>' . ucfirst($pst) . '</option>';
                                }
                                ?>
                            </select>
                            <button id="update-payment-status-btn" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <?php if (in_array($order['order_status'], [ORDER_STATUS_PENDING, ORDER_STATUS_CONFIRMED])): ?>
                        <div class="pt-4 border-t border-gray-100">
                             <button id="cancel-order-btn" 
                                    class="w-full py-2 px-4 bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2"
                                    data-order-id="<?php echo $order['order_id']; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancel Order
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                 <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Customer</h2>
                 <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-lg">
                        <?php echo strtoupper(substr($order['customer_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['email'] ?? 'No Email'); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['phone'] ?? 'No Phone'); ?></p>
                    </div>
                 </div>
            </div>

            <!-- Address Cards -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <!-- Shipping -->
                 <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                         <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                         <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Shipping Address</h2>
                    </div>
                    <div class="pl-6 text-sm text-gray-600 leading-relaxed">
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['shipping_full_name'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($order['shipping_address_line1'] ?? ''); ?></p>
                        <?php if (!empty($order['shipping_address_line2'])): ?>
                            <p><?php echo htmlspecialchars($order['shipping_address_line2']); ?></p>
                        <?php endif; ?>
                        <p>
                            <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?>
                        </p>
                        <p>
                            <?php echo htmlspecialchars($order['shipping_pincode'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?>
                        </p>
                    </div>
                 </div>
                 
                 <!-- Billing -->
                 <div class="border-t pt-6">
                     <div class="flex items-center gap-2 mb-3">
                         <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                         <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Billing Address</h2>
                    </div>
                    <div class="pl-6 text-sm text-gray-600 leading-relaxed">
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['billing_full_name'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($order['billing_address_line1'] ?? ''); ?></p>
                        <?php if (!empty($order['billing_address_line2'])): ?>
                            <p><?php echo htmlspecialchars($order['billing_address_line2']); ?></p>
                        <?php endif; ?>
                        <p>
                            <?php echo htmlspecialchars($order['billing_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['billing_state'] ?? ''); ?>
                        </p>
                        <p>
                            <?php echo htmlspecialchars($order['billing_pincode'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($order['billing_country'] ?? ''); ?>
                        </p>
                    </div>
                 </div>
            </div>
            
            <!-- Shipping Management -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Shipment Info</h2>
                
                <?php if (!empty($order['tracking_id'])): ?>
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4">
                        <p class="text-xs text-blue-600 uppercase font-semibold">Tracking Number</p>
                        <div class="flex items-center justify-between mt-1">
                             <p class="font-mono font-medium text-blue-900"><?php echo htmlspecialchars($order['tracking_id']); ?></p>
                             <?php if (!empty($order['courier_partner'])): ?>
                                <a href="https://www.google.com/search?q=<?php echo urlencode($order['courier_partner'] . ' tracking ' . $order['tracking_id']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                             <?php endif; ?>
                        </div>
                        <?php if(!empty($order['courier_partner'])): ?>
                            <p class="text-xs text-blue-500 mt-1">via <?php echo htmlspecialchars($order['courier_partner']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form id="update-shipping-form" class="space-y-3">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 mb-1">COURIER PARTNER</label>
                         <input type="text" name="courier_partner" 
                                value="<?php echo htmlspecialchars($order['courier_partner'] ?? ''); ?>"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 mb-1">TRACKING ID</label>
                         <input type="text" name="tracking_id" 
                                value="<?php echo htmlspecialchars($order['tracking_id'] ?? ''); ?>"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                         <label class="block text-xs font-semibold text-gray-500 mb-1">ESTIMATED DELIVERY</label>
                         <input type="date" name="estimated_delivery" 
                                value="<?php echo !empty($order['estimated_delivery']) ? date('Y-m-d', strtotime($order['estimated_delivery'])) : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                        Update Shipping
                    </button>
                </form>
             </div>

        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/admin-orders.js"></script>
