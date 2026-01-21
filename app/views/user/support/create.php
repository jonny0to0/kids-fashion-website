<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex items-center gap-2">
            <a href="<?php echo SITE_URL; ?>/support" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Tickets
            </a>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-6">Create New Ticket</h1>

        <div class="bg-white rounded-lg shadow p-6 md:p-8">
            <form action="<?php echo SITE_URL; ?>/support/create" method="POST">
                <!-- Subject -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Subject <span class="text-pink-500">*</span></label>
                    <input type="text" name="subject" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" placeholder="Briefly describe your issue">
                </div>

                <!-- Category & Order ID Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Category <span class="text-pink-500">*</span></label>
                        <select name="category" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white">
                            <option value="" disabled selected>Select a category</option>
                            <option value="order">Order Issue</option>
                            <option value="payment">Payment / Refund</option>
                            <option value="product">Product Question</option>
                            <option value="account">Account Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Related Order (Optional)</label>
                        <select name="order_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white">
                            <option value="">Select an order</option>
                            <?php foreach($orders as $order): ?>
                                <option value="<?php echo $order['order_id']; ?>">Order #<?php echo $order['order_id']; ?> (<?php echo date('M d', strtotime($order['created_at'])); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Message -->
                <div class="mb-8">
                    <label class="block text-gray-700 font-medium mb-2">Message <span class="text-pink-500">*</span></label>
                    <textarea name="message" rows="6" required class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-pink-500" placeholder="Please provide as much detail as possible..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <a href="<?php echo SITE_URL; ?>/support" class="text-gray-600 hover:text-gray-800 mr-4 font-medium">Cancel</a>
                    <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 transition-colors font-semibold shadow-md transform hover:-translate-y-0.5 transition-transform duration-150">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
