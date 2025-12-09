<?php
$pageTitle = $action . ' Category';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Category</h1>
        <a href="<?php echo SITE_URL; ?>/admin/categories" class="text-pink-600 hover:underline mt-2 inline-block">← Back to Categories</a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Category Name *</label>
                    <input type="text" name="name" required
                           value="<?php echo htmlspecialchars($category['name'] ?? ($data['name'] ?? '')); ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"><?php echo htmlspecialchars($category['description'] ?? ($data['description'] ?? '')); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Parent Category</label>
                    <select name="parent_id"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">None (Top Level)</option>
                        <?php foreach ($parentCategories as $parent): ?>
                            <?php if ($category && $category['category_id'] != $parent['category_id']): ?>
                                <option value="<?php echo $parent['category_id']; ?>"
                                        <?php echo ($category['parent_id'] ?? ($data['parent_id'] ?? '')) == $parent['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($parent['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Display Order</label>
                        <input type="number" name="display_order"
                               value="<?php echo $category['display_order'] ?? ($data['display_order'] ?? 0); ?>"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <div class="mt-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                       <?php echo ($category['is_active'] ?? ($data['is_active'] ?? true)) ? 'checked' : ''; ?>
                                       class="mr-2">
                                <span class="text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-bold">
                    <?php echo $action; ?> Category
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/categories" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 font-bold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>


