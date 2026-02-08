<?php
$pageTitle = $pageTitle ?? 'Product Details';

// Include breadcrumb and quick actions helpers
require_once __DIR__ . '/../_breadcrumb.php';
?>

<div class="container mx-auto py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Products', 'url' => '/admin/products'],
        ['label' => $product['name']]
    ]);
    ?>

    <!-- Modern Compact Product Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 relative overflow-hidden">
        <div class="relative flex flex-col sm:flex-row gap-5 items-start sm:items-center">
            <!-- Image Container (Smaller) -->
            <div class="shrink-0 relative group">
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-lg overflow-hidden border-2 border-gray-100 shadow-sm flex items-center justify-center">
                    <?php if (!empty($product['primary_image'])): ?>
                        <img src="<?php echo SITE_URL . $product['primary_image']; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="flex-1 min-w-0 pt-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <!-- Status Badge (Smaller) -->
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded textxs font-medium border <?php
                    echo $product['status'] === PRODUCT_STATUS_ACTIVE ? 'bg-green-50 text-green-700 border-green-100' :
                        ($product['status'] === PRODUCT_STATUS_OUT_OF_STOCK ? 'bg-red-50 text-red-700 border-red-100' : 'bg-gray-50 text-gray-700 border-gray-200');
                    ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?php
                        echo $product['status'] === PRODUCT_STATUS_ACTIVE ? 'bg-green-500' :
                            ($product['status'] === PRODUCT_STATUS_OUT_OF_STOCK ? 'bg-red-500' : 'bg-gray-500');
                        ?>"></span>
                        <?php echo str_replace('_', ' ', $product['status']); ?>
                    </span>
                    
                    <span class="text-gray-300 text-xs">|</span>
                    
                    <span class="text-xs text-gray-500 font-mono">
                        SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                    </span>
                </div>

                <h1 class="text-lg sm:text-xl font-bold text-gray-800 leading-tight mb-3">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>

                <!-- Key Metrics (Compact) -->
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-gray-500 text-xs">Stock:</span>
                        <span class="font-semibold text-gray-900"><?php echo number_format($product['stock_quantity']); ?></span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-gray-500 text-xs">Price:</span>
                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <div class="flex items-baseline gap-1.5">
                                <span class="font-bold text-gray-900">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                <span class="text-xs text-gray-400 line-through">₹<?php echo number_format($product['price'], 2); ?></span>
                            </div>
                        <?php else: ?>
                            <span class="font-bold text-gray-900">₹<?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Compact) -->
            <div class="flex flex-row sm:flex-col gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <a href="<?php echo SITE_URL; ?>/admin/product/edit/<?php echo $product['product_id']; ?>"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </a>
                <button onclick="window.print()" 
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-xs font-medium rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Total Revenue</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">₹<?php echo number_format($salesStats['total_revenue'], 2); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Units Sold</h3>
            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo number_format($salesStats['total_units_sold']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Total Orders</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo number_format($salesStats['total_orders']); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Returns</h3>
            <p class="text-3xl font-bold text-red-600 mt-2"><?php echo number_format($salesStats['returned_units']); ?></p>
        </div>
    </div>

    <!-- Product Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Product Attributes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <div class="grid grid-cols-3 gap-4 border-b pb-2">
                <span class="text-gray-500 font-medium">Price</span>
                <span class="col-span-2 text-gray-900 font-medium">₹<?php echo number_format($product['price'], 2); ?></span>
            </div>
            <?php if (!empty($product['sale_price'])): ?>
            <div class="grid grid-cols-3 gap-4 border-b pb-2">
                <span class="text-gray-500 font-medium">Sale Price</span>
                <span class="col-span-2 text-red-600 font-bold">₹<?php echo number_format($product['sale_price'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="grid grid-cols-3 gap-4 border-b pb-2">
                <span class="text-gray-500 font-medium">Cost Price</span>
                <span class="col-span-2 text-gray-900">₹<?php echo number_format($product['cost_price'] ?? 0, 2); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 border-b pb-2">
                <span class="text-gray-500 font-medium">Weight</span>
                <span class="col-span-2 text-gray-900"><?php echo $product['weight'] ? $product['weight'] . ' kg' : 'N/A'; ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 border-b pb-2">
                <span class="text-gray-500 font-medium">Created On</span>
                <span class="col-span-2 text-gray-900"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Sales History Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Sales History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No sales history found for this product yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>" class="hover:underline">
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? 'Guest')); ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?php echo number_format($order['item_quantity']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?php echo number_format($order['item_total'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    switch($order['order_status']) {
                                        case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
