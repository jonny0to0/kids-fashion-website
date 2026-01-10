<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Notification Channels</h3>
        <div class="space-y-3">
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_email_enabled]" value="1" id="email_enabled" 
                       <?php echo $getValue('notification_email_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="email_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable Email Notifications</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_sms_enabled]" value="1" id="sms_enabled" 
                       <?php echo $getValue('notification_sms_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="sms_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable SMS Notifications</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_whatsapp_enabled]" value="1" id="whatsapp_enabled" 
                       <?php echo $getValue('notification_whatsapp_enabled') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="whatsapp_enabled" class="ml-2 text-sm font-medium text-gray-700">Enable WhatsApp Notifications</label>
            </div>
        </div>
    </div>
    
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Notification Events</h3>
        <div class="space-y-3">
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_order_placed]" value="1" id="notify_order_placed" 
                       <?php echo $getValue('notification_order_placed') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="notify_order_placed" class="ml-2 text-sm font-medium text-gray-700">Notify on Order Placed</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_order_shipped]" value="1" id="notify_order_shipped" 
                       <?php echo $getValue('notification_order_shipped') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="notify_order_shipped" class="ml-2 text-sm font-medium text-gray-700">Notify on Order Shipped</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_order_delivered]" value="1" id="notify_order_delivered" 
                       <?php echo $getValue('notification_order_delivered') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="notify_order_delivered" class="ml-2 text-sm font-medium text-gray-700">Notify on Order Delivered</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_payment_failed]" value="1" id="notify_payment_failed" 
                       <?php echo $getValue('notification_payment_failed') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="notify_payment_failed" class="ml-2 text-sm font-medium text-gray-700">Notify on Payment Failed</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="settings[notification_refund_processed]" value="1" id="notify_refund_processed" 
                       <?php echo $getValue('notification_refund_processed') ? 'checked' : ''; ?>
                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                <label for="notify_refund_processed" class="ml-2 text-sm font-medium text-gray-700">Notify on Refund Processed</label>
            </div>
        </div>
    </div>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> Message templates can be customized in future updates.
        </p>
    </div>
</div>

