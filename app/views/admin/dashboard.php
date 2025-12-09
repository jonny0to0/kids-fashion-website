<?php
$pageTitle = 'Admin Dashboard';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars(Session::get('user_name')); ?>!</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Products</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_products']); ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_orders']); ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Customers</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_customers']); ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Categories</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_categories']); ?></p>
                </div>
                <div class="bg-pink-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="<?php echo SITE_URL; ?>/admin/product/add" class="block w-full bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-center font-medium">
                    Add New Product
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/category/add" class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center font-medium">
                    Add New Category
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/products" class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-center font-medium">
                    Manage Products
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/categories" class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-center font-medium">
                    Manage Categories
                </a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Orders</h2>
            <?php if (empty($recentOrders)): ?>
                <p class="text-gray-500">No recent orders</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="border-b border-gray-200 pb-3 last:border-0 last:pb-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($order['order_number']); ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-800">₹<?php echo number_format($order['final_amount'], 2); ?></p>
                                    <span class="text-xs px-2 py-1 rounded <?php 
                                        echo $order['order_status'] === ORDER_STATUS_DELIVERED ? 'bg-green-100 text-green-800' : 
                                            ($order['order_status'] === ORDER_STATUS_CANCELLED ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                    ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


