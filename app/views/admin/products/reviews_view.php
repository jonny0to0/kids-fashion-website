<?php
/**
 * Admin Review Details View
 */
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Review Details</h1>
        <p class="text-gray-600 mt-1">Viewing review #
            <?php echo $review['review_id']; ?>
        </p>
    </div>
    <a href="<?php echo SITE_URL; ?>/admin/products/reviews"
        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
            </path>
        </svg>
        Back to Reviews
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content: Review & Product -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Review Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Review Content</h2>

            <div class="flex items-center gap-4 mb-4">
                <div class="flex items-center gap-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-6 h-6 <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-200'; ?>"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                    <?php endfor; ?>
                </div>
                <span class="text-lg font-medium text-gray-800">
                    <?php echo $review['rating']; ?>/5
                </span>
            </div>

            <?php if (!empty($review['title'])): ?>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    <?php echo htmlspecialchars($review['title']); ?>
                </h3>
            <?php endif; ?>

            <div class="prose max-w-none text-gray-700 mb-6">
                <?php echo nl2br(htmlspecialchars($review['review_text'] ?? $review['comment'] ?? '')); ?>
            </div>

            <?php if (!empty($review['media_json'])):
                $media = json_decode($review['media_json'], true);
                if (!empty($media)):
                    ?>
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Attached Photos</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($media as $img): ?>
                                <a href="<?php echo SITE_URL . '/' . $img; ?>" target="_blank" class="block">
                                    <img src="<?php echo SITE_URL . '/' . $img; ?>"
                                        class="w-24 h-24 object-cover rounded-lg border border-gray-200 hover:opacity-75 transition">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; endif; ?>
        </div>

        <!-- Product Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Product Information</h2>
            <div class="flex items-start gap-4">
                <?php if (!empty($review['product_image'])): ?>
                    <img src="<?php echo SITE_URL . '/' . $review['product_image']; ?>"
                        class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                <?php endif; ?>
                <div>
                    <h3 class="font-medium text-gray-900 text-lg">
                        <a href="<?php echo SITE_URL . '/products/detail/' . $review['product_id']; ?>" target="_blank"
                            class="hover:text-pink-600 hover:underline">
                            <?php echo htmlspecialchars($review['product_name']); ?>
                        </a>
                    </h3>
                    <p class="text-gray-500 text-sm mt-1">SKU:
                        <?php echo htmlspecialchars($review['sku']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Customer & Actions -->
    <div class="space-y-6">
        <!-- Status & Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Review Status</h2>

            <div class="mb-6">
                <span class="block text-sm text-gray-500 mb-1">Current Status</span>
                <?php
                $statusColor = match ($review['status'] ?? $review['is_approved']) {
                    'APPROVED', 1, '1' => 'bg-green-100 text-green-800',
                    'PENDING', 0, '0' => 'bg-yellow-100 text-yellow-800',
                    'REJECTED' => 'bg-red-100 text-red-800',
                    'HIDDEN' => 'bg-gray-100 text-gray-800',
                    default => 'bg-gray-100 text-gray-600'
                };
                $statusLabel = match ($review['status'] ?? $review['is_approved']) {
                    'APPROVED', 1, '1' => 'Approved',
                    'PENDING', 0, '0' => 'Pending',
                    default => ucfirst(strtolower($review['status'] ?? 'Unknown'))
                };
                ?>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium <?php echo $statusColor; ?>">
                    <?php echo $statusLabel; ?>
                </span>
            </div>

            <form id="reviewStatusForm" class="space-y-3">
                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">

                <?php if (($review['status'] ?? '') !== 'APPROVED' && ($review['is_approved'] ?? 0) != 1): ?>
                    <button type="button" onclick="updateReviewStatus(<?php echo $review['review_id']; ?>, 'APPROVED')"
                        class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                        Approve Review
                    </button>
                <?php endif; ?>

                <?php if (($review['status'] ?? '') !== 'REJECTED' && ($review['status'] ?? '') !== 'HIDDEN'): ?>
                    <button type="button" onclick="updateReviewStatus(<?php echo $review['review_id']; ?>, 'HIDDEN')"
                        class="w-full py-2 px-4 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition">
                        Hide / Reject
                    </button>
                <?php endif; ?>

                <button type="button" onclick="deleteReview(<?php echo $review['review_id']; ?>)"
                    class="w-full py-2 px-4 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg font-medium transition mt-4">
                    Delete Review
                </button>
            </form>
        </div>

        <!-- Customer info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Info</h2>
            <div class="flex items-center gap-3 mb-4">
                <?php if (!empty($review['customer_image'])): ?>
                    <img src="<?php echo SITE_URL . '/' . $review['customer_image']; ?>"
                        class="w-12 h-12 rounded-full object-cover">
                <?php else: ?>
                    <div
                        class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-lg">
                        <?php echo strtoupper(substr($review['first_name'] ?? 'U', 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="font-medium text-gray-900">
                        <?php echo htmlspecialchars(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                        <?php echo htmlspecialchars($review['customer_email'] ?? ''); ?>
                    </div>
                </div>
            </div>

            <?php if ($order): ?>
                <div class="border-t pt-4 mt-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Verified Purchase</h3>
                    <div class="text-sm">
                        <p class="text-gray-600">Order: <a
                                href="<?php echo SITE_URL; ?>/admin/orders/details/<?php echo $order['order_id']; ?>"
                                class="text-blue-600 hover:underline">#
                                <?php echo $order['order_number']; ?>
                            </a></p>
                        <p class="text-gray-600">Date:
                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                        </p>
                        <p class="mt-1">
                            <span
                                class="px-2 py-0.5 rounded text-xs font-medium 
                                <?php echo $order['order_status'] === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="border-t pt-4 mt-4">
                    <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                        Unverified Purchase
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateReviewStatus(id, status) {
        if (!confirm('Are you sure you want to change status to ' + status + '?')) return;

        fetch('<?php echo SITE_URL; ?>/admin/products/reviews-update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'review_id=' + id + '&status=' + status
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network error');
            });
    }

    function deleteReview(id) {
        if (!confirm('Are you sure you want to DELETE this review permanently? This cannot be undone.')) return;

        fetch('<?php echo SITE_URL; ?>/admin/products/reviews-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'review_id=' + id
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review deleted successfully.');
                    window.location.href = '<?php echo SITE_URL; ?>/admin/products/reviews';
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network error');
            });
    }
</script>