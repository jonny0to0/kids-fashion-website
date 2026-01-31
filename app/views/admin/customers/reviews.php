<?php
/**
 * Customer Reviews & Feedback Page
 */
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Customer Reviews & Feedback</h1>
    <p class="text-gray-600 mt-1">View all customer reviews and feedback</p>
</div>

<?php
require_once __DIR__ . '/../_breadcrumb.php';

renderBreadcrumb([
    ['label' => 'Home', 'url' => '/admin'],
    ['label' => 'Customers', 'url' => '/admin/customers'],
    ['label' => 'Reviews & Feedback']
]);
?>

<!-- Filters -->
<div class="admin-card mb-6">
    <form method="GET" action="<?php echo SITE_URL; ?>/admin/customers/reviews" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                   placeholder="Search by customer name, email, or product..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
        </div>
        <button type="submit" class="btn-pink-gradient px-6 py-2 rounded-lg font-medium">
            Filter
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?php echo SITE_URL; ?>/admin/customers/reviews" class="px-6 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Reviews Table -->
<div class="admin-card">
    <?php if (empty($reviews)): ?>
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            <p class="text-gray-500 text-lg">No reviews found</p>
        </div>
    <?php else: ?>
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            <?php foreach ($reviews as $review): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-medium text-gray-800"><?php echo htmlspecialchars($review['customer_name'] ?? 'N/A'); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($review['customer_email'] ?? ''); ?></div>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-medium text-gray-700 mr-1"><?php echo $review['rating'] ?? 0; ?></span>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Product</div>
                        <div class="font-medium text-gray-800 text-sm line-clamp-2 overflow-hidden text-ellipsis"><?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($review['sku'] ?? ''); ?></div>
                    </div>
                    
                    <div class="mb-3 bg-gray-50 p-3 rounded text-sm text-gray-700 italic">
                        "<?php echo htmlspecialchars(substr($review['review_text'] ?? '', 0, 150)); ?><?php if (strlen($review['review_text'] ?? '') > 150): ?>...<?php endif; ?>"
                    </div>
                    
                    <?php 
                    $media = !empty($review['media_json']) ? json_decode($review['media_json'], true) : [];
                    if (!empty($media) && is_array($media)): 
                    ?>
                        <div class="flex gap-2 mb-3">
                            <?php foreach (array_slice($media, 0, 3) as $image): ?>
                                <a href="<?php echo htmlspecialchars($image); ?>" target="_blank" class="block">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Review Image" class="w-12 h-12 object-cover rounded border border-gray-200">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500">
                            <?php echo date('M d, Y', strtotime($review['created_at'] ?? 'now')); ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $review['product_slug'] ?? $review['product_id']; ?>" 
                           target="_blank"
                           class="text-pink-600 hover:text-pink-800 font-medium text-sm flex items-center gap-1">
                            View Product
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 w-1/4">Product</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Rating</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 w-1/3">Review</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-800"><?php echo htmlspecialchars($review['customer_name'] ?? 'N/A'); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($review['customer_email'] ?? ''); ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-800 line-clamp-2 overflow-hidden text-ellipsis" title="<?php echo htmlspecialchars($review['product_name'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?>
                                </div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($review['sku'] ?? ''); ?></div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-4 h-4 <?php echo $i <= ($review['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="max-w-md text-sm text-gray-700">
                                    <?php echo htmlspecialchars(substr($review['review_text'] ?? '', 0, 100)); ?>
                                    <?php if (strlen($review['review_text'] ?? '') > 100): ?>...<?php endif; ?>
                                </div>
                                <?php 
                                $media = !empty($review['media_json']) ? json_decode($review['media_json'], true) : [];
                                if (!empty($media) && is_array($media)): 
                                ?>
                                    <div class="flex gap-2 mt-2">
                                        <?php foreach (array_slice($media, 0, 3) as $image): ?>
                                            <a href="<?php echo htmlspecialchars($image); ?>" target="_blank" class="block">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="Review Image" class="w-10 h-10 object-cover rounded border border-gray-200">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo date('M d, Y', strtotime($review['created_at'] ?? 'now')); ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo $review['product_slug'] ?? $review['product_id']; ?>" 
                                   target="_blank"
                                   class="text-pink-600 hover:text-pink-800 font-medium text-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($pagination)): ?>
            <div class="mt-6">
                <?php echo $pagination->render(); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

