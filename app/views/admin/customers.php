<?php
$pageTitle = 'Manage Customers';
?>

<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manage Customers</h2>
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/customers" class="flex items-center gap-3">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by name, email, or phone" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 w-64">
            <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Search</button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo SITE_URL; ?>/admin/customers" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="mb-4 text-sm text-gray-600">
        Total Customers: <span class="font-semibold"><?php echo number_format($total); ?></span>
    </div>
    
    <?php if (!empty($customers)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-semibold">
                                        <?php echo strtoupper(substr($customer['name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($customer['name'] ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500">ID: <?php echo $customer['user_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></div>
                                <?php if (!empty($customer['phone'])): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo number_format($customer['order_count'] ?? 0); ?> orders
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = $customer['status'] ?? 'active';
                                $statusClass = $status === 'active' ? 'badge-active' : 'badge-inactive';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total > $perPage): ?>
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <?php echo (($page - 1) * $perPage) + 1; ?> to <?php echo min($page * $perPage, $total); ?> of <?php echo number_format($total); ?> customers
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page * $perPage < $total): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No customers found</h3>
            <p class="mt-1 text-sm text-gray-500">
                <?php echo !empty($search) ? 'Try adjusting your search criteria.' : 'No customers have registered yet.'; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

