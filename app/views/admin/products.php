<?php
$pageTitle = 'Manage Products';

// Include breadcrumb and quick actions helpers
require_once __DIR__ . '/_breadcrumb.php';
require_once __DIR__ . '/_quick_actions.php';
?>

<?php echo getQuickActionsStyles(); ?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
        ['label' => 'Products']
    ]);
    ?>

    <div class="flex justify-between items-start mb-8">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-800">Manage Products</h1>
            <p class="text-gray-600 mt-2">Add, edit, or delete products</p>
        </div>
        <div class="ml-4">
            <?php
            renderQuickActions([
                [
                    'type' => 'primary',
                    'icon' => 'plus',
                    'label' => 'Add Product',
                    'url' => '/admin/product/add',
                    'tooltip' => 'Create a new product',
                    'aria-label' => 'Add new product'
                ]
            ], 'top-right');
            ?>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/products" class="flex gap-4">
            <input type="text" name="search" placeholder="Search products by name or SKU..."
                value="<?php echo htmlspecialchars($search); ?>"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo SITE_URL; ?>/admin/products"
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Image
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No products found. <a href="<?php echo SITE_URL; ?>/admin/product/add"
                                    class="text-pink-600 hover:underline">Add your first product</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <?php if (!empty($product['primary_image'])): ?>
                                        <img src="<?php echo SITE_URL . $product['primary_image']; ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-20 h-20 rounded">
                                        <!-- object-cover -->
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">SKU:
                                        <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <span
                                                class="text-red-600 font-bold">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <span
                                                class="text-gray-400 line-through ml-2">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="font-bold">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($product['stock_quantity']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                    echo $product['status'] === PRODUCT_STATUS_ACTIVE ? 'bg-green-100 text-green-800' :
                                        ($product['status'] === PRODUCT_STATUS_OUT_OF_STOCK ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800');
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo SITE_URL; ?>/admin/product/edit/<?php echo $product['product_id']; ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                    <a href="<?php echo SITE_URL; ?>/admin/product/delete/<?php echo $product['product_id']; ?>"
                                        class="text-red-600 hover:text-red-900 delete-product-link"
                                        data-product-id="<?php echo $product['product_id']; ?>">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pagination && $pagination->getTotalPages() > 1): ?>
        <div class="mt-6 flex justify-center">
            <?php echo $pagination->render(); ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteLinks = document.querySelectorAll('.delete-product-link');
        deleteLinks.forEach(link => {
            link.addEventListener('click', async function (e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const result = await showConfirm(
                    'Delete Product',
                    'Are you sure you want to delete this product? This action cannot be undone.',
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