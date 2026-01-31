<?php
$pageTitle = 'Manage Orders';

// Include breadcrumb helper
require_once __DIR__ . '/../_breadcrumb.php';

// Helper function to mask sensitive data
function maskPhone($phone)
{
    if (empty($phone) || strlen($phone) < 4)
        return $phone;
    return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
}

function maskEmail($email)
{
    if (empty($email))
        return $email;
    $parts = explode('@', $email);
    if (count($parts) !== 2)
        return $email;
    $name = $parts[0];
    if (strlen($name) <= 2)
        return $email;
    $masked = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 2)) . '@' . $parts[1];
    return $masked;
}

// Check user role for data masking
$isFullAdmin = Session::isAdmin();

// Ensure analytics is always available with defaults
$analytics = $analytics ?? [];
$analytics['orders_today'] = $analytics['orders_today'] ?? 0;
$analytics['pending_orders'] = $analytics['pending_orders'] ?? 0;
$analytics['shipped_orders'] = $analytics['shipped_orders'] ?? 0;
$analytics['delivered_orders'] = $analytics['delivered_orders'] ?? 0;
$analytics['total_revenue'] = $analytics['total_revenue'] ?? 0;

// Prepare revenue trend data for chart
$revenueTrend = $revenueTrend ?? [];
$revenueChartData = [];
$revenueChartLabels = [];

// Generate last 30 days data
$dateMap = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $revenueChartLabels[] = date('M d', strtotime($date));
    $revenueChartData[] = 0; // Default to 0
    $dateMap[$date] = count($revenueChartData) - 1;
}

// Map actual revenue data to chart data
foreach ($revenueTrend as $item) {
    $date = $item['date'] ?? '';
    if (isset($dateMap[$date])) {
        $revenueChartData[$dateMap[$date]] = (float) ($item['revenue'] ?? 0);
    }
}
?>

