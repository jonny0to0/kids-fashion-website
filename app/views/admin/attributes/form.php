<?php
$pageTitle = $action . ' Attribute';

// Include breadcrumb and back button helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_back_button.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Attributes', 'url' => '/admin/attributes'],
        ['label' => $action . ' Attribute']
    ]);
    ?>
    
    <div class="mb-8">
        <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Attribute</h1>
        </div>
        <?php renderBackButton('Attributes', '/admin/attributes', 'top-left'); ?>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8 max-w-3xl mx-auto">
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
            <div class="space-y-6">
                <!-- Attribute Group Selection (NEW - Recommended) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Attribute Group <span class="text-pink-600">(Recommended)</span></label>
                    <select name="group_id" id="group_id"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Attribute Group (Optional)</option>
                        <?php if (!empty($attributeGroups)): ?>
                            <?php foreach ($attributeGroups as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>"
                                        <?php echo ($attribute['group_id'] ?? ($data['group_id'] ?? '')) == $group['group_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                    <?php if (!empty($group['description'])): ?>
                                        - <?php echo htmlspecialchars($group['description']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        <strong>New System:</strong> Assign attribute to a group. Groups can be assigned to multiple categories with inheritance.
                        <a href="<?php echo SITE_URL; ?>/admin/attribute-groups" class="text-pink-600 hover:underline">Manage Groups</a>
                    </p>
                </div>
                
                <!-- Category Selection (LEGACY - for backward compatibility) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Category <span class="text-gray-400">(Legacy - use Attribute Group instead)</span></label>
                    <select name="category_id" id="category_id"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Category (Optional if Group selected)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($attribute['category_id'] ?? ($data['category_id'] ?? '')) == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        <strong>Legacy:</strong> Direct category assignment. Use Attribute Group above for better organization.
                    </p>
                </div>
                
                <script>
                // Ensure at least one of category_id or group_id is selected
                document.querySelector('form').addEventListener('submit', function(e) {
                    const categoryId = document.getElementById('category_id').value;
                    const groupId = document.getElementById('group_id').value;
                    if (!categoryId && !groupId) {
                        e.preventDefault();
                        alert('Please select either an Attribute Group or Category');
                        return false;
                    }
                });
                </script>
                
                <!-- Attribute Name -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Attribute Name *</label>
                    <input type="text" name="attribute_name" required
                           value="<?php echo htmlspecialchars($attribute['attribute_name'] ?? ($data['attribute_name'] ?? '')); ?>"
                           placeholder="e.g., Size, Color, Material"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-sm text-gray-500 mt-1">The display name for this attribute</p>
                </div>
                
                <!-- Attribute Type -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Attribute Type *</label>
                    <select name="attribute_type" id="attribute_type" required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="text" <?php echo ($attribute['attribute_type'] ?? ($data['attribute_type'] ?? 'text')) == 'text' ? 'selected' : ''; ?>>Text</option>
                        <option value="select" <?php echo ($attribute['attribute_type'] ?? ($data['attribute_type'] ?? '')) == 'select' ? 'selected' : ''; ?>>Select (Dropdown)</option>
                        <option value="number" <?php echo ($attribute['attribute_type'] ?? ($data['attribute_type'] ?? '')) == 'number' ? 'selected' : ''; ?>>Number</option>
                        <option value="textarea" <?php echo ($attribute['attribute_type'] ?? ($data['attribute_type'] ?? '')) == 'textarea' ? 'selected' : ''; ?>>Textarea</option>
                        <option value="color" <?php echo ($attribute['attribute_type'] ?? ($data['attribute_type'] ?? '')) == 'color' ? 'selected' : ''; ?>>Color</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">The input type for this attribute</p>
                </div>
                
                <!-- Options (for select type) -->
                <div id="options_field" style="display: none;">
                    <label class="block text-gray-700 font-medium mb-2">Options *</label>
                    <textarea name="attribute_options" id="attribute_options" rows="4"
                              placeholder='Enter options separated by commas (e.g., "Small, Medium, Large") or as JSON array (e.g., ["Small","Medium","Large"])'
                              class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"><?php 
                        $optionsValue = '';
                        if (!empty($attribute['attribute_options'])) {
                            $decoded = json_decode($attribute['attribute_options'], true);
                            if (is_array($decoded)) {
                                $optionsValue = implode(', ', $decoded);
                            } else {
                                $optionsValue = $attribute['attribute_options'];
                            }
                        } else if (!empty($data['attribute_options'])) {
                            $optionsValue = $data['attribute_options'];
                        }
                        echo htmlspecialchars($optionsValue);
                    ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">For select type: Enter options separated by commas (e.g., "S, M, L, XL")</p>
                </div>
                
                <!-- Display Order -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Display Order</label>
                    <input type="number" name="display_order" min="0"
                           value="<?php echo $attribute['display_order'] ?? ($data['display_order'] ?? 0); ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <p class="text-sm text-gray-500 mt-1">Lower numbers appear first (0 = first)</p>
                </div>
                
                <!-- Required -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_required" value="1"
                               <?php echo ($attribute['is_required'] ?? ($data['is_required'] ?? 0)) ? 'checked' : ''; ?>
                               class="mr-2">
                        <span class="text-gray-700">Required Attribute</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">If checked, products in this category must have a value for this attribute</p>
                </div>
                
                <!-- Active Status -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($attribute['is_active'] ?? ($data['is_active'] ?? 1)) ? 'checked' : ''; ?>
                               class="mr-2">
                        <span class="text-gray-700">Active</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Inactive attributes won't appear in product forms</p>
                </div>
                
                <!-- Rule-Based Attributes (Dependencies) -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rule-Based Attributes (Like Flipkart)</h3>
                    <p class="text-sm text-gray-500 mb-4">Configure conditional attributes that show/hide based on parent attribute values</p>
                    
                    <!-- Depends On (Parent Attribute) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Depends On (Parent Attribute)</label>
                        <select name="depends_on" id="depends_on"
                                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                            <option value="">None (Always visible)</option>
                            <?php 
                            // Use availableAttributes passed from controller, or get all attributes
                            $availableAttributes = $availableAttributes ?? [];
                            // Filter out current attribute if editing
                            if (!empty($attribute['attribute_id'])) {
                                $availableAttributes = array_filter($availableAttributes, function($attr) use ($attribute) {
                                    return $attr['attribute_id'] != $attribute['attribute_id'];
                                });
                            }
                            foreach ($availableAttributes as $attr): ?>
                                <option value="<?php echo $attr['attribute_id']; ?>"
                                        <?php echo ($attribute['depends_on'] ?? ($data['depends_on'] ?? '')) == $attr['attribute_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($attr['attribute_name']); ?>
                                    <?php if ($attr['attribute_type'] === 'select'): ?>
                                        (Select)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Select a parent attribute. This attribute will only show when the parent has a specific value.</p>
                    </div>
                    
                    <!-- Show When Condition -->
                    <div id="show_when_field" style="display: none;">
                        <label class="block text-gray-700 font-medium mb-2">Show When (Condition)</label>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Condition Type</label>
                                <select name="show_when_type" id="show_when_type"
                                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                                    <option value="value">Equals specific value</option>
                                    <option value="in">Is one of these values</option>
                                    <option value="not_in">Is NOT one of these values</option>
                                </select>
                            </div>
                            
                            <div id="show_when_value_field">
                                <label class="block text-sm text-gray-600 mb-1">Value</label>
                                <input type="text" name="show_when_value" id="show_when_value"
                                       placeholder="e.g., Sports"
                                       value="<?php 
                                       if (!empty($attribute['show_when_decoded'])) {
                                           echo htmlspecialchars($attribute['show_when_decoded']['value'] ?? '');
                                       } elseif (!empty($data['show_when_value'])) {
                                           echo htmlspecialchars($data['show_when_value']);
                                       }
                                       ?>"
                                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                                <p class="text-sm text-gray-500 mt-1">Enter the exact value that triggers showing this attribute</p>
                            </div>
                            
                            <div id="show_when_values_field" style="display: none;">
                                <label class="block text-sm text-gray-600 mb-1">Values (comma-separated)</label>
                                <input type="text" name="show_when_values" id="show_when_values"
                                       placeholder="e.g., Sports, Formal"
                                       value="<?php 
                                       if (!empty($attribute['show_when_decoded']) && !empty($attribute['show_when_decoded']['values'])) {
                                           echo htmlspecialchars(implode(', ', $attribute['show_when_decoded']['values']));
                                       } elseif (!empty($data['show_when_values'])) {
                                           echo htmlspecialchars($data['show_when_values']);
                                       }
                                       ?>"
                                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                                <p class="text-sm text-gray-500 mt-1">Enter multiple values separated by commas</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Metadata -->
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_filterable" value="1"
                                       <?php echo ($attribute['is_filterable'] ?? ($data['is_filterable'] ?? 0)) ? 'checked' : ''; ?>
                                       class="mr-2">
                                <span class="text-gray-700">Use for Filtering</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Allow customers to filter products by this attribute</p>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_variant" value="1"
                                       <?php echo ($attribute['is_variant'] ?? ($data['is_variant'] ?? 0)) ? 'checked' : ''; ?>
                                       class="mr-2">
                                <span class="text-gray-700">Use for Variants</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">This attribute creates product variants (e.g., Size, Color)</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 font-bold">
                    <?php echo $action; ?> Attribute
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/attributes" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 font-bold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide options field based on attribute type
document.getElementById('attribute_type').addEventListener('change', function() {
    const optionsField = document.getElementById('options_field');
    const optionsInput = document.getElementById('attribute_options');
    
    if (this.value === 'select') {
        optionsField.style.display = 'block';
        optionsInput.required = true;
    } else {
        optionsField.style.display = 'none';
        optionsInput.required = false;
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const attributeType = document.getElementById('attribute_type').value;
    const optionsField = document.getElementById('options_field');
    const optionsInput = document.getElementById('attribute_options');
    
    if (attributeType === 'select') {
        optionsField.style.display = 'block';
        optionsInput.required = true;
    }
    
    // Handle dependency fields
    const dependsOn = document.getElementById('depends_on');
    const showWhenField = document.getElementById('show_when_field');
    const showWhenType = document.getElementById('show_when_type');
    const showWhenValueField = document.getElementById('show_when_value_field');
    const showWhenValuesField = document.getElementById('show_when_values_field');
    
    function toggleShowWhen() {
        if (dependsOn.value) {
            showWhenField.style.display = 'block';
            updateShowWhenFields();
        } else {
            showWhenField.style.display = 'none';
        }
    }
    
    function updateShowWhenFields() {
        const type = showWhenType.value;
        if (type === 'value') {
            showWhenValueField.style.display = 'block';
            showWhenValuesField.style.display = 'none';
        } else {
            showWhenValueField.style.display = 'none';
            showWhenValuesField.style.display = 'block';
        }
    }
    
    dependsOn.addEventListener('change', toggleShowWhen);
    showWhenType.addEventListener('change', updateShowWhenFields);
    
    // Initialize
    toggleShowWhen();
});
</script>

