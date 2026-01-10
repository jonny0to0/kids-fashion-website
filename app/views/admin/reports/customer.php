<?php
/**
 * Customer Reports Page
 * Comprehensive customer analytics and insights
 */
$pageTitle = 'Customer Reports';

// Ensure all variables are set with defaults
$summaryMetrics = $summaryMetrics ?? ['total_customers' => 0, 'new_customers' => 0, 'active_customers' => 0, 'inactive_customers' => 0, 'customers_with_orders' => 0, 'returning_customers' => 0];
$customerGrowth = $customerGrowth ?? [];
$customerSegmentation = $customerSegmentation ?? [];
$customerValueMetrics = $customerValueMetrics ?? [];
$retentionMetrics = $retentionMetrics ?? ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
$retentionRate = $retentionRate ?? 0;
$avgAOV = $avgAOV ?? 0;
$avgCLV = $avgCLV ?? 0;
$topCustomers = $topCustomers ?? [];
$customersList = $customersList ?? [];
$totalCustomers = $totalCustomers ?? 0;
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
$filters = $filters ?? [];
$days = $days ?? 30;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Customer Reports</h1>
    <p class="text-gray-600 mt-1">Analyze customer behavior, value, and retention patterns</p>
</div>

<!-- Filter Bar -->
<div class="admin-card mb-6">
    <form method="GET" action="<?php echo SITE_URL; ?>/admin/reports" id="customerReportFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <input type="hidden" name="type" value="customer">
        
        <!-- Date Range -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <select name="days" id="dateRangeSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="7" <?php echo ($days ?? 30) == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo ($days ?? 30) == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo ($days ?? 30) == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                <option value="365" <?php echo ($days ?? 30) == 365 ? 'selected' : ''; ?>>Last Year</option>
                <option value="custom" <?php echo !empty($filters['date_from']) ? 'selected' : ''; ?>>Custom Range</option>
            </select>
        </div>
        
        <!-- Custom Date Range (hidden by default) -->
        <div id="customDateRange" class="<?php echo !empty($filters['date_from']) ? '' : 'hidden'; ?>">
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        
        <div id="customDateRangeTo" class="<?php echo !empty($filters['date_from']) ? '' : 'hidden'; ?>">
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        
        <!-- Customer Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type</label>
            <select name="customer_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Customers</option>
                <option value="new" <?php echo ($filters['customer_type'] ?? '') === 'new' ? 'selected' : ''; ?>>New Customers</option>
                <option value="returning" <?php echo ($filters['customer_type'] ?? '') === 'returning' ? 'selected' : ''; ?>>Returning Customers</option>
            </select>
        </div>
        
        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Status</option>
                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="suspended" <?php echo ($filters['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            </select>
        </div>
        
        <!-- Search -->
        <div class="lg:col-span-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="flex gap-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                       placeholder="Search by name, email, or phone..." 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">Apply Filters</button>
                <a href="<?php echo SITE_URL; ?>/admin/reports?type=customer" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Key Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <!-- Total Customers -->
    <div class="admin-card cursor-pointer hover:shadow-lg transition-shadow" onclick="filterByMetric('all')">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($summaryMetrics['total_customers'] ?? 0); ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- New Customers -->
    <div class="admin-card cursor-pointer hover:shadow-lg transition-shadow" onclick="filterByMetric('new')">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">New Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($summaryMetrics['new_customers'] ?? 0); ?></p>
                <p class="text-xs text-green-600 mt-1">+<?php echo number_format($summaryMetrics['new_customers'] ?? 0); ?> this period</p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Returning Customers -->
    <div class="admin-card cursor-pointer hover:shadow-lg transition-shadow" onclick="filterByMetric('returning')">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Returning Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($summaryMetrics['returning_customers'] ?? 0); ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Active Customers -->
    <div class="admin-card cursor-pointer hover:shadow-lg transition-shadow" onclick="filterByMetric('active')">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($summaryMetrics['active_customers'] ?? 0); ?></p>
            </div>
            <div class="bg-pink-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Inactive Customers -->
    <div class="admin-card cursor-pointer hover:shadow-lg transition-shadow" onclick="filterByMetric('inactive')">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Inactive Customers</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($summaryMetrics['inactive_customers'] ?? 0); ?></p>
            </div>
            <div class="bg-gray-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Report Sections -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Customer Growth Report -->
    <div class="admin-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Customer Growth</h2>
            <button onclick="exportChart('growthChart')" class="text-sm text-pink-600 hover:text-pink-700">Export</button>
        </div>
        <?php if (!empty($customerGrowth)): ?>
            <div style="position: relative; height: 300px;">
                <canvas id="growthChart"></canvas>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No customer growth data available for the selected period.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Customer Segmentation Report -->
    <div class="admin-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Customer Segmentation</h2>
            <button onclick="exportChart('segmentationChart')" class="text-sm text-pink-600 hover:text-pink-700">Export</button>
        </div>
        <?php if (!empty($customerSegmentation)): ?>
            <div style="position: relative; height: 300px;">
                <canvas id="segmentationChart"></canvas>
            </div>
            <div class="mt-4">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700">Type</th>
                            <th class="px-4 py-2 text-right text-gray-700">Count</th>
                            <th class="px-4 py-2 text-right text-gray-700">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalSeg = array_sum(array_column($customerSegmentation, 'customer_count'));
                        foreach ($customerSegmentation as $segment): 
                            $percentage = $totalSeg > 0 ? ($segment['customer_count'] / $totalSeg) * 100 : 0;
                        ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($segment['customer_type']); ?></td>
                            <td class="px-4 py-2 text-right"><?php echo number_format($segment['customer_count']); ?></td>
                            <td class="px-4 py-2 text-right"><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No customer segmentation data available for the selected period.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Value Report -->
<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Customer Value Metrics</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="text-center p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Average Order Value (AOV)</p>
            <p class="text-2xl font-bold text-blue-600">$<?php echo number_format($avgAOV, 2); ?></p>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Customer Lifetime Value (CLV)</p>
            <p class="text-2xl font-bold text-green-600">$<?php echo number_format($avgCLV, 2); ?></p>
        </div>
        <div class="text-center p-4 bg-purple-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-purple-600">$<?php echo number_format(!empty($customerValueMetrics) ? array_sum(array_column($customerValueMetrics, 'total_spent')) : 0, 2); ?></p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-700">Customer</th>
                    <th class="px-4 py-2 text-left text-gray-700">Email</th>
                    <th class="px-4 py-2 text-center text-gray-700">Orders</th>
                    <th class="px-4 py-2 text-right text-gray-700">Total Spent</th>
                    <th class="px-4 py-2 text-right text-gray-700">AOV</th>
                    <th class="px-4 py-2 text-right text-gray-700">CLV</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customerValueMetrics)): ?>
                    <?php foreach (array_slice($customerValueMetrics, 0, 10) as $customer): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                        <td class="px-4 py-2 text-gray-600"><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td class="px-4 py-2 text-center"><?php echo number_format($customer['total_orders']); ?></td>
                        <td class="px-4 py-2 text-right font-semibold text-green-600">$<?php echo number_format($customer['total_spent'], 2); ?></td>
                        <td class="px-4 py-2 text-right">$<?php echo number_format($customer['avg_order_value'], 2); ?></td>
                        <td class="px-4 py-2 text-right">$<?php echo number_format($customer['customer_lifetime_value'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No customer value data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- Repeat Purchase & Retention Report -->
    <div class="admin-card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Repeat Purchase & Retention</h2>
            <button onclick="exportChart('retentionChart')" class="text-sm text-pink-600 hover:text-pink-700">Export</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">One-Time Buyers</p>
                <p class="text-2xl font-bold text-blue-600"><?php echo number_format($retentionMetrics['one_time_buyers'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Repeat Buyers</p>
                <p class="text-2xl font-bold text-green-600"><?php echo number_format($retentionMetrics['repeat_buyers'] ?? 0); ?></p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Retention Rate</p>
                <p class="text-2xl font-bold text-purple-600"><?php echo number_format($retentionRate, 1); ?>%</p>
            </div>
        </div>
        <?php if (($retentionMetrics['one_time_buyers'] ?? 0) > 0 || ($retentionMetrics['repeat_buyers'] ?? 0) > 0): ?>
            <div style="position: relative; height: 250px;">
                <canvas id="retentionChart"></canvas>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500">No retention data available for the selected period.</p>
            </div>
        <?php endif; ?>
    </div>

<!-- Top Customers Report -->
<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Top Customers</h2>
        <div class="flex gap-2">
            <button onclick="exportTable('topCustomersTable')" class="text-sm text-pink-600 hover:text-pink-700 px-3 py-1 border border-pink-300 rounded">Export CSV</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table id="topCustomersTable" class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-700">Rank</th>
                    <th class="px-4 py-2 text-left text-gray-700">Customer</th>
                    <th class="px-4 py-2 text-left text-gray-700">Email</th>
                    <th class="px-4 py-2 text-center text-gray-700">Orders</th>
                    <th class="px-4 py-2 text-right text-gray-700">Total Spent</th>
                    <th class="px-4 py-2 text-right text-gray-700">AOV</th>
                    <th class="px-4 py-2 text-left text-gray-700">Last Order</th>
                    <th class="px-4 py-2 text-center text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topCustomers)): ?>
                    <?php foreach ($topCustomers as $index => $customer): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-semibold">#<?php echo $index + 1; ?></td>
                        <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                        <td class="px-4 py-2 text-gray-600"><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td class="px-4 py-2 text-center"><?php echo number_format($customer['total_orders']); ?></td>
                        <td class="px-4 py-2 text-right font-semibold text-green-600">$<?php echo number_format($customer['total_spent'], 2); ?></td>
                        <td class="px-4 py-2 text-right">$<?php echo number_format($customer['avg_order_value'], 2); ?></td>
                        <td class="px-4 py-2 text-gray-600"><?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'N/A'; ?></td>
                        <td class="px-4 py-2 text-center">
                            <a href="<?php echo SITE_URL; ?>/admin/customers?search=<?php echo urlencode($customer['email']); ?>" 
                               class="text-pink-600 hover:text-pink-700 text-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No top customers data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed Customer Table -->
<div class="admin-card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">All Customers</h2>
        <div class="flex gap-2">
            <button onclick="exportTable('customersTable')" class="text-sm text-pink-600 hover:text-pink-700 px-3 py-1 border border-pink-300 rounded">Export CSV</button>
        </div>
    </div>
    
    <?php if (!empty($customersList)): ?>
        <div class="overflow-x-auto">
            <table id="customersTable" class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-700 cursor-pointer" onclick="sortTable('customer_name')">
                            Customer <span class="sort-indicator">↕</span>
                        </th>
                        <th class="px-4 py-2 text-left text-gray-700">Email / Phone</th>
                        <th class="px-4 py-2 text-center text-gray-700 cursor-pointer" onclick="sortTable('total_orders')">
                            Orders <span class="sort-indicator">↕</span>
                        </th>
                        <th class="px-4 py-2 text-right text-gray-700 cursor-pointer" onclick="sortTable('total_spent')">
                            Total Spent <span class="sort-indicator">↕</span>
                        </th>
                        <th class="px-4 py-2 text-left text-gray-700">Last Order</th>
                        <th class="px-4 py-2 text-center text-gray-700">Status</th>
                        <th class="px-4 py-2 text-center text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customersList as $customer): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                        <td class="px-4 py-2">
                            <div class="text-gray-600"><?php echo htmlspecialchars($customer['email']); ?></div>
                            <?php if (!empty($customer['phone'])): ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($customer['phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 text-center font-semibold"><?php echo number_format($customer['total_orders']); ?></td>
                        <td class="px-4 py-2 text-right font-semibold text-green-600">$<?php echo number_format($customer['total_spent'], 2); ?></td>
                        <td class="px-4 py-2 text-gray-600">
                            <?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'No orders'; ?>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-full text-xs <?php echo $customer['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($customer['status']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="<?php echo SITE_URL; ?>/admin/customers?search=<?php echo urlencode($customer['email']); ?>" 
                               class="text-pink-600 hover:text-pink-700 text-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalCustomers > $perPage): ?>
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <?php echo (($currentPage - 1) * $perPage) + 1; ?> to <?php echo min($currentPage * $perPage, $totalCustomers); ?> of <?php echo number_format($totalCustomers); ?> customers
            </div>
            <div class="flex gap-2">
                <?php if ($currentPage > 1): ?>
                    <a href="?type=customer&page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query($filters); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($currentPage * $perPage < $totalCustomers): ?>
                    <a href="?type=customer&page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query($filters); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <p class="mt-4 text-gray-500">No customer data found for the selected period.</p>
            <p class="mt-2 text-sm text-gray-400">Try adjusting your filters or date range.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle custom date range
    const dateRangeSelect = document.getElementById('dateRangeSelect');
    const customDateRange = document.getElementById('customDateRange');
    const customDateRangeTo = document.getElementById('customDateRangeTo');
    
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.classList.remove('hidden');
                customDateRangeTo.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');
                customDateRangeTo.classList.add('hidden');
            }
        });
    }
    
    // Customer Growth Chart
    const growthCtx = document.getElementById('growthChart');
    if (growthCtx) {
        const growthData = <?php echo json_encode($customerGrowth); ?>;
        if (growthData && growthData.length > 0) {
            const labels = growthData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const newCustomers = growthData.map(item => parseInt(item.new_customers) || 0);
            
            new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'New Customers',
                    data: newCustomers,
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
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
            });
        }
    }
    
    // Customer Segmentation Chart
    const segCtx = document.getElementById('segmentationChart');
    if (segCtx) {
        const segData = <?php echo json_encode($customerSegmentation); ?>;
        if (segData && segData.length > 0) {
            const labels = segData.map(item => item.customer_type);
            const counts = segData.map(item => parseInt(item.customer_count) || 0);
            
            new Chart(segCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
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
    }
    
    // Retention Chart
    const retentionCtx = document.getElementById('retentionChart');
    if (retentionCtx) {
        const retentionData = {
            oneTime: <?php echo $retentionMetrics['one_time_buyers'] ?? 0; ?>,
            repeat: <?php echo $retentionMetrics['repeat_buyers'] ?? 0; ?>
        };
        
        if (retentionData.oneTime > 0 || retentionData.repeat > 0) {
            new Chart(retentionCtx, {
            type: 'bar',
            data: {
                labels: ['One-Time Buyers', 'Repeat Buyers'],
                datasets: [{
                    label: 'Customers',
                    data: [retentionData.oneTime, retentionData.repeat],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
            });
        }
    }
});

// Filter by metric card click
function filterByMetric(type) {
    const form = document.getElementById('customerReportFilters');
    if (type === 'new') {
        form.querySelector('[name="customer_type"]').value = 'new';
    } else if (type === 'returning') {
        form.querySelector('[name="customer_type"]').value = 'returning';
    } else if (type === 'active') {
        form.querySelector('[name="status"]').value = 'active';
    } else if (type === 'inactive') {
        form.querySelector('[name="status"]').value = 'suspended';
    }
    form.submit();
}

// Export chart
function exportChart(chartId) {
    const canvas = document.querySelector('#' + chartId);
    if (canvas) {
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = chartId + '.png';
        link.href = url;
        link.click();
    }
}

// Export table to CSV
function exportTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', tableId + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Sort table (simple client-side sort)
let sortDirection = {};
function sortTable(column) {
    // This is a placeholder - for full sorting, implement server-side sorting
    console.log('Sort by:', column);
}
</script>
