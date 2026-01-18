<?php
$pageTitle = 'Dashboard';
?>

<!-- KPI / Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Products Card -->
    <div class="admin-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Products</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_products']); ?>
                </p>
                <?php if ($stats['products_this_week'] > 0): ?>
                    <p class="text-xs text-gray-500 mt-1">+<?php echo $stats['products_this_week']; ?> this week</p>
                <?php else: ?>
                    <p class="text-xs text-gray-500 mt-1">–</p>
                <?php endif; ?>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Orders Card -->
    <div class="admin-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_orders']); ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">–</p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Customers Card -->
    <div class="admin-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_customers']); ?>
                </p>
                <?php if ($stats['customers_this_week'] > 0): ?>
                    <p class="text-xs text-gray-500 mt-1">+<?php echo $stats['customers_this_week']; ?> this week</p>
                <?php else: ?>
                    <p class="text-xs text-gray-500 mt-1">–</p>
                <?php endif; ?>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Categories Card -->
    <div class="admin-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Categories</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">
                    <?php echo number_format($stats['total_categories']); ?></p>
                <?php if ($stats['categories_this_week'] > 0): ?>
                    <p class="text-xs text-gray-500 mt-1">+<?php echo $stats['categories_this_week']; ?> this week</p>
                <?php else: ?>
                    <p class="text-xs text-gray-500 mt-1">–</p>
                <?php endif; ?>
            </div>
            <div class="bg-orange-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Panel -->
<div class="admin-card mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-3">
        <a href="<?php echo SITE_URL; ?>/admin/product/add"
            class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center justify-center gap-2 transition-all duration-200 w-full sm:w-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Product
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/categories/add"
            class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center justify-center gap-2 transition-all duration-200 w-full sm:w-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Category
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/orders"
            class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center justify-center gap-2 transition-all duration-200 w-full sm:w-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                </path>
            </svg>
            Manage Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/hero-banners"
            class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center justify-center gap-2 transition-all duration-200 w-full sm:w-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
            </svg>
            Manage Banners
        </a>
    </div>
</div>

<!-- Hero Banner Management Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Left Information Card -->
    <div class="admin-card">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Hero Banner Management</h2>
        <p class="text-gray-600 text-sm mb-4">Reimagine Amazon seller central and Shopify</p>
        <a href="<?php echo SITE_URL; ?>/admin/hero-banners"
            class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center justify-center gap-2 transition-all duration-200 w-full sm:w-auto">
            Manage Banners
        </a>
    </div>

    <!-- Right Preview Card -->
    <div class="admin-card">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Hero Banner Management Card</h2>
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                <span class="font-semibold text-gray-800">Active Banners:</span>
                <span class="text-green-600 font-medium"><?php echo $stats['active_hero_banners']; ?></span>
                <span class="text-gray-400">/ <?php echo $stats['total_hero_banners']; ?></span>
            </p>
        </div>

        <?php if (!empty($stats['all_banners'])): ?>
            <div class="grid grid-cols-3 gap-2 mb-4">
                <?php
                $bannerCount = 0;
                foreach ($stats['all_banners'] as $banner):
                    if ($bannerCount >= 3)
                        break;
                    $isActive = ($banner['status'] ?? 'inactive') === 'active';
                    $imageUrl = $banner['desktop_image'] ?? $banner['mobile_image'] ?? '';
                    ?>
                    <div class="relative">
                        <?php if ($imageUrl): ?>
                            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($imageUrl); ?>"
                                alt="<?php echo htmlspecialchars($banner['title'] ?? ''); ?>"
                                class="w-full h-20 object-cover rounded border border-gray-200">
                        <?php else: ?>
                            <div class="w-full h-20 bg-gray-200 rounded border border-gray-200 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <span
                            class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-xs font-medium <?php echo $isActive ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <?php
                    $bannerCount++;
                endforeach;
                ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-500">No banners available</p>
        <?php endif; ?>

        <a href="<?php echo SITE_URL; ?>/admin/hero-banners"
            class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center gap-2 w-full justify-center transition-all duration-200">
            Manage Banners
        </a>
    </div>
</div>

