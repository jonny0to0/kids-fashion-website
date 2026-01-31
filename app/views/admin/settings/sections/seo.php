<?php
$settings = $currentSettings;
$getValue = function($key, $default = '') use ($settings) {
    return $settings[$key]['value'] ?? $default;
};
?>

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
        <input type="text" name="settings[seo_meta_title]" value="<?php echo htmlspecialchars($getValue('seo_meta_title')); ?>" 
               maxlength="60"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Recommended: 50-60 characters</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
        <textarea name="settings[seo_meta_description]" rows="3" maxlength="160"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"><?php echo htmlspecialchars($getValue('seo_meta_description')); ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Recommended: 150-160 characters</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Homepage Keywords</label>
        <input type="text" name="settings[seo_homepage_keywords]" value="<?php echo htmlspecialchars($getValue('seo_homepage_keywords')); ?>" 
               placeholder="keyword1, keyword2, keyword3"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Comma-separated keywords</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
        <input type="text" name="settings[seo_google_analytics_id]" value="<?php echo htmlspecialchars($getValue('seo_google_analytics_id')); ?>" 
               placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Your Google Analytics tracking ID</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Pixel ID</label>
        <input type="text" name="settings[seo_facebook_pixel_id]" value="<?php echo htmlspecialchars($getValue('seo_facebook_pixel_id')); ?>" 
               placeholder="123456789012345"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Your Facebook Pixel ID</p>
    </div>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> These settings will be reflected in the frontend head tags automatically.
        </p>
    </div>
</div>

