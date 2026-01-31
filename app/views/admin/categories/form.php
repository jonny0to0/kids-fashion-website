<?php
$pageTitle = $action . ' Category';
// print_r($category);
// exit;

error_log('FORM category: ' . ($category['category_id'] ?? 'NONE'));

// Include breadcrumb and back button helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_back_button.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Home', 'url' => '/admin'],
        ['label' => 'Categories', 'url' => '/admin/categories'],
        ['label' => $action . ' Category']
    ]);
    ?>
    
    <div class="mb-8">
        <div class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $action; ?> Category</h1>
        </div>
        <?php renderBackButton('Categories', '/admin/categories', 'top-left'); ?>
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
        
        <form method="POST" enctype="multipart/form-data" id="category-form">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Basic Information</h2>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Category Name *</label>
                    <input type="text" name="name" id="category-name" required
                           value="<?php 
                               if (!empty($errors) && isset($data['name'])) {
                                   echo htmlspecialchars($data['name']);
                               } elseif (!empty($category['name'])) {
                                   echo htmlspecialchars($category['name']);
                               }
                           ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                           placeholder="Enter category name">
                    <p class="text-sm text-gray-500 mt-1">The slug will be auto-generated from the name</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                              placeholder="Enter category description"><?php 
                              if (!empty($errors) && isset($category['description'])) {
                                  echo htmlspecialchars($category['description']);
                              } elseif (!empty($category['description'])) {
                                  echo htmlspecialchars($category['description']);
                              }
                          ?></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Parent Category</label>
                    <select name="parent_id" id="parent-category"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">None (Top Level)</option>
                        <?php foreach ($parentCategories as $parent): ?>
                            <option value="<?php echo $parent['category_id']; ?>"
                                    <?php 
                                    $selectedParentId = null;
                                    if (!empty($errors) && isset($data['parent_id'])) {
                                        $selectedParentId = $data['parent_id'];
                                    } elseif (!empty($category['parent_id'])) {
                                        $selectedParentId = $category['parent_id'];
                                    }
                                    echo ($selectedParentId == $parent['category_id']) ? 'selected' : '';
                                    ?>>
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Select a parent category to create a sub-category</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Display Order</label>
                    <input type="number" name="display_order" min="0"
                           value="<?php 
                               if (!empty($errors) && isset($data['display_order'])) {
                                   echo (int)$data['display_order'];
                               } elseif (!empty($category['display_order'])) {
                                   echo (int)$category['display_order'];
                               } else {
                                   echo 0;
                               }
                           ?>"
                           class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500"
                           placeholder="0">
                    <p class="text-sm text-gray-500 mt-1">Lower numbers appear first</p>
                </div>
                
                <!-- Image Upload -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Category Image</h2>
                    
                    <!-- Current Image -->
                    <?php if (!empty($category['image'])): ?>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Current Image</label>
                            <div class="relative inline-block">
                                <img src="<?php echo SITE_URL . $category['image']; ?>" 
                                     alt="Category Image" 
                                     class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Upload a new image to replace the current one</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload New Image -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <?php echo !empty($category['image']) ? 'Upload New Image' : 'Upload Image'; ?>
                        </label>
                        <input type="file" name="image" id="category-image" accept="image/*"
                               class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <p class="text-sm text-gray-500 mt-1">Recommended: Square image, minimum 300x300px</p>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Status</h2>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                               <?php 
                               $isActive = 1;
                               if (!empty($errors) && isset($data['is_active'])) {
                                   $isActive = (int)$data['is_active'];
                               } elseif (!empty($category['is_active'])) {
                                   $isActive = (int)$category['is_active'];
                               }
                               echo $isActive ? 'checked' : '';
                               ?>
                               class="mr-2 w-5 h-5">
                        <span class="text-gray-700 font-medium">Active Category</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1">Inactive categories will not be displayed on the frontend</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('category-form');
    const mode = '<?php echo $mode ?? 'add'; ?>';
    
    // Ensure clean form state for Add mode
    if (mode === 'add' && !<?php echo !empty($errors) ? 'true' : 'false'; ?>) {
        // Only reset if there are no errors (errors mean we're re-displaying the form)
        form.reset();
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const nameInput = document.getElementById('category-name');
        if (!nameInput || !nameInput.value.trim()) {
            e.preventDefault();
            alert('Category name is required');
            return false;
        }
    });
});
</script>

