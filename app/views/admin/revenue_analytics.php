<?php
$pageTitle = 'Revenue Analytics (Detailed Report)';
?>

<div class="mb-6">
    <!-- Header with Export Options -->
    <div class="admin-card mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Revenue Analytics - Detailed Report</h2>
                <p class="text-sm text-gray-600 mt-1">Financial reporting & auditing with advanced filters and export options</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics/export?format=csv&<?php echo http_build_query($_GET); ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium inline-flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/analytics" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium inline-flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Quick View
                </a>
            </div>
        </div>
        
        <!-- Advanced Filters -->
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/revenue-analytics" class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <!-- Quick Days Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quick Select</label>
                    <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="7" <?php echo ($days ?? 30) == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo ($days ?? 30) == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90" <?php echo ($days ?? 30) == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                        <option value="365" <?php echo ($days ?? 30) == 365 ? 'selected' : ''; ?>>Last Year</option>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories ?? [] as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo ($categoryId ?? 0) == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Payment Method Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">All Methods</option>
                        <option value="cod" <?php echo ($paymentMethod ?? '') == 'cod' ? 'selected' : ''; ?>>Cash on Delivery</option>
                        <option value="online" <?php echo ($paymentMethod ?? '') == 'online' ? 'selected' : ''; ?>>Online Payment</option>
                        <option value="upi" <?php echo ($paymentMethod ?? '') == 'upi' ? 'selected' : ''; ?>>UPI</option>
                        <option value="wallet" <?php echo ($paymentMethod ?? '') == 'wallet' ? 'selected' : ''; ?>>Wallet</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply Filters</button>
                <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium inline-flex items-center">Clear Filters</a>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards with Comparisons -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">₹<?php echo number_format($totalRevenue, 2); ?></p>
            <p class="text-xs text-gray-500 mt-1">Selected period</p>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Average Daily Revenue</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">₹<?php echo number_format($averageDailyRevenue, 2); ?></p>
            <p class="text-xs text-gray-500 mt-1">Per day</p>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Month-over-Month</p>
            <p class="text-2xl font-bold mt-2 <?php echo ($momGrowth ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <?php echo ($momGrowth ?? 0) >= 0 ? '+' : ''; ?><?php echo number_format($momGrowth ?? 0, 1); ?>%
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Current: ₹<?php echo number_format($currentMonthRevenue ?? 0, 2); ?><br>
                Previous: ₹<?php echo number_format($previousMonthRevenue ?? 0, 2); ?>
            </p>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Year-over-Year</p>
            <p class="text-2xl font-bold mt-2 <?php echo ($yoyGrowth ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <?php echo ($yoyGrowth ?? 0) >= 0 ? '+' : ''; ?><?php echo number_format($yoyGrowth ?? 0, 1); ?>%
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Current: ₹<?php echo number_format($currentYearRevenue ?? 0, 2); ?><br>
                Previous: ₹<?php echo number_format($previousYearRevenue ?? 0, 2); ?>
            </p>
        </div>
    </div>
    
    <!-- Revenue Trend Chart -->
    <div class="admin-card mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue Trend</h3>
        <div style="height: 400px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Revenue by Payment Method -->
    <?php if (!empty($revenueByPaymentMethod)): ?>
    <div class="admin-card mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue by Payment Method</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($revenueByPaymentMethod as $method): 
                        $percentage = $totalRevenue > 0 ? ($method['revenue'] / $totalRevenue) * 100 : 0;
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo ucfirst(htmlspecialchars($method['payment_method'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($method['order_count']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹<?php echo number_format($method['revenue'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($percentage, 1); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Revenue by Category -->
    <?php if (!empty($revenueByCategory)): ?>
    <div class="admin-card mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue by Category</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($revenueByCategory as $cat): 
                        $percentage = $totalRevenue > 0 ? ($cat['revenue'] / $totalRevenue) * 100 : 0;
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cat['category_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($cat['order_count']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹<?php echo number_format($cat['revenue'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($percentage, 1); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Top Products by Revenue (Drill-down Table) -->
    <div class="admin-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Top Products by Revenue (Drill-down)</h3>
            <span class="text-sm text-gray-500">Showing top 50 products</span>
        </div>
        <?php if (!empty($topProducts)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($topProducts as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($product['order_count']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($product['total_quantity'] ?? 0); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₹<?php echo number_format($product['revenue'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">No revenue data available for the selected period and filters.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        const revenueData = <?php echo json_encode($revenueData); ?>;
        const labels = revenueData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const revenues = revenueData.map(item => parseFloat(item.revenue) || 0);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    borderColor: 'rgb(219, 39, 119)',
                    backgroundColor: 'rgba(219, 39, 119, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₹' + context.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
