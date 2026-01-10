<?php
/**
 * Sales Reports Page
 */
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Sales Reports</h1>
    <p class="text-gray-600 mt-1">View detailed sales analytics and trends</p>
</div>

<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Sales Analytics</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/reports" class="flex items-center gap-3">
            <input type="hidden" name="type" value="sales">
            <select name="days" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="7" <?php echo ($days ?? 30) == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo ($days ?? 30) == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo ($days ?? 30) == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                <option value="365" <?php echo ($days ?? 30) == 365 ? 'selected' : ''; ?>>Last Year</option>
            </select>
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply</button>
        </form>
    </div>
    
    <?php if (!empty($salesData)): ?>
        <!-- Sales Chart -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Sales Trend</h3>
            <div style="position: relative; height: 400px; max-height: 400px; overflow: hidden;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Order Status Distribution -->
        <?php if (!empty($statusDistribution)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Order Status Distribution</h3>
                <div style="position: relative; height: 300px; max-height: 300px; overflow: hidden;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <p class="text-gray-500">No sales data available for the selected period.</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($salesData)): ?>
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . date('M d', strtotime($item['date'])) . "'"; }, $salesData)); ?>],
            datasets: [{
                label: 'Revenue',
                data: [<?php echo implode(',', array_column($salesData, 'revenue')); ?>],
                borderColor: 'rgb(219, 39, 119)',
                backgroundColor: 'rgba(219, 39, 119, 0.1)',
                tension: 0.4
            }, {
                label: 'Orders',
                data: [<?php echo implode(',', array_column($salesData, 'order_count')); ?>],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Status Chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx && <?php echo !empty($statusDistribution) ? 'true' : 'false'; ?>) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . ucfirst($item['order_status']) . "'"; }, $statusDistribution)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($statusDistribution, 'count')); ?>],
                backgroundColor: [
                    'rgb(219, 39, 119)',
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)'
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
</script>
<?php endif; ?>

