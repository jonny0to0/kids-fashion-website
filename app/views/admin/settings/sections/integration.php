<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
$isEncrypted = function($key) use ($settings) {
    return $settings[$key]['is_encrypted'] ?? false;
};
?>

<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Google Maps API</h3>
            <button type="button" class="text-sm text-pink-600 hover:text-pink-700 test-connection-btn" data-integration="google_maps">
                Test Connection
            </button>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Google Maps API Key</label>
            <input type="text" name="settings[integration_google_maps_key]" 
                   value="<?php echo $isEncrypted('integration_google_maps_key') ? '••••••••••••' : htmlspecialchars($getValue('integration_google_maps_key')); ?>" 
                   placeholder="<?php echo $isEncrypted('integration_google_maps_key') ? 'Enter new key to update' : 'Enter Google Maps API Key'; ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        </div>
    </div>
    
    <div class="border-b border-gray-200 pb-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">SMTP Email Configuration</h3>
            <button type="button" class="text-sm text-pink-600 hover:text-pink-700 test-connection-btn" data-integration="smtp">
                Test Connection
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                <input type="text" name="settings[integration_smtp_host]" value="<?php echo htmlspecialchars($getValue('integration_smtp_host')); ?>" 
                       placeholder="smtp.gmail.com"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                <input type="number" name="settings[integration_smtp_port]" value="<?php echo htmlspecialchars($getValue('integration_smtp_port', '587')); ?>" 
                       placeholder="587"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                <input type="text" name="settings[integration_smtp_username]" value="<?php echo htmlspecialchars($getValue('integration_smtp_username')); ?>" 
                       placeholder="your-email@gmail.com"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                <input type="password" name="settings[integration_smtp_password]" 
                       value="<?php echo $isEncrypted('integration_smtp_password') ? '••••••••••••' : htmlspecialchars($getValue('integration_smtp_password')); ?>" 
                       placeholder="<?php echo $isEncrypted('integration_smtp_password') ? 'Enter new password to update' : 'Enter SMTP Password'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Encryption</label>
                <select name="settings[integration_smtp_encryption]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="tls" <?php echo $getValue('integration_smtp_encryption') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo $getValue('integration_smtp_encryption') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    <option value="none" <?php echo $getValue('integration_smtp_encryption') === 'none' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>
        </div>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">SMS Gateway</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMS Gateway Provider</label>
                <input type="text" name="settings[integration_sms_gateway]" value="<?php echo htmlspecialchars($getValue('integration_sms_gateway')); ?>" 
                       placeholder="Twilio, AWS SNS, etc."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMS API Key</label>
                <input type="password" name="settings[integration_sms_api_key]" 
                       value="<?php echo $isEncrypted('integration_sms_api_key') ? '••••••••••••' : htmlspecialchars($getValue('integration_sms_api_key')); ?>" 
                       placeholder="<?php echo $isEncrypted('integration_sms_api_key') ? 'Enter new key to update' : 'Enter SMS API Key'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
        </div>
    </div>
</div>

