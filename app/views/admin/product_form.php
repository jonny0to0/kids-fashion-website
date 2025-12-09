<?php
$pageTitle = $action . ' Product';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Product</h1>
        <a href="<?php echo SITE_URL; ?>/admin/products" class="text-pink-600 hover:underline mt-2 inline-block">← Back to Products</a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8 max-w-4xl mx-auto">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Product Name *</label>
                    <input type="text" name="name" required
                           value="<?php echo htmlspecialchars($product['name'] ?? ($data['name'] ?? '')); ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Category *</label>
                    <select name="category_id" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($product['category_id'] ?? ($data['category_id'] ?? '')) == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
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
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Pricing & Inventory</h2>
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
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Product Attributes</h2>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Age Group *</label>
                    <select name="age_group" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Age Group</option>
                        <option value="0-1" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '0-1' ? 'selected' : ''; ?>>0-1 years</option>
                        <option value="1-3" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '1-3' ? 'selected' : ''; ?>>1-3 years</option>
                        <option value="3-5" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '3-5' ? 'selected' : ''; ?>>3-5 years</option>
                        <option value="5-8" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '5-8' ? 'selected' : ''; ?>>5-8 years</option>
                        <option value="8-12" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '8-12' ? 'selected' : ''; ?>>8-12 years</option>
                        <option value="12-14" <?php echo ($product['age_group'] ?? ($data['age_group'] ?? '')) == '12-14' ? 'selected' : ''; ?>>12-14 years</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Gender *</label>
                    <select name="gender" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Gender</option>
                        <option value="boy" <?php echo ($product['gender'] ?? ($data['gender'] ?? '')) == 'boy' ? 'selected' : ''; ?>>Boy</option>
                        <option value="girl" <?php echo ($product['gender'] ?? ($data['gender'] ?? '')) == 'girl' ? 'selected' : ''; ?>>Girl</option>
                        <option value="unisex" <?php echo ($product['gender'] ?? ($data['gender'] ?? '')) == 'unisex' ? 'selected' : ''; ?>>Unisex</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Brand</label>
                    <input type="text" name="brand"
                           value="<?php echo htmlspecialchars($product['brand'] ?? ($data['brand'] ?? '')); ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Material</label>
                    <input type="text" name="material"
                           value="<?php echo htmlspecialchars($product['material'] ?? ($data['material'] ?? '')); ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <!-- Status & Flags -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Status & Flags</h2>
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
                    <div class="flex gap-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1"
                                   <?php echo ($product['is_featured'] ?? ($data['is_featured'] ?? 0)) ? 'checked' : ''; ?>
                                   class="mr-2">
                            <span class="text-gray-700">Featured Product</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_new_arrival" value="1"
                                   <?php echo ($product['is_new_arrival'] ?? ($data['is_new_arrival'] ?? 0)) ? 'checked' : ''; ?>
                                   class="mr-2">
                            <span class="text-gray-700">New Arrival</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_bestseller" value="1"
                                   <?php echo ($product['is_bestseller'] ?? ($data['is_bestseller'] ?? 0)) ? 'checked' : ''; ?>
                                   class="mr-2">
                            <span class="text-gray-700">Bestseller</span>
                        </label>
                    </div>
                </div>
                
                <!-- Images -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Product Images</h2>
                    <label class="block text-gray-700 font-medium mb-2">Upload Images</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-sm text-gray-500 mt-1">You can select multiple images. The first image will be set as primary.</p>
                </div>
            </div>
            
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-bold">
                    <?php echo $action; ?> Product
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/products" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 font-bold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>


