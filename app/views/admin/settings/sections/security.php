<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Password Length</label>
        <input type="number" name="settings[security_password_min_length]" value="<?php echo htmlspecialchars($getValue('security_password_min_length', '8')); ?>" 
               min="6" max="32"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Minimum characters required for user passwords</p>
    </div>
    
    <div class="flex items-center">
        <input type="checkbox" name="settings[security_2fa_enabled]" value="1" id="2fa_enabled" 
               <?php echo $getValue('security_2fa_enabled') ? 'checked' : ''; ?>
               class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
        <label for="2fa_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Two-Factor Authentication</label>
        <p class="text-xs text-gray-500 ml-2">Require 2FA for admin accounts</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Login Attempt Limit</label>
        <input type="number" name="settings[security_login_attempt_limit]" value="<?php echo htmlspecialchars($getValue('security_login_attempt_limit', '5')); ?>" 
               min="3" max="10"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Maximum failed login attempts before account lockout</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (seconds)</label>
        <input type="number" name="settings[security_session_timeout]" value="<?php echo htmlspecialchars($getValue('security_session_timeout', '3600')); ?>" 
               min="300" max="86400" step="300"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Time in seconds before session expires (default: 3600 = 1 hour)</p>
    </div>
    
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <p class="text-sm text-yellow-800">
            <strong>Security Note:</strong> All security changes are logged for audit purposes.
        </p>
    </div>
</div>

