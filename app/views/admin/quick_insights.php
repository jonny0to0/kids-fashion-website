<?php
$pageTitle = 'Quick Insights';
?>

<div class="admin-card mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Quick Insights</h2>

    <!-- Weekly Insights -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">This Week</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm text-gray-600">New Products</p>
                <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo number_format($productsThisWeek); ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-sm text-gray-600">New Orders</p>
                <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($ordersThisWeek); ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-sm text-gray-600">Revenue</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">₹<?php echo number_format($revenueThisWeek, 2); ?>
                </p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <p class="text-sm text-gray-600">New Customers</p>
                <p class="text-2xl font-bold text-orange-600 mt-1"><?php echo number_format($customersThisWeek); ?></p>
            </div>
        </div>
    </div>

    <!-- Monthly Insights -->
    <div>
        <h3 class="text-lg font-semibold text-gray-700 mb-4">This Month</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm text-gray-600">New Products</p>
                <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo number_format($productsThisMonth); ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-sm text-gray-600">New Orders</p>
                <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($ordersThisMonth); ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-sm text-gray-600">Revenue</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">₹<?php echo number_format($revenueThisMonth, 2); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
        <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics"
            class="btn-pink-gradient w-full sm:w-auto justify-center px-6 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
            View Revenue Analytics
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/reports"
            class="bg-white border border-gray-300 text-gray-700 w-full sm:w-auto justify-center px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center gap-2">
            View Full Reports
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/recent-orders"
            class="bg-white border border-gray-300 text-gray-700 w-full sm:w-auto justify-center px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center gap-2">
            View Recent Orders
        </a>
    </div>
</div>