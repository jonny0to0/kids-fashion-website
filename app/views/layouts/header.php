<?php
// Ensure user is active (kick out deactivated users)
// This MUST be before any HTML output to avoid "headers already sent" errors during redirect
$userStatusCheck = Session::ensureUserActive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Kids Fashion E-commerce Platform'; ?>">
    
    <!-- Favicon -->
    <?php
    try {
        $settingsModel = new Settings();
        $favicon = $settingsModel->get('store_favicon', '');
        if (!empty($favicon)) {
            $faviconUrl = SITE_URL . '/' . ltrim($favicon, '/');
            // Add cache busting version parameter
            $version = file_exists(PUBLIC_PATH . '/' . ltrim($favicon, '/')) ? filemtime(PUBLIC_PATH . '/' . ltrim($favicon, '/')) : time();
            echo '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($faviconUrl) . '?v=' . $version . '">' . "\n";
        }
    } catch (Exception $e) {
        // Silently fail if settings can't be loaded
        error_log("Header favicon error: " . $e->getMessage());
    }
    ?>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        body { overflow-x: hidden; }
        img { max-width: 100%; height: auto; }
        
        /* Product Card Enhancements */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Smooth animations for product cards */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Enhanced hover effects */
        .group:hover .group-hover\:scale-110 {
            transform: scale(1.1);
        }
        
        /* Wishlist button active state */
        .wishlist-btn.in-wishlist svg {
            fill: currentColor;
            color: #ec4899;
        }
        
        /* Hero Banner Slider Styles */
        .hero-banner-section {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 100vw;
            margin: 0;
            padding: 0;
        }
        
        .hero-banner-container {
            position: relative;
            width: 100%;
            max-width: 100%;
            height: 400px;
            max-height: 400px;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Responsive height constraints - Mobile First */
        @media (min-width: 640px) {
            .hero-banner-container {
                height: 500px;
                max-height: 500px;
            }
        }
        
        @media (min-width: 768px) {
            .hero-banner-container {
                height: 600px;
                max-height: 600px;
            }
        }
        
        @media (min-width: 1024px) {
            .hero-banner-container {
                height: 700px;
                max-height: 700px;
            }
        }
        
        /* Prevent overflow on very large screens */
        @media (min-width: 1920px) {
            .hero-banner-container {
                max-height: 800px;
                height: 800px;
            }
        }
        
        /* Ensure container never exceeds viewport height */
        @media (max-height: 600px) {
            .hero-banner-container {
                max-height: 50vh;
                height: 50vh;
            }
        }
        
        @media (max-height: 800px) and (min-width: 768px) {
            .hero-banner-container {
                max-height: 60vh;
            }
        }
        
        .hero-banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: opacity 0.6s ease-in-out;
            will-change: opacity;
            overflow: hidden;
        }
        
        .hero-banner-slide.active {
            opacity: 1;
            z-index: 10;
        }
        
        .hero-banner-slide > div {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .hero-banner-slide picture,
        .hero-banner-slide img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            margin: 0;
            padding: 0;
        }
        
        /* Prevent any image overflow */
        .hero-banner-slide * {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .hero-nav-arrow {
            transition: all 0.3s ease;
        }
        
        .hero-nav-arrow:hover {
            transform: translateY(-50%) scale(1.1);
        }
        
        .hero-pagination-dot {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .hero-pagination-dot:hover {
            background-color: rgba(255, 255, 255, 0.75) !important;
        }
        
        /* Optimize for LCP (Largest Contentful Paint) */
        .hero-banner-slide:first-child img {
            fetchpriority: high;
        }
        
        /* Custom scrollbar for better aesthetics */
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
        
        /* Product card image loading state */
        .product-card-image {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        }
        
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Smooth scroll header transitions */
        #main-nav-content {
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
            transform: translateY(0);
            opacity: 1;
        }
        
        #main-nav.nav-hidden #main-nav-content {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        
        /* SweetAlert2 toast position adjustment - move down slightly */
        .swal2-container.swal2-top-end {
            top: 55px !important;
        }
        
        /* Category Slider Styles */
        .category-slider-wrapper {
            position: relative;
        }
        
        .category-slider-container {
            position: relative;
            width: 100%;
        }
        
        .category-slider-track {
            display: flex;
            will-change: transform;
        }
        
        .category-slider-item {
            flex: 0 0 auto;
            width: 100px;
        }
        
        @media (min-width: 640px) {
            .category-slider-item {
                width: 120px;
            }
        }
        
        @media (min-width: 1024px) {
            .category-slider-item {
                width: 140px;
            }
        }
        
        .category-image {
            transition: all 0.3s ease;
        }
        
        .category-card:hover .category-image {
            transform: scale(1.05);
        }
        
        .category-slider-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .category-slider-nav:active {
            transform: scale(0.95) translateY(-50%);
        }
        
        .category-slider-nav-prev:active {
            transform: scale(0.95) translateY(-50%);
        }
        
        .category-slider-nav-next:active {
            transform: scale(0.95) translateY(-50%);
        }
        
        /* Smooth scrolling for touch devices */
        .category-slider-container {
            -webkit-overflow-scrolling: touch;
        }
        
        /* Hero Slider Styles */
        .hero-slider-section {
            position: relative;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #6366f1 100%);
        }
        
        .hero-slider-container {
            position: relative;
            overflow: hidden;
        }
        
        .hero-slide {
            will-change: transform, opacity;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        
        .hero-slide-active {
            z-index: 10;
        }
        
        .hero-slide-inactive {
            z-index: 0;
            pointer-events: none;
        }
        
        .hero-slide-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
        }
        
        /* Hero Content Animations */
        .hero-content {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s,
                        transform 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
        }
        
        .hero-content-visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .hero-content-hidden {
            opacity: 0;
            transform: translateY(30px);
        }
        
        /* Hero Products Grid Animations */
        .hero-products-grid {
            opacity: 0;
            transform: translateY(40px) scale(0.95);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s,
                        transform 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s;
        }
        
        .hero-products-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        .hero-products-hidden {
            opacity: 0;
            transform: translateY(40px) scale(0.95);
        }
        
        /* Hero Product Cards */
        .hero-product-card {
            will-change: transform;
            animation: heroCardFadeIn 0.6s ease-out backwards;
        }
        
        @keyframes heroCardFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Hero CTA Button */
        .hero-cta-button {
            will-change: transform;
            position: relative;
            overflow: hidden;
        }
        
        .hero-cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .hero-cta-button:hover::before {
            left: 100%;
        }
        
        /* Hero Navigation Buttons */
        .hero-nav-button {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, background-color;
        }
        
        .hero-nav-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .hero-nav-button:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .hero-nav-button:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }
        
        @media (max-width: 640px) {
            .hero-nav-button {
                width: 40px;
                height: 40px;
            }
        }
        
        /* Hero Pagination Dots */
        .hero-pagination-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.5);
            background: transparent;
            cursor: pointer;
            padding: 0;
            will-change: width, background-color;
        }
        
        .hero-pagination-active {
            width: 32px;
            height: 12px;
            border-radius: 6px;
            background: white;
            border-color: white;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.5);
        }
        
        .hero-pagination-inactive {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .hero-pagination-inactive:hover {
            background: rgba(255, 255, 255, 0.5);
            border-color: rgba(255, 255, 255, 0.7);
        }
        
        /* Performance Optimizations */
        .hero-slider-section * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Responsive Typography Adjustments */
        @media (max-width: 640px) {
            .hero-content h2 {
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .hero-content p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
        }
        
        /* Loading State */
        .hero-product-card img {
            transition: opacity 0.3s;
        }
        
        .hero-product-card img[loading="lazy"] {
            opacity: 0;
        }
        
        .hero-product-card img[loading="lazy"].loaded {
            opacity: 1;
        }
        
    </style>
    
    <!-- SweetAlert2 for beautiful notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/custom.css">

    <!-- Load main.js before Alpine.js to ensure functions are available -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Set SITE_URL for JavaScript -->
    <script>
        window.SITE_URL = '<?php echo SITE_URL; ?>';
        
        <?php if ($userStatusCheck === 'deactivated'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Account Deactivated',
                text: 'Your account has been deactivated. Please contact support for further assistance.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?php echo SITE_URL; ?>/user/logout';
                }
            });
        });
        <?php endif; ?>
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav id="main-nav" class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div id="main-nav-content" class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <?php
                    // Load logo configuration using centralized branding helper (SINGLE SOURCE OF TRUTH)
                    require_once APP_PATH . '/helpers/Branding.php';
                    $logoConfig = Branding::getLogo('frontend');
                    
                    // Render logo using centralized branding helper
                    $logoHtml = Branding::renderLogo(
                        $logoConfig,
                        SITE_URL,
                        ['class' => 'w-auto object-contain h-10 sm:h-12'],
                        ['class' => 'text-xl sm:text-2xl font-bold']
                    );
                    // Adjust max-width for navigation bar
                    if ($logoConfig['type'] === 'image' && $logoConfig['hasImage']) {
                        $logoHtml = str_replace(
                            'max-width: ' . $logoConfig['maxWidth'] . 'px;',
                            'max-width: ' . min($logoConfig['maxWidth'], 200) . 'px;',
                            $logoHtml
                        );
                    }
                    echo $logoHtml;
                    ?>
                </div>

                <!-- Suspended User Warning Banner -->
                <?php if (Session::isLoggedIn() && Session::get('user_status') === USER_STATUS_SUSPENDED): 
                    $suspensionCode = Session::get('suspension_code');
                    $reasonMap = [
                        'FRAUD_ORDER' => 'Suspicious order activity detected.',
                        'PAYMENT_ABUSE' => 'Payment verification required.',
                        'REVIEW_SPAM' => 'Review policy violation.',
                        'POLICY_VIOLATION' => 'Terms of Service violation.',
                        'MULTIPLE_ACCOUNTS' => 'Multiple account usage detected.',
                        'ABUSE_SUPPORT' => 'Support communication policy violation.',
                        'AUTO_RISK' => 'Security risk detected.',
                        'OTHER' => 'Account under review.'
                    ];
                    $displayReason = $reasonMap[$suspensionCode] ?? 'Account under review.';
                ?>
                <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-[60] w-full max-w-2xl px-4">
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 shadow-lg rounded-r" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                            <div>
                                <p class="font-bold">Account Suspended</p>
                                <p class="text-sm"><?php echo htmlspecialchars($displayReason); ?></p>
                                <p class="text-xs mt-1">Please contact support@example.com for assistance.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Search Bar - Hidden on mobile, shown on md+ -->
                <div class="hidden md:flex flex-1 max-w-lg mx-4 lg:mx-8">
                    <form action="<?php echo SITE_URL; ?>/product/search" method="GET" class="flex w-full">
                        <input type="text" name="q" placeholder="Search for products..." 
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                        <button type="submit" class="bg-pink-600 text-white px-4 lg:px-6 py-2 rounded-r-lg hover:bg-pink-700 text-sm">
                            Search
                        </button>
                    </form>
                </div>
                
                <!-- Right Menu -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <?php if (Session::isLoggedIn()): ?>
                        <!-- Wishlist -->
                        <a href="<?php echo SITE_URL; ?>/user/wishlist" class="text-gray-700 hover:text-pink-600 relative" title="Wishlist">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span id="wishlist-count" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" style="display: none;">0</span>
                        </a>
                        
                        <!-- Cart -->
                        <a href="<?php echo SITE_URL; ?>/cart" class="text-gray-700 hover:text-pink-600 relative" title="Cart">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span id="cart-count" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" style="display: none;">0</span>
                        </a>
                        
                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 text-gray-700 hover:text-pink-600">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="hidden sm:inline text-sm"><?php echo htmlspecialchars(Session::get('user_name')); ?></span>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <?php if (Session::isAdmin()): ?>
                                    <!-- Admin Menu -->
                                    <a href="<?php echo SITE_URL; ?>/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-semibold">Admin Dashboard</a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="<?php echo SITE_URL; ?>/admin/products" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Products</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/categories" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Categories</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/attributes" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Attributes</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/hero-banners" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Hero Banners</a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                <?php endif; ?>
                                <!-- Common Menu Items -->
                                <a href="<?php echo SITE_URL; ?>/user/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="<?php echo SITE_URL; ?>/order" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                <a href="<?php echo SITE_URL; ?>/user/wishlist" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
                                <a href="<?php echo SITE_URL; ?>/support" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Help / Support</a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="<?php echo SITE_URL; ?>/user/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register -->
                        <a href="<?php echo SITE_URL; ?>/user/login" class="text-gray-700 hover:text-pink-600 text-sm sm:text-base">Login</a>
                        <a href="<?php echo SITE_URL; ?>/user/register" class="bg-pink-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-pink-700 text-sm sm:text-base">Sign Up</a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 hover:text-pink-600 ml-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Search Bar -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-200 py-3">
                <form action="<?php echo SITE_URL; ?>/product/search" method="GET" class="flex px-4">
                    <input type="text" name="q" placeholder="Search for products..." 
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                    <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-r-lg hover:bg-pink-700 text-sm">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </nav>
    
    <!-- Categories Bar -->
    <?php
    // NOTE: Use a distinct variable name to avoid clashing with view data
    // (e.g. admin category pages also pass $categories to the view)
    $headerCategoryModel = new Category();
    // For the public navigation bar we intentionally show ONLY active categories
    $navCategories = $headerCategoryModel->getParentCategories();
    ?>
    <?php if (!empty($navCategories)): ?>
    <div class="bg-white sticky top-16 z-40 border-b border-gray-200 transition-transform duration-300 ease-in-out" 
         x-data="{ 
             categoriesOpen: false,
             lastScroll: 0,
             isVisible: true,
             init() {
                 this.lastScroll = window.pageYOffset;
                 window.addEventListener('scroll', () => {
                     const currentScroll = window.pageYOffset;
                     if (currentScroll > 100) { // Only hide after scrolling past 100px
                         if (currentScroll > this.lastScroll) {
                             // Scrolling down - hide
                             this.isVisible = false;
                         } else {
                             // Scrolling up - show
                             this.isVisible = true;
                         }
                     } else {
                         // Near top - always show
                         this.isVisible = true;
                     }
                     this.lastScroll = currentScroll;
                 });
             }
         }"
         :class="isVisible ? 'translate-y-0' : '-translate-y-full'">
        <div class="container mx-auto md:px-4">
            <!-- Desktop Categories Bar -->
            <div class="hidden md:flex items-center justify-start py-3 overflow-x-auto">
                <div class="flex items-center space-x-1 lg:space-x-2">
                    <a href="<?php echo SITE_URL; ?>/product" class="px-4 py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg font-medium text-sm whitespace-nowrap transition">
                        All Products
                    </a>
                    <?php foreach ($navCategories as $nacategory): ?>
                        <a href="<?php echo SITE_URL; ?>/product?category=<?php echo urlencode($nacategory['slug']); ?>" 
                           class="px-4 py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg font-medium text-sm whitespace-nowrap transition">
                            <?php echo htmlspecialchars($nacategory['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Mobile Categories Bar -->
            <div class="md:hidden">
                <button @click="categoriesOpen = !categoriesOpen" 
                        class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-gray-100 transition">
                    <span class="font-medium">Browse Categories</span>
                    <svg class="w-5 h-5 transition-transform" :class="categoriesOpen ? 'rotate-180' : ''" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="categoriesOpen" x-cloak class="border-t border-gray-200 bg-white">
                    <div class="px-4 py-2">
                        <a href="<?php echo SITE_URL; ?>/product" 
                           class="block px-4 py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg font-medium text-sm mb-1">
                            All Products
                        </a>
                        <?php foreach ($navCategories as $nacategory): ?>
                            <a href="<?php echo SITE_URL; ?>/product?category=<?php echo urlencode($nacategory['slug']); ?>" 
                               class="block px-4 py-2 text-gray-700 hover:text-pink-600 hover:bg-pink-50 rounded-lg font-medium text-sm mb-1">
                                <?php echo htmlspecialchars($nacategory['name']); ?>
                            </a>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Flash Messages (handled by SweetAlert in main.js) -->
    <?php if ($flashMessage = Session::getFlash('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative hidden" role="alert" data-flash-type="success">
            <span class="block sm:inline"><?php echo htmlspecialchars($flashMessage); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($flashMessage = Session::getFlash('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative hidden" role="alert" data-flash-type="error">
            <span class="block sm:inline"><?php echo htmlspecialchars($flashMessage); ?></span>
        </div>
    <?php endif; ?>

