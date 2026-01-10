<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Flat Rate Shipping Cost</label>
        <div class="relative">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
            <input type="number" name="settings[shipping_flat_rate]" value="<?php echo htmlspecialchars($getValue('shipping_flat_rate', '5.00')); ?>" 
                   step="0.01" min="0"
                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        </div>
        <p class="text-xs text-gray-500 mt-1">Default shipping cost for all orders</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Free Shipping Threshold</label>
        <div class="relative">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
            <input type="number" name="settings[shipping_free_threshold]" value="<?php echo htmlspecialchars($getValue('shipping_free_threshold', '50.00')); ?>" 
                   step="0.01" min="0"
                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        </div>
        <p class="text-xs text-gray-500 mt-1">Orders above this amount qualify for free shipping</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Delivery Time</label>
        <input type="text" name="settings[shipping_estimated_days]" value="<?php echo htmlspecialchars($getValue('shipping_estimated_days', '5-7')); ?>" 
               placeholder="e.g., 5-7 days"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Displayed to customers during checkout</p>
    </div>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> Advanced shipping zones and weight-based pricing can be configured in future updates.
        </p>
    </div>
</div>

