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
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Product Performance</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/reports" class="flex items-center gap-3">
            <input type="hidden" name="type" value="product">
            <select name="days" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="7" <?php echo ($days ?? 30) == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo ($days ?? 30) == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo ($days ?? 30) == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                <option value="365" <?php echo ($days ?? 30) == 365 ? 'selected' : ''; ?>>Last Year</option>
            </select>
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply</button>
        </form>
    </div>
    
    <?php if (!empty($productPerformance)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">SKU</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Units Sold</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productPerformance as $product): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                            <td class="py-3 px-4 text-center font-semibold"><?php echo number_format($product['total_sold'] ?? 0); ?></td>
                            <td class="py-3 px-4 text-right font-semibold text-green-600">$<?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <p class="text-gray-500">No product performance data available for the selected period.</p>
        </div>
    <?php endif; ?>
</div>

