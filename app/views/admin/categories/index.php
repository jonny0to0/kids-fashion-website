<?php
$pageTitle = 'Manage Categories';

// Include breadcrumb and quick actions helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_quick_actions.php';
?>

<?php echo getQuickActionsStyles(); ?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
        ['label' => 'Categories']
    ]);
    ?>
    
    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-8">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-800">Manage Categories</h1>
            <p class="text-gray-600 mt-2">Add, edit, delete, or activate/deactivate categories</p>
            <p class="text-xs text-gray-400 mt-1">Data fetched directly from database • Always up-to-date</p>
        </div>
        <div class="w-full sm:w-auto sm:ml-4">
            <?php
            renderQuickActions([
                [
                    'type' => 'primary',
                    'icon' => 'plus',
                    'label' => 'Add Category',
                    'url' => '/admin/categories/add',
                    'tooltip' => 'Creates a new parent category',
                    'aria-label' => 'Add new category'
                ],
                [
                    'type' => 'secondary',
                    'icon' => 'refresh',
                    'label' => 'Refresh',
                    'url' => '/admin/categories',
                    'tooltip' => 'Refresh categories list',
                    'aria-label' => 'Refresh categories'
                ]
            ], 'top-right');
            ?>
        </div>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/categories" class="flex gap-4 flex-wrap">
            <input type="text" name="search" placeholder="Search categories by name or slug..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                Search
            </button>
            <?php if (!empty($search) || !empty($statusFilter)): ?>
                <a href="<?php echo SITE_URL; ?>/admin/categories" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (isset($error) && $error): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-red-800 font-medium"><?php echo htmlspecialchars($error); ?></p>
                                <p class="text-red-600 text-sm mt-2">Please refresh the page or contact support if the problem persists.</p>
                            </div>
                        </td>
                    </tr>
                <?php elseif (empty($categories)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <p class="text-lg font-medium mb-2">No categories found</p>
                                <p class="text-sm text-gray-600 mb-4">
                                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                                        Try adjusting your search or filter criteria.
                                    <?php else: ?>
                                        Get started by adding your first category.
                                    <?php endif; ?>
                                </p>
                                <a href="<?php echo SITE_URL; ?>/admin/categories/add" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 font-medium">
                                    + Add New Category
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($category['image'])): ?>
                                    <img src="<?php echo SITE_URL . $category['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>"
                                         class="w-16 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['name']); ?></div>
                                <div class="text-sm text-gray-500">Slug: <?php echo htmlspecialchars($category['slug']); ?></div>
                                <?php if ($category['child_count'] > 0): ?>
                                    <div class="text-xs text-blue-600 mt-1"><?php echo $category['child_count']; ?> sub-category(ies)</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if (!empty($category['parent_id'])) {
                                    $parentCategoryModel = new Category();
                                    $parentCategory = $parentCategoryModel->find($category['parent_id']);
                                    echo $parentCategory ? htmlspecialchars($parentCategory['name']) : 'N/A';
                                } else {
                                    echo '<span class="text-gray-400">—</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($category['product_count'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $category['display_order']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $category['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                ?>">
                                    <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if (!empty($category['created_at'])) {
                                    $createdDate = new DateTime($category['created_at']);
                                    echo $createdDate->format('M d, Y');
                                    echo '<br><span class="text-xs text-gray-400">' . $createdDate->format('h:i A') . '</span>';
                                } else {
                                    echo '<span class="text-gray-400">—</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="<?php echo SITE_URL; ?>/admin/categories/edit/<?php echo $category['category_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <?php if ($category['is_active']): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/categories/deactivate/<?php echo $category['category_id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-900"
                                           onclick="return confirm('Are you sure you want to deactivate this category?');">Deactivate</a>
                                    <?php else: ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/categories/activate/<?php echo $category['category_id']; ?>" 
                                           class="text-green-600 hover:text-green-900"
                                           onclick="return confirm('Are you sure you want to activate this category?');">Activate</a>
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/admin/categories/delete/<?php echo $category['category_id']; ?>" 
                                       class="text-red-600 hover:text-red-900 delete-category-link"
                                       data-category-id="<?php echo $category['category_id']; ?>"
                                       data-category-name="<?php echo htmlspecialchars($category['name']); ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('.delete-category-link');
    deleteLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-category-id');
            const categoryName = this.getAttribute('data-category-name');
            const result = await showConfirm(
                'Delete Category',
                `Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.`,
                'Yes, Delete',
                'Cancel',
                'warning'
            );
            
            if (result.isConfirmed) {
                window.location.href = this.href;
            }
        });
    });
});
</script>

