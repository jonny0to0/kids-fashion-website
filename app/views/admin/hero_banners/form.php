<?php
$pageTitle = ($action === 'Add' ? 'Add' : 'Edit') . ' Hero Banner';
$isEdit = !empty($banner);

// Include breadcrumb and back button helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_back_button.php';
?>

<div class="container mx-auto px-4 sm:px-4 py-4 md:py-8 max-w-4xl">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Hero Banners', 'url' => '/admin/hero-banners'],
        ['label' => $action . ' Hero Banner']
    ]);
    ?>
    
    <div class="mb-8">
        <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Hero Banner</h1>
        </div>
        <p class="text-gray-600 mt-2">Configure banner settings, images, and scheduling</p>
        <?php renderBackButton('Hero Banners', '/admin/hero-banners', 'top-left'); ?>
    </div>
    
    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-4 md:p-6 space-y-6" id="banner-form">
        <!-- Visibility Controls -->
        <div class="border-b border-gray-200 pb-6 bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Visibility Controls</h2>
            <p class="text-sm text-gray-600 mb-4">Control which elements are displayed on the frontend</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Content Toggle -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <label for="content_enabled" class="text-sm font-semibold text-gray-700 block">
                                Banner Content
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Title, Description, CTA Button</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   id="content_enabled" 
                                   class="sr-only peer"
                                   <?php echo ($banner['content_enabled'] ?? true) ? 'checked' : ''; ?>
                                   onchange="toggleContentVisibility(this.checked)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                        </label>
                    </div>
                    <input type="hidden" name="content_enabled" id="content_enabled_hidden" value="<?php echo ($banner['content_enabled'] ?? true) ? '1' : '0'; ?>">
                    <div class="mt-2">
                        <span id="content_status" class="text-xs font-medium <?php echo ($banner['content_enabled'] ?? true) ? 'text-green-600' : 'text-gray-400'; ?>">
                            <?php echo ($banner['content_enabled'] ?? true) ? '✓ Enabled' : '✗ Disabled'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Image Toggle -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <label for="image_enabled" class="text-sm font-semibold text-gray-700 block">
                                Banner Images
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Desktop & Mobile Images</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   id="image_enabled" 
                                   class="sr-only peer"
                                   <?php echo ($banner['image_enabled'] ?? true) ? 'checked' : ''; ?>
                                   onchange="toggleImageVisibility(this.checked)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                        </label>
                    </div>
                    <input type="hidden" name="image_enabled" id="image_enabled_hidden" value="<?php echo ($banner['image_enabled'] ?? true) ? '1' : '0'; ?>">
                    <div class="mt-2">
                        <span id="image_status" class="text-xs font-medium <?php echo ($banner['image_enabled'] ?? true) ? 'text-green-600' : 'text-gray-400'; ?>">
                            <?php echo ($banner['image_enabled'] ?? true) ? '✓ Enabled' : '✗ Disabled'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Basic Information -->
        <div class="border-b border-gray-200 pb-6" id="content-section">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo htmlspecialchars($banner['title'] ?? ''); ?>"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                             name="description" 
                             rows="3"
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"><?php echo htmlspecialchars($banner['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <div class="border-b border-gray-200 pb-6" id="image-section">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Banner Images</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="desktop_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Desktop Image <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(Recommended: 1920x600px, WebP format)</span>
                    </label>
                    <?php if ($isEdit && !empty($banner['desktop_image'])): ?>
                        <div class="mb-3" id="desktop_image_preview" style="opacity: 1 !important;">
                            <img src="<?php echo SITE_URL . $banner['desktop_image']; ?>" 
                                 alt="Current desktop image"
                                 class="w-full h-32 object-cover rounded-lg border border-gray-300"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('.image-error').style.display='block';">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                            <p class="text-xs text-red-500 mt-1 image-error" style="display: none;">Image not found: <?php echo htmlspecialchars($banner['desktop_image']); ?></p>
                        </div>
                    <?php endif; ?>
                    <input type="file" 
                           id="desktop_image" 
                           name="desktop_image" 
                           accept="image/jpeg,image/png,image/webp,image/avif"
                           <?php echo !$isEdit ? 'required' : ''; ?>
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label for="mobile_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Mobile Image <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(Recommended: 768x400px, WebP format)</span>
                    </label>
                    <?php if ($isEdit && !empty($banner['mobile_image'])): ?>
                        <div class="mb-3" id="mobile_image_preview" style="opacity: 1 !important;">
                            <img src="<?php echo SITE_URL . $banner['mobile_image']; ?>" 
                                 alt="Current mobile image"
                                 class="w-full h-32 object-cover rounded-lg border border-gray-300"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('.image-error').style.display='block';">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                            <p class="text-xs text-red-500 mt-1 image-error" style="display: none;">Image not found: <?php echo htmlspecialchars($banner['mobile_image']); ?></p>
                        </div>
                    <?php endif; ?>
                    <input type="file" 
                           id="mobile_image" 
                           name="mobile_image" 
                           accept="image/jpeg,image/png,image/webp,image/avif"
                           <?php echo !$isEdit ? 'required' : ''; ?>
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="border-b border-gray-200 pb-6" id="cta-section">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Call to Action</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cta_text" class="block text-sm font-medium text-gray-700 mb-2">
                        CTA Button Text
                    </label>
                    <input type="text" 
                           id="cta_text" 
                           name="cta_text" 
                           value="<?php echo htmlspecialchars($banner['cta_text'] ?? 'Shop Now'); ?>"
                           placeholder="e.g., Shop Now, Explore, Buy Now"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label for="cta_url" class="block text-sm font-medium text-gray-700 mb-2">
                        CTA Redirect URL
                    </label>
                    <input type="text" 
                           id="cta_url" 
                           name="cta_url" 
                           value="<?php echo htmlspecialchars($banner['cta_url'] ?? ''); ?>"
                           placeholder="e.g., /product?category=summer or https://example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-xs text-gray-500 mt-1">Accepts both relative URLs (starting with /) and absolute URLs (http:// or https://)</p>
                </div>
            </div>
        </div>
        
        <!-- Targeting & Visibility -->
        <div class="border-b border-gray-200 pb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Targeting & Visibility</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="target_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Target Type <span class="text-red-500">*</span>
                    </label>
                    <select id="target_type" 
                            name="target_type" 
                            required
                            onchange="toggleTargetId()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="homepage" <?php echo ($banner['target_type'] ?? 'homepage') === 'homepage' ? 'selected' : ''; ?>>Homepage</option>
                        <option value="category" <?php echo ($banner['target_type'] ?? '') === 'category' ? 'selected' : ''; ?>>Category</option>
                        <option value="campaign" <?php echo ($banner['target_type'] ?? '') === 'campaign' ? 'selected' : ''; ?>>Campaign/Event</option>
                    </select>
                </div>
                
                <div id="target_id_container" style="display: <?php echo ($banner['target_type'] ?? 'homepage') === 'homepage' ? 'none' : 'block'; ?>;">
                    <label for="target_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Category/Campaign
                    </label>
                    <select id="target_id" 
                            name="target_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($banner['target_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="device_visibility" class="block text-sm font-medium text-gray-700 mb-2">
                        Device Visibility <span class="text-red-500">*</span>
                    </label>
                    <select id="device_visibility" 
                            name="device_visibility" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="both" <?php echo ($banner['device_visibility'] ?? 'both') === 'both' ? 'selected' : ''; ?>>Both Desktop & Mobile</option>
                        <option value="desktop" <?php echo ($banner['device_visibility'] ?? '') === 'desktop' ? 'selected' : ''; ?>>Desktop Only</option>
                        <option value="mobile" <?php echo ($banner['device_visibility'] ?? '') === 'mobile' ? 'selected' : ''; ?>>Mobile Only</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Priority & Scheduling -->
        <div class="border-b border-gray-200 pb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Priority & Scheduling</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Priority (0-100) <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">Higher priority appears first</span>
                    </label>
                    <input type="number" 
                           id="priority" 
                           name="priority" 
                           value="<?php echo $banner['priority'] ?? 0; ?>"
                           min="0" 
                           max="100" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="active" <?php echo ($banner['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($banner['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Start Date & Time
                        <span class="text-xs text-gray-500">(Optional - Leave empty for immediate start)</span>
                    </label>
                    <input type="datetime-local" 
                           id="start_date" 
                           name="start_date" 
                           value="<?php echo !empty($banner['start_date']) ? date('Y-m-d\TH:i', strtotime($banner['start_date'])) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        End Date & Time
                        <span class="text-xs text-gray-500">(Optional - Leave empty for no end date)</span>
                    </label>
                    <input type="datetime-local" 
                           id="end_date" 
                           name="end_date" 
                           value="<?php echo !empty($banner['end_date']) ? date('Y-m-d\TH:i', strtotime($banner['end_date'])) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Slider Settings -->
        <div class="border-b border-gray-200 pb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Slider Settings</h2>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="auto_slide_enabled" 
                           name="auto_slide_enabled" 
                           value="1"
                           <?php echo ($banner['auto_slide_enabled'] ?? true) ? 'checked' : ''; ?>
                           class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500">
                    <label for="auto_slide_enabled" class="ml-2 text-sm font-medium text-gray-700">
                        Enable Auto-Slide
                    </label>
                </div>
                
                <div>
                    <label for="slide_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        Slide Duration (milliseconds)
                        <span class="text-xs text-gray-500">(4000-6000ms recommended)</span>
                    </label>
                    <input type="number" 
                           id="slide_duration" 
                           name="slide_duration" 
                           value="<?php echo $banner['slide_duration'] ?? 5000; ?>"
                           min="4000" 
                           max="6000" 
                           step="100"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="flex justify-end gap-4 pt-6">
            <a href="<?php echo SITE_URL; ?>/admin/hero-banners" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-medium">
                <?php echo $action === 'Add' ? 'Create Banner' : 'Update Banner'; ?>
            </button>
        </div>
    </form>
</div>

<script>
function toggleTargetId() {
    const targetType = document.getElementById('target_type').value;
    const targetIdContainer = document.getElementById('target_id_container');
    const targetId = document.getElementById('target_id');
    
    if (targetType === 'homepage') {
        targetIdContainer.style.display = 'none';
        targetId.value = '';
    } else {
        targetIdContainer.style.display = 'block';
        if (targetType === 'campaign') {
            targetId.required = false;
        } else {
            targetId.required = true;
        }
    }
}

// Toggle content visibility
function toggleContentVisibility(enabled) {
    const contentSection = document.getElementById('content-section');
    const ctaSection = document.getElementById('cta-section');
    const contentStatus = document.getElementById('content_status');
    const contentHidden = document.getElementById('content_enabled_hidden');
    const titleField = document.getElementById('title');
    
    // Update hidden field
    contentHidden.value = enabled ? '1' : '0';
    
    // Update status text
    if (enabled) {
        contentStatus.textContent = '✓ Enabled';
        contentStatus.className = 'text-xs font-medium text-green-600';
    } else {
        contentStatus.textContent = '✗ Disabled';
        contentStatus.className = 'text-xs font-medium text-gray-400';
    }
    
    // Show/hide content sections with smooth transition
    if (enabled) {
        contentSection.style.display = 'block';
        ctaSection.style.display = 'block';
        setTimeout(() => {
            contentSection.style.opacity = '1';
            ctaSection.style.opacity = '1';
        }, 10);
        // Re-enable fields and restore required attribute
        const contentFields = contentSection.querySelectorAll('input, textarea');
        const ctaFields = ctaSection.querySelectorAll('input');
        contentFields.forEach(field => {
            field.disabled = false;
            // Restore required for title field and clear placeholder if it was auto-set
            if (field.id === 'title') {
                field.setAttribute('required', 'required');
                field.required = true;
                if (field.hasAttribute('data-placeholder-set') && field.value === 'Banner Image Only') {
                    field.value = '';
                }
                field.removeAttribute('data-placeholder-set');
            }
        });
        ctaFields.forEach(field => {
            field.disabled = false;
        });
    } else {
        contentSection.style.opacity = '0.5';
        ctaSection.style.opacity = '0.5';
        // Make fields non-required when disabled
        const contentFields = contentSection.querySelectorAll('input, textarea');
        const ctaFields = ctaSection.querySelectorAll('input');
        contentFields.forEach(field => {
            // Remove required attribute from DOM (not just property) BEFORE disabling
            field.removeAttribute('required');
            field.required = false;
            field.disabled = true;
            // Set placeholder title if empty to satisfy backend validation
            if (field.id === 'title' && !field.value.trim() && !field.hasAttribute('data-placeholder-set')) {
                field.value = 'Banner Image Only';
                field.setAttribute('data-placeholder-set', 'true');
            }
        });
        ctaFields.forEach(field => {
            field.removeAttribute('required');
            field.required = false;
            field.disabled = true;
        });
    }
}

// Toggle image visibility
function toggleImageVisibility(enabled) {
    const imageSection = document.getElementById('image-section');
    const imageStatus = document.getElementById('image_status');
    const imageHidden = document.getElementById('image_enabled_hidden');
    
    // Update hidden field
    imageHidden.value = enabled ? '1' : '0';
    
    // Update status text
    if (enabled) {
        imageStatus.textContent = '✓ Enabled';
        imageStatus.className = 'text-xs font-medium text-green-600';
    } else {
        imageStatus.textContent = '✗ Disabled';
        imageStatus.className = 'text-xs font-medium text-gray-400';
    }
    
    // Show/hide image section with smooth transition
    // Always show the section (images should be visible in edit mode for preview)
    imageSection.style.display = 'block';
    
    if (enabled) {
        setTimeout(() => {
            imageSection.style.opacity = '1';
        }, 10);
        // Re-enable image fields
        const imageFields = imageSection.querySelectorAll('input[type="file"]');
        imageFields.forEach(field => {
            field.disabled = false;
            // Only make required if it's a new banner and images are enabled
            const isEditMode = field.hasAttribute('data-edit-mode');
            if (!isEditMode) {
                field.required = true;
            } else {
                field.required = false; // Optional in edit mode
            }
        });
    } else {
        imageSection.style.opacity = '0.5';
        // Disable image fields and make them optional
        const imageFields = imageSection.querySelectorAll('input[type="file"]');
        imageFields.forEach(field => {
            field.disabled = true;
            field.required = false;
            // Clear file selection (but keep preview images visible)
            field.value = '';
        });
        // Keep image previews fully visible even when images are disabled
        const desktopPreview = document.getElementById('desktop_image_preview');
        const mobilePreview = document.getElementById('mobile_image_preview');
        if (desktopPreview) {
            desktopPreview.style.opacity = '1';
        }
        if (mobilePreview) {
            mobilePreview.style.opacity = '1';
        }
    }
}

// Form submission handler to ensure proper validation and data handling
function handleFormSubmit(e) {
    const contentEnabled = document.getElementById('content_enabled').checked;
    const form = e.target;
    
    // If content is disabled, prevent HTML5 validation and set placeholder value
    if (!contentEnabled) {
        // Prevent default HTML5 validation
        e.preventDefault();
        
        const titleField = document.getElementById('title');
        // Ensure title has a value (for backend validation)
        if (!titleField.value.trim()) {
            titleField.value = 'Banner Image Only';
            titleField.setAttribute('data-placeholder-set', 'true');
        }
        
        // Remove required attribute to prevent validation errors
        titleField.removeAttribute('required');
        
        // Re-enable the field temporarily for submission (if it was disabled)
        const wasDisabled = titleField.disabled;
        if (wasDisabled) {
            titleField.disabled = false;
        }
        
        // Ensure image file inputs are not required in edit mode (backend preserves existing images)
        <?php if ($isEdit): ?>
        const desktopImageInput = document.getElementById('desktop_image');
        const mobileImageInput = document.getElementById('mobile_image');
        if (desktopImageInput) {
            desktopImageInput.removeAttribute('required');
        }
        if (mobileImageInput) {
            mobileImageInput.removeAttribute('required');
        }
        <?php endif; ?>
        
        // Submit the form programmatically
        setTimeout(() => {
            form.submit();
        }, 10);
        
        return false;
    }
    
    // For content-enabled forms, ensure image fields are not required in edit mode
    <?php if ($isEdit): ?>
    const desktopImageInput = document.getElementById('desktop_image');
    const mobileImageInput = document.getElementById('mobile_image');
    if (desktopImageInput && (!desktopImageInput.files || desktopImageInput.files.length === 0)) {
        desktopImageInput.removeAttribute('required');
    }
    if (mobileImageInput && (!mobileImageInput.files || mobileImageInput.files.length === 0)) {
        mobileImageInput.removeAttribute('required');
    }
    <?php endif; ?>
    
    // Backend already handles preserving existing images when no new file is uploaded
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const contentEnabled = document.getElementById('content_enabled').checked;
    const imageEnabled = document.getElementById('image_enabled').checked;
    const form = document.getElementById('banner-form');
    
    // Add form submit handler
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Set initial state
    toggleContentVisibility(contentEnabled);
    toggleImageVisibility(imageEnabled);
    
    // If content is disabled on page load and title is empty, set placeholder
    if (!contentEnabled) {
        const titleField = document.getElementById('title');
        if (titleField && !titleField.value.trim()) {
            titleField.value = 'Banner Image Only';
            titleField.setAttribute('data-placeholder-set', 'true');
        }
    }
    
    // Mark edit mode for image fields (so they're not required)
    <?php if ($isEdit): ?>
    const imageFields = document.querySelectorAll('#image-section input[type="file"]');
    imageFields.forEach(field => {
        field.setAttribute('data-edit-mode', 'true');
    });
    <?php endif; ?>
    
    // Add transition styles
    const style = document.createElement('style');
    style.textContent = `
        #content-section, #cta-section, #image-section {
            transition: opacity 0.3s ease-in-out;
        }
        #content-section input:disabled,
        #content-section textarea:disabled,
        #cta-section input:disabled,
        #image-section input:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }
    `;
    document.head.appendChild(style);
});
</script>

