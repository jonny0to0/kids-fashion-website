<?php
// Ensure items is always defined
if (!isset($items)) {
    $items = [];
}

// Ensure shippingAddress is always defined
if (!isset($shippingAddress)) {
    $shippingAddress = null;
}

// Ensure billingAddress is always defined
if (!isset($billingAddress)) {
    $billingAddress = null;
}
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="<?php echo SITE_URL; ?>/order" class="text-pink-600 hover:underline flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6 pb-6 border-b">
                <h1 class="text-3xl font-bold mb-2">Order Details</h1>
                <p class="text-gray-600">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                <p class="text-sm text-gray-500 mt-1">Placed on
                    <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
            </div>

            <!-- Order Status -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold mb-2">Order Status</h3>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-medium capitalize 
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-purple-100 text-purple-800',
                            'shipped' => 'bg-indigo-100 text-indigo-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        echo $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>">
                        <?php echo $order['order_status']; ?>
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                <?php if (empty($items) || count($items) === 0): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p class="text-gray-500 text-lg">No order items found</p>
                        <p class="text-gray-400 text-sm mt-2">This order does not have any items associated with it.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($items as $item): ?>
                            <div class="flex gap-4 border-b pb-4 last:border-b-0 last:pb-0">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo SITE_URL; ?><?php echo htmlspecialchars($item['product_image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                        class="w-20 h-20 object-cover rounded border border-gray-200">
                                <?php else: ?>
                                    <div
                                        class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center border border-gray-300">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="font-medium text-lg mb-1">
                                        <?php if (!empty($item['slug'])): ?>
                                            <a href="<?php echo SITE_URL; ?>/product/detail/<?php echo htmlspecialchars($item['slug']); ?>"
                                                class="text-pink-600 hover:underline">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2">
                                        <span><strong>Quantity:</strong>
                                            <?php echo htmlspecialchars($item['quantity']); ?></span>
                                        <span><strong>Unit Price:</strong>
                                            ₹<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                    <?php if (!empty($item['discount']) && $item['discount'] > 0): ?>
                                        <p class="text-sm text-green-600 mt-1">
                                            Discount: ₹<?php echo number_format($item['discount'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['tax']) && $item['tax'] > 0): ?>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Tax: ₹<?php echo number_format($item['tax'], 2); ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Review Section -->
                                    <?php if ($order['order_status'] === 'delivered'): ?>
                                        <div class="mt-3">
                                            <?php if (!empty($item['has_reviewed'])): ?>
                                                <span
                                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                    Refview Submitted
                                                </span>
                                            <?php else: ?>
                                                <button type="button"
                                                    onclick="openReviewModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['product_name'])); ?>')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-pink-200 rounded-lg text-sm font-medium text-pink-600 hover:bg-pink-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                                                        </path>
                                                    </svg>
                                                    Write Review
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-gray-900">₹<?php echo number_format($item['total'], 2); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">Item Total</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Addresses -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <?php if ($shippingAddress): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Shipping Address</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium"><?php echo htmlspecialchars($shippingAddress['full_name']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($shippingAddress['address_line1']); ?></p>
                            <?php if (!empty($shippingAddress['address_line2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($shippingAddress['address_line2']); ?></p>
                            <?php endif; ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($shippingAddress['city'] ?? ''); ?>,
                                <?php echo htmlspecialchars($shippingAddress['state'] ?? ''); ?> -
                                <?php echo htmlspecialchars($shippingAddress['pincode'] ?? ''); ?>
                                <!-- postal_code -->
                            </p>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($shippingAddress['phone']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($billingAddress): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Billing Address</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium"><?php echo htmlspecialchars($billingAddress['full_name']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($billingAddress['address_line1']); ?></p>
                            <?php if (!empty($billingAddress['address_line2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($billingAddress['address_line2']); ?></p>
                            <?php endif; ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($billingAddress['city'] ?? ''); ?>,
                                <?php echo htmlspecialchars($billingAddress['state'] ?? ''); ?> -
                                <?php echo htmlspecialchars($billingAddress['pincode'] ?? ''); ?>
                                <!-- postal_code -->
                            </p>
                            <p class="text-gray-600">Phone: <?php echo htmlspecialchars($billingAddress['phone']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                <div class="space-y-2 max-w-md ml-auto">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['tax_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Tax:</span>
                            <span>₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['shipping_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Shipping:</span>
                            <span>₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-xl font-bold pt-2 border-t">
                        <span>Total:</span>
                        <span class="text-pink-600">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Payment Method:</span>
                        <span class="capitalize"><?php echo strtoupper($order['payment_method']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Payment Status:</span>
                        <span class="capitalize 
                            <?php
                            $paymentStatusColors = [
                                'pending' => 'text-yellow-600',
                                'paid' => 'text-green-600',
                                'failed' => 'text-red-600',
                                'refunded' => 'text-blue-600'
                            ];
                            echo $paymentStatusColors[$order['payment_status']] ?? '';
                            ?>">
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <?php if (!empty($order['notes'])): ?>
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-lg font-semibold mb-2">Order Notes</h3>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t flex gap-4">
                <a href="<?php echo SITE_URL; ?>/order"
                    class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300">
                    Back to Orders
                </a>
                <?php if ($order['order_status'] === ORDER_STATUS_PENDING || $order['order_status'] === ORDER_STATUS_CONFIRMED): ?>
                    <a href="<?php echo SITE_URL; ?>/order/cancel/<?php echo htmlspecialchars($order['order_number']); ?>"
                        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 cancel-order-link"
                        data-order-number="<?php echo htmlspecialchars($order['order_number']); ?>">
                        Cancel Order
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Write a Review</h3>
            <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form action="<?php echo SITE_URL; ?>/reviews/submit" method="POST" class="p-6">
            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
            <input type="hidden" name="product_id" id="modalProductId">

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">Reviewing:</p>
                <p class="font-semibold text-gray-900" id="modalProductName">Product Name</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                            <svg class="w-8 h-8 text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 transition"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500 outline-none"
                    placeholder="Is it good? Bad? Excellent?">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Review</label>
                <textarea name="review_text" rows="4"
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500 outline-none"
                    placeholder="Tell us more about your experience..." required></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeReviewModal()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit"
                    class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-medium">Submit
                    Review</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReviewModal(productId, productName) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalProductName').textContent = productName;
        const modal = document.getElementById('reviewModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReviewModal() {
        const modal = document.getElementById('reviewModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close when clicking outside
    document.getElementById('reviewModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeReviewModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const cancelLink = document.querySelector('.cancel-order-link');
        if (cancelLink) {
            cancelLink.addEventListener('click', async function (e) {
                e.preventDefault();
                const orderNumber = this.getAttribute('data-order-number');
                const result = await showConfirm(
                    'Cancel Order',
                    'Are you sure you want to cancel this order? This action cannot be undone.',
                    'Yes, Cancel',
                    'No, Keep Order',
                    'warning'
                );

                if (result.isConfirmed) {
                    window.location.href = this.href;
                }
            });
        }
    });
</script>