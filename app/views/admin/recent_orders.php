<?php
$pageTitle = 'Recent Orders';
?>

<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Recent Orders</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/recent-orders"
            class="flex flex-col md:flex-row w-full md:w-auto gap-3">
            <select name="status"
                class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <select name="limit"
                class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20 Orders</option>
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 Orders</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 Orders</option>
            </select>
            <button type="submit"
                class="w-full md:w-auto btn-pink-gradient px-6 py-2 rounded-lg font-medium">Filter</button>
        </form>
    </div>

    <?php if (!empty($recentOrders)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    #<?php echo htmlspecialchars($order['order_id']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹<?php echo number_format($order['final_amount'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClass = 'badge-pending';
                                if ($order['order_status'] === 'delivered')
                                    $statusClass = 'badge-delivered';
                                elseif ($order['order_status'] === 'shipped')
                                    $statusClass = 'badge-shipped';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>"
                                    class="text-pink-600 hover:text-pink-900">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
            <p class="mt-1 text-sm text-gray-500">No recent orders match your criteria.</p>
        </div>
    <?php endif; ?>
</div>

<div class="admin-card">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-3">
        <a href="<?php echo SITE_URL; ?>/admin/orders"
            class="w-full sm:w-auto justify-center btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
            View All Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics"
            class="w-full sm:w-auto justify-center bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center gap-2">
            Revenue Analytics
        </a>
    </div>
</div>