<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Kids Fashion E-commerce Platform'; ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        body { overflow-x: hidden; }
        img { max-width: 100%; height: auto; }
    </style>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Set SITE_URL for JavaScript -->
    <script>
        window.SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false, categoryMenuOpen: false }">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo SITE_URL; ?>" class="text-xl sm:text-2xl font-bold text-pink-600">
                        <?php echo SITE_NAME; ?>
                    </a>
                </div>
                
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
                        <a href="<?php echo SITE_URL; ?>/user/wishlist" class="text-gray-700 hover:text-pink-600" title="Wishlist">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </a>
                        
                        <!-- Cart -->
                        <a href="<?php echo SITE_URL; ?>/cart" class="text-gray-700 hover:text-pink-600 relative" title="Cart">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span id="cart-count" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
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
                                    <div class="border-t border-gray-200 my-1"></div>
                                <?php endif; ?>
                                <!-- Common Menu Items -->
                                <a href="<?php echo SITE_URL; ?>/user/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="<?php echo SITE_URL; ?>/order" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                <a href="<?php echo SITE_URL; ?>/user/wishlist" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
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
        
        <!-- Category Menu -->
        <div class="border-t border-gray-200 bg-gray-50">
            <div class="container mx-auto px-4">
                <!-- Desktop Category Menu -->
                <div class="hidden md:flex space-x-6 py-3 overflow-x-auto">
                    <?php
                    $categoryModel = new Category();
                    $categories = $categoryModel->getAllActive();
                    foreach ($categories as $category):
                    ?>
                        <a href="<?php echo SITE_URL; ?>/product?category=<?php echo $category['category_id']; ?>" 
                           class="text-gray-700 hover:text-pink-600 font-medium whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Mobile Category Menu -->
                <div class="md:hidden">
                    <button @click="categoryMenuOpen = !categoryMenuOpen" class="w-full py-3 flex items-center justify-between text-gray-700 font-medium">
                        <span>Categories</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="categoryMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="categoryMenuOpen" x-cloak class="pb-3 space-y-2">
                        <?php
                        foreach ($categories as $category):
                        ?>
                            <a href="<?php echo SITE_URL; ?>/product?category=<?php echo $category['category_id']; ?>" 
                               class="block px-4 py-2 text-gray-700 hover:text-pink-600 hover:bg-gray-100 rounded text-sm">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if ($flashMessage = Session::getFlash('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($flashMessage); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($flashMessage = Session::getFlash('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($flashMessage); ?></span>
        </div>
    <?php endif; ?>

