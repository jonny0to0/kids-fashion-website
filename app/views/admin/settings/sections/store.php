<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <!-- Maintenance Mode Notice -->
    <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-pink-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-pink-800 mb-1">Maintenance Mode Management</h3>
                <p class="text-xs text-pink-700 mb-3">To enable or disable maintenance mode, configure maintenance messages, or set maintenance schedules, please use the dedicated Maintenance Mode settings page.</p>
                <a href="<?php echo SITE_URL; ?>/admin/settings?section=maintenance" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Go to Maintenance Mode Settings
                </a>
            </div>
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Default Product Display Mode</label>
        <select name="settings[product_display_mode]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <option value="grid" <?php echo $getValue('product_display_mode') === 'grid' ? 'selected' : ''; ?>>Grid</option>
            <option value="list" <?php echo $getValue('product_display_mode') === 'list' ? 'selected' : ''; ?>>List</option>
        </select>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Products Per Page</label>
        <input type="number" name="settings[products_per_page]" value="<?php echo htmlspecialchars($getValue('products_per_page', '24')); ?>" 
               min="12" max="100" step="12"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
    </div>
    
    <div class="flex items-center">
        <input type="checkbox" name="settings[guest_checkout]" value="1" id="guest_checkout" 
               <?php echo $getValue('guest_checkout') ? 'checked' : ''; ?>
               class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
        <label for="guest_checkout" class="ml-2 text-sm font-medium text-gray-700">Enable Guest Checkout</label>
        <p class="text-xs text-gray-500 ml-2">Allow customers to checkout without creating an account</p>
    </div>
</div>

