<?php
$pageTitle = 'Reports / Analytics';
?>

<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Reports / Analytics</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/reports" class="flex items-center gap-3">
            <select name="days" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                <option value="365" <?php echo $days == 365 ? 'selected' : ''; ?>>Last Year</option>
            </select>
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply</button>
        </form>
    </div>
    
    <!-- Sales Chart -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Sales Trend</h3>
        <div style="position: relative; height: 400px; max-height: 400px; overflow: hidden;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    
    <!-- Order Status Distribution -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Order Status Distribution</h3>
        <div style="position: relative; height: 300px; max-height: 300px; overflow: hidden;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Product Performance -->
<div class="admin-card mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top Performing Products</h3>
    <?php if (!empty($productPerformance)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($productPerformance as $product): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($product['sku']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format($product['total_sold']); ?>
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
        <p class="text-gray-500 text-center py-8">No product performance data available for the selected period.</p>
    <?php endif; ?>
</div>

<!-- Summary Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="admin-card">
        <h4 class="text-sm font-medium text-gray-600 mb-2">Total Orders</h4>
        <p class="text-3xl font-bold text-gray-800">
            <?php echo number_format(array_sum(array_column($salesData, 'order_count'))); ?>
        </p>
    </div>
    <div class="admin-card">
        <h4 class="text-sm font-medium text-gray-600 mb-2">Total Revenue</h4>
        <p class="text-3xl font-bold text-gray-800">
            ₹<?php echo number_format(array_sum(array_column($salesData, 'revenue')), 2); ?>
        </p>
    </div>
    <div class="admin-card">
        <h4 class="text-sm font-medium text-gray-600 mb-2">Average Order Value</h4>
        <p class="text-3xl font-bold text-gray-800">
            <?php 
            $totalOrders = array_sum(array_column($salesData, 'order_count'));
            $totalRevenue = array_sum(array_column($salesData, 'revenue'));
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            echo '₹' . number_format($avgOrderValue, 2);
            ?>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesData = <?php echo json_encode($salesData); ?>;
        const labels = salesData.map(item => item.date);
        const orderCounts = salesData.map(item => parseInt(item.order_count) || 0);
        const revenues = salesData.map(item => parseFloat(item.revenue) || 0);
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Orders',
                        data: orderCounts,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4
                    },
                    {
                        label: 'Revenue (₹)',
                        data: revenues,
                        borderColor: 'rgb(219, 39, 119)',
                        backgroundColor: 'rgba(219, 39, 119, 0.1)',
                        yAxisID: 'y1',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = <?php echo json_encode($statusDistribution); ?>;
        const statusLabels = statusData.map(item => item.order_status.charAt(0).toUpperCase() + item.order_status.slice(1));
        const statusCounts = statusData.map(item => parseInt(item.count) || 0);
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(107, 114, 128, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        });
    }
});
</script>

