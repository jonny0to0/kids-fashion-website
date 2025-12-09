<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8">My Orders</h2>
    
    <?php if (!empty($orders)): ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold">Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                            <p class="text-gray-600 text-sm">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-pink-600">₹<?php echo number_format($order['final_amount'], 2); ?></p>
                            <span class="inline-block px-3 py-1 rounded-full text-sm capitalize 
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
                    
                    <div class="flex gap-4 mt-4">
                        <a href="<?php echo SITE_URL; ?>/order/detail/<?php echo $order['order_number']; ?>" 
                           class="text-pink-600 hover:underline">
                            View Details
                        </a>
                        <?php if ($order['order_status'] === 'pending' || $order['order_status'] === 'confirmed'): ?>
                            <a href="<?php echo SITE_URL; ?>/order/cancel/<?php echo $order['order_number']; ?>" 
                               class="text-red-600 hover:underline"
                               onclick="return confirm('Are you sure you want to cancel this order?');">
                                Cancel Order
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-gray-500 text-lg mb-4">You haven't placed any orders yet.</p>
            <a href="<?php echo SITE_URL; ?>/product" 
               class="inline-block bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                Start Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

