<?php
$pageTitle = 'Manage Category Attributes';

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
        ['label' => 'Attributes']
    ]);
    ?>
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div class="flex-1 w-full md:w-auto">
            <h1 class="text-3xl font-bold text-gray-800">Manage Category Attributes</h1>
            <p class="text-gray-600 mt-2">Define attributes for each category (e.g., Size, Color, Material)</p>
        </div>
        <div class="w-full md:w-auto">
            <?php
            renderQuickActions([
                [
                    'type' => 'primary',
                    'icon' => 'plus',
                    'label' => 'Add Attribute',
                    'url' => '/admin/attributes/add',
                    'tooltip' => 'Create a new attribute',
                    'aria-label' => 'Add new attribute'
                ],
                [
                    'type' => 'secondary',
                    'icon' => 'settings',
                    'label' => 'Attribute Groups',
                    'url' => '/admin/attribute-groups',
                    'tooltip' => 'Manage attribute groups',
                    'aria-label' => 'Manage attribute groups'
                ]
            ], 'top-right');
            ?>
        </div>
    </div>
    
    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/attributes" class="flex gap-4 flex-wrap">
            <input type="text" name="search" placeholder="Search attributes..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
            <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Categories</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $categoryFilter == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                Search
            </button>
            <?php if (!empty($search) || $categoryFilter > 0): ?>
                <a href="<?php echo SITE_URL; ?>/admin/attributes" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Attributes List -->
    <?php if (empty($groupedAttributes)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">No attributes found.</p>
            <a href="<?php echo SITE_URL; ?>/admin/attributes/add" class="text-pink-600 hover:underline mt-2 inline-block">
                Add your first attribute →
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($groupedAttributes as $group): ?>
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                <div class="bg-pink-50 px-6 py-4 border-b border-pink-200">
                    <h2 class="text-xl font-bold text-gray-800">
                        <?php echo htmlspecialchars($group['group_name']); ?>
                        <span class="text-sm font-normal text-gray-600 ml-2">
                            (<?php echo count($group['attributes']); ?> attribute<?php echo count($group['attributes']) !== 1 ? 's' : ''; ?>)
                        </span>
                        <?php if ($group['group_type'] === 'group'): ?>
                            <span class="text-xs font-normal text-pink-600 ml-2">(Attribute Group)</span>
                        <?php endif; ?>
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Options</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($group['attributes'] as $attribute): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($attribute['attribute_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($attribute['attribute_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($attribute['attribute_type'] === 'select' && !empty($attribute['options'])): ?>
                                            <div class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars(implode(', ', array_slice($attribute['options'], 0, 3))); ?>
                                                <?php if (count($attribute['options']) > 3): ?>
                                                    <span class="text-gray-400">+<?php echo count($attribute['options']) - 3; ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="attribute-required-checkbox w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500 focus:ring-2" 
                                                   data-attribute-id="<?php echo $attribute['attribute_id']; ?>"
                                                   <?php echo $attribute['is_required'] ? 'checked' : ''; ?>
                                                   onchange="toggleAttributeRequired(<?php echo $attribute['attribute_id']; ?>, this.checked)">
                                            <span class="ml-2 text-sm text-gray-700">
                                                <?php echo $attribute['is_required'] ? 'Required' : 'Optional'; ?>
                                            </span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo $attribute['display_order']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($attribute['is_active']): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="<?php echo SITE_URL; ?>/admin/attributes/edit/<?php echo $attribute['attribute_id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <?php if ($attribute['is_active']): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/attributes/deactivate/<?php echo $attribute['attribute_id']; ?>" 
                                                   class="text-yellow-600 hover:text-yellow-900"
                                                   onclick="return confirm('Are you sure you want to deactivate this attribute?');">Deactivate</a>
                                            <?php else: ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/attributes/activate/<?php echo $attribute['attribute_id']; ?>" 
                                                   class="text-green-600 hover:text-green-900">Activate</a>
                                            <?php endif; ?>
                                            <a href="<?php echo SITE_URL; ?>/admin/attributes/delete/<?php echo $attribute['attribute_id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure you want to delete this attribute? This action cannot be undone.');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleAttributeRequired(attributeId, isRequired) {
    // Find the checkbox and related elements
    const checkbox = document.querySelector(`input[data-attribute-id="${attributeId}"]`);
    if (!checkbox) {
        console.error('Checkbox not found for attribute ID:', attributeId);
        return;
    }
    
    const label = checkbox.closest('label');
    const statusText = label.querySelector('span');
    const originalState = !isRequired; // Store original state for potential revert
    
    // Show loading state
    checkbox.disabled = true;
    statusText.textContent = 'Updating...';
    
    // Make AJAX request
    fetch('<?php echo SITE_URL; ?>/admin/attributes/toggle-required/' + attributeId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            is_required: isRequired ? 1 : 0
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update status text based on the new state
            statusText.textContent = isRequired ? 'Required' : 'Optional';
        } else {
            // Revert checkbox state on error
            checkbox.checked = originalState;
            statusText.textContent = originalState ? 'Required' : 'Optional';
            alert(data.message || 'Failed to update attribute required status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert checkbox state on error
        checkbox.checked = originalState;
        statusText.textContent = originalState ? 'Required' : 'Optional';
        alert('An error occurred while updating the attribute. Please try again.');
    })
    .finally(() => {
        // Re-enable checkbox
        checkbox.disabled = false;
    });
}
</script>

