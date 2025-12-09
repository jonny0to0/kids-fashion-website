<?php
$pageTitle = 'Manage Categories';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manage Categories</h1>
            <p class="text-gray-600 mt-2">Add, edit, or delete categories</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/category/add" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium">
            + Add New Category
        </a>
    </div>
    
    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No categories found. <a href="<?php echo SITE_URL; ?>/admin/category/add" class="text-pink-600 hover:underline">Add your first category</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['name']); ?></div>
                                <?php if (!empty($category['description'])): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($category['description'], 0, 50)); ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($category['slug']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($category['product_count'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $category['display_order'] ?? 0; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo ($category['is_active'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                ?>">
                                    <?php echo ($category['is_active'] ?? false) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo SITE_URL; ?>/admin/category/edit/<?php echo $category['category_id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                <a href="<?php echo SITE_URL; ?>/admin/category/delete/<?php echo $category['category_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this category?');"
                                   class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


