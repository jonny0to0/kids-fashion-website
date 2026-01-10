<?php
$pageTitle = 'Assign Attribute Groups to Categories';

// Include breadcrumb and back button helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_back_button.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <?php
        // Render breadcrumb
        renderBreadcrumb([
            ['label' => 'Dashboard', 'url' => '/admin'],
            ['label' => 'Attribute Groups', 'url' => '/admin/attribute-groups'],
            ['label' => 'Assign to Categories']
        ]);
        ?>
        
        <div class="mb-6">
            <div class="flex items-center">
                <h1 class="text-3xl font-bold text-gray-800">Assign Attribute Groups to Categories</h1>
            </div>
            <p class="text-gray-600 mt-2">Assign attribute groups to categories. Child categories will automatically inherit parent's groups.</p>
            <?php renderBackButton('Attribute Groups', '/admin/attribute-groups', 'top-left'); ?>
        </div>
        
        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Inheritance:</strong> When you assign attribute groups to a parent category, 
                        all child categories automatically inherit those groups. You can also assign additional groups directly to child categories.
                    </p>
                </div>
            </div>
        </div>
        
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <div class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Category *</label>
                    <select name="category_id" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select a Category</option>
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
                        
                        foreach ($parentCategories as $parent): ?>
                            <option value="<?php echo $parent['category_id']; ?>">
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                            <?php if (isset($childCategories[$parent['category_id']])): ?>
                                <?php foreach ($childCategories[$parent['category_id']] as $child): ?>
                                    <option value="<?php echo $child['category_id']; ?>">
                                        &nbsp;&nbsp;└─ <?php echo htmlspecialchars($child['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Attribute Groups *</label>
                    <p class="text-sm text-gray-500 mb-3">Select one or more attribute groups to assign to this category</p>
                    <div class="border border-gray-300 rounded p-4 max-h-96 overflow-y-auto">
                        <?php if (empty($groups)): ?>
                            <p class="text-gray-500">No attribute groups available. <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/add" class="text-pink-600 hover:underline">Create one first</a>.</p>
                        <?php else: ?>
                            <?php foreach ($groups as $group): ?>
                                <label class="flex items-start p-3 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="group_ids[]" value="<?php echo $group['group_id']; ?>"
                                           class="mt-1 mr-3 h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($group['group_name']); ?>
                                        </div>
                                        <?php if (!empty($group['description'])): ?>
                                            <div class="text-sm text-gray-500 mt-1">
                                                <?php echo htmlspecialchars($group['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium">
                        Assign Groups
                    </button>
                    <a href="<?php echo SITE_URL; ?>/admin/attribute-groups" 
                       class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 font-medium">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

