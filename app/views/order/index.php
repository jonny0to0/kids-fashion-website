<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Sidebar - Filters -->
            <div class="w-full lg:w-1/4">
                <div class="bg-white shadow-sm rounded-sm sticky top-24 overflow-hidden hidden lg:block"
                    id="desktop-filters">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <h3 class="font-medium text-lg text-gray-800">Filters</h3>
                            <?php if (!empty($filters['status']) || $filters['time'] !== 'last_30_days' || !empty($filters['search'])): ?>
                                <a href="<?= SITE_URL ?>/order"
                                    class="text-pink-600 text-xs font-medium hover:underline">CLEAR ALL</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="<?= SITE_URL ?>/order" method="GET" id="filter-form">
                        <!-- Search Hidden Input (to preserve search when filtering) -->
                        <?php if (!empty($filters['search'])): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($filters['search']) ?>">
                        <?php endif; ?>

                        <!-- Order Status -->
                        <div class="p-4 border-b border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Order Status</h4>
                            <div class="space-y-2">
                                <?php
                                $statuses = [
                                    'on_the_way' => 'On the way',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                    'returned' => 'Returned'
                                ];
                                // Map DB statuses to these groups if needed, or just use direct DB statuses
                                // Simpler to use exact DB statuses for now or map them in controller. 
                                // Let's use direct DB statuses available in system
                                $dbStatuses = [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                    'returned' => 'Returned'
                                ];
                                ?>
                                <?php foreach ($dbStatuses as $key => $label): ?>
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="checkbox" name="status[]" value="<?= $key ?>"
                                            class="form-checkbox h-4 w-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 transition duration-150 ease-in-out"
                                            <?= in_array($key, $filters['status']) ? 'checked' : '' ?>
                                            onchange="this.form.submit()">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900"><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Time -->
                        <div class="p-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Order Time</h4>
                            <div class="space-y-2">
                                <?php
                                $times = [
                                    'last_30_days' => 'Last 30 days',
                                    '2025' => '2025',
                                    '2024' => '2024',
                                    'older' => 'Older'
                                ];
                                ?>
                                <?php foreach ($times as $key => $label): ?>
                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                        <input type="radio" name="time" value="<?= $key ?>"
                                            class="form-radio h-4 w-4 text-pink-600 border-gray-300 focus:ring-pink-500 transition duration-150 ease-in-out"
                                            <?= $filters['time'] === $key ? 'checked' : '' ?> onchange="this.form.submit()">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900"><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Content - Orders -->
            <div class="w-full lg:w-3/4">
                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden mb-4">
                    <button type="button" onclick="document.getElementById('mobile-filters').classList.remove('hidden')"
                        class="w-full bg-white border border-gray-300 rounded-md py-2 px-4 flex items-center justify-center space-x-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="font-medium text-gray-700">Filters</span>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="bg-white p-3 rounded-sm shadow-sm mb-4 sticky top-0 z-10">
                    <form action="<?= SITE_URL ?>/order" method="GET" class="flex gap-2">
                        <!-- Preserve other filters -->
                        <?php if (is_array($filters['status'])): ?>
                            <?php foreach ($filters['status'] as $status): ?>
                                <input type="hidden" name="status[]" value="<?= $status ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <input type="hidden" name="time" value="<?= htmlspecialchars($filters['time']) ?>">

                        <!-- Search Bar -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-sm leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:bg-white focus:ring-1 focus:ring-pink-500 sm:text-sm transition duration-150 ease-in-out"
                                placeholder="Search by product, brand or order ID">
                        </div>
                        <button type="submit"
                            class="bg-pink-600 text-white px-6 py-2 rounded-sm font-medium text-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 uppercase">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Orders List -->
                <?php if (!empty($orders)): ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Determine Status Color
                            $statusColorClass = 'text-gray-600';
                            $statusDotColor = 'bg-gray-600';
                            switch ($order['order_status']) {
                                case 'delivered':
                                    $statusColorClass = 'text-green-600';
                                    $statusDotColor = 'bg-green-600';
                                    break;
                                case 'cancelled':
                                    $statusColorClass = 'text-red-600';
                                    $statusDotColor = 'bg-red-600';
                                    break;
                                case 'returned':
                                    $statusColorClass = 'text-gray-600';
                                    $statusDotColor = 'bg-gray-600';
                                    break;
                                default: // on the way etc
                                    $statusColorClass = 'text-orange-500';
                                    $statusDotColor = 'bg-orange-500';
                            }
                            ?>

                            <div
                                class="bg-white border border-gray-200 rounded-sm hover:shadow-md transition-shadow duration-200">
                                <!-- Order Header -->
                                <div
                                    class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center gap-2 bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pink-50 text-pink-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Order ID</p>
                                            <p class="font-bold text-gray-800">#<?= htmlspecialchars($order['order_number']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2.5 w-2.5 rounded-full <?= $statusDotColor ?>"></div>
                                        <span
                                            class="font-bold text-sm uppercase <?= $statusColorClass ?>"><?= $order['order_status'] ?></span>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="p-6">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="flex flex-col sm:flex-row gap-4 mb-6 last:mb-0">
                                            <!-- Image -->
                                            <div class="w-20 h-20 flex-shrink-0 border border-gray-200 rounded overflow-hidden">
                                                <?php
                                                $productImage = $item['product_image'];
                                                if (empty($productImage)) {
                                                    $imgUrl = SITE_URL . '/public/assets/images/placeholder.jpg';
                                                } elseif (strpos($productImage, 'http') === 0) {
                                                    $imgUrl = $productImage;
                                                } elseif (strpos($productImage, '/') === 0) {
                                                    $imgUrl = SITE_URL . $productImage;
                                                } else {
                                                    // Fallback relative path handling
                                                    $imgUrl = SITE_URL . '/public/images/products/' . $productImage;
                                                }
                                                ?>
                                                <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                    class="w-full h-full object-contain">
                                            </div>

                                            <!-- Details -->
                                            <div class="flex-1">
                                                <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                                    <div>
                                                        <a href="<?= SITE_URL ?>/product/detail/<?= $item['slug'] ?>"
                                                            class="font-medium text-gray-800 hover:text-pink-600 hover:underline transition">
                                                            <?= htmlspecialchars($item['product_name']) ?>
                                                        </a>
                                                        <!-- Optional Description/Variant -->
                                                        <!-- <p class="text-xs text-gray-500 mt-1">Size: M</p> -->
                                                        <p class="text-xs text-gray-500 mt-1">Qty: <?= $item['quantity'] ?></p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-bold text-gray-900">₹<?= number_format($item['price'], 0) ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons Row for Item (Review) -->
                                                <div class="mt-3 flex items-center gap-4">
                                                    <?php if ($order['order_status'] === 'delivered'): ?>
                                                        <?php if (!empty($item['has_reviewed'])): ?>
                                                            <div class="flex items-center text-green-600 text-sm font-medium">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                                    viewBox="0 0 20 20" fill="currentColor">
                                                                    <path
                                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                                <?= $item['review_rating'] ?> Rated
                                                            </div>
                                                            <button type="button"
                                                                onclick="viewReview('<?= htmlspecialchars($item['review_id'] ?? '') ?>')"
                                                                class="text-pink-600 hover:underline text-xs ml-2">View Review</button>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                onclick="openReviewModal(<?= $item['product_id'] ?>, '<?= $order['order_id'] ?>', '<?= htmlspecialchars($item['product_name'] ?? '', ENT_QUOTES) ?>', '<?= $imgUrl ?>')"
                                                                class="flex items-center text-pink-600 hover:text-pink-800 font-medium text-sm transition focus:outline-none">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                                </svg>
                                                                Rate & Review Product
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php elseif ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'returned'): ?>
                                                        <!-- Not delivered yet -->
                                                        <button onclick="showReviewNotAvailable()"
                                                            class="text-gray-400 cursor-not-allowed font-medium text-sm flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                            </svg>
                                                            Rate & Review Product
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Card Footer: Order Summary & Actions -->
                                <div
                                    class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <div class="text-xs text-gray-500">
                                        Placed on: <span
                                            class="font-medium text-gray-800"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <div class="flex gap-4">
                                        <!-- Common Actions -->
                                        <a href="<?= SITE_URL ?>/order/detail/<?= $order['order_number'] ?>"
                                            class="text-sm font-semibold text-pink-600 hover:underline">
                                            VIEW DETAILS
                                        </a>
                                        <?php if ($order['order_status'] === 'delivered'): ?>
                                            <a href="<?= SITE_URL ?>/order/invoice/<?= $order['order_number'] ?>"
                                                class="text-sm font-semibold text-pink-600 hover:underline flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                INVOICE
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="mt-8 flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Simple previous/next buttons -->
                                    <?php if ($pagination['current_page'] > 1): ?>
                                        <a href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= htmlspecialchars($filters['search']) ?>&time=<?= $filters['time'] ?><?= http_build_query(['status' => $filters['status']]) ? '&' . http_build_query(['status' => $filters['status']]) : '' ?>"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Previous
                                        </a>
                                    <?php endif; ?>

                                    <span
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                                    </span>

                                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                        <a href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= htmlspecialchars($filters['search']) ?>&time=<?= $filters['time'] ?><?= http_build_query(['status' => $filters['status']]) ? '&' . http_build_query(['status' => $filters['status']]) : '' ?>"
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php else: ?>
                    <div class="bg-white rounded shadow-sm p-12 text-center">
                        <img src="<?= SITE_URL ?>/public/assets/images/empty-orders.svg"
                            onerror="this.src='https://rukminim1.flixcart.com/www/800/800/promos/16/05/2019/d438a32e-765a-4d8b-b4a6-520b560971e8.png?q=90'"
                            alt="No Orders" class="w-48 h-48 mx-auto mb-6 opacity-80">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">No orders found</h3>
                        <p class="text-gray-500 mb-6">Adjust your filters to find what you're looking for.</p>
                        <?php if (!empty($filters['status']) || $filters['time'] !== 'last_30_days' || !empty($filters['search'])): ?>
                            <a href="<?= SITE_URL ?>/order" class="text-pink-600 font-medium hover:underline">Clear Filters</a>
                        <?php else: ?>
                            <a href="<?= SITE_URL ?>/product"
                                class="inline-block bg-pink-600 text-white px-6 py-3 rounded shadow hover:bg-pink-700 transition">Start
                                Shopping</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Filter Drawer -->
