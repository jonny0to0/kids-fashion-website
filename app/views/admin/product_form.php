<?php
$pageTitle = $action . ' Product';

// Include breadcrumb and back button helpers
require_once __DIR__ . '/_breadcrumb.php';
require_once __DIR__ . '/_back_button.php';
?>

<!-- Cropper.js CSS -->
<link href="<?php echo SITE_URL; ?>/assets/css/cropper.min.css" rel="stylesheet">
<style>
    /* Cropper Container overrides */
    .img-container img {
        max-width: 100%;
    }
</style>

<div class="container mx-auto px-2 md:px-4 py-4 md:py-8">
    <!-- px-4 -->
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Products', 'url' => '/admin/products'],
        ['label' => $action . ' Product']
    ]);
    ?>

    <div class="mb-4 md:mb-8">
        <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Product</h1>
        </div>
        <?php renderBackButton('Products', '/admin/products', 'top-left'); ?>
    </div>

    <div class="bg-white rounded-lg shadow-md p-3 md:p-8 max-w-5xl mx-auto w-full">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="productForm" novalidate>
            <!-- Hidden Input for Image Order -->
            <input type="hidden" name="image_order" id="image_order" value="">

            <style>
                .scrollbar-hide::-webkit-scrollbar {
                    display: none;
                }
                .scrollbar-hide {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
            </style>

            <!-- Tabs Navigation -->
            <div class="mb-8 flex items-center gap-2 group">
                <!-- Left Arrow -->
                <button type="button" onclick="scrollTabs('left')" id="tab-scroll-left" 
                    class="shrink-0 z-10 p-2 bg-white rounded-full shadow-md text-gray-600 hover:text-pink-600 focus:outline-none hidden hover:bg-gray-50 border border-gray-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>

                <nav id="tabs-container" class="flex-1 flex flex-nowrap gap-2 md:gap-3 bg-transparent overflow-x-auto scrollbar-hide scroll-smooth" aria-label="Tabs">
                    <button type="button" onclick="switchTab('general')" id="tab-general" 
                        class="tab-btn active flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-semibold transition-all duration-300 ease-in-out focus:outline-none bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-lg shadow-pink-500/30 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        General
                    </button>
                    <button type="button" onclick="switchTab('pricing')" id="tab-pricing" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Pricing
                    </button>
                    <button type="button" onclick="switchTab('inventory')" id="tab-inventory" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Inventory
                    </button>
                    <button type="button" onclick="switchTab('attributes')" id="tab-attributes" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Attributes
                    </button>
                    <button type="button" onclick="switchTab('variants')" id="tab-variants" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Variants
                    </button>
                    <button type="button" onclick="switchTab('shipping')" id="tab-shipping" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        Shipping
                    </button>
                    <button type="button" onclick="switchTab('warranty')" id="tab-warranty" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Warranty
                    </button>
                    <button type="button" onclick="switchTab('seo')" id="tab-seo" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        SEO
                    </button>
                    <button type="button" onclick="switchTab('images')" id="tab-images" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Images
                    </button>
                    <button type="button" onclick="switchTab('review')" id="tab-review" 
                        class="tab-btn flex-none shrink-0 flex items-center justify-center gap-2 py-2.5 px-5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-white/60 focus:outline-none transition-all duration-300 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Review
                    </button>
                </nav>

                <!-- Right Arrow -->
                <button type="button" onclick="scrollTabs('right')" id="tab-scroll-right" 
                    class="shrink-0 z-10 p-2 bg-white rounded-full shadow-md text-gray-600 hover:text-pink-600 focus:outline-none hidden hover:bg-gray-50 border border-gray-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content" id="content-general">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Product Name *</label>
                        <input type="text" name="name" required
                            value="<?php echo htmlspecialchars($product['name'] ?? ($data['name'] ?? '')); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Category *</label>
                        <select name="category_id" id="category_id" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="">Select Category</option>
                            <?php if (!empty($categories)): ?>
                                <?php
                                $parentCategories = [];
                                $childCategories = [];
                                foreach ($categories as $category) {
                                    if (empty($category['parent_id'])) {
                                        $parentCategories[] = $category;
                                    } else {
                                        if (!isset($childCategories[$category['parent_id']])) {
                                            $childCategories[$category['parent_id']] = [];
                                        }
                                        $childCategories[$category['parent_id']][] = $category;
                                    }
                                }
                                foreach ($parentCategories as $parent): ?>
                                    <option value="<?php echo $parent['category_id']; ?>" <?php echo ($product['category_id'] ?? ($data['category_id'] ?? '')) == $parent['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($parent['name']); ?>
                                    </option>
                                    <?php if (isset($childCategories[$parent['category_id']])): ?>
                                        <?php foreach ($childCategories[$parent['category_id']] as $child): ?>
                                            <option value="<?php echo $child['category_id']; ?>" <?php echo ($product['category_id'] ?? ($data['category_id'] ?? '')) == $child['category_id'] ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;└─ <?php echo htmlspecialchars($child['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="active" <?php echo ($product['status'] ?? ($data['status'] ?? 'active')) == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($product['status'] ?? ($data['status'] ?? '')) == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="out_of_stock" <?php echo ($product['status'] ?? ($data['status'] ?? '')) == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Short Description</label>
                        <textarea name="short_description" rows="2"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none"><?php echo htmlspecialchars($product['short_description'] ?? ($data['short_description'] ?? '')); ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea name="description" rows="5"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none"><?php echo htmlspecialchars($product['description'] ?? ($data['description'] ?? '')); ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex flex-col md:flex-row gap-3 md:gap-6 mt-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? ($data['is_featured'] ?? 0)) ? 'checked' : ''; ?> class="mr-2">
                                <span class="text-gray-700">Featured Product</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_new_arrival" value="1" <?php echo ($product['is_new_arrival'] ?? ($data['is_new_arrival'] ?? 0)) ? 'checked' : ''; ?> class="mr-2">
                                <span class="text-gray-700">New Arrival</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_bestseller" value="1" <?php echo ($product['is_bestseller'] ?? ($data['is_bestseller'] ?? 0)) ? 'checked' : ''; ?> class="mr-2">
                                <span class="text-gray-700">Bestseller</span>
                            </label>
                        </div>
                    </div>
                 </div>
            </div>

            <div class="tab-content hidden" id="content-pricing">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Price (₹) *</label>
                        <input type="number" name="price" step="0.01" required
                            value="<?php echo $product['price'] ?? ($data['price'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Sale Price (₹)</label>
                        <input type="number" name="sale_price" step="0.01"
                            value="<?php echo $product['sale_price'] ?? ($data['sale_price'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Cost Price (₹)</label>
                        <input type="number" name="cost_price" step="0.01"
                            value="<?php echo $product['cost_price'] ?? ($data['cost_price'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>
                    
                    <!-- NEW: Tax Fields -->
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Tax Settings</h3>
                    </div>

                     <div>
                        <label class="block text-gray-700 font-medium mb-2">Tax Status</label>
                        <select name="tax_status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="taxable" <?php echo ($product['tax_status'] ?? 'taxable') == 'taxable' ? 'selected' : ''; ?>>Taxable</option>
                            <option value="none" <?php echo ($product['tax_status'] ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>

                     <div>
                        <label class="block text-gray-700 font-medium mb-2">Tax Class</label>
                        <select name="tax_class" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="standard" <?php echo ($product['tax_class'] ?? 'standard') == 'standard' ? 'selected' : ''; ?>>Standard</option>
                            <option value="reduced" <?php echo ($product['tax_class'] ?? '') == 'reduced' ? 'selected' : ''; ?>>Reduced Rate</option>
                            <option value="zero" <?php echo ($product['tax_class'] ?? '') == 'zero' ? 'selected' : ''; ?>>Zero Rate</option>
                        </select>
                    </div>

                     <div>
                        <label class="block text-gray-700 font-medium mb-2">GST Percent (%)</label>
                        <input type="number" name="gst_percent" step="0.01"
                            value="<?php echo $product['gst_percent'] ?? 18.00; ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>
                 </div>
            </div>

            <div class="tab-content hidden" id="content-inventory">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">SKU</label>
                        <input type="text" name="sku"
                            value="<?php echo htmlspecialchars($product['sku'] ?? ($data['sku'] ?? '')); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" required
                            value="<?php echo $product['stock_quantity'] ?? ($data['stock_quantity'] ?? 0); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Attributes Tab -->
            <div class="tab-content hidden" id="content-attributes">
                <div class="md:col-span-2">
                    <?php
                    // Build product attributes map for existing values
                    $productAttributesMap = [];
                    if (!empty($productAttributes)) {
                        foreach ($productAttributes as $pa) {
                            $productAttributesMap[$pa['attribute_id']] = $pa['attribute_value'];
                        }
                    }

                    // Helper function to render attribute field
                    function renderAttributeField($attribute, $currentValue)
                    {
                        $fieldHtml = '';
                        $fieldName = 'attribute_' . $attribute['attribute_id'];
                        $fieldId = 'attribute_' . $attribute['attribute_id'];
                        $required = $attribute['is_required'] ? 'required' : '';
                        $requiredClass = $attribute['is_required'] ? 'required' : '';

                        // Add dependency data attributes
                        $dataAttrs = '';
                        if (!empty($attribute['depends_on'])) {
                            $dataAttrs = 'data-depends-on="' . htmlspecialchars($attribute['depends_on']) . '" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '"';
                            if (!empty($attribute['show_when_decoded'])) {
                                $dataAttrs .= ' data-show-when=\'' . json_encode($attribute['show_when_decoded'], JSON_HEX_APOS | JSON_HEX_QUOT) . '\'';
                            }
                        }
                        $dataAttrs .= ' class="attribute-input w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '"';

                        if ($attribute['attribute_type'] === 'select' && !empty($attribute['options'])) {
                            $fieldHtml = '<select name="' . $fieldName . '" id="' . $fieldId . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                            $fieldHtml .= '<option value="">Select ' . htmlspecialchars($attribute['attribute_name']) . '</option>';
                            foreach ($attribute['options'] as $option) {
                                $selected = ($currentValue === $option) ? 'selected' : '';
                                $fieldHtml .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                            }
                            $fieldHtml .= '</select>';
                        } elseif ($attribute['attribute_type'] === 'textarea') {
                            $fieldHtml = '<textarea name="' . $fieldName . '" id="' . $fieldId . '" rows="3" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">' . htmlspecialchars($currentValue) . '</textarea>';
                        } elseif ($attribute['attribute_type'] === 'number') {
                            $fieldHtml = '<input type="number" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                        } elseif ($attribute['attribute_type'] === 'color') {
                            $fieldHtml = '<div class="flex gap-2">';
                            $fieldHtml .= '<input type="color" name="' . $fieldName . '_color" id="' . $fieldId . '_color" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' class="h-10 w-20 border border-gray-300 rounded focus:outline-none focus:ring-0 focus:outline-none">';
                            $fieldHtml .= '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" placeholder="Color name" ' . $required . ' ' . $dataAttrs . ' class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                            $fieldHtml .= '</div>';
                        } else {
                            $fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                        }

                        return $fieldHtml;
                    }

                    function shouldSpanFullWidth($attribute)
                    {
                        return $attribute['attribute_type'] === 'textarea';
                    }
                    ?>

                    <?php if (!empty($commonAttributes)): ?>
                        <div class="mb-4 md:mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Other</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="common-attributes-container">
                                <?php foreach ($commonAttributes as $attribute):
                                    $currentValue = $productAttributesMap[$attribute['attribute_id']] ?? '';
                                    $colSpanClass = shouldSpanFullWidth($attribute) ? 'md:col-span-2' : '';
                                    ?>
                                    <div class="<?php echo $colSpanClass; ?> attribute-field<?php echo !empty($attribute['depends_on']) ? ' attribute-dependent hidden' : ''; ?>"
                                        data-attribute-id="<?php echo $attribute['attribute_id']; ?>" <?php echo !empty($attribute['depends_on']) ? ' data-depends-on="' . htmlspecialchars($attribute['depends_on']) . '"' : ''; ?>>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <?php echo htmlspecialchars($attribute['attribute_name']); ?>
                                            <?php if ($attribute['is_required']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>
                                        <?php echo renderAttributeField($attribute, $currentValue); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4 md:mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Category-Specific Attributes</h3>
                        <p class="text-sm text-gray-500 mb-4">Select a category to load relevant attributes</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="category-attributes-container">
                            <?php
                            if (!empty($categoryAttributes)):
                                foreach ($categoryAttributes as $attribute):
                                    $currentValue = $productAttributesMap[$attribute['attribute_id']] ?? '';
                                    $colSpanClass = shouldSpanFullWidth($attribute) ? 'md:col-span-2' : '';
                                    ?>
                                    <div class="<?php echo $colSpanClass; ?>">
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <?php echo htmlspecialchars($attribute['attribute_name']); ?>
                                            <?php if ($attribute['is_required']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>
                                        <?php echo renderAttributeField($attribute, $currentValue); ?>
                                    </div>
                                    <?php
                                endforeach;
                            else:
                                ?>
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500">Select a category to load category-specific attributes</p>
                                </div>
                                <?php
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variants Tab -->
            <div class="tab-content hidden" id="content-variants">
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 md:mb-4">Product Variants</h2>
                    <p class="text-sm text-gray-500 mb-4">Add size and color combinations with individual stock and pricing</p>
                    <div id="variants-container">
                        <?php if (!empty($variants)): ?>
                            <?php foreach ($variants as $index => $variant): ?>
                                <div class="variant-item mb-4 p-4 border border-gray-200 rounded-lg" data-variant-index="<?php echo $index; ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <input type="hidden" name="variants[<?php echo $index; ?>][variant_id]" value="<?php echo $variant['variant_id']; ?>">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Size *</label>
                                            <input type="text" name="variants[<?php echo $index; ?>][size]" value="<?php echo htmlspecialchars($variant['size']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Color</label>
                                            <input type="text" name="variants[<?php echo $index; ?>][color]" value="<?php echo htmlspecialchars($variant['color'] ?? ''); ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Stock *</label>
                                            <input type="number" name="variants[<?php echo $index; ?>][stock_quantity]" value="<?php echo $variant['stock_quantity']; ?>" required min="0" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Additional Price (₹)</label>
                                            <input type="number" step="0.01" name="variants[<?php echo $index; ?>][additional_price]" value="<?php echo $variant['additional_price']; ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" onclick="removeVariant(<?php echo $index; ?>)" class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="addVariant()" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm font-medium">+ Add Variant</button>
                </div>
            </div>
            
            <!-- New Shipping Tab -->
            <div class="tab-content hidden" id="content-shipping">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Weight (kg)</label>
                        <input type="number" name="weight" step="0.01"
                            value="<?php echo $product['weight'] ?? ''; ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Dimensions (L × W × H)</label>
                        <input type="text" name="dimensions" placeholder="e.g. 10x10x5 cm"
                            value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Shipping Class</label>
                        <select name="shipping_class" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="">No Shipping Class</option>
                            <option value="heavy" <?php echo ($product['shipping_class'] ?? '') == 'heavy' ? 'selected' : ''; ?>>Heavy Item</option>
                            <option value="light" <?php echo ($product['shipping_class'] ?? '') == 'light' ? 'selected' : ''; ?>>Lightweight</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- New Return & Warranty Tab -->
            <div class="tab-content hidden" id="content-warranty">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                     <div>
                        <label class="block text-gray-700 font-medium mb-2">Return Policy</label>
                        <select name="return_policy" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="no_return" <?php echo ($product['return_policy'] ?? '') == 'no_return' ? 'selected' : ''; ?>>No Return</option>
                            <option value="7_days" <?php echo ($product['return_policy'] ?? '7_days') == '7_days' ? 'selected' : ''; ?>>7 Days Return</option>
                            <option value="10_days" <?php echo ($product['return_policy'] ?? '') == '10_days' ? 'selected' : ''; ?>>10 Days Return</option>
                            <option value="15_days" <?php echo ($product['return_policy'] ?? '') == '15_days' ? 'selected' : ''; ?>>15 Days Return</option>
                            <option value="30_days" <?php echo ($product['return_policy'] ?? '') == '30_days' ? 'selected' : ''; ?>>30 Days Return</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Warranty Type</label>
                        <select name="warranty_type" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                            <option value="none" <?php echo ($product['warranty_type'] ?? 'none') == 'none' ? 'selected' : ''; ?>>No Warranty</option>
                            <option value="manufacturer" <?php echo ($product['warranty_type'] ?? '') == 'manufacturer' ? 'selected' : ''; ?>>Manufacturer Warranty</option>
                            <option value="seller" <?php echo ($product['warranty_type'] ?? '') == 'seller' ? 'selected' : ''; ?>>Seller Warranty</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Warranty Duration</label>
                        <input type="text" name="warranty_duration" placeholder="e.g. 1 Year"
                            value="<?php echo htmlspecialchars($product['warranty_duration'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Warranty Description</label>
                        <textarea name="warranty_description" rows="3"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none"><?php echo htmlspecialchars($product['warranty_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- New SEO Tab -->
            <div class="tab-content hidden" id="content-seo">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Meta Title</label>
                        <input type="text" name="meta_title"
                            value="<?php echo htmlspecialchars($product['meta_title'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none" placeholder="SEO Title">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords"
                            value="<?php echo htmlspecialchars($product['meta_keywords'] ?? ''); ?>"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none" placeholder="comma, separated, keywords">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none"><?php echo htmlspecialchars($product['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Images Tab (Content reused from existing logic) -->
            <div class="tab-content hidden" id="content-images"> 
                 <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 md:mb-4">Product Images</h2>

                    <div class="mb-4 md:mb-6 bg-pink-50 border border-pink-200 rounded-lg p-3 md:p-4">
                        <button type="button" onclick="toggleImageGuidelines()" class="w-full flex items-center justify-between text-left">
                            <span class="font-semibold text-pink-800 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Image Upload Guidelines
                            </span>
                            <svg id="guidelines-icon" class="w-5 h-5 text-pink-800 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="image-guidelines-content" class="hidden mt-4 text-sm text-gray-700 space-y-3">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">📸 Recommended Images (Minimum 4, Best: 6-7):</h4>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li><strong>Main Image (Required):</strong> Pure white background (#FFFFFF), square (1:1), product covers 85-90%</li>
                                    <li><strong>Zoom Image:</strong> High-resolution version (2000x2000px+) of the Main Image.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($images) && is_array($images) && !empty($product)): ?>
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-3">Current Images</label>
                            <div class="space-y-4" id="existing-images">
                                <?php foreach ($images as $image): ?>
                                    <div class="flex flex-col md:flex-row gap-4 p-4 border border-gray-200 rounded-lg items-center bg-gray-50 group hover:shadow transition-shadow" data-image-id="<?php echo $image['image_id']; ?>">
                                        <!-- Drag Handle -->
                                        <div class="cursor-move text-gray-400 hover:text-gray-600 p-2 drag-handle" title="Drag to reorder">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                        </div>
                                        <div class="relative w-24 h-24 flex-shrink-0 border bg-white">
                                            <img src="<?php echo SITE_URL . $image['image_url']; ?>" alt="Product Image" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png';">
                                            <?php if ($image['is_primary']): ?>
                                                <span class="absolute top-0 left-0 bg-pink-600 text-white text-[10px] px-1 rounded-br">Primary</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 w-full relative">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Zoom Image Status</label>
                                                    <?php if (!empty($image['zoom_image_url'])): ?>
                                                        <div class="flex items-center text-sm text-green-600 font-medium">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            Uploaded
                                                            <a href="<?php echo SITE_URL . $image['zoom_image_url']; ?>" target="_blank" class="text-xs text-pink-600 underline ml-2">View</a>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-sm text-gray-500 italic">None set</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Update Zoom Image</label>
                                                    <input type="file" name="existing_zoom_images[<?php echo $image['image_id']; ?>]" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="deleteProductImage(<?php echo $image['image_id']; ?>, <?php echo $product['product_id']; ?>)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50" title="Delete Image">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="pt-4 md:pt-6">
                        <h3 class="font-bold text-gray-800 mb-3 md:mb-4">Add New Images</h3>
                        <div id="new-images-container" class="space-y-4"></div>
                        <button type="button" onclick="addNewImageRow()" class="mt-4 flex items-center text-pink-600 font-semibold hover:text-pink-700">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Image Row
                        </button>
                    </div>
                </div>
            </div>

            <!-- Review & Submit Tab -->
            <div class="tab-content hidden" id="content-review">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Review & Submit</h2>
                    <p class="text-gray-600 mb-8">Please review all product details before submitting. Click the button below to validate all fields and create the product.</p>
                    
                    <div id="review-errors" class="hidden text-left bg-red-50 border border-red-200 rounded-lg p-4 mb-6 mx-auto max-w-2xl">
                        <div class="flex items-center mb-2 text-red-700 font-bold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Validation Errors Found
                        </div>
                        <ul class="list-disc list-inside text-red-600 text-sm" id="review-error-list"></ul>
                    </div>

                    <button type="button" onclick="handleFormSubmit()" 
                        class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-bold text-lg shadow-lg transform transition hover:-translate-y-1">
                        <?php echo $action; ?> Product
                    </button>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col md:flex-row items-center gap-4">
                <!-- Back Button: Mobile Order 2, Desktop Order 1 (Left) -->
                <button type="button" id="prev-btn" onclick="prevTab()" class="hidden w-full md:w-auto bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 font-medium transition-colors order-2 md:order-1 md:mr-auto">
                    ← Back
                </button>
                
                <!-- Cancel Button: Mobile Order 3 (Bottom), Desktop Order 2 -->
                <a href="<?php echo SITE_URL; ?>/admin/products"
                    class="w-full md:w-auto px-6 py-2 text-gray-600 hover:text-gray-800 font-medium text-center transition-colors border border-gray-200 md:border-transparent rounded-lg order-3 md:order-2">
                    Cancel
                </a>

                <!-- Next Button: Mobile Order 1 (Top), Desktop Order 3 -->
                <button type="button" id="next-btn" onclick="nextTab()" class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-colors flex justify-center items-center order-1 md:order-3">
                    Next Step →
                </button>
            </div>
        </form>
    </div>
    <!-- Error Modal -->
    <div id="error-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden transform transition-all">
            <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-red-800">Validation Errors</h3>
                <button type="button" onclick="closeErrorModal()" class="text-red-500 hover:text-red-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Please correct the following errors before proceeding:</p>
                <ul id="modal-error-list" class="space-y-2 mb-6 max-h-60 overflow-y-auto">
                    <!-- Errors injected here -->
                </ul>
                <div class="flex justify-end">
                    <button type="button" onclick="closeErrorModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 font-medium">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
    <div id="cropper-modal" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop with blur -->
        <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" id="cropper-backdrop"></div>

        <div class="fixed inset-0 z-[10000] overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 scale-95" id="cropper-panel">
                    
                    <!-- Header -->
                    <div class="bg-gray-50/50 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 tracking-tight" id="modal-title">Adjust Image</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Crop and position your image perfecty</p>
                        </div>
                        <button type="button" onclick="closeCropperModal()" class="rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Layout: Image Area + Sidebar Controls -->
                    <div class="flex flex-col lg:flex-row bg-white h-[600px] lg:h-[500px]">
                        <!-- Main Cropping Area -->
                        <div class="flex-1 bg-gray-900 relative flex items-center justify-center overflow-hidden w-full h-full">
                            <div class="img-container w-full h-full">
                                <img id="cropper-image" src="" alt="Picture" class="max-w-full block">
                            </div>
                        </div>

                        <!-- Sidebar Controls -->
                        <div class="w-full lg:w-72 bg-white border-l border-gray-100 flex flex-col z-20 shadow-[-5px_0_15px_-5px_rgb(0,0,0,0.05)]">
                            <div class="flex-1 p-6 space-y-8 overflow-y-auto">
                                
                                <!-- Aspect Ratio -->
                                <div class="space-y-3">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Aspect Ratio</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" onclick="setAspectRatio(0.8, this)" class="ratio-btn active px-3 py-2 text-sm border border-pink-500 bg-pink-50 text-pink-700 rounded-lg font-medium hover:bg-pink-100 transition-all focus:outline-none focus:ring-2 focus:ring-pink-500/20">
                                            4:5 <span class="text-xs opacity-75 font-normal ml-1">Card (Default)</span>
                                        </button>
                                        <button type="button" onclick="setAspectRatio(1, this)" class="ratio-btn px-3 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg font-medium hover:border-gray-300 hover:bg-gray-50 transition-all focus:outline-none">
                                            1:1 <span class="text-xs opacity-75 font-normal ml-1">Square</span>
                                        </button>
                                        <button type="button" onclick="setAspectRatio(4/3, this)" class="ratio-btn px-3 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg font-medium hover:border-gray-300 hover:bg-gray-50 transition-all focus:outline-none">
                                            4:3
                                        </button>
                                        <button type="button" onclick="setAspectRatio(NaN, this)" class="ratio-btn px-3 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg font-medium hover:border-gray-300 hover:bg-gray-50 transition-all focus:outline-none">
                                            Free
                                        </button>
                                    </div>
                                </div>

                                <!-- Rotation -->
                                <div class="space-y-3">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Rotation</label>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="cropper.rotate(-90)" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors" title="Rotate Left">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        </button>
                                        <button type="button" onclick="cropper.rotate(90)" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors" title="Rotate Right">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Zoom -->
                                <div class="space-y-3">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Zoom</label>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="cropper.zoom(-0.1)" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors" title="Zoom Out">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path></svg>
                                        </button>
                                        <button type="button" onclick="cropper.zoom(0.1)" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors" title="Zoom In">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="p-6 border-t border-gray-100 bg-gray-50/50 space-y-3">
                                <button type="button" id="crop-btn" class="w-full justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-all transform hover:scale-[1.02] active:scale-95 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Apply Crop
                                </button>
                                <button type="button" onclick="closeCropperModal()" class="w-full justify-center py-3 px-4 border border-gray-200 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all hover:border-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- Cropper.js Library -->
<script src="<?php echo SITE_URL; ?>/assets/js/cropper.min.js"></script>

<script>
    const siteUrl = window.SITE_URL || '<?php echo SITE_URL; ?>';
    let variantIndex = <?php echo !empty($variants) ? count($variants) : 0; ?>;
    // Determine if we are in Edit Mode
    const isEditMode = <?php echo isset($product['product_id']) ? 'true' : 'false'; ?>;

    let imageRowIndex = 0; // Global counter for unique IDs

    // Initialize with one row if needed
    document.addEventListener('DOMContentLoaded', function () {
        // Only auto-add a row if NOT in edit mode, or if container is empty
        const folderContainer = document.getElementById('new-images-container');
        if (folderContainer && folderContainer.children.length === 0) {
            addNewImageRow();
        }

        // Initialize SortableJS for Image Reordering
        const imageList = document.getElementById('existing-images');
        if (imageList) {
            new Sortable(imageList, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-pink-50',
                onEnd: function () {
                    updateImageOrder();
                }
            });
        }
    });

    // Update the hidden input with the new order of image IDs
    function updateImageOrder() {
        const imageList = document.getElementById('existing-images');
        if (!imageList) return;

        const imageIds = Array.from(imageList.children).map(row => row.getAttribute('data-image-id'));
        document.getElementById('image_order').value = imageIds.join(',');

        // Update Visual Badges (Fake Primary Update for UX)
        Array.from(imageList.children).forEach((row, index) => {
            const badge = row.querySelector('.absolute span'); // "Primary" badge
            if (index === 0) {
                if (!badge) {
                    const imgContainer = row.querySelector('.relative');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'absolute top-0 left-0 bg-pink-600 text-white text-[10px] px-1 rounded-br';
                    newBadge.innerText = 'Primary';
                    imgContainer.appendChild(newBadge);
                }
            } else {
                if (badge) badge.remove();
            }
        });

        // AJAX Auto-Save
        const productId = <?php echo isset($product['product_id']) ? $product['product_id'] : 0; ?>;
        if (productId > 0) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('image_order', imageIds.join(','));

            fetch(siteUrl + '/admin/products/reorder-images', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Image order saved successfully');
                    // Optional: Show a small toast notification here
                } else {
                    console.error('Failed to save image order:', data.message);
                    alert('Failed to save image order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving image order:', error);
                alert('Error saving image order. Please check console.');
            });
        }
    }


    // Preview Image Helper
    function previewImage(input) {
        const container = input.nextElementSibling;
        const placeholder = container.querySelector('.preview-placeholder');
        const img = container.querySelector('.preview-img');
        const removeBtn = container.querySelector('.remove-file-btn');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
                container.classList.add('border-pink-500', 'bg-pink-50', 'border-solid');
                container.classList.remove('border-dashed');
                
                // Show remove button
                if(removeBtn) removeBtn.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            resetPreview(input);
        }
    }

    function resetPreview(input) {
        // Clear input
        input.value = '';
        
        const container = input.nextElementSibling;
        const placeholder = container.querySelector('.preview-placeholder');
        const img = container.querySelector('.preview-img');
        const removeBtn = container.querySelector('.remove-file-btn');

        img.src = '';
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
        container.classList.remove('border-pink-500', 'bg-pink-50', 'border-solid');
        container.classList.add('border-dashed');
        if(removeBtn) removeBtn.classList.add('hidden');
    }

    // CropperJS Variables
    let cropper;
    let currentInput; // The file input currently being cropped
    
    function initCropper(input) {
        if (input.files && input.files[0]) {
            currentInput = input;
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const image = document.getElementById('cropper-image');
                image.src = e.target.result;
                
                // Show modal with animation
                const modal = document.getElementById('cropper-modal');
                const backdrop = document.getElementById('cropper-backdrop');
                const panel = document.getElementById('cropper-panel');
                
                modal.classList.remove('hidden');
                
                // Trigger reflow
                void modal.offsetWidth;
                
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
                
                // Initialize Cropper (destroy existing if any)
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(image, {
                    aspectRatio: 0.8, // Default 4:5 for standard card
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            
            reader.readAsDataURL(file);
            
            // Re-bind save button - Use generic listener to avoid stacking
            const cropBtn = document.getElementById('crop-btn');
            // Remove old listener if possible (cloning is a quick hack to clear listeners)
            const newCropBtn = cropBtn.cloneNode(true);
            cropBtn.parentNode.replaceChild(newCropBtn, cropBtn);
            
            newCropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                saveCrop();
            });
        }
    }
    
    function saveCrop() {
        if (!cropper) {
            console.error('Cropper not initialized');
            return;
        }
        
        // Get cropped canvas - Enforce strict 4:5 output size (e.g., 800x1000)
        // or just rely on aspect ratio and set max width/height
        const canvas = cropper.getCroppedCanvas({
            width: 800, 
            height: 1000, 
            minWidth: 400,
            minHeight: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        if (!canvas) {
             console.error('Could not get cropped canvas');
             return;
        }

        const originalFile = currentInput.files[0];
        const fileType = originalFile ? originalFile.type : 'image/jpeg';
        
        canvas.toBlob(function(blob) {
            if (!blob) {
                console.error('Canvas could not be converted to Blob');
                return;
            }

            // Create a new File object from the blob
            const fileName = originalFile ? originalFile.name : 'cropped-image.jpg';
            const croppedFile = new File([blob], fileName, { type: fileType });
            
            // Create a DataTransfer to update the file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            currentInput.files = dataTransfer.files;
            
            // Mark as separately handled if needed, or just proceed
            currentInput._cropped = true;

            // Update preview
            previewImage(currentInput); 
            
            // Close modal
            closeCropperModal();
            
        }, fileType);
    }
    
    function closeCropperModal() {
        const modal = document.getElementById('cropper-modal');
        const backdrop = document.getElementById('cropper-backdrop');
        const panel = document.getElementById('cropper-panel');
        
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'scale-95');
        panel.classList.remove('opacity-100', 'scale-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('cropper-image').src = '';
            
            // If we closed without saving and input has value (meaning it was a fresh selection)
            // we should probably clear the input so user can re-select same file if they want
            if (currentInput && !currentInput._cropped) {
                 resetPreview(currentInput);
            }
            if (currentInput) {
                currentInput._cropped = false; // Reset flag
            }
        }, 300); // Wait for transition
    }
    
    function setAspectRatio(ratio, btn) {
        if (cropper) {
            cropper.setAspectRatio(ratio);
            
            // Update active state of buttons
            document.querySelectorAll('.ratio-btn').forEach(b => {
                b.classList.remove('active', 'border-pink-500', 'bg-pink-50', 'text-pink-700', 'font-medium');
                b.classList.add('border-gray-200', 'text-gray-600', 'hover:border-gray-300');
            });
            
            if(btn) {
                btn.classList.add('active', 'border-pink-500', 'bg-pink-50', 'text-pink-700', 'font-medium');
                btn.classList.remove('border-gray-200', 'text-gray-600', 'hover:border-gray-300');
            }
        }
    }

    function addNewImageRow() {
        const container = document.getElementById('new-images-container');
        if (!container) return;
        
        // Use global counter for unique index
        const index = imageRowIndex++;
        
        // Determine validation based on mode
        const requiredAttr = isEditMode ? '' : 'required';
        const requiredLabel = isEditMode ? '' : ' <span class="text-red-500">*</span>';
        const rowLabel = container.children.length + 1; // Visual label only

        const row = document.createElement('div');
        row.className = 'group relative bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-all mb-4 animate-fade-in-up';

        row.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <span class="bg-gray-100 text-gray-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">#${rowLabel}</span>
                    New Image Set
                </span>
                ${index > 0 || isEditMode ? `
                <button type="button" onclick="this.closest('.group').remove()" 
                    class="text-gray-400 hover:text-red-500 p-1 transition-colors rounded-full hover:bg-red-50" title="Remove Image Set">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                ` : ''}
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Main Image Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Main Image${requiredLabel}</label>
                    <div class="relative group/input">
                        <input type="file" name="new_images[${index}][main]" accept="image/*" ${requiredAttr}
                               onchange="initCropper(this)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                               
                        <div class="preview-container border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center text-center transition-all duration-200 group-hover/input:border-pink-400 group-hover/input:bg-pink-50/50 min-h-[180px] relative overflow-hidden bg-gray-50">
                            
                            <!-- Placeholder -->
                            <div class="preview-placeholder flex flex-col items-center">
                                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 text-pink-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Click to upload</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP</p>
                            </div>

                            <!-- Preview Image -->
                            <img src="" class="preview-img hidden w-full h-full object-contain absolute inset-0 z-0 p-2" />
                            
                            <!-- Remove File Button (Visible ONLY when file selected) -->
                            <button type="button" class="remove-file-btn hidden absolute top-2 right-2 z-20 bg-white rounded-full p-1 shadow-md hover:bg-gray-100 text-gray-500" 
                                    onclick="event.preventDefault(); resetPreview(this.closest('.relative').querySelector('input'))">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Zoom Image Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider flex justify-between">
                        Zoom Image
                        <span class="text-gray-400 font-normal normal-case text-[10px] bg-gray-100 px-2 rounded-full">Optional</span>
                    </label>
                    <div class="relative group/input">
                        <input type="file" name="new_images[${index}][zoom]" accept="image/*"
                               onchange="previewImage(this)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                               
                        <div class="preview-container border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center text-center transition-all duration-200 group-hover/input:border-blue-400 group-hover/input:bg-blue-50/50 min-h-[180px] relative overflow-hidden bg-gray-50">
                            
                            <!-- Placeholder -->
                            <div class="preview-placeholder flex flex-col items-center">
                                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 text-blue-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Add zoom view</p>
                                <p class="text-xs text-gray-400 mt-1">High-res detail</p>
                            </div>

                            <!-- Preview Image -->
                            <img src="" class="preview-img hidden w-full h-full object-contain absolute inset-0 z-0 p-2" />
                            
                            <!-- Remove File Button -->
                            <button type="button" class="remove-file-btn hidden absolute top-2 right-2 z-20 bg-white rounded-full p-1 shadow-md hover:bg-gray-100 text-gray-500" 
                                    onclick="event.preventDefault(); resetPreview(this.closest('.relative').querySelector('input'))">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(row);
    }

    // Toggle image guidelines
    function toggleImageGuidelines() {
        const content = document.getElementById('image-guidelines-content');
        const icon = document.getElementById('guidelines-icon');

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // Image deletion
    async function deleteProductImage(imageId, productId) {
        if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('product_id', productId);

            const response = await fetch(siteUrl + '/admin/product-image-delete', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Remove the image element from DOM
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.transition = 'opacity 0.3s';
                    imageElement.style.opacity = '0';
                    setTimeout(() => {
                        imageElement.remove();

                        // Show message if no images left
                        const existingImages = document.getElementById('existing-images');
                        if (existingImages && existingImages.children.length === 0) {
                            existingImages.parentElement.innerHTML = '<p class="text-sm text-gray-500">No images uploaded yet.</p>';
                        }
                    }, 300);
                }
                // Optional: Show toast
            } else {
                alert(data.message || 'Failed to delete image');
            }
        } catch (error) {
            console.error('Error deleting image:', error);
            alert('An error occurred. Please try again.');
        }
    }
    
    // Variant management
    function addVariant() {
        const container = document.getElementById('variants-container');
        const variantHtml = `
        <div class="variant-item mb-4 p-4 border border-gray-200 rounded-lg" data-variant-index="${variantIndex}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Size *</label>
                    <input type="text" name="variants[${variantIndex}][size]" required
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Color</label>
                    <input type="text" name="variants[${variantIndex}][color]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Stock *</label>
                    <input type="number" name="variants[${variantIndex}][stock_quantity]" required min="0"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Additional Price (₹)</label>
                    <input type="number" step="0.01" name="variants[${variantIndex}][additional_price]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeVariant(${variantIndex})" 
                            class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', variantHtml);
        variantIndex++;
    }

    function removeVariant(index) {
        const variantItem = document.querySelector(`[data-variant-index="${index}"]`);
        if (variantItem) {
            variantItem.remove();
        }
    }


    const tabOrder = ['general', 'pricing', 'inventory', 'attributes', 'variants', 'shipping', 'warranty', 'seo', 'images', 'review'];

    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Show selected tab content
        document.getElementById('content-' + tabId).classList.remove('hidden');
        
        // Reset all tab buttons to INACTIVE state
        document.querySelectorAll('.tab-btn').forEach(btn => {
            // Remove active classes
            btn.classList.remove('active', 'bg-gradient-to-r', 'from-pink-500', 'to-rose-500', 'text-white', 'shadow-lg', 'shadow-pink-500/30');
            
            // Add inactive classes
            btn.classList.add('text-gray-500', 'hover:text-gray-900', 'hover:bg-white/60');
        });
        
        // Highlight active tab button
        const activeBtn = document.getElementById('tab-' + tabId);
        if(activeBtn){
            // Remove inactive classes
            activeBtn.classList.remove('text-gray-500', 'hover:text-gray-900', 'hover:bg-white/60');
            
            // Add active classes
            activeBtn.classList.add('active', 'bg-gradient-to-r', 'from-pink-500', 'to-rose-500', 'text-white', 'shadow-lg', 'shadow-pink-500/30');
        }

        updateNavButtons(tabId);
    }
    
    function updateNavButtons(currentTabId) {
        const currentIndex = tabOrder.indexOf(currentTabId);
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        
        // Show/Hide Prev
        if (currentIndex > 0) {
            prevBtn.classList.remove('hidden');
        } else {
            prevBtn.classList.add('hidden');
        }

        // Show/Hide Next
        if (currentIndex < tabOrder.length - 1) {
            nextBtn.classList.remove('hidden');
            nextBtn.textContent = 'Next Step →';
            nextBtn.onclick = nextTab; // Re-bind function
        } else {
            nextBtn.classList.add('hidden'); // Hide on review tab, Submit button is inside tab content
        }
    }

    function nextTab() {
        const currentTabId = getCurrentTabId();
        if (validateTab(currentTabId)) {
            const currentIndex = tabOrder.indexOf(currentTabId);
            if (currentIndex < tabOrder.length - 1) {
                switchTab(tabOrder[currentIndex + 1]);
                window.scrollTo(0, 0);
            }
        }
    }

    function prevTab() {
        const currentTabId = getCurrentTabId();
        const currentIndex = tabOrder.indexOf(currentTabId);
        if (currentIndex > 0) {
            switchTab(tabOrder[currentIndex - 1]);
            window.scrollTo(0, 0);
        }
    }
    
    function getCurrentTabId() {
        const activeContent = document.querySelector('.tab-content:not(.hidden)');
        return activeContent ? activeContent.id.replace('content-', '') : 'general';
    }

    function validateTab(tabId) {
        const container = document.getElementById('content-' + tabId);
        const inputs = container.querySelectorAll('input, select, textarea');
        let isValid = true;
        let errors = [];

        // Clear previous error styles
        container.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500', 'ring-2', 'ring-red-200'));
        container.querySelectorAll('.text-red-500.text-xs').forEach(el => el.remove());

        inputs.forEach(input => {
            // Check if input is technically visible and required
            // Note: simple 'required' check on hidden inputs is what caused the bug. 
            // Here we are only checking inputs INSIDE the current visible container.
            if (input.hasAttribute('required') && !input.value.trim() && !input.disabled) {
                isValid = false;
                markInputError(input, 'This field is required');
                errors.push({
                    field: input.name,
                    label: getLabelFor(input),
                    element: input
                });
            }
            
            // Should also check pattern/type/email etc if needed, but basic required is the main blocker
            if (input.type === 'number' && input.hasAttribute('required') && input.value === '') {
                 isValid = false;
                 markInputError(input, 'Value required');
                 errors.push({
                    field: input.name,
                    label: getLabelFor(input),
                    element: input
                });
            }
        });

        if (!isValid) {
            showErrorModal(errors);
        }

        return isValid;
    }

    function markInputError(input, message) {
        input.classList.add('border-red-500', 'ring-2', 'ring-red-200');
        // Check if message already exists
        const parent = input.parentElement;
        if (!parent.querySelector('.text-red-500.text-xs')) {
            const msg = document.createElement('p');
            msg.className = 'text-red-500 text-xs mt-1';
            msg.innerText = message;
            parent.appendChild(msg);
        }
    }

    function getLabelFor(input) {
        // Try to find label
        const parent = input.closest('div');
        const label = parent ? parent.querySelector('label') : null;
        return label ? label.innerText.replace('*', '').trim() : input.name;
    }

    function showErrorModal(errors) {
        const modal = document.getElementById('error-modal');
        const list = document.getElementById('modal-error-list');
        list.innerHTML = '';

        errors.forEach(err => {
            const li = document.createElement('li');
            li.className = 'bg-red-50 p-3 rounded flex justify-between items-center';
            li.innerHTML = `
                <span class="text-sm text-red-700 font-semibold">${err.label}</span>
                <button type="button" onclick="focusField('${err.element.id || err.element.name}')" class="text-xs bg-white text-red-600 border border-red-200 px-2 py-1 rounded hover:bg-red-50">Find Field</button>
            `;
            // We need a way to reference the element for focusField. 
            // Since we might switch tabs, we need to pass tab info too.
            // Actually, we are only validating CURRENT tab for "Next" button.
            // But for Final Submit, we validate all.
            li.querySelector('button').onclick = () => {
                closeErrorModal();
                if(err.tabId && err.tabId !== getCurrentTabId()){
                    switchTab(err.tabId);
                }
                setTimeout(() => {
                    err.element.focus();
                    err.element.scrollIntoView({behavior: 'smooth', block: 'center'});
                }, 100);
            };
            list.appendChild(li);
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeErrorModal() {
        document.getElementById('error-modal').classList.add('hidden');
        document.getElementById('error-modal').classList.remove('flex');
    }

    function handleFormSubmit() {
        // Validate ALL tabs
        let allErrors = [];
        let isValid = true;
        
        tabOrder.forEach(tabId => {
            if (tabId === 'review') return; // Skip review tab itself
            
            const container = document.getElementById('content-' + tabId);
            const inputs = container.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                 if (input.hasAttribute('required') && !input.value.trim() && !input.disabled) {
                    isValid = false;
                    allErrors.push({
                        field: input.name,
                        label: getLabelFor(input),
                        element: input,
                        tabId: tabId
                    });
                }
            });
        });

        // Specific Validation: Images (Required for new products)
        if (!isEditMode) {
            const newImages = document.querySelectorAll('input[name^="new_images"][name$="[main]"]');
            let hasImage = false;
            
            // Check if any new image input has a file
            newImages.forEach(input => {
                if (input.value) hasImage = true;
            });

            // Also check if there are existing images (unlikely in Add mode but good safety)
            const existingImages = document.querySelectorAll('#existing-images .bg-gray-50');
            if (existingImages.length > 0) hasImage = true;

            if (!hasImage) {
                isValid = false;
                allErrors.push({
                    field: 'image_upload',
                    label: 'At least one Product Image is required',
                    element: document.querySelector('button[onclick="addNewImageRow()"]'),
                    tabId: 'images'
                });
            }
        }

        if (!isValid) {
            showErrorModal(allErrors);
            // Also show errors on review page
            const reviewList = document.getElementById('review-error-list');
            const reviewErrors = document.getElementById('review-errors');
            reviewList.innerHTML = '';
            allErrors.forEach(err => {
                 reviewList.innerHTML += `<li>${err.label} (in ${err.tabId})</li>`;
            });
            reviewErrors.classList.remove('hidden');
            return;
        }

        // If valid, submit form
        document.getElementById('productForm').submit();
    }
    
    // Function to render attribute field HTML with dependency support
    function renderAttributeField(attribute, currentValue) {
        if (!attribute.attribute_id || !attribute.attribute_name) {
            console.warn('Invalid attribute:', attribute);
            return '';
        }

        const required = attribute.is_required ? 'required' : '';
        const requiredSpan = attribute.is_required ? '<span class="text-red-500">*</span>' : '';
        const options = (attribute.options && Array.isArray(attribute.options)) ? attribute.options : [];
        const attributeType = attribute.attribute_type || 'text';
        const dependsOn = attribute.depends_on || null;
        const showWhen = attribute.show_when_decoded || null;

        // Add data attributes for dependency tracking
        let dataAttrs = '';
        if (dependsOn) {
            dataAttrs = `data-depends-on="${dependsOn}"`;
            if (showWhen) {
                dataAttrs += ` data-show-when='${JSON.stringify(showWhen)}'`;
            }
        }

        let inputHtml = '';
        let inputId = `attribute_${attribute.attribute_id}`;

        if (attributeType === 'select' && options.length > 0) {
            inputHtml = `<select name="${inputId}" id="${inputId}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="${attribute.attribute_id}">
            <option value="">Select ${escapeHtml(attribute.attribute_name)}</option>`;
            options.forEach(option => {
                const optionValue = escapeHtml(String(option));
                inputHtml += `<option value="${optionValue}" ${currentValue === option ? 'selected' : ''}>${optionValue}</option>`;
            });
            inputHtml += '</select>';
        } else if (attributeType === 'textarea') {
            inputHtml = `<textarea name="${inputId}" id="${inputId}" rows="3" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="${attribute.attribute_id}">${escapeHtml(currentValue)}</textarea>`;
        } else if (attributeType === 'number') {
            inputHtml = `<input type="number" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="${attribute.attribute_id}">`;
        } else if (attributeType === 'color') {
            inputHtml = `<div class="flex gap-2">
            <input type="color" name="${inputId}_color" id="${inputId}_color" value="${escapeHtml(currentValue)}" ${required} class="h-10 w-20 border border-gray-300 rounded focus:outline-none focus:ring-0 focus:outline-none">
            <input type="text" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" placeholder="Color name" ${required} ${dataAttrs} class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="${attribute.attribute_id}">
        </div>`;
        } else {
            inputHtml = `<input type="text" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-0 focus:outline-none attribute-input" data-attribute-id="${attribute.attribute_id}">`;
        }

        const colSpanClass = attributeType === 'textarea' ? 'md:col-span-2' : '';
        const hiddenClass = dependsOn ? 'attribute-dependent hidden' : '';

        return `<div class="${colSpanClass} attribute-field ${hiddenClass}" data-attribute-id="${attribute.attribute_id}" ${dependsOn ? `data-depends-on="${dependsOn}"` : ''}>
        <label class="block text-gray-700 font-medium mb-2">${escapeHtml(attribute.attribute_name)} ${requiredSpan}</label>
        ${inputHtml}
    </div>`;
    }

    // Function to get existing attribute values from form
    function getExistingAttributeValues() {
        const values = {};
        const inputs = document.querySelectorAll('[name^="attribute_"]');
        inputs.forEach(input => {
            if (input.name.startsWith('attribute_') && !input.name.endsWith('_color')) {
                const attributeId = input.name.replace('attribute_', '');
                values[attributeId] = input.value;
            }
        });
        return values;
    }

    // Function to load category-specific attributes
    function loadCategoryAttributes(categoryId, existingValues = {}) {
        const container = document.getElementById('category-attributes-container');

        if (!categoryId) {
            container.innerHTML = '<div class="md:col-span-2"><p class="text-sm text-gray-500">Select a category to load category-specific attributes</p></div>';
            return;
        }

        // Show loading
        container.innerHTML = '<div class="md:col-span-2"><p class="text-sm text-gray-500">Loading category attributes...</p></div>';

        // Fetch attributes
        const url = `${siteUrl}/admin/get-category-attributes?category_id=${categoryId}`;
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text.substring(0, 200));
                        throw new Error('Server returned non-JSON response');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Attributes response:', data);
                if (data.success) {
                    // Use categoryAttributes if available, otherwise fall back to attributes for backward compatibility
                    const categoryAttributes = data.categoryAttributes || (data.attributes || []);

                    if (categoryAttributes.length > 0) {
                        let html = '';

                        // Use category-specific base and dependent attributes if available, otherwise filter categoryAttributes
                        const baseAttributes = data.categoryBaseAttributes || categoryAttributes.filter(attr => !attr.depends_on);
                        const dependentAttributes = data.categoryDependentAttributes || categoryAttributes.filter(attr => attr.depends_on);

                        // Render base attributes first (no dependencies)
                        baseAttributes.forEach(attribute => {
                            const currentValue = existingValues[attribute.attribute_id] || '';
                            html += renderAttributeField(attribute, currentValue);
                        });

                        // Render dependent attributes (will be hidden initially)
                        dependentAttributes.forEach(attribute => {
                            const currentValue = existingValues[attribute.attribute_id] || '';
                            html += renderAttributeField(attribute, currentValue);
                        });

                        container.innerHTML = html;

                        // Set up dependency listeners after rendering
                        // Use categoryAttributeMap if available, otherwise build from categoryAttributes
                        const categoryAttributeMap = data.categoryAttributeMap || {};
                        if (Object.keys(categoryAttributeMap).length === 0 && categoryAttributes.length > 0) {
                            categoryAttributes.forEach(attr => {
                                categoryAttributeMap[attr.attribute_id] = attr;
                            });
                        }
                        setupAttributeDependencies(categoryAttributeMap);
                    } else {
                        container.innerHTML = '<div class="md:col-span-2"><p class="text-sm text-gray-500">No category-specific attributes defined for this category</p></div>';
                    }
                } else {
                    const errorMsg = data.message || 'Failed to load attributes';
                    console.error('Error in attributes response:', data);
                    container.innerHTML = `<div class="md:col-span-2"><p class="text-sm text-red-500">${escapeHtml(errorMsg)}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error loading attributes:', error);
                console.error('URL:', url);
                container.innerHTML = '<div class="md:col-span-2"><p class="text-sm text-red-500">Error loading attributes. Please try again.</p></div>';
            });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Set up attribute dependency rules (show/hide based on parent values)
    function setupAttributeDependencies(attributeMap) {
        // Find all dependent attributes
        const dependentFields = document.querySelectorAll('.attribute-field[data-depends-on]');

        dependentFields.forEach(field => {
            const dependsOnId = field.getAttribute('data-depends-on');
            const parentInput = document.querySelector(`#attribute_${dependsOnId}, [name="attribute_${dependsOnId}"]`);

            if (parentInput) {
                // Initial check
                checkDependency(field, parentInput, attributeMap);

                // Listen for changes on parent attribute
                parentInput.addEventListener('change', function () {
                    checkDependency(field, parentInput, attributeMap);
                });

                // Also listen for input events (for text fields)
                if (parentInput.tagName === 'INPUT' && parentInput.type !== 'select-one') {
                    parentInput.addEventListener('input', function () {
                        checkDependency(field, parentInput, attributeMap);
                    });
                }
            }
        });
    }

    // Check if a dependent attribute should be shown based on parent value
    function checkDependency(field, parentInput, attributeMap) {
        const attributeId = field.getAttribute('data-attribute-id');
        const attribute = attributeMap[attributeId];

        if (!attribute || !attribute.show_when_decoded) {
            return;
        }

        const parentValue = parentInput.value ? parentInput.value.trim() : '';
        const condition = attribute.show_when_decoded;

        let shouldShow = false;

        // Simple value match: {"value": "Sports"}
        if (condition.value !== undefined) {
            shouldShow = parentValue === condition.value.trim();
        }
        // Array match: {"operator": "in", "values": ["Sports", "Formal"]}
        else if (condition.operator === 'in' && condition.values) {
            shouldShow = condition.values.some(val => val.trim() === parentValue);
        }
        // Not in: {"operator": "not_in", "values": ["Casual"]}
        else if (condition.operator === 'not_in' && condition.values) {
            shouldShow = !condition.values.some(val => val.trim() === parentValue);
        }

        // Show or hide the field
        if (shouldShow) {
            field.classList.remove('hidden');
            // Make required fields required again when shown
            const input = field.querySelector('.attribute-input');
            if (input && attribute.is_required) {
                input.required = true;
            }
        } else {
            field.classList.add('hidden');
            // Remove required when hidden to prevent validation errors
            const input = field.querySelector('.attribute-input');
            if (input) {
                input.required = false;
                input.value = ''; // Clear value when hidden
            }
        }
    }

    // Load category attributes when category is selected
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category_id');

        // Set up dependencies for common attributes (rendered server-side)
        <?php if (!empty($commonAttributes)): ?>
            const commonAttributeMap = {};
            <?php foreach ($commonAttributes as $attr): ?>
                commonAttributeMap[<?php echo $attr['attribute_id']; ?>] = {
                    attribute_id: <?php echo $attr['attribute_id']; ?>,
                    depends_on: <?php echo $attr['depends_on'] ?? 'null'; ?>,
                    show_when_decoded: <?php echo !empty($attr['show_when_decoded']) ? json_encode($attr['show_when_decoded']) : 'null'; ?>,
                    is_required: <?php echo $attr['is_required'] ? 'true' : 'false'; ?>
                };
            <?php endforeach; ?>
            setupAttributeDependencies(commonAttributeMap);
        <?php endif; ?>

        if (categorySelect) {
            // Set up change event listener
            categorySelect.addEventListener('change', function () {
                // Get existing values from form before loading new attributes
                const existingValues = getExistingAttributeValues();
                loadCategoryAttributes(this.value, existingValues);
            });

            // Load attributes on page load if category is already selected (for edit mode or form errors)
            if (categorySelect.value) {
                // Get existing attribute values
                const existingValues = {};
                <?php if (!empty($productAttributes)): ?>
                    <?php foreach ($productAttributes as $pa): ?>
                        existingValues[<?php echo $pa['attribute_id']; ?>] = '<?php echo addslashes($pa['attribute_value']); ?>';
                    <?php endforeach; ?>
                <?php endif; ?>
                loadCategoryAttributes(categorySelect.value, existingValues);
            }
            // Note: Common attributes are already rendered server-side, so they're always visible
        }
    });

    // Variant management
    function addVariant() {
        const container = document.getElementById('variants-container');
        const variantHtml = `
        <div class="variant-item mb-4 p-4 border border-gray-200 rounded-lg" data-variant-index="${variantIndex}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Size *</label>
                    <input type="text" name="variants[${variantIndex}][size]" required
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Color</label>
                    <input type="text" name="variants[${variantIndex}][color]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Stock *</label>
                    <input type="number" name="variants[${variantIndex}][stock_quantity]" required min="0"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Additional Price (₹)</label>
                    <input type="number" step="0.01" name="variants[${variantIndex}][additional_price]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-0 focus:outline-none">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeVariant(${variantIndex})" 
                            class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', variantHtml);
        variantIndex++;
    }

    function removeVariant(index) {
        const variantItem = document.querySelector(`[data-variant-index="${index}"]`);
        if (variantItem) {
            variantItem.remove();
        }
    }

    // Toggle image guidelines
    function toggleImageGuidelines() {
        const content = document.getElementById('image-guidelines-content');
        const icon = document.getElementById('guidelines-icon');

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // Toggle Zoom Upload Field
    function toggleZoomUpload() {
        const toggle = document.getElementById('enable-zoom-toggle');
        const container = document.getElementById('zoom-upload-container');
        const bg = document.getElementById('zoom-toggle-bg');
        const dot = document.getElementById('zoom-toggle-dot');

        if (toggle.checked) {
            container.classList.remove('hidden');
            bg.classList.remove('bg-gray-200');
            bg.classList.add('bg-pink-500');
            dot.classList.add('translate-x-full');
        } else {
            container.classList.add('hidden');
            bg.classList.remove('bg-pink-500');
            bg.classList.add('bg-gray-200');
            dot.classList.remove('translate-x-full');

            // Clear the file input when disabled
            const input = container.querySelector('input[type="file"]');
            if (input) input.value = '';
        }
    }

    // Image deletion
    async function deleteProductImage(imageId, productId) {
        const result = await showConfirm(
            'Delete Image',
            'Are you sure you want to delete this image? This action cannot be undone.',
            'Yes, Delete',
            'Cancel',
            'warning'
        );

        if (!result.isConfirmed) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('product_id', productId);

            const response = await fetch(siteUrl + '/admin/product-image-delete', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Remove the image element from DOM
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.transition = 'opacity 0.3s';
                    imageElement.style.opacity = '0';
                    setTimeout(() => {
                        imageElement.remove();

                        // Show message if no images left
                        const existingImages = document.getElementById('existing-images');
                        if (existingImages && existingImages.children.length === 0) {
                            existingImages.parentElement.innerHTML = '<p class="text-sm text-gray-500">No images uploaded yet.</p>';
                        }
                    }, 300);
                }

                // Show success message
                showToast(data.message || 'Image deleted successfully', 'success');
            } else {
                showToast(data.message || 'Failed to delete image', 'error');
            }
        } catch (error) {
            console.error('Error deleting image:', error);
            showToast('An error occurred. Please try again.', 'error');
        }
    }

    // Tab Scrolling Logic
    function scrollTabs(direction) {
        const container = document.getElementById('tabs-container');
        if (!container) return;

        // Calculate scroll amount (approximately one tab width + gap)
        // We can try to find the first visible tab width
        const firstTab = container.querySelector('.tab-btn');
        const scrollAmount = firstTab ? firstTab.offsetWidth + 12 : 150; // 12px for gap

        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }

    // Update Scroll Buttons Visibility
    function updateScrollButtons() {
        const container = document.getElementById('tabs-container');
        const leftBtn = document.getElementById('tab-scroll-left');
        const rightBtn = document.getElementById('tab-scroll-right');

        if (!container || !leftBtn || !rightBtn) return;

        // Check if scrollable
        if (container.scrollWidth <= container.clientWidth) {
            leftBtn.classList.add('hidden');
            rightBtn.classList.add('hidden');
            return;
        }

        // Left Button
        if (container.scrollLeft <= 5) { // Small buffer
            leftBtn.classList.add('hidden');
        } else {
            leftBtn.classList.remove('hidden');
        }

        // Right Button
        // Use Math.ceil or buffer to account for sub-pixel rounding
        if (Math.ceil(container.scrollLeft + container.clientWidth) >= container.scrollWidth - 5) {
            rightBtn.classList.add('hidden');
        } else {
            rightBtn.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('tabs-container');
        if (container) {
            container.addEventListener('scroll', updateScrollButtons);
            window.addEventListener('resize', updateScrollButtons);
            // Initial check
            updateScrollButtons();
            
            // Re-check after a brief delay ensuring layout is settled
            setTimeout(updateScrollButtons, 100);
        }
    });
</script>