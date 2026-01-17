<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Review Management</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6 border border-gray-200">
        <form id="filter-form" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm">
                    <option value="all" <?php echo $filters['status'] === 'all' ? 'selected' : ''; ?>>All Statuses
                    </option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved
                    </option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected
                    </option>
                    <option value="hidden" <?php echo $filters['status'] === 'hidden' ? 'selected' : ''; ?>>Hidden
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <select name="rating"
                    class="rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm">
                    <option value="">All Ratings</option>
                    <option value="5" <?php echo $filters['rating'] == '5' ? 'selected' : ''; ?>>5 Stars</option>
                    <option value="4" <?php echo $filters['rating'] == '4' ? 'selected' : ''; ?>>4 Stars</option>
                    <option value="3" <?php echo $filters['rating'] == '3' ? 'selected' : ''; ?>>3 Stars</option>
                    <option value="2" <?php echo $filters['rating'] == '2' ? 'selected' : ''; ?>>2 Stars</option>
                    <option value="1" <?php echo $filters['rating'] == '1' ? 'selected' : ''; ?>>1 Star</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                    placeholder="Search reviews, products, or users..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm">
            </div>

            <button type="submit"
                class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm font-medium transition-colors">
                Apply Filters
            </button>

            <a href="<?php echo SITE_URL; ?>/admin/reviews"
                class="text-gray-600 hover:text-gray-800 text-sm font-medium px-2">
                Reset
            </a>
        </form>
    </div>

    <!-- Reviews List -->
    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
        <?php if (empty($reviews)): ?>
            <div class="p-8 text-center text-gray-500">
                <p>No reviews found matching your criteria.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($reviews as $review): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors" id="review-<?php echo $review['review_id']; ?>">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Review Content -->
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            <?php
                                            echo match ($review['status']) {
                                                'APPROVED' => 'bg-green-100 text-green-800',
                                                'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                'HIDDEN' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo $review['status']; ?>
                                        </span>
                                        <?php if ($review['report_count'] > 0): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-50 text-red-600 rounded-full flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <?php echo $review['report_count']; ?> Reports
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($review['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <h3 class="font-bold text-gray-900 text-lg">
                                        <?php if ($review['product_slug']): ?>
                                            <a href="<?php echo SITE_URL; ?>/product/<?php echo $review['product_slug']; ?>"
                                                target="_blank" class="hover:text-pink-600 hover:underline">
                                                <?php echo htmlspecialchars($review['product_name'] ?? 'Unknown Product'); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($review['product_name'] ?? 'Unknown Product'); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="text-sm text-gray-600">
                                        by <span class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                        </span>
                                        <span class="text-gray-400 mx-1">•</span>
                                        <?php echo htmlspecialchars($review['email']); ?>
                                        <?php if ($review['is_verified_purchase']): ?>
                                            <span
                                                class="text-green-600 text-xs font-medium ml-2 border border-green-200 px-1.5 py-0.5 rounded">Verified
                                                Purchase</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="flex text-yellow-400 mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'fill-current' : 'text-gray-300'; ?>"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($review['title']): ?>
                                        <h4 class="font-bold text-gray-800 text-sm">
                                            <?php echo htmlspecialchars($review['title']); ?>
                                        </h4>
                                    <?php endif; ?>
                                    <p class="text-gray-700 mt-1">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </p>

                                    <?php
                                    $media = !empty($review['media_json']) ? json_decode($review['media_json'], true) : [];
                                    if (!empty($media) && is_array($media)):
                                        ?>
                                        <div class="flex gap-2 mt-3">
                                            <?php foreach ($media as $m): ?>
                                                <a href="<?php echo SITE_URL . $m; ?>" target="_blank">
                                                    <img src="<?php echo SITE_URL . $m; ?>"
                                                        class="h-16 w-16 object-cover rounded border border-gray-200 hover:opacity-80">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Admin Reply Section -->
                                <div class="bg-gray-50 rounded p-3 mt-4"
                                    id="reply-container-<?php echo $review['review_id']; ?>">
                                    <?php if (!empty($review['admin_reply'])): ?>
                                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Admin Reply</p>
                                        <p class="text-sm text-gray-700">
                                            <?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?>
                                        </p>
                                    <?php else: ?>
                                        <button onclick="toggleReplyForm(<?php echo $review['review_id']; ?>)"
                                            class="text-sm text-pink-600 hover:text-pink-700 font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            Reply to this review
                                        </button>
                                        <form id="reply-form-<?php echo $review['review_id']; ?>" class="hidden mt-2"
                                            onsubmit="submitReply(event, <?php echo $review['review_id']; ?>)">
                                            <textarea name="reply_text" rows="2" class="w-full rounded border-gray-300 text-sm mb-2"
                                                placeholder="Type your reply here..." required></textarea>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="toggleReplyForm(<?php echo $review['review_id']; ?>)"
                                                    class="text-gray-500 text-xs hover:text-gray-700">Cancel</button>
                                                <button type="submit"
                                                    class="bg-pink-600 text-white px-3 py-1 rounded text-xs hover:bg-pink-700">Send
                                                    Reply</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex flex-row md:flex-col gap-2 min-w-[140px] border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-4">
                                <?php if ($review['status'] === 'PENDING'): ?>
                                    <button onclick="updateStatus(<?php echo $review['review_id']; ?>, 'APPROVED')"
                                        class="flex-1 bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700 transition w-full text-center">
                                        Approve
                                    </button>
                                    <button onclick="updateStatus(<?php echo $review['review_id']; ?>, 'REJECTED')"
                                        class="flex-1 bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700 transition w-full text-center">
                                        Reject
                                    </button>
                                <?php elseif ($review['status'] === 'APPROVED'): ?>
                                    <button onclick="updateStatus(<?php echo $review['review_id']; ?>, 'HIDDEN')"
                                        class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-50 transition w-full text-center">
                                        Hide
                                    </button>
                                <?php else: ?>
                                    <button onclick="updateStatus(<?php echo $review['review_id']; ?>, 'APPROVED')"
                                        class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-50 transition w-full text-center">
                                        Re-Approve
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing page <span class="font-medium">
                                    <?php echo $currentPage; ?>
                                </span> of <span class="font-medium">
                                    <?php echo $totalPages; ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_merge($filters, ['page' => null])); ?>"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i == $currentPage ? 'text-pink-600 bg-pink-50 z-10' : 'text-gray-500 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    async function updateStatus(reviewId, status) {
        if (!confirm(`Are you sure you want to change status to ${status}?`)) return;

        try {
            const formData = new FormData();
            formData.append('review_id', reviewId);
            formData.append('status', status);

            const response = await fetch('<?php echo SITE_URL; ?>/admin/reviews/update_status', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to update status');
            }
        } catch (e) {
            alert('An error occurred');
        }
    }

    function toggleReplyForm(reviewId) {
        const form = document.getElementById(`reply-form-${reviewId}`);
        form.classList.toggle('hidden');
    }

    async function submitReply(e, reviewId) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('review_id', reviewId);

        try {
            const response = await fetch('<?php echo SITE_URL; ?>/admin/reviews/reply', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to send reply');
            }
        } catch (e) {
            alert('An error occurred');
        }
    }
</script>