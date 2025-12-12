<div class="container mx-auto px-4 py-6 sm:py-8 max-w-7xl">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8">My Profile</h2>
        
        <?php if (Session::isAdmin()): ?>
            <!-- Admin Dashboard Link -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-blue-900 text-sm sm:text-base">Admin Access</h3>
                        <p class="text-xs sm:text-sm text-blue-700">You have administrative privileges</p>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/admin" class="bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm sm:text-base whitespace-nowrap">
                        Go to Admin Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Customer Dashboard Links -->
        <?php if (!Session::isAdmin()): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a href="<?php echo SITE_URL; ?>/order" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">My Orders</h3>
                            <p class="text-sm text-gray-600">View order history</p>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/user/wishlist" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex items-center">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Wishlist</h3>
                            <p class="text-sm text-gray-600">Saved items</p>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/user/profile" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-2 border-pink-500">
                    <div class="flex items-center">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Profile</h3>
                            <p class="text-sm text-gray-600">Edit your profile</p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 lg:p-8">
            <h3 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">Profile Information</h3>
            <form method="POST" action="<?php echo SITE_URL; ?>/user/profile">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">First Name</label>
                        <input type="text" name="first_name" required
                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Last Name</label>
                        <input type="text" name="last_name" required
                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                               disabled
                               class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                        <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone</label>
                        <input type="tel" name="phone"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">User Type</label>
                        <input type="text" value="<?php echo ucfirst(htmlspecialchars($user['user_type'] ?? 'customer')); ?>"
                               disabled
                               class="w-full border border-gray-300 rounded px-4 py-2 bg-gray-100">
                    </div>
                </div>
                
                <div class="mt-4 sm:mt-6">
                    <button type="submit" class="w-full sm:w-auto bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-bold text-sm sm:text-base">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

