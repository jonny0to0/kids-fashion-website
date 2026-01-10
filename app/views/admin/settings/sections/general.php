<?php
$settings = $currentSettings;
// CRITICAL FIX: Ensure logo_type is always available, even if not in settings array
// This prevents defaulting to 'text' when the setting exists in database but wasn't loaded
$settingsModel = new Settings();
$getValue = function($key, $default = '') use ($settings, $settingsModel) {
    // First try to get from settings array
    if (isset($settings[$key]['value'])) {
        return $settings[$key]['value'];
    }
    // CRITICAL: For logo_type, always check database directly to ensure we get the saved value
    if ($key === 'logo_type') {
        $dbValue = $settingsModel->get('logo_type', $default);
        // Normalize to ensure it's either 'image' or 'text'
        return in_array($dbValue, ['image', 'text']) ? $dbValue : $default;
    }
    // For other settings, return default
    return $default;
};
?>

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Store Name</label>
        <input type="text" name="settings[store_name]" value="<?php echo htmlspecialchars($getValue('store_name')); ?>" 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
    </div>
    
    <!-- Store Logo Configuration Section -->
    <div class="border-t border-gray-200 pt-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Store Logo Configuration</h3>
        <p class="text-sm text-gray-600 mb-6">Configure the logo displayed in dashboard sidebar, header, and login screen.</p>
        
        <!-- Logo Type Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Logo Type</label>
            <div class="flex gap-4">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="settings[logo_type]" value="image" 
                           <?php echo $getValue('logo_type', 'text') === 'image' ? 'checked' : ''; ?>
                           class="mr-2 text-pink-500 focus:ring-pink-500" 
                           onchange="toggleLogoType('image')">
                    <span class="text-sm text-gray-700">Image-Based Logo</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="settings[logo_type]" value="text" 
                           <?php echo $getValue('logo_type', 'text') === 'text' ? 'checked' : ''; ?>
                           class="mr-2 text-pink-500 focus:ring-pink-500" 
                           onchange="toggleLogoType('text')">
                    <span class="text-sm text-gray-700">Text-Based Logo</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-2">Select whether to use an uploaded image or styled text for your store logo.</p>
        </div>
        
        <?php 
        $logoType = $getValue('logo_type', 'text');
        $showImageLogo = $logoType === 'image';
        $showTextLogo = $logoType === 'text';
        ?>
        
        <!-- Image Logo Configuration -->
        <div id="image-logo-config" class="<?php echo $showImageLogo ? '' : 'hidden'; ?> mb-6 space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h4 class="font-medium text-gray-800">Image Logo Settings</h4>
            
            <!-- Logo Preview Container (hidden by default, shown after upload) -->
            <?php 
            $logoImage = $getValue('logo_image');
            if (empty($logoImage)) {
                // Check legacy dashboard_logo for backward compatibility
                $logoImage = $getValue('dashboard_logo', '');
            }
            $hasExistingLogo = !empty($logoImage);
            ?>
            <div class="mb-4 <?php echo $hasExistingLogo ? '' : 'hidden'; ?>" id="logo-preview-container">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Preview</label>
                <?php if ($hasExistingLogo): ?>
                    <div class="inline-block relative p-3 bg-white border border-gray-300 rounded-lg group">
                        <?php 
                        $logoPath = '/' . ltrim($logoImage, '/');
                        ?>
                        <img src="<?php echo SITE_URL . $logoPath; ?>" 
                             alt="Logo Preview" 
                             id="logo-preview-img"
                             class="h-16 w-auto max-w-[200px] object-contain"
                             style="max-height: <?php echo $getValue('logo_image_max_height', '60'); ?>px; max-width: <?php echo $getValue('logo_image_max_width', '200'); ?>px;">
                        <!-- Close/Remove Button -->
                        <button type="button" 
                                onclick="removeLogoPreview()"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600 transition-colors"
                                title="Remove logo and upload new one">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Preview of logo as it will appear in dashboard. Click the X icon to remove and upload a new one.</p>
                <?php endif; ?>
            </div>
            
            <!-- Logo Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload Logo Image <span class="text-red-500">*</span>
                </label>
                <input type="file" 
                       name="settings[logo_image]" 
                       id="logo-image-input"
                       accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp"
                       onchange="previewLogoImage(this)"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                <p class="text-xs text-gray-500 mt-1">
                    Supported formats: PNG, JPG, SVG, WEBP. 
                    <strong>Recommended dimensions:</strong> max height 40-60px, max width 160-200px. 
                    Images will be automatically resized to fit if larger. SVG files are preferred for scalability.
                </p>
                <p class="text-xs text-red-500 mt-1" id="logo-image-error"></p>
            </div>
            
            <!-- Dimension Controls -->
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Height (px)
                    </label>
                    <input type="number" 
                           name="settings[logo_image_max_height]" 
                           value="<?php echo htmlspecialchars($getValue('logo_image_max_height', '60')); ?>"
                           min="20" max="200" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                           onchange="updateLogoPreview()">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 40-60px. Logo will be resized to this height if larger.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Display Width (px)
                    </label>
                    <input type="number" 
                           name="settings[logo_image_max_width]" 
                           value="<?php echo htmlspecialchars($getValue('logo_image_max_width', '200')); ?>"
                           min="100" max="400" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                           onchange="updateLogoPreview()">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 160-200px. Logo will be resized to this width if larger.</p>
                </div>
            </div>
        </div>
        
        <!-- Text Logo Configuration -->
        <div id="text-logo-config" class="<?php echo $showTextLogo ? '' : 'hidden'; ?> mb-6 space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h4 class="font-medium text-gray-800">Text Logo Settings</h4>
            
            <!-- Text Preview -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Preview</label>
                <div class="p-4 bg-slate-700 rounded-lg">
                    <div id="text-logo-preview" 
                         class="text-white"
                         style="
                             font-size: <?php echo $getValue('logo_text_font_size_sidebar', '20'); ?>px;
                             font-weight: <?php echo $getValue('logo_text_font_weight', '600'); ?>;
                             color: <?php echo htmlspecialchars($getValue('logo_text_color', '#ffffff')); ?>;
                             max-width: <?php echo $getValue('logo_text_max_width', '200'); ?>px;
                         ">
                        <?php 
                        $logoText = $getValue('logo_text');
                        if (empty($logoText)) {
                            $logoText = $getValue('store_name', SITE_NAME);
                        }
                        echo htmlspecialchars($logoText);
                        ?>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Preview of text logo as it will appear in dashboard</p>
            </div>
            
            <!-- Store Name/Text -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Store Name / Logo Text
                </label>
                <input type="text" 
                       name="settings[logo_text]" 
                       id="logo-text-input"
                       value="<?php echo htmlspecialchars($getValue('logo_text', $getValue('store_name', SITE_NAME))); ?>"
                       placeholder="<?php echo htmlspecialchars($getValue('store_name', SITE_NAME)); ?>"
                       onchange="updateTextLogoPreview()"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                <p class="text-xs text-gray-500 mt-1">Leave empty to use store name</p>
            </div>
            
            <!-- Font Settings -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Font Size - Sidebar (px)
                    </label>
                    <input type="number" 
                           name="settings[logo_text_font_size_sidebar]" 
                           value="<?php echo htmlspecialchars($getValue('logo_text_font_size_sidebar', '20')); ?>"
                           min="12" max="32" 
                           onchange="updateTextLogoPreview()"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 18-22px</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Font Size - Header (px)
                    </label>
                    <input type="number" 
                           name="settings[logo_text_font_size_header]" 
                           value="<?php echo htmlspecialchars($getValue('logo_text_font_size_header', '18')); ?>"
                           min="12" max="28" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 16-18px</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Font Weight
                    </label>
                    <select name="settings[logo_text_font_weight]" 
                            onchange="updateTextLogoPreview()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="400" <?php echo $getValue('logo_text_font_weight', '600') == '400' ? 'selected' : ''; ?>>Normal (400)</option>
                        <option value="500" <?php echo $getValue('logo_text_font_weight', '600') == '500' ? 'selected' : ''; ?>>Medium (500)</option>
                        <option value="600" <?php echo $getValue('logo_text_font_weight', '600') == '600' ? 'selected' : ''; ?>>Semi-Bold (600)</option>
                        <option value="700" <?php echo $getValue('logo_text_font_weight', '600') == '700' ? 'selected' : ''; ?>>Bold (700)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Recommended: 600-700</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Text Color
                    </label>
                    <div class="flex gap-2">
                        <input type="color" 
                               name="settings[logo_text_color]" 
                               value="<?php echo htmlspecialchars($getValue('logo_text_color', '#ffffff')); ?>"
                               onchange="updateTextLogoPreview()"
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               value="<?php echo htmlspecialchars($getValue('logo_text_color', '#ffffff')); ?>"
                               onchange="updateTextLogoColor(this.value)"
                               placeholder="#ffffff"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Hex color code (e.g., #ffffff for white)</p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Maximum Width (px)
                </label>
                <input type="number" 
                       name="settings[logo_text_max_width]" 
                       value="<?php echo htmlspecialchars($getValue('logo_text_max_width', '200')); ?>"
                       min="100" max="400" 
                       onchange="updateTextLogoPreview()"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                <p class="text-xs text-gray-500 mt-1">Maximum width for text wrapping</p>
            </div>
        </div>
    </div>
    
    <script>
    // Toggle between image and text logo configurations
    function toggleLogoType(type) {
        const imageConfig = document.getElementById('image-logo-config');
        const textConfig = document.getElementById('text-logo-config');
        
        if (type === 'image') {
            imageConfig.classList.remove('hidden');
            textConfig.classList.add('hidden');
        } else {
            imageConfig.classList.add('hidden');
            textConfig.classList.remove('hidden');
        }
    }
    
    // Preview uploaded logo image
    function previewLogoImage(input) {
        const errorDiv = document.getElementById('logo-image-error');
        errorDiv.textContent = '';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp'];
            
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                errorDiv.textContent = 'Invalid file type. Please upload PNG, JPG, SVG, or WEBP.';
                input.value = '';
                return;
            }
            
            // Validate file size
            if (file.size > maxSize) {
                errorDiv.textContent = 'File size exceeds 2MB. Please upload a smaller image.';
                input.value = '';
                return;
            }
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('logo-preview-container');
                if (!previewContainer) return;
                
                // Show the preview container (remove hidden class)
                previewContainer.classList.remove('hidden');
                
                // Get dimension constraints
                const maxHeight = document.querySelector('input[name="settings[logo_image_max_height]"]')?.value || 60;
                const maxWidth = document.querySelector('input[name="settings[logo_image_max_width]"]')?.value || 200;
                
                // Update or create preview HTML
                previewContainer.innerHTML = `
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo Preview</label>
                    <div class="inline-block relative p-3 bg-white border border-gray-300 rounded-lg group">
                        <img src="${e.target.result}" 
                             alt="Logo Preview" 
                             id="logo-preview-img"
                             class="h-16 w-auto max-w-[200px] object-contain"
                             style="max-height: ${maxHeight}px; max-width: ${maxWidth}px;">
                        <!-- Close/Remove Button -->
                        <button type="button" 
                                onclick="removeLogoPreview()"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
                                title="Remove logo and upload new one">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Preview of logo as it will appear in dashboard. Click the X icon to remove and upload a new one.</p>
                `;
                
                updateLogoPreview();
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Remove logo preview and allow re-upload
    function removeLogoPreview() {
        if (confirm('Are you sure you want to remove this logo? You can upload a new one after removing.')) {
            // Clear the file input
            const fileInput = document.getElementById('logo-image-input');
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Hide the preview container
            const previewContainer = document.getElementById('logo-preview-container');
            if (previewContainer) {
                previewContainer.classList.add('hidden');
                previewContainer.innerHTML = ''; // Clear the content
            }
            
            // Clear any error messages
            const errorDiv = document.getElementById('logo-image-error');
            if (errorDiv) {
                errorDiv.textContent = '';
            }
            
            // Add a hidden input to signal removal on form submit (if saving existing logo removal)
            const form = document.querySelector('form[data-group="general"]');
            if (form) {
                // Remove existing removal marker if any
                const existingMarker = form.querySelector('input[name="logo_image_remove"]');
                if (existingMarker) {
                    existingMarker.remove();
                }
                
                // Add removal marker
                const removeMarker = document.createElement('input');
                removeMarker.type = 'hidden';
                removeMarker.name = 'logo_image_remove';
                removeMarker.value = '1';
                form.appendChild(removeMarker);
            }
        }
    }
    
    // Update logo preview dimensions
    function updateLogoPreview() {
        const previewImg = document.getElementById('logo-preview-img');
        if (previewImg) {
            const maxHeight = document.querySelector('input[name="settings[logo_image_max_height]"]').value || 60;
            const maxWidth = document.querySelector('input[name="settings[logo_image_max_width]"]').value || 200;
            previewImg.style.maxHeight = maxHeight + 'px';
            previewImg.style.maxWidth = maxWidth + 'px';
        }
    }
    
    // Update text logo preview
    function updateTextLogoPreview() {
        const preview = document.getElementById('text-logo-preview');
        if (!preview) return;
        
        const text = document.getElementById('logo-text-input').value || 
                     document.querySelector('input[name="settings[store_name]"]').value || 
                     'Store Name';
        const fontSize = document.querySelector('input[name="settings[logo_text_font_size_sidebar]"]').value || 20;
        const fontWeight = document.querySelector('select[name="settings[logo_text_font_weight]"]').value || 600;
        const color = document.querySelector('input[name="settings[logo_text_color]"]').value || '#ffffff';
        const maxWidth = document.querySelector('input[name="settings[logo_text_max_width]"]').value || 200;
        
        preview.textContent = text;
        preview.style.fontSize = fontSize + 'px';
        preview.style.fontWeight = fontWeight;
        preview.style.color = color;
        preview.style.maxWidth = maxWidth + 'px';
    }
    
    // Update text logo color from hex input
    function updateTextLogoColor(hexValue) {
        // Validate hex color
        if (/^#[0-9A-F]{6}$/i.test(hexValue)) {
            document.querySelector('input[name="settings[logo_text_color]"][type="color"]').value = hexValue;
            updateTextLogoPreview();
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial logo type visibility
        const logoType = document.querySelector('input[name="settings[logo_type]"]:checked').value;
        toggleLogoType(logoType);
        
        // Update text logo preview initially
        updateTextLogoPreview();
    });
    </script>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
        <?php $favicon = $getValue('store_favicon'); ?>
        <?php if ($favicon): ?>
            <div class="mb-2">
                <?php 
                // Ensure path starts with / and remove any double slashes
                $faviconPath = '/' . ltrim($favicon, '/');
                ?>
                <img src="<?php echo SITE_URL . $faviconPath; ?>" alt="Favicon" class="h-16 w-16">
            </div>
        <?php endif; ?>
        <input type="file" name="settings[store_favicon]" accept="image/*" 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        <p class="text-xs text-gray-500 mt-1">Recommended: 32x32px, ICO or PNG</p>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
        <input type="email" name="settings[support_email]" value="<?php echo htmlspecialchars($getValue('support_email')); ?>" 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
        <input type="text" name="settings[contact_phone]" value="<?php echo htmlspecialchars($getValue('contact_phone')); ?>" 
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Default Language</label>
        <select name="settings[default_language]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <option value="en" <?php echo $getValue('default_language') === 'en' ? 'selected' : ''; ?>>English</option>
            <option value="es" <?php echo $getValue('default_language') === 'es' ? 'selected' : ''; ?>>Spanish</option>
            <option value="fr" <?php echo $getValue('default_language') === 'fr' ? 'selected' : ''; ?>>French</option>
        </select>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
        <select name="settings[default_currency]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <option value="USD" <?php echo $getValue('default_currency') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
            <option value="EUR" <?php echo $getValue('default_currency') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
            <option value="GBP" <?php echo $getValue('default_currency') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
            <option value="INR" <?php echo $getValue('default_currency') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
        </select>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
        <select name="settings[timezone]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            <option value="UTC" <?php echo $getValue('timezone') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
            <option value="America/New_York" <?php echo $getValue('timezone') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
            <option value="America/Chicago" <?php echo $getValue('timezone') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time (CT)</option>
            <option value="America/Denver" <?php echo $getValue('timezone') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time (MT)</option>
            <option value="America/Los_Angeles" <?php echo $getValue('timezone') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time (PT)</option>
            <option value="Asia/Kolkata" <?php echo $getValue('timezone') === 'Asia/Kolkata' ? 'selected' : ''; ?>>India Standard Time (IST)</option>
        </select>
    </div>
</div>

