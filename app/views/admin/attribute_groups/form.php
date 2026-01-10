<?php
$pageTitle = $action . ' Attribute Group';

// Include breadcrumb and back button helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_back_button.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <?php
        // Render breadcrumb
        renderBreadcrumb([
            ['label' => 'Dashboard', 'url' => '/admin'],
            ['label' => 'Attribute Groups', 'url' => '/admin/attribute-groups'],
            ['label' => $action . ' Attribute Group']
        ]);
        ?>
        
        <div class="mb-6">
            <div class="flex items-center">
                <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Attribute Group</h1>
            </div>
            <p class="text-gray-600 mt-2">Create or edit an attribute group to organize related attributes</p>
            <?php renderBackButton('Attribute Groups', '/admin/attribute-groups', 'top-left'); ?>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <ul class="list-disc list-inside text-red-700">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <div class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Group Name *</label>
                    <input type="text" name="group_name" required
                           value="<?php echo htmlspecialchars($group['group_name'] ?? ($data['group_name'] ?? '')); ?>"
                           placeholder="e.g., Common, Fashion Basic, Electronics Specs"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-sm text-gray-500 mt-1">A unique name for this attribute group</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" rows="3"
                              placeholder="Describe what attributes belong to this group..."
                              class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"><?php echo htmlspecialchars($group['description'] ?? ($data['description'] ?? '')); ?></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Display Order</label>
                        <input type="number" name="display_order" 
                               value="<?php echo $group['display_order'] ?? ($data['display_order'] ?? 0); ?>"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="is_active" 
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="1" <?php echo (($group['is_active'] ?? ($data['is_active'] ?? 1)) == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo (($group['is_active'] ?? ($data['is_active'] ?? 1)) == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium">
                        <?php echo $action; ?> Group
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