<!-- Recent Activity and Analytics Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Recent Orders Table -->
    <div class="lg:col-span-2 admin-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
            <a href="<?php echo SITE_URL; ?>/admin/orders"
                class="text-sm text-pink-600 hover:text-pink-700 font-medium">
                View All →
            </a>
        </div>
        <?php if (empty($recentOrders)): ?>
            <p class="text-gray-500">No recent orders</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <div class="md:max-h-96 md:overflow-y-auto">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700">Order ID</th>
                                <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700">Customer</th>
                                <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-left py-2 px-2 text-sm font-semibold text-gray-700">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-2 text-sm text-gray-800">
                                        <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>"
                                            class="text-pink-600 hover:text-pink-700 font-medium">
                                            <?php echo htmlspecialchars($order['order_number']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-700">
                                        <?php
                                        $customerName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
                                        echo htmlspecialchars($customerName ?: ($order['email'] ?? 'Admin'));
                                        ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm font-semibold text-gray-800">
                                        $<?php echo number_format($order['final_amount'], 2); ?></td>
                                    <td class="py-3 px-2">
                                        <?php
                                        $status = strtolower($order['order_status'] ?? 'pending');
                                        $badgeClass = 'badge-pending';
                                        if ($status === 'shipped') {
                                            $badgeClass = 'badge-shipped';
                                        } elseif ($status === 'delivered') {
                                            $badgeClass = 'badge-delivered';
                                        }
                                        ?>
                                        <span class="px-2 py-1 rounded text-xs font-medium <?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-600">
                                        <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Analytics Section -->
    <div class="space-y-6">
        <!-- Orders per Day Chart -->
        <div class="admin-card">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Orders per Day</h2>
            <div style="height: 200px; position: relative;">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Revenue Summary Chart -->
        <div class="admin-card">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Revenue Summary</h2>
            <div style="height: 200px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Wait for Chart.js to be fully loaded before initializing
    (function () {
        'use strict';

        let retryCount = 0;
        const maxRetries = 50; // Maximum 5 seconds (50 * 100ms)

        // Check if Chart is available
        function initCharts() {
            if (typeof Chart === 'undefined') {
                retryCount++;
                if (retryCount < maxRetries) {
                    console.warn('Chart.js is not loaded. Retrying... (' + retryCount + '/' + maxRetries + ')');
                    setTimeout(initCharts, 100);
                } else {
                    console.error('Chart.js failed to load after ' + maxRetries + ' attempts. Charts will not be displayed.');
                }
                return;
            }

            try {
                // Prepare chart data
                const ordersData = <?php echo json_encode($ordersPerDay ?? []); ?>;
                const revenueData = <?php echo json_encode($revenuePerDay ?? []); ?>;

                // Generate last 7 days labels
                const last7Days = [];
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    last7Days.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                }

                // Prepare orders chart data - map by date string
                const ordersMap = {};
                if (Array.isArray(ordersData)) {
                    ordersData.forEach(item => {
                        if (item && item.date) {
                            const date = new Date(item.date);
                            if (!isNaN(date.getTime())) {
                                const dateKey = date.toISOString().split('T')[0];
                                ordersMap[dateKey] = parseInt(item.count || 0);
                            }
                        }
                    });
                }

                // Prepare revenue chart data - map by date string
                const revenueMap = {};
                if (Array.isArray(revenueData)) {
                    revenueData.forEach(item => {
                        if (item && item.date) {
                            const date = new Date(item.date);
                            if (!isNaN(date.getTime())) {
                                const dateKey = date.toISOString().split('T')[0];
                                revenueMap[dateKey] = parseFloat(item.revenue || 0);
                            }
                        }
                    });
                }

                // Generate data for last 7 days
                const ordersChartValues = [];
                const revenueChartValues = [];

                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    const dateKey = date.toISOString().split('T')[0];
                    ordersChartValues.push(ordersMap[dateKey] || 0);
                    revenueChartValues.push(revenueMap[dateKey] || 0);
                }

                const ordersChartData = {
                    labels: last7Days,
                    datasets: [{
                        label: 'Orders',
                        data: ordersChartValues,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2
                    }]
                };

                const revenueChartData = {
                    labels: last7Days,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: revenueChartValues,
                        backgroundColor: 'rgba(236, 72, 153, 0.2)',
                        borderColor: 'rgb(236, 72, 153)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                };

                // Initialize Orders Chart (Bar Chart)
                const ordersCtx = document.getElementById('ordersChart');
                if (ordersCtx) {
                    try {
                        new Chart(ordersCtx, {
                            type: 'bar',
                            data: ordersChartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        enabled: true
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing orders chart:', error);
                    }
                }

                // Initialize Revenue Chart (Line Chart)
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    try {
                        new Chart(revenueCtx, {
                            type: 'line',
                            data: revenueChartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        enabled: true,
                                        callbacks: {
                                            label: function (context) {
                                                return '$' + context.parsed.y.toFixed(2);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                return '$' + value.toFixed(0);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing revenue chart:', error);
                    }
                }
            } catch (error) {
                console.error('Error preparing chart data:', error);
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            // DOM is already ready
            initCharts();
        }
    })();
</script>