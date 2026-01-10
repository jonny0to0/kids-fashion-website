<?php
$pageTitle = 'Analytics (Quick View)';
?>

<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Analytics - Quick View</h2>
            <p class="text-sm text-gray-600 mt-1">At-a-glance analytics for fast decision-making</p>
        </div>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/analytics" class="flex items-center gap-3">
            <select name="days" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
            </select>
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium text-sm">Apply</button>
        </form>
    </div>
    
    <!-- High-Level KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">₹<?php echo number_format($totalRevenue, 2); ?></p>
            <?php if ($revenueGrowth != 0): ?>
                <p class="text-xs mt-1 <?php echo $revenueGrowth > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $revenueGrowth > 0 ? '↑' : '↓'; ?> <?php echo number_format(abs($revenueGrowth), 1); ?>% vs previous period
                </p>
            <?php endif; ?>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Total Orders</p>
            <p class="text-2xl font-bold text-gray-800 mt-2"><?php echo number_format($totalOrders); ?></p>
            <p class="text-xs text-gray-500 mt-1"><?php echo $days; ?> days</p>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Average Order Value</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">₹<?php echo number_format($averageOrderValue, 2); ?></p>
            <p class="text-xs text-gray-500 mt-1">Per order</p>
        </div>
        
        <div class="admin-card">
            <p class="text-sm text-gray-600 font-medium">Conversion Snapshot</p>
            <p class="text-2xl font-bold text-gray-800 mt-2"><?php echo $totalOrders > 0 ? number_format(($totalOrders / max($totalOrders, 1)) * 100, 1) : '0'; ?>%</p>
            <p class="text-xs text-gray-500 mt-1">Orders vs Revenue</p>
        </div>
    </div>
    
    <!-- Summary Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Trend (Line Chart) -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue Trend</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <!-- Orders vs Revenue (Combined Chart) -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Orders vs Revenue</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="ordersRevenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Link to Detailed Report -->
    <div class="admin-card bg-gradient-to-r from-pink-50 to-purple-50 border border-pink-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Need More Details?</h3>
                <p class="text-sm text-gray-600 mt-1">Access advanced filters, export options, and detailed analysis in Revenue Analytics</p>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics" class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                View Detailed Report →
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const revenueData = <?php echo json_encode($revenueData); ?>;
        const labels = revenueData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const revenues = revenueData.map(item => parseFloat(item.revenue) || 0);
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    borderColor: 'rgb(219, 39, 119)',
                    backgroundColor: 'rgba(219, 39, 119, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
    
    // Orders vs Revenue Combined Chart
    const ordersRevenueCtx = document.getElementById('ordersRevenueChart');
    if (ordersRevenueCtx) {
        const ordersData = <?php echo json_encode($ordersData); ?>;
        const revenueData = <?php echo json_encode($revenueData); ?>;
        
        // Create a map for quick lookup
        const ordersMap = {};
        ordersData.forEach(item => {
            if (item && item.date) {
                ordersMap[item.date] = parseInt(item.count || 0);
            }
        });
        
        const revenueMap = {};
        revenueData.forEach(item => {
            if (item && item.date) {
                revenueMap[item.date] = parseFloat(item.revenue || 0);
            }
        });
        
        // Get all unique dates and sort
        const allDates = [...new Set([...Object.keys(ordersMap), ...Object.keys(revenueMap)])].sort();
        const labels = allDates.map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        const orders = allDates.map(date => ordersMap[date] || 0);
        const revenues = allDates.map(date => revenueMap[date] || 0);
        
        new Chart(ordersRevenueCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Orders',
                        data: orders,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (₹)',
                        data: revenues,
                        type: 'line',
                        borderColor: 'rgb(219, 39, 119)',
                        backgroundColor: 'rgba(219, 39, 119, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
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
                                if (context.datasetIndex === 0) {
                                    return 'Orders: ' + context.parsed.y;
                                } else {
                                    return 'Revenue: ₹' + context.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Orders'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        },
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

