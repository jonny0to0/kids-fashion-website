<?php
$pageTitle = $action . ' Product';

// Include breadcrumb and back button helpers
require_once __DIR__ . '/_breadcrumb.php';
require_once __DIR__ . '/_back_button.php';
?>

<div class="container mx-auto px-2 md:px-4 py-4 md:py-8">
    <!-- px-4 -->
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
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

    <div class="bg-white rounded-lg shadow-md p-3 md:p-8 max-w-4xl mx-auto w-full">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Product Name *</label>
                    <input type="text" name="name" required
                        value="<?php echo htmlspecialchars($product['name'] ?? ($data['name'] ?? '')); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Category *</label>
                    <select name="category_id" id="category_id" required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Category</option>
                        <?php if (!empty($categories)): ?>
                            <?php
                            // Organize categories by parent
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

                            // Display parent categories and their children
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
                    <label class="block text-gray-700 font-medium mb-2">SKU</label>
                    <input type="text" name="sku"
                        value="<?php echo htmlspecialchars($product['sku'] ?? ($data['sku'] ?? '')); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Short Description</label>
                    <textarea name="short_description" rows="2"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"><?php echo htmlspecialchars($product['short_description'] ?? ($data['short_description'] ?? '')); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" rows="5"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"><?php echo htmlspecialchars($product['description'] ?? ($data['description'] ?? '')); ?></textarea>
                </div>

                <!-- Pricing -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 mt-4 md:mb-4 md:mt-6">Pricing & Inventory</h2>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Price (₹) *</label>
                    <input type="number" name="price" step="0.01" required
                        value="<?php echo $product['price'] ?? ($data['price'] ?? ''); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Sale Price (₹)</label>
                    <input type="number" name="sale_price" step="0.01"
                        value="<?php echo $product['sale_price'] ?? ($data['sale_price'] ?? ''); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Cost Price (₹)</label>
                    <input type="number" name="cost_price" step="0.01"
                        value="<?php echo $product['cost_price'] ?? ($data['cost_price'] ?? ''); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" required
                        value="<?php echo $product['stock_quantity'] ?? ($data['stock_quantity'] ?? 0); ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <!-- Product Attributes -->
                <div class="md:col-span-2">
                    <!-- <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Product Attributes</h2> -->

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
                        $dataAttrs .= ' class="attribute-input w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '"';

                        if ($attribute['attribute_type'] === 'select' && !empty($attribute['options'])) {
                            $fieldHtml = '<select name="' . $fieldName . '" id="' . $fieldId . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                            $fieldHtml .= '<option value="">Select ' . htmlspecialchars($attribute['attribute_name']) . '</option>';
                            foreach ($attribute['options'] as $option) {
                                $selected = ($currentValue === $option) ? 'selected' : '';
                                $fieldHtml .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                            }
                            $fieldHtml .= '</select>';
                        } elseif ($attribute['attribute_type'] === 'textarea') {
                            $fieldHtml = '<textarea name="' . $fieldName . '" id="' . $fieldId . '" rows="3" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">' . htmlspecialchars($currentValue) . '</textarea>';
                        } elseif ($attribute['attribute_type'] === 'number') {
                            $fieldHtml = '<input type="number" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                        } elseif ($attribute['attribute_type'] === 'color') {
                            $fieldHtml = '<div class="flex gap-2">';
                            $fieldHtml .= '<input type="color" name="' . $fieldName . '_color" id="' . $fieldId . '_color" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' class="h-10 w-20 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500">';
                            $fieldHtml .= '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" placeholder="Color name" ' . $required . ' ' . $dataAttrs . ' class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                            $fieldHtml .= '</div>';
                        } else {
                            $fieldHtml = '<input type="text" name="' . $fieldName . '" id="' . $fieldId . '" value="' . htmlspecialchars($currentValue) . '" ' . $required . ' ' . $dataAttrs . ' class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="' . htmlspecialchars($attribute['attribute_id']) . '">';
                        }

                        return $fieldHtml;
                    }

                    // Helper function to check if attribute field should span full width
                    function shouldSpanFullWidth($attribute)
                    {
                        return $attribute['attribute_type'] === 'textarea';
                    }
                    ?>

                    <!-- Common Attributes (Always visible) -->
                    <?php if (!empty($commonAttributes)): ?>
                        <div class="mb-4 md:mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Other - </h3>
                            <!-- Common Attributes -->
                            <!-- <p class="text-sm text-gray-500 mb-4">These attributes are available for all products</p> -->
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

                    <!-- Category-Specific Attributes (Dynamic based on Category) -->
                    <div class="mb-4 md:mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Category-Specific Attributes</h3>
                        <p class="text-sm text-gray-500 mb-4">Select a category to load relevant attributes</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="category-attributes-container">
                            <?php
                            // Show existing category attributes if available (works for both Add and Edit)
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
                                    <p class="text-sm text-gray-500">Select a category to load category-specific attributes
                                    </p>
                                </div>
                                <?php
                            endif;
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Status & Flags -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 mt-4 md:mb-4 md:mt-6">Status & Flags</h2>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Status</label>
                    <select name="status"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="active" <?php echo ($product['status'] ?? ($data['status'] ?? 'active')) == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($product['status'] ?? ($data['status'] ?? '')) == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="out_of_stock" <?php echo ($product['status'] ?? ($data['status'] ?? '')) == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <div class="flex flex-col md:flex-row gap-3 md:gap-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? ($data['is_featured'] ?? 0)) ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-gray-700">Featured Product</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_new_arrival" value="1" <?php echo ($product['is_new_arrival'] ?? ($data['is_new_arrival'] ?? 0)) ? 'checked' : ''; ?>
                                class="mr-2">
                            <span class="text-gray-700">New Arrival</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_bestseller" value="1" <?php echo ($product['is_bestseller'] ?? ($data['is_bestseller'] ?? 0)) ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-gray-700">Bestseller</span>
                        </label>
                    </div>
                </div>

                <!-- Product Variants -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 mt-4 md:mb-4 md:mt-6">Product Variants</h2>
                    <p class="text-sm text-gray-500 mb-4">Add size and color combinations with individual stock and
                        pricing</p>

                    <div id="variants-container">
                        <?php if (!empty($variants)): ?>
                            <?php foreach ($variants as $index => $variant): ?>
                                <div class="variant-item mb-4 p-4 border border-gray-200 rounded-lg"
                                    data-variant-index="<?php echo $index; ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <input type="hidden" name="variants[<?php echo $index; ?>][variant_id]"
                                            value="<?php echo $variant['variant_id']; ?>">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Size *</label>
                                            <input type="text" name="variants[<?php echo $index; ?>][size]"
                                                value="<?php echo htmlspecialchars($variant['size']); ?>" required
                                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Color</label>
                                            <input type="text" name="variants[<?php echo $index; ?>][color]"
                                                value="<?php echo htmlspecialchars($variant['color'] ?? ''); ?>"
                                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Stock *</label>
                                            <input type="number" name="variants[<?php echo $index; ?>][stock_quantity]"
                                                value="<?php echo $variant['stock_quantity']; ?>" required min="0"
                                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2 text-sm">Additional Price
                                                (₹)</label>
                                            <input type="number" step="0.01"
                                                name="variants[<?php echo $index; ?>][additional_price]"
                                                value="<?php echo $variant['additional_price']; ?>"
                                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" onclick="removeVariant(<?php echo $index; ?>)"
                                                class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" onclick="addVariant()"
                        class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm font-medium">
                        + Add Variant
                    </button>
                </div>

                <!-- Images -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 mt-4 md:mb-4 md:mt-6">Product Images</h2>

                    <!-- Image Guidelines (Collapsible) -->
                    <div class="mb-4 md:mb-6 bg-pink-50 border border-pink-200 rounded-lg p-3 md:p-4">
                        <button type="button" onclick="toggleImageGuidelines()"
                            class="w-full flex items-center justify-between text-left">
                            <span class="font-semibold text-pink-800 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Image Upload Guidelines
                            </span>
                            <svg id="guidelines-icon" class="w-5 h-5 text-pink-800 transform transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="image-guidelines-content" class="hidden mt-4 text-sm text-gray-700 space-y-3">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">📸 Recommended Images (Minimum 4, Best:
                                    6-7):</h4>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li><strong>Main Image (Required):</strong> Pure white background (#FFFFFF), square
                                        (1:1), product covers 85-90%</li>
                                    <li><strong>Zoom Image:</strong> High-resolution version (2000x2000px+) of the Main
                                        Image.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Images -->
                    <?php if (!empty($images) && is_array($images) && !empty($product)): ?>
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-3">Current Images</label>
                            <div class="space-y-4" id="existing-images">
                                <?php foreach ($images as $image): ?>
                                    <div class="flex flex-col md:flex-row gap-4 p-4 border border-gray-200 rounded-lg items-center bg-gray-50"
                                        data-image-id="<?php echo $image['image_id']; ?>">
                                        <!-- Main Thumb -->
                                        <div class="relative w-24 h-24 flex-shrink-0 border bg-white">
                                            <img src="<?php echo SITE_URL . $image['image_url']; ?>" alt="Product Image"
                                                class="w-full h-full object-contain">
                                            <?php if ($image['is_primary']): ?>
                                                <span
                                                    class="absolute top-0 left-0 bg-pink-600 text-white text-[10px] px-1 rounded-br">Primary</span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Zoom Info & Upload -->
                                        <div class="flex-1 w-full relative">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <!-- Zoom Status -->
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Zoom Image
                                                        Status</label>
                                                    <?php if (!empty($image['zoom_image_url'])): ?>
                                                        <div class="flex items-center text-sm text-green-600 font-medium">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Uploaded
                                                            <a href="<?php echo SITE_URL . $image['zoom_image_url']; ?>"
                                                                target="_blank"
                                                                class="text-xs text-pink-600 underline ml-2">View</a>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-sm text-gray-500 italic">None set</div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Upload New Zoom -->
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Update Zoom
                                                        Image</label>
                                                    <input type="file"
                                                        name="existing_zoom_images[<?php echo $image['image_id']; ?>]"
                                                        accept="image/*"
                                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Action -->
                                        <button type="button"
                                            onclick="deleteProductImage(<?php echo $image['image_id']; ?>, <?php echo $product['product_id']; ?>)"
                                            class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50"
                                            title="Delete Image">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Upload New Images -->
                    <div class="pt-4 md:pt-6">
                        <h3 class="font-bold text-gray-800 mb-3 md:mb-4">Add New Images</h3>


                        <div id="new-images-container" class="space-y-4">
                            <!-- Dynamic rows will be added here -->
                        </div>

                        <button type="button" onclick="addNewImageRow()"
                            class="mt-4 flex items-center text-pink-600 font-semibold hover:text-pink-700">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Image Row
                        </button>
                    </div>

                    <script>
                        // Determine if we are in Edit Mode
                        const isEditMode = <?php echo isset($product['product_id']) ? 'true' : 'false'; ?>;

                        // Initialize with one row
                        document.addEventListener('DOMContentLoaded', function () {
                            // Only auto-add a row if NOT in edit mode, or if container is empty
                            if (document.getElementById('new-images-container').children.length === 0) {
                                // If in edit mode, we generally don't want to force an empty row unless desired?
                                // User request: "optional only for product edit page"
                                // If we interpret this as "the input itself should not be required", we can still show it.
                                addNewImageRow();
                            }
                        });

                        function addNewImageRow() {
                            const container = document.getElementById('new-images-container');
                            const index = container.children.length;

                            // Determine validation based on mode
                            // Edit Mode: All optional
                            // Add Mode: Required
                            const requiredAttr = isEditMode ? '' : 'required';
                            const requiredLabel = isEditMode ? '' : ' <span class="text-red-500">*</span>';
                            const rowLabel = index + 1;

                            const row = document.createElement('div');
                            // Mobile: Minimal clean row | Desktop: Grid
                            row.className = 'group relative border-b border-gray-100 pb-4 mb-4 last:border-0 last:mb-0 last:pb-0';

                            row.innerHTML = `
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm font-semibold text-gray-500">Image Set #${rowLabel}</span>
                                    ${index > 0 || isEditMode ? `
                                    <button type="button" onclick="this.closest('.group').remove()" 
                                        class="text-red-500 hover:text-red-700 p-1 transition-colors" title="Remove Image Set">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    ` : ''}
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700">Main Image${requiredLabel}</label>
                                        <input type="file" name="new_images[${index}][main]" accept="image/*" ${requiredAttr}
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-full file:border-0
                                                      file:text-xs file:font-semibold
                                                      file:bg-pink-50 file:text-pink-700
                                                      hover:file:bg-pink-100
                                                      cursor-pointer border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-sm font-medium text-gray-700">Zoom Image <span class="text-gray-400 font-normal">(Optional)</span></label>
                                        <input type="file" name="new_images[${index}][zoom]" accept="image/*"
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-full file:border-0
                                                      file:text-xs file:font-semibold
                                                      file:bg-gray-100 file:text-gray-700
                                                      hover:file:bg-gray-200
                                                      cursor-pointer border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors">
                                    </div>
                                </div>
                            `;

                            container.appendChild(row);
                        }
                    </script>

                </div>
            </div>

            <div class="mt-6 md:mt-8 flex flex-col sm:flex-row gap-4">
                <button type="submit"
                    class="w-full sm:w-auto bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-bold">
                    <?php echo $action; ?> Product
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/products"
                    class="w-full sm:w-auto text-center bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 font-bold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const siteUrl = window.SITE_URL || '<?php echo SITE_URL; ?>';
    let variantIndex = <?php echo !empty($variants) ? count($variants) : 0; ?>;

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
            dataAttrs = `data-depends-on="${dependsOn}" data-attribute-id="${attribute.attribute_id}"`;
            if (showWhen) {
                dataAttrs += ` data-show-when='${JSON.stringify(showWhen)}'`;
            }
        }

        let inputHtml = '';
        let inputId = `attribute_${attribute.attribute_id}`;

        if (attributeType === 'select' && options.length > 0) {
            inputHtml = `<select name="${inputId}" id="${inputId}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="${attribute.attribute_id}">
            <option value="">Select ${escapeHtml(attribute.attribute_name)}</option>`;
            options.forEach(option => {
                const optionValue = escapeHtml(String(option));
                inputHtml += `<option value="${optionValue}" ${currentValue === option ? 'selected' : ''}>${optionValue}</option>`;
            });
            inputHtml += '</select>';
        } else if (attributeType === 'textarea') {
            inputHtml = `<textarea name="${inputId}" id="${inputId}" rows="3" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="${attribute.attribute_id}">${escapeHtml(currentValue)}</textarea>`;
        } else if (attributeType === 'number') {
            inputHtml = `<input type="number" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="${attribute.attribute_id}">`;
        } else if (attributeType === 'color') {
            inputHtml = `<div class="flex gap-2">
            <input type="color" name="${inputId}_color" id="${inputId}_color" value="${escapeHtml(currentValue)}" ${required} class="h-10 w-20 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-500">
            <input type="text" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" placeholder="Color name" ${required} ${dataAttrs} class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="${attribute.attribute_id}">
        </div>`;
        } else {
            inputHtml = `<input type="text" name="${inputId}" id="${inputId}" value="${escapeHtml(currentValue)}" ${required} ${dataAttrs} class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 attribute-input" data-attribute-id="${attribute.attribute_id}">`;
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
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Color</label>
                    <input type="text" name="variants[${variantIndex}][color]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Stock *</label>
                    <input type="number" name="variants[${variantIndex}][stock_quantity]" required min="0"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2 text-sm">Additional Price (₹)</label>
                    <input type="number" step="0.01" name="variants[${variantIndex}][additional_price]"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
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
</script>