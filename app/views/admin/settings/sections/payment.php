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
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Cash on Delivery</h3>
        <div class="flex items-center">
            <input type="checkbox" name="settings[payment_cod_enabled]" value="1" id="cod_enabled" 
                   <?php echo $getValue('payment_cod_enabled') ? 'checked' : ''; ?>
                   class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
            <label for="cod_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Cash on Delivery</label>
        </div>
    </div>
    
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Razorpay</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" name="settings[payment_razorpay_enabled]" value="1" id="razorpay_enabled" 
                       <?php echo $getValue('payment_razorpay_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="razorpay_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Razorpay</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Razorpay Key ID</label>
                <input type="text" name="settings[payment_razorpay_key]" 
                       value="<?php echo $isEncrypted('payment_razorpay_key') ? '••••••••••••' : htmlspecialchars($getValue('payment_razorpay_key')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_razorpay_key') ? 'Enter new key to update' : 'Enter Razorpay Key ID'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Razorpay Secret Key</label>
                <input type="password" name="settings[payment_razorpay_secret]" 
                       value="<?php echo $isEncrypted('payment_razorpay_secret') ? '••••••••••••' : htmlspecialchars($getValue('payment_razorpay_secret')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_razorpay_secret') ? 'Enter new secret to update' : 'Enter Razorpay Secret'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode</label>
                <select name="settings[payment_razorpay_mode]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="sandbox" <?php echo $getValue('payment_razorpay_mode') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                    <option value="live" <?php echo $getValue('payment_razorpay_mode') === 'live' ? 'selected' : ''; ?>>Live</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Stripe</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" name="settings[payment_stripe_enabled]" value="1" id="stripe_enabled" 
                       <?php echo $getValue('payment_stripe_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="stripe_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Stripe</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Publishable Key</label>
                <input type="text" name="settings[payment_stripe_key]" 
                       value="<?php echo $isEncrypted('payment_stripe_key') ? '••••••••••••' : htmlspecialchars($getValue('payment_stripe_key')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_stripe_key') ? 'Enter new key to update' : 'Enter Stripe Publishable Key'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Secret Key</label>
                <input type="password" name="settings[payment_stripe_secret]" 
                       value="<?php echo $isEncrypted('payment_stripe_secret') ? '••••••••••••' : htmlspecialchars($getValue('payment_stripe_secret')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_stripe_secret') ? 'Enter new secret to update' : 'Enter Stripe Secret Key'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode</label>
                <select name="settings[payment_stripe_mode]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="sandbox" <?php echo $getValue('payment_stripe_mode') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                    <option value="live" <?php echo $getValue('payment_stripe_mode') === 'live' ? 'selected' : ''; ?>>Live</option>
                </select>
            </div>
        </div>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">PayPal</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" name="settings[payment_paypal_enabled]" value="1" id="paypal_enabled" 
                       <?php echo $getValue('payment_paypal_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="paypal_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable PayPal</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Client ID</label>
                <input type="text" name="settings[payment_paypal_client_id]" 
                       value="<?php echo $isEncrypted('payment_paypal_client_id') ? '••••••••••••' : htmlspecialchars($getValue('payment_paypal_client_id')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_paypal_client_id') ? 'Enter new ID to update' : 'Enter PayPal Client ID'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Secret</label>
                <input type="password" name="settings[payment_paypal_secret]" 
                       value="<?php echo $isEncrypted('payment_paypal_secret') ? '••••••••••••' : htmlspecialchars($getValue('payment_paypal_secret')); ?>" 
                       placeholder="<?php echo $isEncrypted('payment_paypal_secret') ? 'Enter new secret to update' : 'Enter PayPal Secret'; ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode</label>
                <select name="settings[payment_paypal_mode]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="sandbox" <?php echo $getValue('payment_paypal_mode') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                    <option value="live" <?php echo $getValue('payment_paypal_mode') === 'live' ? 'selected' : ''; ?>>Live</option>
                </select>
            </div>
        </div>
    </div>
</div>