<style>
    .insight-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        padding: 1.5rem;
        border-left: 4px solid;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .insight-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .insight-card.blue {
        border-left-color: #3b82f6;
    }

    .insight-card.purple {
        border-left-color: #a855f7;
    }

    .insight-card.orange {
        border-left-color: #f97316;
    }

    .insight-card.green {
        border-left-color: #10b981;
    }

    /* Active state styles */
    .insight-card.active {
        background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(249, 250, 251, 1) 100%);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
        border-left-width: 6px;
    }

    .insight-card.active.blue {
        border-left-color: #2563eb;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .insight-card.active.purple {
        border-left-color: #9333ea;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(168, 85, 247, 0.2);
    }

    .insight-card.active.orange {
        border-left-color: #ea580c;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(249, 115, 22, 0.2);
    }

    .insight-card.active.green {
        border-left-color: #059669;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .insight-card.active .text-3xl {
        font-weight: 800;
    }

    .insight-card.active .text-sm {
        font-weight: 600;
        color: #374151;
    }

    /* Inactive state - subtle fade */
    .insight-card:not(.active) {
        opacity: 0.85;
    }

    .insight-card:not(.active):hover {
        opacity: 1;
    }

    .revenue-chart-container {
        position: relative;
        height: 300px;
        margin-top: 1rem;
    }

    .sticky-filter {
        position: sticky;
        top: 80px;
        z-index: 50;
        background: white;
        padding-top: 1rem;
        margin-top: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .sticky-filter {
            position: relative;
            top: 0;
        }
    }

    .table-header-sticky {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f9fafb;
    }

    /* Fix date range overflow */
    .filter-section {
        overflow-x: visible;
        box-sizing: border-box;
    }

    .filter-section input[type="date"] {
        min-width: 0;
        box-sizing: border-box;
        flex-shrink: 1;
    }

    .filter-section .flex.items-center.gap-2 {
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    /* Ensure grid doesn't overflow */
    .filter-section .grid {
        width: 100%;
        box-sizing: border-box;
    }

    .filter-section .grid>div {
        min-width: 0;
        box-sizing: border-box;
    }

    @media (max-width: 1280px) {
        .filter-section .lg\:col-span-2 {
            grid-column: span 2 / span 2;
        }
    }

    @media (max-width: 1024px) {
        .filter-section .grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-section .lg\:col-span-2 {
            grid-column: span 2 / span 2;
        }
    }

    @media (max-width: 768px) {
        .filter-section .grid {
            grid-template-columns: 1fr;
        }

        .filter-section .lg\:col-span-2 {
            grid-column: span 1 / span 1;
        }
    }
</style>

<div class="w-full px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Orders']
    ]);
    ?>

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manage Orders</h1>
    </div>

    <!-- Quick Insights Cards -->
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Quick Insights</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="quick-insights-container">
            <!-- Today's Orders -->
            <a href="<?php echo SITE_URL; ?>/admin/orders?date_from=<?php echo date('Y-m-d'); ?>"
                class="insight-card blue block" data-insight-id="today" data-filter-type="date"
                data-filter-value="<?php echo date('Y-m-d'); ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Today's Orders</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?php echo number_format($analytics['orders_today']); ?></p>
                    </div>
                    <div class="p-3 rounded-full" style="background: rgba(59, 130, 246, 0.1);">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Pending Orders -->
            <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_PENDING; ?>"
                class="insight-card purple block" data-insight-id="pending" data-filter-type="status"
                data-filter-value="<?php echo ORDER_STATUS_PENDING; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pending Orders</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?php echo number_format($analytics['pending_orders']); ?></p>
                    </div>
                    <div class="p-3 rounded-full" style="background: rgba(168, 85, 247, 0.1);">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Shipped Orders -->
            <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_SHIPPED; ?>"
                class="insight-card orange block" data-insight-id="shipped" data-filter-type="status"
                data-filter-value="<?php echo ORDER_STATUS_SHIPPED; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Shipped Orders</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?php echo number_format($analytics['shipped_orders']); ?></p>
                    </div>
                    <div class="p-3 rounded-full" style="background: rgba(249, 115, 22, 0.1);">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                            </path>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Delivered Orders -->
            <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_DELIVERED; ?>"
                class="insight-card green block" data-insight-id="delivered" data-filter-type="status"
                data-filter-value="<?php echo ORDER_STATUS_DELIVERED; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Delivered Orders</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?php echo number_format($analytics['delivered_orders']); ?></p>
                    </div>
                    <div class="p-3 rounded-full" style="background: rgba(16, 185, 129, 0.1);">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Revenue Trend Section -->
    <div class="mb-6 md:bg-white md:rounded-lg md:shadow md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Revenue Trend</h2>
                <p class="text-sm text-gray-600 mt-1">All Delivered Orders (Global Revenue)</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    $<?php echo number_format($analytics['total_revenue'], 2); ?></p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="date" id="revenue-date-filter"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                    value="<?php echo isset($_GET['revenue_date_from']) ? htmlspecialchars($_GET['revenue_date_from']) : date('Y-m-d', strtotime('-30 days')); ?>">
                <button type="button" onclick="applyRevenueFilter()" class="p-2 text-gray-600 hover:text-gray-900 flex-shrink-0"
                    title="Apply date filter to revenue chart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="revenue-chart-container">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filter-section bg-white rounded-lg shadow-md p-6 mb-6 sticky-filter">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/orders" class="space-y-4" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search Order ID / Tracking ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                        placeholder="Search..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <!-- Order Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">All Statuses</label>
                    <select name="order_status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">All Statuses</option>
                        <option value="<?php echo ORDER_STATUS_PENDING; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_PENDING) ? 'selected' : ''; ?>>Pending</option>
                        <option value="<?php echo ORDER_STATUS_CONFIRMED; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_CONFIRMED) ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="<?php echo ORDER_STATUS_PROCESSING; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_PROCESSING) ? 'selected' : ''; ?>>Processing</option>
                        <option value="<?php echo ORDER_STATUS_SHIPPED; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_SHIPPED) ? 'selected' : ''; ?>>Shipped</option>
                        <option value="<?php echo ORDER_STATUS_DELIVERED; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_DELIVERED) ? 'selected' : ''; ?>>Delivered</option>
                        <option value="<?php echo ORDER_STATUS_CANCELLED; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_CANCELLED) ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="<?php echo ORDER_STATUS_RETURNED; ?>" <?php echo (isset($filters['order_status']) && $filters['order_status'] === ORDER_STATUS_RETURNED) ? 'selected' : ''; ?>>Returned</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">All Payments</label>
                    <select name="payment_status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">All Payments</option>
                        <option value="<?php echo PAYMENT_STATUS_PENDING; ?>" <?php echo (isset($filters['payment_status']) && $filters['payment_status'] === PAYMENT_STATUS_PENDING) ? 'selected' : ''; ?>>Pending</option>
                        <option value="<?php echo PAYMENT_STATUS_PAID; ?>" <?php echo (isset($filters['payment_status']) && $filters['payment_status'] === PAYMENT_STATUS_PAID) ? 'selected' : ''; ?>>Paid</option>
                        <option value="<?php echo PAYMENT_STATUS_FAILED; ?>" <?php echo (isset($filters['payment_status']) && $filters['payment_status'] === PAYMENT_STATUS_FAILED) ? 'selected' : ''; ?>>Failed</option>
                        <option value="<?php echo PAYMENT_STATUS_REFUNDED; ?>" <?php echo (isset($filters['payment_status']) && $filters['payment_status'] === PAYMENT_STATUS_REFUNDED) ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>

                <!-- Date Range Selector -->
                <div class="lg:col-span-2 xl:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="flex flex-col sm:flex-row items-center gap-2 min-w-0 w-full">
                        <input type="date" name="date_from"
                            value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>"
                            class="date-picker w-full sm:flex-1 min-w-0 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <span class="text-gray-500 px-1 hidden sm:inline">–</span>
                        <span class="text-gray-500 px-1 sm:hidden">to</span>
                        <input type="date" name="date_to"
                            value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>"
                            class="date-picker w-full sm:flex-1 min-w-0 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="submit"
                    class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Apply Filters
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/orders"
                    class="w-full sm:w-auto text-center bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 table-header-sticky">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                            ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No orders found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>"
                                        class="text-blue-600 hover:text-blue-700 font-medium">
                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                            <span class="text-gray-600 font-medium text-sm">
                                                <?php echo strtoupper(substr($order['customer_name'] ?? 'N', 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $paymentStatus = strtolower($order['payment_status'] ?? 'pending');
                                    $paymentBadgeClass = 'bg-yellow-100 text-yellow-800';
                                    $paymentDotClass = 'bg-yellow-500';
                                    if ($paymentStatus === 'paid') {
                                        $paymentBadgeClass = 'bg-green-100 text-green-800';
                                        $paymentDotClass = 'bg-green-500';
                                    } elseif ($paymentStatus === 'failed') {
                                        $paymentBadgeClass = 'bg-red-100 text-red-800';
                                        $paymentDotClass = 'bg-red-500';
                                    } elseif ($paymentStatus === 'refunded') {
                                        $paymentBadgeClass = 'bg-gray-100 text-gray-800';
                                        $paymentDotClass = 'bg-gray-500';
                                    }
                                    ?>
                                    <div class="flex items-center">
                                        <span class="w-2 h-2 rounded-full <?php echo $paymentDotClass; ?> mr-2"></span>
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full <?php echo $paymentBadgeClass; ?>">
                                            <?php echo ucfirst($paymentStatus); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $orderStatus = strtolower($order['order_status'] ?? 'pending');
                                    $orderBadgeClass = 'bg-yellow-100 text-yellow-800';
                                    if ($orderStatus === 'confirmed') {
                                        $orderBadgeClass = 'bg-blue-100 text-blue-800';
                                    } elseif ($orderStatus === 'processing') {
                                        $orderBadgeClass = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($orderStatus === 'shipped') {
                                        $orderBadgeClass = 'bg-purple-100 text-purple-800';
                                    } elseif ($orderStatus === 'delivered') {
                                        $orderBadgeClass = 'bg-green-100 text-green-800';
                                    } elseif ($orderStatus === 'cancelled') {
                                        $orderBadgeClass = 'bg-red-100 text-red-800';
                                    } elseif ($orderStatus === 'returned') {
                                        $orderBadgeClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $orderBadgeClass; ?>">
                                        <?php echo ucfirst($orderStatus); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                    $<?php echo number_format($order['final_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo SITE_URL; ?>/admin/orders/<?php echo $order['order_id']; ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    <button type="button" class="text-gray-600 hover:text-gray-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pagination && $pagination->getTotalPages() > 1): ?>
        <div class="mt-6 flex justify-center">
            <?php echo $pagination->render(); ?>
        </div>
    <?php endif; ?>

    <!-- Add New Order Button -->
    <div class="fixed bottom-6 right-6">
        <a href="<?php echo SITE_URL; ?>/admin/orders/add"
            class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Order
        </a>
    </div>
</div>

<script>
    // Revenue Trend Chart
    // IMPORTANT: This chart is independent of Quick Insights card clicks
    // - Chart always shows global revenue (all delivered orders)
    // - Card clicks only filter the orders table, NOT the revenue chart
    // - Chart only changes when revenue-specific date filters are applied
    (function () {
        'use strict';

        window.revenueChart = null;
        let retryCount = 0;
        const maxRetries = 100;

        function initRevenueChart() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined' || typeof Chart.Chart === 'undefined') {
                retryCount++;
                if (retryCount < maxRetries) {
                    setTimeout(initRevenueChart, 100);
                } else {
                    console.error('Chart.js failed to load after ' + maxRetries + ' attempts');
                    // Show error message in chart container
                    const container = document.getElementById('revenueTrendChart');
                    if (container && container.parentElement) {
                        container.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Chart.js library failed to load. Please refresh the page.</div>';
                    }
                }
                return;
            }

            try {
                const ctx = document.getElementById('revenueTrendChart');
                if (!ctx) {
                    console.warn('Revenue chart canvas element not found');
                    return;
                }

                // Destroy existing chart if it exists
                if (window.revenueChart) {
                    window.revenueChart.destroy();
                }

                const revenueData = <?php echo json_encode($revenueChartData); ?>;
                const revenueLabels = <?php echo json_encode($revenueChartLabels); ?>;

                // Use Chart.Chart if Chart.js v4, otherwise use Chart
                const ChartConstructor = typeof Chart.Chart !== 'undefined' ? Chart.Chart : Chart;

                window.revenueChart = new ChartConstructor(ctx, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
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
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                console.log('Revenue chart initialized successfully');
            } catch (error) {
                console.error('Error initializing revenue chart:', error);
                const container = document.getElementById('revenueTrendChart');
                if (container && container.parentElement) {
                    container.parentElement.innerHTML = '<div class="text-center text-red-500 py-8">Error loading chart: ' + error.message + '</div>';
                }
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                // Wait a bit more for Chart.js to be fully loaded
                setTimeout(initRevenueChart, 200);
            });
        } else {
            // DOM already loaded, wait a bit for Chart.js
            setTimeout(initRevenueChart, 200);
        }
    })();


    // Flatpickr initialization removed to allow native browser date pickers (Option 1)

    // IMPROVEMENT: Open native date picker when clicking anywhere in the input
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(function(input) {
                input.addEventListener('click', function(e) {
                    // Prevent default only if necessary, but usually showPicker() works fine on its own
                    // check if showPicker is supported
                    if ('showPicker' in HTMLInputElement.prototype) {
                        try {
                            input.showPicker();
                        } catch (err) {
                            // Ignore errors (e.g. if already open or similar)
                            console.log('Error opening picker:', err);
                        }
                    }
                });
            });
        });
    })();

    // Scroll to Orders Table if filters are applied
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            // Check if any filter parameters are present
            if (urlParams.has('search') || urlParams.has('order_status') || urlParams.has('payment_status') || urlParams.has('date_from') || urlParams.has('date_to')) {
                // Find the orders table container
                const tableContainer = document.querySelector('.bg-white.rounded-lg.shadow-md.overflow-hidden');
                
                // Or finding the filter section if we want to show the filters too, 
                // but requirement says "Page should scroll directly to the Orders table section"
                // So let's target the table container which is right after the filter section
                
                // Alternatively, we can find the table header which has a sticky class
                const tableHeader = document.querySelector('.table-header-sticky');
                
                if (tableHeader) {
                   // Scroll with a bit of offset for the sticky header
                   const yOffset = -100; // Account for any fixed headers
                   const element = tableHeader.closest('.bg-white'); // Get the card containing the table
                   
                   if (element) {
                        const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
                        window.scrollTo({top: y, behavior: 'smooth'});
                   }
                }
            }
        });
    })();

    // Revenue filter function (AJAX)
    // Uses the new ordersRevenueData endpoint to update chart without page reload
    function applyRevenueFilter() {
        const dateFrom = document.getElementById('revenue-date-filter').value;
        const btn = document.querySelector('button[onclick="applyRevenueFilter()"]');
        const originalContent = btn.innerHTML;
        
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        // Build URL
        const url = new URL('<?php echo SITE_URL; ?>/admin/orders/revenue-data', window.location.origin);
        if (dateFrom) {
            url.searchParams.set('revenue_date_from', dateFrom);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (window.revenueChart && data.labels && data.data) {
                window.revenueChart.data.labels = data.labels;
                window.revenueChart.data.datasets[0].data = data.data;
                window.revenueChart.update();
                
                // Update total text if returned
                if (data.total_revenue !== undefined) {
                    const totalEl = document.querySelector('.admin-card h2 + p + p');
                    if (totalEl) {
                        totalEl.innerText = '$' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_revenue);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching revenue data:', error);
            // Optional: show error toast
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    // Quick Insights Active State Management
    (function () {
        'use strict';

        let activeCardId = null;

        /**
         * Determine which card should be active based on URL parameters
         */
        function determineActiveCard() {
            const urlParams = new URLSearchParams(window.location.search);
            const dateFrom = urlParams.get('date_from');
            const orderStatus = urlParams.get('order_status');
            const today = new Date().toISOString().split('T')[0];

            // Check if today's date filter is active
            if (dateFrom === today && !orderStatus) {
                return 'today';
            }

            // Check order status filters
            if (orderStatus) {
                const statusMap = {
                    '<?php echo ORDER_STATUS_PENDING; ?>': 'pending',
                    '<?php echo ORDER_STATUS_SHIPPED; ?>': 'shipped',
                    '<?php echo ORDER_STATUS_DELIVERED; ?>': 'delivered'
                };
                return statusMap[orderStatus] || null;
            }

            // Default to 'today' if no filters are active
            return 'today';
        }

        /**
         * Set active card by ID
         */
        function setActiveCard(cardId) {
            // Remove active class from all cards
            const allCards = document.querySelectorAll('.insight-card');
            allCards.forEach(card => {
                card.classList.remove('active');
            });

            // Add active class to selected card
            const targetCard = document.querySelector(`[data-insight-id="${cardId}"]`);
            if (targetCard) {
                targetCard.classList.add('active');
                activeCardId = cardId;
            }
        }

        /**
         * Handle card click
         * 
         * IMPORTANT: Card clicks only filter the orders table.
         * The Revenue Trend chart remains unchanged and shows global revenue.
         * This ensures stable financial analytics independent of operational filters.
         */
        function handleCardClick(event) {
            const card = event.currentTarget;
            const cardId = card.getAttribute('data-insight-id');

            if (!cardId) return;

            // Set active state
            setActiveCard(cardId);

            // Store active state in sessionStorage for persistence
            sessionStorage.setItem('activeInsightCard', cardId);

            // Note: Revenue chart is NOT updated here
            // Chart remains showing global revenue data
        }

        /**
         * Initialize Quick Insights
         */
        function initQuickInsights() {
            const container = document.getElementById('quick-insights-container');
            if (!container) return;

            const cards = container.querySelectorAll('.insight-card');
            if (cards.length === 0) return;

            // Determine active card from URL parameters (prioritize URL over sessionStorage)
            const urlParams = new URLSearchParams(window.location.search);
            let initialActiveCard = determineActiveCard();

            // If URL has specific filters, use them and clear sessionStorage
            if (urlParams.has('date_from') || urlParams.has('order_status')) {
                // Clear stored card since URL parameters take precedence
                sessionStorage.removeItem('activeInsightCard');
            } else {
                // No URL filters, check sessionStorage for last selected card
                const storedCard = sessionStorage.getItem('activeInsightCard');
                if (storedCard) {
                    initialActiveCard = storedCard;
                }
            }

            // Set initial active card (default to 'today' if nothing else)
            if (initialActiveCard) {
                setActiveCard(initialActiveCard);
            } else {
                // Fallback to 'today' as default
                setActiveCard('today');
            }

            // Add click handlers to all cards
            cards.forEach(card => {
                card.addEventListener('click', handleCardClick);

                // Add keyboard accessibility
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'button');
                card.setAttribute('aria-pressed', 'false');

                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        card.click();
                    }
                });
            });

            // Update ARIA attributes when active state changes
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const card = mutation.target;
                        const isActive = card.classList.contains('active');
                        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    }
                });
            });

            cards.forEach(card => {
                observer.observe(card, { attributes: true, attributeFilter: ['class'] });
            });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQuickInsights);
        } else {
            initQuickInsights();
        }
    })();

    // Ensure filter form works correctly
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                // Form will submit normally via GET, no need to prevent default
                // But we can add loading state if needed
                const submitBtn = filterForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Searching...';
                }

                // Clear active card state when filters are applied
                sessionStorage.removeItem('activeInsightCard');
            });
        }

        console.log('Admin Orders page initialized');
    });
</script>