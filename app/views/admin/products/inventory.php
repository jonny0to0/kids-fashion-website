<?php
/**
 * Products Inventory Management Page
 */
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
    <p class="text-gray-600 mt-1">Manage product stock levels and inventory</p>
</div>

<!-- Filters -->
<div class="admin-card mb-6">
    <form method="GET" action="<?php echo SITE_URL; ?>/admin/products/inventory"
        class="flex flex-col md:flex-row md:items-end gap-3">
        <div class="flex-1 w-full md:w-auto">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                placeholder="Search by name or SKU..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
        </div>
        <div class="w-full md:w-[150px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
            <select name="stock"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <option value="all" <?php echo ($stockFilter ?? 'all') === 'all' ? 'selected' : ''; ?>>All Products
                </option>
                <option value="low" <?php echo ($stockFilter ?? '') === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out" <?php echo ($stockFilter ?? '') === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>
        <button type="submit" class="w-full md:w-auto btn-pink-gradient px-6 py-2 rounded-lg font-medium">
            Filter
        </button>
        <?php if (!empty($search) || ($stockFilter ?? 'all') !== 'all'): ?>
            <a href="<?php echo SITE_URL; ?>/admin/products/inventory"
                class="w-full md:w-auto text-center px-6 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Products Table -->
<div class="admin-card p-2 md:p-6">
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                </path>
            </svg>
            <p class="text-gray-500 text-lg">No products found</p>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">SKU</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Current Stock</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Low Stock Threshold</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($product['primary_image'])): ?>
                                        <img src="<?php echo SITE_URL . '/' . $product['primary_image']; ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="w-12 h-12 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <span
                                        class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                            <td class="py-3 px-4 text-center">
                                <?php
                                $currentStock = $product['current_stock'] ?? 0;
                                $defaultLowStockThreshold = 10;
                                ?>
                                <span
                                    class="font-semibold <?php echo $currentStock <= 0 ? 'text-red-600' : ($currentStock <= $defaultLowStockThreshold ? 'text-orange-600' : 'text-green-600'); ?>">
                                    <?php echo number_format($currentStock); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-600">
                                <?php echo number_format($defaultLowStockThreshold); ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($currentStock <= 0): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Out of
                                        Stock</span>
                                <?php elseif ($currentStock <= $defaultLowStockThreshold): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">Low
                                        Stock</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">In
                                        Stock</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="<?php echo SITE_URL; ?>/admin/product/edit/<?php echo $product['product_id']; ?>"
                                    class="text-pink-600 hover:text-pink-800 font-medium text-sm">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden space-y-4">
            <?php foreach ($products as $product): ?>
                <?php
                $currentStock = $product['current_stock'] ?? 0;
                $defaultLowStockThreshold = 10;
                ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-start gap-3 mb-3">
                        <?php if (!empty($product['primary_image'])): ?>
                            <img src="<?php echo SITE_URL . '/' . $product['primary_image']; ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover rounded">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <a href="<?php echo SITE_URL; ?>/admin/product/edit/<?php echo $product['product_id']; ?>"
                                class="block">
                                <h3 class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                            </a>
                            <p class="text-sm text-gray-500 mt-1">SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="block text-gray-500 text-xs">Current Stock</span>
                            <span
                                class="font-semibold <?php echo $currentStock <= 0 ? 'text-red-600' : ($currentStock <= $defaultLowStockThreshold ? 'text-orange-600' : 'text-green-600'); ?>">
                                <?php echo number_format($currentStock); ?>
                            </span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <span class="block text-gray-500 text-xs">Threshold</span>
                            <span
                                class="font-medium text-gray-700"><?php echo number_format($defaultLowStockThreshold); ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4 border-t pt-3">
                        <div>
                            <?php if ($currentStock <= 0): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Out of Stock</span>
                            <?php elseif ($currentStock <= $defaultLowStockThreshold): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">Low
                                    Stock</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">In Stock</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/product/edit/<?php echo $product['product_id']; ?>"
                            class="text-pink-600 hover:text-pink-800 font-medium text-sm">
                            Edit Product
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($pagination)): ?>
            <div class="mt-6">
                <?php echo $pagination->render(); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>