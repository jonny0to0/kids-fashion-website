<?php
$pageTitle = 'Manage Hero Banners';

// Include breadcrumb and quick actions helpers
require_once __DIR__ . '/../_breadcrumb.php';
require_once __DIR__ . '/../_quick_actions.php';
?>

<?php echo getQuickActionsStyles(); ?>

<div class="container mx-auto px-4 sm:px-4 py-4 md:py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
        ['label' => 'Hero Banners']
    ]);
    ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-8 gap-4 md:gap-0">
        <div class="flex-1">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Hero Banners</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">Control homepage and category hero banners</p>
        </div>
        <div class="w-full md:w-auto ml-0 md:ml-4">
            <?php
            renderQuickActions([
                [
                    'type' => 'primary',
                    'icon' => 'plus',
                    'label' => 'Add Banner',
                    'url' => '/admin/hero-banner/add',
                    'tooltip' => 'Create a new hero banner',
                    'aria-label' => 'Add new hero banner'
                ]
            ], 'top-right');
            ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/hero-banners" class="flex gap-4 flex-wrap">
            <select name="status"
                class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Status</option>
                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active
                </option>
                <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                    Inactive</option>
            </select>
            <select name="target_type"
                class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="">All Types</option>
                <option value="homepage" <?php echo ($filters['target_type'] ?? '') === 'homepage' ? 'selected' : ''; ?>>
                    Homepage</option>
                <option value="category" <?php echo ($filters['target_type'] ?? '') === 'category' ? 'selected' : ''; ?>>
                    Category</option>
                <option value="campaign" <?php echo ($filters['target_type'] ?? '') === 'campaign' ? 'selected' : ''; ?>>
                    Campaign</option>
            </select>
            <button type="submit" class="w-full md:w-auto bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                Filter
            </button>
            <?php if (!empty($filters)): ?>
                <a href="<?php echo SITE_URL; ?>/admin/hero-banners"
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> Only up to 5 active banners will be displayed on the frontend. Banners are ordered by
            priority (highest first), then display order. Drag and drop to reorder banners.
        </p>
    </div>

    <!-- Banners List with Drag & Drop -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (empty($banners)): ?>
            <div class="px-6 py-12 text-center text-gray-500">
                No banners found. <a href="<?php echo SITE_URL; ?>/admin/hero-banner/add"
                    class="text-pink-600 hover:underline">Add your first banner</a>
            </div>
        <?php else: ?>
            <div id="banners-list" class="divide-y divide-gray-200">
                <?php foreach ($banners as $banner): ?>
                    <div class="banner-item p-4 md:px-6 md:py-4 hover:bg-gray-50 transition-colors"
                        data-banner-id="<?php echo $banner['banner_id']; ?>" draggable="true">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6">
                            <!-- Drag Handle -->
                            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16">
                                    </path>
                                </svg>
                            </div>

                            <!-- Banner Preview -->
                            <div class="w-full md:w-auto flex-shrink-0">
                                <div class="relative w-full h-48 md:w-48 md:h-28 bg-gray-100 rounded-lg overflow-hidden">
                                    <img src="<?php echo SITE_URL . $banner['desktop_image']; ?>"
                                        alt="<?php echo htmlspecialchars($banner['title']); ?>"
                                        class="w-full h-full object-cover">
                                    <div class="absolute top-1 right-1 bg-black/50 text-white text-xs px-2 py-1 rounded">
                                        Desktop
                                    </div>
                                </div>
                            </div>

                            <!-- Banner Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-lg text-gray-900 mb-1">
                                    <?php echo htmlspecialchars($banner['title']); ?>
                                </h3>
                                <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <span class="font-medium">Priority:</span>
                                        <span class="px-2 py-1 bg-gray-100 rounded"><?php echo $banner['priority']; ?></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="font-medium">Target:</span>
                                        <span
                                            class="px-2 py-1 bg-blue-100 text-blue-800 rounded"><?php echo ucfirst($banner['target_type']); ?></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="font-medium">Device:</span>
                                        <span
                                            class="px-2 py-1 bg-purple-100 text-purple-800 rounded"><?php echo ucfirst($banner['device_visibility']); ?></span>
                                    </span>
                                    <?php if ($banner['start_date'] || $banner['end_date']): ?>
                                        <span class="flex items-center gap-1">
                                            <span class="font-medium">Schedule:</span>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                                <?php
                                                if ($banner['start_date'] && $banner['end_date']) {
                                                    echo date('M d', strtotime($banner['start_date'])) . ' - ' . date('M d', strtotime($banner['end_date']));
                                                } elseif ($banner['start_date']) {
                                                    echo 'From ' . date('M d, Y', strtotime($banner['start_date']));
                                                } elseif ($banner['end_date']) {
                                                    echo 'Until ' . date('M d, Y', strtotime($banner['end_date']));
                                                }
                                                ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($banner['cta_text']) && !empty($banner['cta_url'])): ?>
                                    <div class="mt-2 text-sm">
                                        <span class="text-gray-500">CTA:</span>
                                        <a href="<?php echo htmlspecialchars($banner['cta_url']); ?>" target="_blank"
                                            class="text-pink-600 hover:underline ml-1">
                                            <?php echo htmlspecialchars($banner['cta_text']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex-shrink-0">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium <?php echo $banner['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo ucfirst($banner['status']); ?>
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex-shrink-0 flex items-center justify-end w-full md:w-auto gap-2 mt-2 md:mt-0">
                                <a href="<?php echo SITE_URL; ?>/admin/hero-banner/edit/<?php echo $banner['banner_id']; ?>"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/hero-banner/toggle-status/<?php echo $banner['banner_id']; ?>"
                                        class="p-2 <?php echo $banner['status'] === 'active' ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50'; ?> rounded-lg transition-colors"
                                        title="<?php echo $banner['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>"
                                        onclick="return confirm('Are you sure you want to <?php echo $banner['status'] === 'active' ? 'deactivate' : 'activate'; ?> this banner?');">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php if ($banner['status'] === 'active'): ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                </path>
                                            <?php else: ?>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            <?php endif; ?>
                                        </svg>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/hero-banner/delete/<?php echo $banner['banner_id']; ?>"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this banner? This action cannot be undone.');">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Drag and Drop functionality
    document.addEventListener('DOMContentLoaded', function () {
        const bannersList = document.getElementById('banners-list');
        if (!bannersList) return;

        let draggedElement = null;

        // Make all banner items draggable
        const bannerItems = bannersList.querySelectorAll('.banner-item');
        bannerItems.forEach(item => {
            item.addEventListener('dragstart', function (e) {
                draggedElement = this;
                this.style.opacity = '0.5';
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', function () {
                this.style.opacity = '1';
                bannerItems.forEach(i => i.classList.remove('border-pink-500', 'border-2'));
            });
            draggedElement = null;
        });

        // Handle drag over
        bannerItems.forEach(item => {
            item.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (draggedElement && draggedElement !== this) {
                    const rect = this.getBoundingClientRect();
                    const midpoint = rect.top + rect.height / 2;

                    if (e.clientY < midpoint) {
                        this.classList.add('border-t-2', 'border-pink-500');
                        this.classList.remove('border-b-2');
                    } else {
                        this.classList.add('border-b-2', 'border-pink-500');
                        this.classList.remove('border-t-2');
                    }
                }
            });

            item.addEventListener('dragleave', function () {
                this.classList.remove('border-pink-500', 'border-t-2', 'border-b-2');
            });

            item.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('border-pink-500', 'border-t-2', 'border-b-2');

                if (draggedElement && draggedElement !== this) {
                    const rect = this.getBoundingClientRect();
                    const midpoint = rect.top + rect.height / 2;

                    if (e.clientY < midpoint) {
                        bannersList.insertBefore(draggedElement, this);
                    } else {
                        bannersList.insertBefore(draggedElement, this.nextSibling);
                    }

                    // Update display order
                    updateDisplayOrder();
                }
            });
        });

        function updateDisplayOrder() {
            const items = bannersList.querySelectorAll('.banner-item');
            const orders = {};

            items.forEach((item, index) => {
                const bannerId = item.dataset.bannerId;
                orders[bannerId] = index + 1;
            });

            // Send AJAX request to update order
            fetch('<?php echo SITE_URL; ?>/admin/hero-banner/update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ orders: orders })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const message = document.createElement('div');
                        message.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        message.textContent = 'Display order updated successfully';
                        document.body.appendChild(message);
                        setTimeout(() => message.remove(), 3000);
                    } else {
                        alert('Failed to update display order: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update display order');
                });
        }
    });
</script>