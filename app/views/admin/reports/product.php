<?php
/**
 * Product Performance Reports Page
 */
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Product Performance Reports</h1>
    <p class="text-gray-600 mt-1">Analyze product sales and performance metrics</p>
</div>

<div class="admin-card mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h2 class="text-xl font-semibold text-gray-800">Product Performance</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/reports" class="flex flex-wrap md:flex-nowrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="type" value="product">
            <select name="days" class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="7" <?php echo ($days ?? 30) == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo ($days ?? 30) == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo ($days ?? 30) == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                <option value="365" <?php echo ($days ?? 30) == 365 ? 'selected' : ''; ?>>Last Year</option>
            </select>
            <button type="submit" class="w-full md:w-auto btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply</button>
        </form>
    </div>
    
    <?php if (!empty($productPerformance)): ?>
        <!-- Desktop Table View -->
        <!-- Desktop Table View -->
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto bg-white rounded-lg border border-gray-100 shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 w-[50%]">Product Details</th>
                        <th class="text-center py-3 px-4 text-sm font-semibold text-gray-600 w-32">SKU</th>
                        <th class="text-center py-3 px-4 text-sm font-semibold text-gray-600 w-48">Units Sold</th>
                        <th class="text-center py-3 px-4 text-sm font-semibold text-gray-600 w-48">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($productPerformance as $product): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                            <td class="py-3 px-4 align-middle">
                                <div class="flex flex-col">
                                    <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" target="_blank" class="font-medium text-gray-900 group-hover:text-pink-600 transition-colors text-base mb-0.5">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-center align-middle">
                                <span class="px-2.5 py-1 rounded-md bg-gray-50 text-gray-600 text-xs font-mono border border-gray-100">
                                    <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center w-48 whitespace-nowrap align-middle">
                                <span class="inline-flex items-center justify-center h-8 px-4 rounded-full bg-blue-50 text-blue-700 font-medium text-sm tabular-nums">
                                    <?php echo number_format($product['total_sold'] ?? 0); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center w-48 whitespace-nowrap align-middle">
                                <span class="font-bold text-gray-900 tabular-nums">
                                    ₹<?php echo number_format($product['revenue'] ?? 0, 2); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            <?php foreach ($productPerformance as $index => $product): ?>
                <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $product['slug']; ?>" target="_blank" class="block group">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 relative overflow-hidden group-hover:shadow-md group-hover:border-pink-200 transition-all duration-200">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-pink-50 to-transparent -mr-8 -mt-8 rounded-full opacity-50"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900 leading-tight mb-1 group-hover:text-pink-600 transition-colors"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-xs text-gray-500 font-mono">SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></p>
                                </div>
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-xs font-bold text-gray-500 border border-gray-100 flex-shrink-0 group-hover:bg-pink-50 group-hover:text-pink-600 group-hover:border-pink-100 transition-colors">
                                    #<?php echo $index + 1; ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 mb-1">Units Sold</span>
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-md bg-blue-50 text-blue-600 group-hover:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <span class="font-bold text-gray-900 text-lg"><?php echo number_format($product['total_sold'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 mb-1">Revenue</span>
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-md bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="font-bold text-gray-900 text-lg">₹<?php echo number_format($product['revenue'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <p class="text-gray-500">No product performance data available for the selected period.</p>
        </div>
    <?php endif; ?>
</div>