<div id="mobile-filters" class="fixed inset-0 z-50 hidden lg:hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"
        onclick="document.getElementById('mobile-filters').classList.add('hidden')"></div>
    <div
        class="absolute right-0 top-0 h-full w-80 bg-white shadow-xl flex flex-col transform transition-transform duration-300">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-lg">Filters</h3>
            <button onclick="document.getElementById('mobile-filters').classList.add('hidden')" class="text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <!-- Cloned Form for Mobile -->
            <form action="<?= SITE_URL ?>/order" method="GET" class="p-4">
                <!-- Search Hidden -->
                <?php if (!empty($filters['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($filters['search']) ?>">
                <?php endif; ?>

                <!-- Status -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 mb-3">Order Status</h4>
                    <div class="space-y-3">
                        <?php foreach ($dbStatuses as $key => $label): ?>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="status[]" value="<?= $key ?>"
                                    class="form-checkbox h-5 w-5 text-pink-600 border-gray-300 rounded" <?= in_array($key, $filters['status']) ? 'checked' : '' ?>>
                                <span class="text-gray-700"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Time -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 mb-3">Order Time</h4>
                    <div class="space-y-3">
                        <?php foreach ($times as $key => $label): ?>
                            <label class="flex items-center space-x-3">
                                <input type="radio" name="time" value="<?= $key ?>"
                                    class="form-radio h-5 w-5 text-pink-600 border-gray-300" <?= $filters['time'] === $key ? 'checked' : '' ?>>
                                <span class="text-gray-700"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sticky bottom-0 left-0 right-0 p-4 bg-white border-t space-y-3">
                    <button type="submit" class="w-full bg-pink-600 text-white font-bold py-3 rounded shadow-lg">Apply
                        Filters</button>
                    <a href="<?= SITE_URL ?>/order" class="block w-full text-center text-gray-600 py-2">Clear
                        Filters</a>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Custom Review Modal (Compact Design) -->
<div id="custom-review-modal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeReviewModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            <form id="customReviewForm" onsubmit="event.preventDefault(); submitReview();" enctype="multipart/form-data"
                class="p-6">
                <input type="hidden" name="product_id" id="review_product_id">
                <input type="hidden" name="order_id" id="review_order_id">
                <input type="hidden" name="is_verified_purchase" value="1">

                <!-- 1. Header (Reduced Height) -->
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-bold text-gray-900">Write a Review</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none"
                        onclick="closeReviewModal()">
                        <span class="text-2xl leading-none">&times;</span>
                    </button>
                </div>

                <!-- 2. Product Info (Inline) -->
                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
                    <div class="w-10 h-10 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                        <img id="review_product_image" src="" alt="Product" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p id="review_product_name" class="text-sm font-medium text-gray-900 truncate">Product Name</p>
                        <p class="text-xs text-gray-500 truncate">Share your experience</p>
                    </div>
                </div>

                <!-- 3. Rating Section (Inline) -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-700">Rating:</span>
                    <div class="flex items-center gap-1" id="star-rating-input">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button"
                                class="star-input text-gray-300 hover:text-yellow-400 focus:outline-none transition-colors p-1"
                                data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                    <path
                                        d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>
                <!-- Error msg for rating -->
                <div id="rating-error" class="hidden text-xs text-red-500 mb-2 text-right">Please select a rating</div>

                <!-- 4. Review Title (Restored) -->
                <div>
                    <label for="review_title" class="block text-sm font-medium text-gray-700 mb-1">Headline
                        <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input type="text" name="title" id="review_title"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm placeholder-gray-400 p-3"
                        placeholder="What's most important to know?">
                </div>

                <!-- 5. Review Description -->
                <div class="mt-4">
                    <label for="review_comment" class="block text-sm font-medium text-gray-700 mb-1">Your Review
                        <span class="text-pink-600">*</span></label>
                    <textarea name="review_text" id="review_comment" rows="4" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm placeholder-gray-400 p-3"
                        placeholder="What did you like or dislike about the product? How was the quality, size, or delivery experience?"></textarea>
                    <div id="comment-error" class="hidden mt-2 text-sm text-red-500">Review text is required</div>
                </div>

                <!-- 6. Add Photos (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Photos <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-pink-400 transition-colors bg-gray-50 hover:bg-white relative">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="review_images" class="relative cursor-pointer bg-white rounded-md font-medium text-pink-600 hover:text-pink-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-pink-500 px-2">
                                    <span>Upload photos</span>
                                    <input id="review_images" name="review_images[]" type="file" class="sr-only" multiple accept="image/*" onchange="handleImagePreview(this)">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB (Max 3)</p>
                        </div>
                    </div>
                    
                    <!-- Image Previews -->
                    <div id="image-preview-container" class="mt-4 grid grid-cols-3 gap-4 hidden">
                        <!-- Previews will be inserted here -->
                    </div>
                </div>

                <!-- 6. Submit Button (Right Aligned & Compact) -->
                <div class="flex justify-end">
                    <button type="submit" id="submitReviewBtn"
                        class="bg-pink-600 text-white rounded-lg shadow-sm py-2 px-6 text-sm font-semibold hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showReviewNotAvailable() {
        Swal.fire({
            title: 'Review Not Available',
            text: 'You can review the product after delivery.',
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#db2777'
        });
    }

    function viewReview(reviewId) {
        Swal.fire({
            title: 'Your Review',
            text: 'Review ID: ' + reviewId,
            icon: 'success'
        });
    }

    // Modal Variables
    const modal = document.getElementById('custom-review-modal');
    const form = document.getElementById('customReviewForm');
    const submitBtn = document.getElementById('submitReviewBtn');

    // Open Modal
    function openReviewModal(productId, orderId, productName, productImage) {
        // Populate Data
        document.getElementById('review_product_id').value = productId;
        document.getElementById('review_order_id').value = orderId;
        document.getElementById('review_product_name').innerText = productName;
        document.getElementById('review_product_image').src = productImage ? productImage : '<?= SITE_URL ?>/public/assets/images/placeholder.jpg';

        // Reset Form
        form.reset();
        document.getElementById('rating-value').value = '';
        // The photo-upload-section is no longer used with the new image upload UI.
        // document.getElementById('photo-upload-section').classList.add('hidden'); 
        resetStars();

        // Reset Errors
        document.getElementById('rating-error').classList.add('hidden');
        document.getElementById('comment-error').classList.add('hidden');

        submitBtn.disabled = false;
        submitBtn.innerText = 'Submit Review';

        // Show Modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close Modal
    function closeReviewModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Star Rating Logic
    function setRating(val) {
        document.getElementById('rating-value').value = val;
        document.querySelectorAll('.star-input').forEach((btn, idx) => {
            // idx is 0-based, val is 1-based.
            if (idx < val) {
                btn.classList.add('text-yellow-400');
                btn.classList.remove('text-gray-300');
            } else {
                btn.classList.add('text-gray-300');
                btn.classList.remove('text-yellow-400');
            }
        });
        document.getElementById('rating-error').classList.add('hidden');
    }

    function resetStars() {
        document.querySelectorAll('.star-input').forEach((btn) => {
            btn.classList.add('text-gray-300');
            btn.classList.remove('text-yellow-400');
        });
    }

    // Toggle Photo Upload - This function is no longer needed with the new UI
    // function togglePhotoUpload() {
    //     const section = document.getElementById('photo-upload-section');
    //     section.classList.toggle('hidden');
    // }

    // Image Preview Logic (New)
    function handleImagePreview(input) {
        const previewContainer = document.getElementById('image-preview-container');
        previewContainer.innerHTML = ''; // Clear existing previews
        previewContainer.classList.add('hidden');

        if (input.files && input.files.length > 0) {
            previewContainer.classList.remove('hidden');
            Array.from(input.files).slice(0, 3).forEach(file => { // Limit to 3 images
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'relative w-24 h-24 rounded-md overflow-hidden border border-gray-200';
                    imgDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                        <button type="button" onclick="removeImage(this)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs leading-none opacity-80 hover:opacity-100">
                            &times;
                        </button>
                    `;
                    previewContainer.appendChild(imgDiv);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    function removeImage(button) {
        const imgDiv = button.closest('div');
        const previewContainer = document.getElementById('image-preview-container');
        previewContainer.removeChild(imgDiv);

        // If no images left, hide the container
        if (previewContainer.children.length === 0) {
            previewContainer.classList.add('hidden');
        }

        // Reset the file input to allow re-uploading the same file if needed
        const fileInput = document.getElementById('review_images');
        fileInput.value = ''; // Clear the selected files
    }


    // Submit Logic
    function submitReview() {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validation
        let isValid = true;

        if (!data.rating) {
            document.getElementById('rating-error').classList.remove('hidden');
            isValid = false;
        } else {
            document.getElementById('rating-error').classList.add('hidden');
        }

        if (!data.review_text || data.review_text.trim().length < 5) {
            document.getElementById('comment-error').innerText = 'Please write at least a few words.';
            document.getElementById('comment-error').classList.remove('hidden');
            isValid = false;
        } else {
            document.getElementById('comment-error').classList.add('hidden');
        }

        if (!isValid) return;

        // Proceed to Submit
        submitBtn.disabled = true;
        submitBtn.innerText = 'Submitting...';

        fetch('<?= SITE_URL ?>/product/submit_review', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    closeReviewModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Review Submitted!',
                        text: 'Thank you for your feedback.',
                        confirmButtonColor: '#db2777'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Submit Review';
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Could not submit review. Please try again later.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Review';
            });
    }

    // Close on Escape Key
    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape" && !modal.classList.contains('hidden')) {
            closeReviewModal();
        }
    });
</script>