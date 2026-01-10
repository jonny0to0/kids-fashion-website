<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <div class="flex items-center">
        <input type="checkbox" name="settings[tax_enabled]" value="1" id="tax_enabled" 
               <?php echo $getValue('tax_enabled') ? 'checked' : ''; ?>
               class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
        <label for="tax_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Tax</label>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Type</label>
        <select name="settings[tax_type]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <option value="GST" <?php echo $getValue('tax_type') === 'GST' ? 'selected' : ''; ?>>GST (Goods and Services Tax)</option>
            <option value="VAT" <?php echo $getValue('tax_type') === 'VAT' ? 'selected' : ''; ?>>VAT (Value Added Tax)</option>
            <option value="Sales Tax" <?php echo $getValue('tax_type') === 'Sales Tax' ? 'selected' : ''; ?>>Sales Tax</option>
            <option value="Custom" <?php echo $getValue('tax_type') === 'Custom' ? 'selected' : ''; ?>>Custom</option>
        </select>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Default Tax Rate (%)</label>
        <div class="relative">
            <input type="number" name="settings[tax_rate]" value="<?php echo htmlspecialchars($getValue('tax_rate', '10.00')); ?>" 
                   step="0.01" min="0" max="100"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">%</span>
        </div>
    </div>
    
    <div class="flex items-center">
        <input type="checkbox" name="settings[tax_inclusive]" value="1" id="tax_inclusive" 
               <?php echo $getValue('tax_inclusive') ? 'checked' : ''; ?>
               class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
        <label for="tax_inclusive" class="ml-2 text-sm font-medium text-gray-700">Tax Inclusive Pricing</label>
        <p class="text-xs text-gray-500 ml-2">Tax is included in product prices</p>
    </div>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> State-wise tax rules can be configured in future updates.
        </p>
    </div>
</div>

