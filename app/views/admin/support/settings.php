<div class="container mx-auto px-6 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
           <div class="flex items-center gap-2 mb-1">
                <a href="<?php echo SITE_URL; ?>/admin/support" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Support Settings</h1>
            </div>
            <p class="text-gray-600 ml-7">Configure helpdesk display options and contact information</p>
        </div>
    </div>

    <div class="max-w-3xl">
        <form action="" method="POST" class="bg-white rounded-lg shadow p-6">
            <!-- General Toggle -->
            <div class="flex items-center justify-between mb-8 pb-8 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Enable Support System</h3>
                    <p class="text-sm text-gray-500">Allow customers to submit tickets and view help page.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="support_enabled" class="sr-only peer" <?php echo ($settings['support_enabled']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                </label>
            </div>

            <!-- Contact Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Helpline Phone Number</label>
                        <input type="text" name="support_phone" value="<?php echo htmlspecialchars($settings['support_phone']['value'] ?? ''); ?>" placeholder="+1 (555) 123-4567" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <p class="text-xs text-gray-500 mt-1">Displayed on the support page.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
                        <input type="email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email']['value'] ?? ''); ?>" placeholder="support@example.com" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <p class="text-xs text-gray-500 mt-1">Displayed on the support page.</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Working Hours</label>
                    <input type="text" name="support_hours" value="<?php echo htmlspecialchars($settings['support_hours']['value'] ?? ''); ?>" placeholder="Mon - Fri: 9:00 AM - 6:00 PM" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Inform customers about your availability.</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors font-medium shadow-md">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
