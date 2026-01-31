<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?> Admin</title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Admin Dashboard'; ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Flatpickr for Date Inputs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Set SITE_URL for JavaScript -->
    <script>
        window.SITE_URL = '<?php echo SITE_URL; ?>';
    </script>

    <style>
        /* Admin Dashboard Styles */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Responsive Images */
        img {
            max-width: 100%;
            height: auto;
        }

        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .admin-main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #f8fafc;
            width: calc(100% - 260px);
            position: relative;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .admin-top-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Sidebar Menu Styles */
        .sidebar-menu-item {
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            cursor: pointer;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
        }

        .sidebar-menu-item:hover {
            background: #334155;
            color: white;
        }

        .sidebar-menu-item.active {
            background: rgb(219 39 119 / var(--tw-bg-opacity, 1));
            --tw-bg-opacity: 1;
            color: white;
        }

        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-submenu.open {
            max-height: 1000px;
        }

        .sidebar-submenu-item {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            text-decoration: none;
        }

        .sidebar-submenu-item:hover {
            background: #334155;
            color: white;
        }

        .sidebar-submenu-item.active {
            background: #334155;
            color: white;
            font-weight: 500;
        }

        /* Nested submenu (Settings under Dashboard) */
        .sidebar-nested-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-nested-submenu.open {
            max-height: 800px;
        }

        .sidebar-nested-submenu-item {
            padding: 0.5rem 1rem 0.5rem 3.5rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .sidebar-nested-submenu-item:hover {
            background: #334155;
            color: white;
        }

        .sidebar-nested-submenu-item.active {
            background: #334155;
            color: white;
            font-weight: 500;
        }

        /* Prevent nested submenu items from triggering parent toggles */
        .sidebar-nested-submenu-item,
        .sidebar-nested-submenu-item * {
            pointer-events: auto;
        }

        /* Ensure nested submenu toggle doesn't interfere with links */
        [data-nested-submenu-toggle] {
            pointer-events: auto;
        }

        [data-nested-submenu-toggle] a {
            pointer-events: none;
        }

        /* Submenu toggle arrow animation */
        .submenu-arrow {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submenu-arrow.rotate-90 {
            transform: rotate(90deg);
        }

        /* Nested submenu arrow animation */
        .nested-submenu-arrow {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nested-submenu-arrow.rotate-90 {
            transform: rotate(90deg);
        }

        /* Flex utilities for menu items */
        .sidebar-menu-item .flex-1 {
            flex: 1;
        }

        /* Pink gradient button */
        .btn-pink-gradient {
            background: linear-gradient(135deg, rgb(219 39 119) 0%, #f472b6 100%);
            color: white;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            border: none;
            outline: none;
        }

        .btn-pink-gradient:hover {
            background: linear-gradient(135deg, #db2777 0%, rgb(219 39 119) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-pink-gradient:active {
            transform: translateY(0);
        }

        .btn-pink-gradient:focus {
            outline: 2px solid rgb(219 39 119);
            outline-offset: 2px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Admin Sidebar Scrollbar - Specific Overrides */
        .admin-sidebar-nav {
            scroll-behavior: smooth;
            overflow-y: auto;
            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }

        .admin-sidebar-nav::-webkit-scrollbar {
            width: 5px; /* Thinner than default */
        }

        .admin-sidebar-nav::-webkit-scrollbar-track {
            background: transparent; /* Seamless look */
        }

        .admin-sidebar-nav::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }

        /* Show scrollbar on hover with higher contrast */
        .admin-sidebar-nav:hover {
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .admin-sidebar-nav:hover::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Card styles */
        .admin-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
        }

        /* Status badges */
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-shipped {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Ensure buttons are clickable and not blocked */
        a.btn-pink-gradient,
        a[class*="btn-"],
        button[class*="btn-"] {
            pointer-events: auto;
            z-index: 1;
            position: relative;
        }

        /* Prevent any overlay from blocking buttons */
        .admin-card a,
        .admin-card button {
            position: relative;
            z-index: 1;
        }

        /* Fix for table links */
        table a {
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // Define navigation state variables before using them
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $parsedPath = parse_url($currentPath, PHP_URL_PATH);

    // Normalize path - remove base path if present
    $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_NAME']));
    $normalizedPath = str_replace($basePath, '', $parsedPath);
    $normalizedPath = trim($normalizedPath, '/');

    // Helper function to check path
    $checkPath = function ($path) use ($normalizedPath, $parsedPath) {
        return strpos($normalizedPath, $path) !== false || strpos($parsedPath, '/' . $path) !== false;
    };

    // 1. Dashboard Section
    $isDashboard = ($normalizedPath === 'admin' || $normalizedPath === '' || empty($normalizedPath));
    $isDashboardOverview = $isDashboard;
    $isDashboardInsights = $checkPath('admin/quick-insights');
    $isDashboardAnalytics = $checkPath('admin/analytics');

    // 2. Catalog Section
    $isCatalog = $checkPath('admin/products') || $checkPath('admin/product/') || $checkPath('admin/categories') || $checkPath('admin/attributes') || $checkPath('admin/attribute-groups');
    $isProducts = $checkPath('admin/products') || $checkPath('admin/product/');
    $isProductList = $normalizedPath === 'admin/products';
    $isProductAdd = $checkPath('admin/product/add');
    $isProductInventory = $checkPath('admin/products/inventory') || $checkPath('admin/products/stock');
    $isProductReviews = $checkPath('admin/products/reviews') || $checkPath('admin/reviews');
    $isCategories = $checkPath('admin/categories');
    $isAttributes = $checkPath('admin/attributes') || $checkPath('admin/attribute-groups');

    // 3. Orders Section
    $isOrders = $checkPath('admin/orders');
    // Check both order_status and status parameters (status for backward compatibility)
    $orderStatus = isset($_GET['order_status']) ? $_GET['order_status'] : (isset($_GET['status']) ? $_GET['status'] : '');
    $isOrdersAll = $isOrders && empty($orderStatus);
    $isOrdersPending = $isOrders && ($orderStatus === ORDER_STATUS_PENDING || $orderStatus === 'pending');
    $isOrdersProcessing = $isOrders && ($orderStatus === ORDER_STATUS_PROCESSING || $orderStatus === 'processing');
    $isOrdersShipped = $isOrders && ($orderStatus === ORDER_STATUS_SHIPPED || $orderStatus === 'shipped');
    $isOrdersCompleted = $isOrders && ($orderStatus === ORDER_STATUS_DELIVERED || $orderStatus === 'delivered' || $orderStatus === 'completed');
    $isOrdersReturns = $isOrders && ($orderStatus === ORDER_STATUS_RETURNED || $orderStatus === 'returns' || $orderStatus === 'refunds');

    // 4. Customers Section
    $isCustomers = $checkPath('admin/customers') || $checkPath('admin/support');
    $isCustomerList = $isCustomers && !$checkPath('admin/customers/groups') && !$checkPath('admin/customers/reviews') && !$checkPath('admin/support');
    $isCustomerGroups = $checkPath('admin/customers/groups');
    $isCustomerReviews = $checkPath('admin/customers/reviews') || $checkPath('admin/customers/feedback');
    $isCustomerSupport = $checkPath('admin/support');

    // 5. Marketing Section
    $isMarketing = $checkPath('admin/promotions') || $checkPath('admin/coupons') || $checkPath('admin/discounts') || $checkPath('admin/hero-banners') || $checkPath('admin/campaigns');
    $isPromotions = $checkPath('admin/promotions') || $checkPath('admin/coupons') || $checkPath('admin/discounts');
    $isCoupons = $checkPath('admin/coupons');
    $isDiscounts = $checkPath('admin/discounts');
    $isBanners = $checkPath('admin/hero-banners') || $checkPath('admin/banners');
    $isCampaignAnalytics = $checkPath('admin/campaigns') || $checkPath('admin/marketing/analytics');

    // 6. Reports Section
    $isReports = $checkPath('admin/reports') || $checkPath('admin/revenue-analytics');
    $reportType = isset($_GET['type']) ? $_GET['type'] : '';
    $isSalesReports = $checkPath('admin/reports') && ($reportType === 'sales' || $reportType === '');
    $isRevenueReports = $checkPath('admin/revenue-analytics') || ($checkPath('admin/reports') && $reportType === 'revenue');
    $isProductReports = $checkPath('admin/reports') && $reportType === 'product';
    $isCustomerReports = $checkPath('admin/reports') && $reportType === 'customer';



    // 8. Settings Section
    $isSettings = $checkPath('admin/settings');
    $settingsSection = isset($_GET['section']) ? $_GET['section'] : 'general';
    $settingsSubsection = isset($_GET['subsection']) ? $_GET['subsection'] : '';
    $isSettingsStore = $isSettings && ($settingsSection === 'general' || $settingsSection === 'store' || $settingsSection === 'seo' || $settingsSection === 'maintenance');
    $isSettingsMaintenance = $isSettings && $settingsSection === 'maintenance';
    $isSettingsPayment = $isSettings && ($settingsSection === 'payment' || $settingsSection === 'tax');
    $isSettingsShipping = $isSettings && $settingsSection === 'shipping';
    $isSettingsShippingZones = $isSettings && $settingsSection === 'shipping' && ($settingsSubsection === 'zones' || empty($settingsSubsection));
    $isSettingsDeliveryMethods = $isSettings && $settingsSection === 'shipping' && $settingsSubsection === 'delivery';
    $isSettingsNotifications = $isSettings && $settingsSection === 'notification';
    $isSettingsSecurity = $isSettings && $settingsSection === 'security';
    $isSettingsRolesPermissions = $isSettings && $settingsSection === 'security' && ($settingsSubsection === 'roles' || empty($settingsSubsection));
    $isSettingsSecuritySettings = $isSettings && $settingsSection === 'security' && $settingsSubsection === 'settings';
    $isSettingsIntegrations = $isSettings && $settingsSection === 'integration';
    $isSettingsBackup = $isSettings && $settingsSection === 'backup';

    // Determine which submenu should be open
    $activeSubmenu = '';
    $activeNestedSubmenu = '';

    if ($isDashboard || $isDashboardOverview || $isDashboardInsights || $isDashboardAnalytics) {
        $activeSubmenu = 'dashboard';
    } elseif ($isCatalog || $isProducts || $isCategories || $isAttributes) {
        $activeSubmenu = 'catalog';
        if ($isProducts) {
            $activeNestedSubmenu = 'products';
        } elseif ($isCategories) {
            $activeNestedSubmenu = 'categories';
        } elseif ($isAttributes) {
            $activeNestedSubmenu = 'attributes';
        }
    } elseif ($isOrders) {
        $activeSubmenu = 'orders';
    } elseif ($isCustomers) {
        $activeSubmenu = 'customers';
    } elseif ($isMarketing) {
        $activeSubmenu = 'marketing';
        if ($isPromotions) {
            $activeNestedSubmenu = 'promotions';
        } elseif ($isBanners) {
            $activeNestedSubmenu = 'banners';
        }
    } elseif ($isReports || $isRevenueReports) {
        $activeSubmenu = 'reports';

    } elseif ($isSettings) {
        $activeSubmenu = 'settings';
        if ($isSettingsStore) {
            $activeNestedSubmenu = 'store-setup';
        } elseif ($isSettingsPayment) {
            $activeNestedSubmenu = 'payments';
        } elseif ($isSettingsShipping) {
            $activeNestedSubmenu = 'shipping';
        } elseif ($isSettingsSecurity) {
            $activeNestedSubmenu = 'security';
        }
    }
    ?>
    <?php
    // Load logo configuration using centralized branding helper (SINGLE SOURCE OF TRUTH)
    require_once APP_PATH . '/helpers/Branding.php';
    $logoConfig = Branding::getLogo('admin');
    ?>
    <div>
        <!-- Left Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar" data-active-submenu="<?php echo $activeSubmenu; ?>"
            data-active-nested-submenu="<?php echo $activeNestedSubmenu; ?>">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-slate-700">
                <?php
                // Render logo using centralized branding helper
                $logoHtml = Branding::renderLogo(
                    $logoConfig,
                    SITE_URL . '/admin',
                    ['class' => 'w-auto object-contain'],
                    ['class' => 'text-xl font-semibold']
                );
                // Adjust max-width for sidebar (slightly narrower)
                if ($logoConfig['type'] === 'image' && $logoConfig['hasImage']) {
                    $logoHtml = str_replace(
                        'max-width: ' . $logoConfig['maxWidth'] . 'px;',
                        'max-width: ' . min($logoConfig['maxWidth'], 180) . 'px;',
                        $logoHtml
                    );
                }
                echo $logoHtml;
                ?>
            </div>

            <!-- Navigation Menu -->
            <nav class="py-4 overflow-y-auto admin-sidebar-nav" style="max-height: calc(100vh - 80px);">
                <!-- 1. Dashboard -->
                <div>
                    <div class="sidebar-menu-item <?php echo ($isDashboard || $isDashboardOverview || $isDashboardInsights || $isDashboardAnalytics) ? 'active' : ''; ?>"
                        data-submenu-toggle="dashboard" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <span class="flex-1">Dashboard</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo ($isDashboard || $isDashboardOverview || $isDashboardInsights || $isDashboardAnalytics) ? 'open' : ''; ?>"
                        data-submenu="dashboard">
                        <a href="<?php echo SITE_URL; ?>/admin"
                            class="sidebar-submenu-item <?php echo $isDashboardOverview ? 'active' : ''; ?>">
                            Overview
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/quick-insights"
                            class="sidebar-submenu-item <?php echo $isDashboardInsights ? 'active' : ''; ?>">
                            Insights
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/analytics"
                            class="sidebar-submenu-item <?php echo $isDashboardAnalytics ? 'active' : ''; ?>">
                            Analytics (Quick View)
                        </a>
                    </div>
                </div>

                <!-- 2. Catalog -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isCatalog ? 'active' : ''; ?>"
                        data-submenu-toggle="catalog" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="flex-1">Catalog</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isCatalog ? 'open' : ''; ?>" data-submenu="catalog">
                        <!-- Products (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isProducts ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="products"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Products</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isProducts ? 'open' : ''; ?>"
                                data-nested-submenu="products">
                                <a href="<?php echo SITE_URL; ?>/admin/products"
                                    class="sidebar-nested-submenu-item <?php echo $isProductList ? 'active' : ''; ?>">
                                    All Products
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/product/add"
                                    class="sidebar-nested-submenu-item <?php echo $isProductAdd ? 'active' : ''; ?>">
                                    Add Product
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/products/inventory"
                                    class="sidebar-nested-submenu-item <?php echo $isProductInventory ? 'active' : ''; ?>">
                                    Inventory
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/products/reviews"
                                    class="sidebar-nested-submenu-item <?php echo $isProductReviews ? 'active' : ''; ?>">
                                    Product Reviews
                                </a>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/categories"
                            class="sidebar-submenu-item <?php echo $isCategories ? 'active' : ''; ?>">
                            Categories
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/attributes"
                            class="sidebar-submenu-item <?php echo $isAttributes ? 'active' : ''; ?>">
                            Attributes
                        </a>
                    </div>
                </div>

                <!-- 3. Orders -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isOrders ? 'active' : ''; ?>" data-submenu-toggle="orders"
                        style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span class="flex-1">Orders</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isOrders ? 'open' : ''; ?>" data-submenu="orders">
                        <a href="<?php echo SITE_URL; ?>/admin/orders"
                            class="sidebar-submenu-item <?php echo $isOrdersAll ? 'active' : ''; ?>">
                            All Orders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_PENDING; ?>"
                            class="sidebar-submenu-item <?php echo $isOrdersPending ? 'active' : ''; ?>">
                            Pending Orders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_PROCESSING; ?>"
                            class="sidebar-submenu-item <?php echo $isOrdersProcessing ? 'active' : ''; ?>">
                            Processing Orders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_SHIPPED; ?>"
                            class="sidebar-submenu-item <?php echo $isOrdersShipped ? 'active' : ''; ?>">
                            Shipped Orders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_DELIVERED; ?>"
                            class="sidebar-submenu-item <?php echo $isOrdersCompleted ? 'active' : ''; ?>">
                            Completed Orders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/orders?order_status=<?php echo ORDER_STATUS_RETURNED; ?>"
                            class="sidebar-submenu-item <?php echo $isOrdersReturns ? 'active' : ''; ?>">
                            Returns / Refunds
                        </a>
                    </div>
                </div>

                <!-- 4. Customers -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isCustomers ? 'active' : ''; ?>"
                        data-submenu-toggle="customers" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span class="flex-1">Customers</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isCustomers ? 'open' : ''; ?>" data-submenu="customers">
                        <a href="<?php echo SITE_URL; ?>/admin/customers"
                            class="sidebar-submenu-item <?php echo $isCustomerList ? 'active' : ''; ?>">
                            Manage Customers
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/customers/groups"
                            class="sidebar-submenu-item <?php echo $isCustomerGroups ? 'active' : ''; ?>">
                            Customer Groups
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/customers/reviews"
                            class="sidebar-submenu-item <?php echo $isCustomerReviews ? 'active' : ''; ?>">
                            Reviews & Feedback
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/support"
                            class="sidebar-submenu-item <?php echo $isCustomerSupport ? 'active' : ''; ?>">
                            Support / Complaints
                        </a>
                    </div>
                </div>

                <!-- 5. Marketing -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isMarketing ? 'active' : ''; ?>"
                        data-submenu-toggle="marketing" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        <span class="flex-1">Marketing</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isMarketing ? 'open' : ''; ?>" data-submenu="marketing">
                        <!-- Promotions (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isPromotions ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="promotions"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Promotions</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isPromotions ? 'open' : ''; ?>"
                                data-nested-submenu="promotions">
                                <a href="<?php echo SITE_URL; ?>/admin/coupons"
                                    class="sidebar-nested-submenu-item <?php echo $isCoupons ? 'active' : ''; ?>">
                                    Coupons
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/discounts"
                                    class="sidebar-nested-submenu-item <?php echo $isDiscounts ? 'active' : ''; ?>">
                                    Discounts
                                </a>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/hero-banners"
                            class="sidebar-submenu-item <?php echo $isBanners ? 'active' : ''; ?>">
                            Banners / Sliders
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/campaigns"
                            class="sidebar-submenu-item <?php echo $isCampaignAnalytics ? 'active' : ''; ?>">
                            Campaign Analytics
                        </a>
                    </div>
                </div>

                <!-- 6. Reports -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isReports ? 'active' : ''; ?>"
                        data-submenu-toggle="reports" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        <span class="flex-1">Reports</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isReports ? 'open' : ''; ?>" data-submenu="reports">
                        <a href="<?php echo SITE_URL; ?>/admin/reports?type=sales"
                            class="sidebar-submenu-item <?php echo $isSalesReports ? 'active' : ''; ?>">
                            Sales Reports
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/revenue-analytics"
                            class="sidebar-submenu-item <?php echo $isRevenueReports ? 'active' : ''; ?>"
                            data-reports-link>
                            Revenue Analytics (Detailed)
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/reports?type=product"
                            class="sidebar-submenu-item <?php echo $isProductReports ? 'active' : ''; ?>">
                            Product Performance
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/reports?type=customer"
                            class="sidebar-submenu-item <?php echo $isCustomerReports ? 'active' : ''; ?>">
                            Customer Reports
                        </a>
                    </div>
                </div>



                <!-- 8. Settings -->
                <div>
                    <div class="sidebar-menu-item <?php echo $isSettings ? 'active' : ''; ?>"
                        data-submenu-toggle="settings" style="cursor: pointer;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="flex-1">Settings</span>
                        <svg class="w-4 h-4 transition-transform submenu-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-submenu <?php echo $isSettings ? 'open' : ''; ?>" data-submenu="settings">
                        <!-- Store Setup (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isSettingsStore ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="store-setup"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Store Setup</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isSettingsStore ? 'open' : ''; ?>"
                                data-nested-submenu="store-setup">
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=general"
                                    class="sidebar-nested-submenu-item <?php echo ($isSettings && $settingsSection === 'general') ? 'active' : ''; ?>">
                                    General Settings
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=store"
                                    class="sidebar-nested-submenu-item <?php echo ($isSettings && $settingsSection === 'store') ? 'active' : ''; ?>">
                                    Store Settings
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=seo"
                                    class="sidebar-nested-submenu-item <?php echo ($isSettings && $settingsSection === 'seo') ? 'active' : ''; ?>">
                                    SEO & Analytics
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=maintenance"
                                    class="sidebar-nested-submenu-item <?php echo $isSettingsMaintenance ? 'active' : ''; ?>">
                                    Maintenance Mode
                                </a>
                            </div>
                        </div>
                        <!-- Payments (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isSettingsPayment ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="payments"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Payments</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isSettingsPayment ? 'open' : ''; ?>"
                                data-nested-submenu="payments">
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=payment"
                                    class="sidebar-nested-submenu-item <?php echo ($isSettings && $settingsSection === 'payment') ? 'active' : ''; ?>">
                                    Payment Methods
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=tax"
                                    class="sidebar-nested-submenu-item <?php echo ($isSettings && $settingsSection === 'tax') ? 'active' : ''; ?>">
                                    Tax Configuration
                                </a>
                            </div>
                        </div>
                        <!-- Shipping (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isSettingsShipping ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="shipping"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Shipping</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isSettingsShipping ? 'open' : ''; ?>"
                                data-nested-submenu="shipping">
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=shipping&subsection=zones"
                                    class="sidebar-nested-submenu-item <?php echo $isSettingsShippingZones ? 'active' : ''; ?>">
                                    Shipping Zones
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=shipping&subsection=delivery"
                                    class="sidebar-nested-submenu-item <?php echo $isSettingsDeliveryMethods ? 'active' : ''; ?>">
                                    Delivery Methods
                                </a>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/settings?section=notification"
                            class="sidebar-submenu-item <?php echo $isSettingsNotifications ? 'active' : ''; ?>">
                            Notifications
                        </a>
                        <!-- Security (Nested Submenu) -->
                        <div>
                            <div class="sidebar-submenu-item <?php echo $isSettingsSecurity ? 'active' : ''; ?>"
                                data-nested-submenu-toggle="security"
                                style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                <span>Security</span>
                                <svg class="w-4 h-4 transition-transform nested-submenu-arrow" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <div class="sidebar-nested-submenu <?php echo $isSettingsSecurity ? 'open' : ''; ?>"
                                data-nested-submenu="security">
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=security&subsection=roles"
                                    class="sidebar-nested-submenu-item <?php echo $isSettingsRolesPermissions ? 'active' : ''; ?>">
                                    Roles & Permissions
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings?section=security&subsection=settings"
                                    class="sidebar-nested-submenu-item <?php echo $isSettingsSecuritySettings ? 'active' : ''; ?>">
                                    Security Settings
                                </a>
                            </div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/settings?section=integration"
                            class="sidebar-submenu-item <?php echo $isSettingsIntegrations ? 'active' : ''; ?>">
                            Integrations
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings?section=backup"
                            class="sidebar-submenu-item <?php echo $isSettingsBackup ? 'active' : ''; ?>">
                            Backup & Maintenance
                        </a>
                    </div>
                </div>

                <!-- 9. System -->
                <div style="margin-top: 1rem; border-top: 1px solid #334155; padding-top: 1rem;">
                    <a href="<?php echo SITE_URL; ?>/user/logout" class="sidebar-menu-item">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>

            <!-- Mobile Menu Toggle -->
            <button data-mobile-menu-close class="md:hidden absolute top-4 right-4 text-white p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-main-content flex-1">
            <!-- Top Header -->
            <header class="admin-top-header">
                <div class="flex items-center justify-between">
                    <!-- Mobile Menu Button -->
                    <button data-mobile-menu-toggle class="md:hidden text-gray-600 hover:text-gray-900 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800 hidden md:block">
                        <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
                    </h1>

                    <!-- Right Section: Search, Notifications, Profile -->
                    <div class="flex items-center gap-4">
                        <!-- Global Search -->
                        <div class="hidden md:block">
                            <form action="<?php echo SITE_URL; ?>/admin/search" method="GET" class="relative">
                                <input type="text" name="q" placeholder="Search product, order, customer"
                                    class="px-4 py-2 pl-10 w-64 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                                <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </form>
                        </div>

                        <!-- Notification Icon -->
                        <!-- Notification Icon -->
                        <div class="relative" data-notification-container>
                             <?php
                            // Fetch unread notifications count
                            // We can use the Notification model directly here since this is a layout file
                            $unreadCount = 0;
                            if (class_exists('Notification') && Session::isLoggedIn()) {
                                $notificationModel = new Notification();
                                $unreadCount = $notificationModel->getUnreadCount(Session::getUserId());
                            }
                            ?>
                            <button data-notification-toggle class="relative text-gray-600 hover:text-gray-900 p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <!-- Notification Red Dot -->
                                <span data-notification-badge class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white <?php echo $unreadCount > 0 ? '' : 'hidden'; ?>"></span>
                            </button>

                            <!-- Notification Dropdown -->
                            <div data-notification-dropdown
                                class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden">
                                <div class="p-3 border-b border-gray-50 flex justify-between items-center bg-gray-50">
                                    <h3 class="font-semibold text-gray-700 text-sm">Notifications</h3>
                                    <button id="mark-all-read" class="text-xs text-pink-600 hover:text-pink-700 font-medium disabled:opacity-50">Mark all read</button>
                                </div>
                                <div id="notification-list" class="max-h-80 overflow-y-auto">
                                    <div class="p-4 text-center text-gray-500 text-sm">
                                        Loading...
                                    </div>
                                </div>
                                <div class="p-2 border-t border-gray-50 bg-gray-50 text-center">
                                    <a href="<?php echo SITE_URL; ?>/admin/notifications"
                                        class="text-xs text-pink-600 font-semibold hover:text-pink-700 block w-full py-1">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Profile -->
                        <div class="relative">
                            <button data-profile-toggle
                                class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                                <div
                                    class="w-8 h-8 rounded-full bg-pink-600 text-white flex items-center justify-center font-semibold">
                                    <?php echo strtoupper(substr(Session::get('user_name') ?? 'A', 0, 1)); ?>
                                </div>
                                <span
                                    class="hidden md:block font-medium"><?php echo htmlspecialchars(Session::get('user_name') ?? 'Admin User'); ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Profile Dropdown -->
                            <div data-profile-dropdown
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                                style="display: none;">
                                <a href="<?php echo SITE_URL; ?>/user/profile"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="<?php echo SITE_URL; ?>/user/logout"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const toggle = document.querySelector('[data-notification-toggle]');
                        const dropdown = document.querySelector('[data-notification-dropdown]');
                        const list = document.getElementById('notification-list');
                        const badge = document.querySelector('[data-notification-badge]');
                        const markAllBtn = document.getElementById('mark-all-read');
                        let isOpen = false;

                        if (!toggle || !dropdown) return;

                        // Toggle dropdown
                        toggle.addEventListener('click', function(e) {
                            e.stopPropagation();
                            isOpen = !isOpen;
                            if (isOpen) {
                                dropdown.classList.remove('hidden');
                                fetchNotifications();
                            } else {
                                dropdown.classList.add('hidden');
                            }
                        });

                        // Close when clicking outside
                        document.addEventListener('click', function(e) {
                            if (isOpen && !dropdown.contains(e.target) && !toggle.contains(e.target)) {
                                isOpen = false;
                                dropdown.classList.add('hidden');
                            }
                        });

                        // Fetch notifications
                        function fetchNotifications() {
                            fetch('<?php echo SITE_URL; ?>/admin/notifications-latest')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        updateBadge(data.unread_count);
                                        renderList(data.notifications);
                                    }
                                })
                                .catch(err => console.error('Error fetching notifications:', err));
                        }

                        // Update Badge
                        function updateBadge(count) {
                            if (!badge) return;
                            if (count > 0) {
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }

                        // Render List
                        function renderList(notifications) {
                            if (!list) return;
                            
                            if (!notifications || notifications.length === 0) {
                                list.innerHTML = `
                                    <div class="p-4 text-center text-gray-500 text-sm flex flex-col items-center">
                                        <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        No new notifications
                                    </div>`;
                                return;
                            }

                            const html = notifications.map(n => {
                                const isRead = parseInt(n.is_read) === 1;
                                const bgClass = isRead ? 'bg-white' : 'bg-blue-50';
                                // Format date safely
                                const date = new Date(n.created_at).toLocaleDateString(undefined, {
                                    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                                });
                                
                                return `
                                    <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors cursor-pointer ${bgClass} group relative" onclick="window.location.href='<?php echo SITE_URL; ?>/admin/notifications-click/${n.notification_id}'">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="text-sm font-medium text-gray-800 line-clamp-1">${n.title}</h4>
                                            <span class="text-xs text-gray-400 whitespace-nowrap ml-2">${date}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 line-clamp-2">${n.message}</p>
                                        ${!isRead ? `<span class="absolute top-3 right-2 w-2 h-2 bg-blue-500 rounded-full"></span>` : ''}
                                    </div>
                                `;
                            }).join('');

                            list.innerHTML = html;
                        }

                        // Mark all as read
                        if (markAllBtn) {
                            markAllBtn.addEventListener('click', function() {
                                fetch('<?php echo SITE_URL; ?>/admin/notifications-mark-all-read', {
                                    method: 'POST'
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        updateBadge(0);
                                        fetchNotifications(); // Refresh list to show read state
                                        // Optional: Toast notification
                                        if (typeof Swal !== 'undefined') {
                                            const Toast = Swal.mixin({
                                                toast: true,
                                                position: 'top-end',
                                                showConfirmButton: false,
                                                timer: 3000
                                            });
                                            Toast.fire({
                                                icon: 'success',
                                                title: 'All notifications marked as read'
                                            });
                                        }
                                    }
                                });
                            });
                        }
                    });
                </script>

            <!-- Page Content -->
            <main class="p-2 md:p-6" style="width: 100%; box-sizing: border-box;">

                <!-- Flash Messages -->
                <?php if ($flashMessage = Session::getFlash('success')): ?>
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert">
                        <span><?php echo htmlspecialchars($flashMessage); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($flashMessage = Session::getFlash('error')): ?>
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span><?php echo htmlspecialchars($flashMessage); ?></span>
                    </div>
                <?php endif; ?>