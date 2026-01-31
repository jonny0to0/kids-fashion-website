<?php
$pageTitle = 'Manage Attribute Groups';

// Include breadcrumb helper
require_once __DIR__ . '/../_breadcrumb.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Attribute Groups']
    ]);
    ?>
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manage Attribute Groups</h1>
            <p class="text-gray-600 mt-2">Organize attributes into reusable groups that can be assigned to categories</p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/assign" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                Assign to Categories
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/add" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium">
                + Add New Group
            </a>
        </div>
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
                    <strong>How it works:</strong> Attribute groups allow you to organize attributes (e.g., "Common", "Fashion Basic") 
                    and assign them to categories. Child categories automatically inherit parent category's attribute groups.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Attribute Groups List -->
    <?php if (empty($groups)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">No attribute groups found.</p>
            <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/add" class="text-pink-600 hover:underline mt-2 inline-block">
                Create your first attribute group →
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attributes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($groups as $group): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($group['description'] ?? '—'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $group['attribute_count'] ?? 0; ?> attribute<?php echo ($group['attribute_count'] ?? 0) !== 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $group['display_order']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($group['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/edit/<?php echo $group['group_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/attribute-groups/delete/<?php echo $group['group_id']; ?>" 
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Are you sure you want to delete this attribute group? This action cannot be undone.');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

