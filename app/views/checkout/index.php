<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-8">Checkout</h2>
    
    <form method="POST" action="<?php echo SITE_URL; ?>/checkout/process">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Shipping & Payment -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold mb-4">Shipping Address</h3>
                    
                    <?php if (!empty($addresses)): ?>
                        <div class="space-y-3 mb-4">
                            <?php foreach ($addresses as $address): ?>
                                <label class="flex items-center border rounded p-3 cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="shipping_address_id" value="<?php echo $address['address_id']; ?>" 
                                           class="mr-3" <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                    <div class="flex-grow">
                                        <p class="font-medium"><?php echo htmlspecialchars($address['full_name']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode']); ?></p>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <button type="button" onclick="editAddress(<?php echo $address['address_id']; ?>); event.preventDefault(); event.stopPropagation();" class="text-blue-600 hover:text-blue-800 p-1" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" onclick="deleteAddress(<?php echo $address['address_id']; ?>); event.preventDefault(); event.stopPropagation();" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <a href="#" onclick="openAddressModal(); return false;" class="text-pink-600 hover:underline">
                            + Add New Address
                        </a>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-yellow-800">No shipping address found. Please add a shipping address to continue.</p>
                        </div>
                        <button type="button" onclick="openAddressModal(); return false;" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 font-medium">
                            + Add Shipping Address
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold mb-4">Payment Method</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center border rounded p-3 cursor-pointer hover:bg-gray-50" id="cod-payment-option">
                            <div class="flex items-center w-full">
                                <input type="radio" name="payment_method" value="<?php echo PAYMENT_METHOD_COD; ?>" id="payment_method_cod" class="mr-3" checked required>
                                <div>
                                    <p class="font-medium">Cash on Delivery</p>
                                    <p class="text-sm text-gray-600">Pay when you receive</p>
                                    <p id="cod-unavailable-msg" class="text-xs text-red-600 hidden mt-1"></p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="flex items-center border rounded p-3 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="<?php echo PAYMENT_METHOD_ONLINE; ?>" class="mr-3">
                            <div>
                                <p class="font-medium">Online Payment</p>
                                <p class="text-sm text-gray-600">Credit/Debit Card, UPI, Wallet</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h3 class="text-xl font-bold mb-4">Order Summary</h3>
                    
                    <div class="space-y-3 mb-4">
                        <?php foreach ($items as $item): ?>
                            <div class="flex justify-between text-sm">
                                <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                                <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                        <div class="mb-4">
                            <h4 class="font-bold text-gray-700 mb-2">Shipping Method</h4>
                            <div id="shipping-methods-container" class="space-y-2">
                                <?php if (!empty($shippingMethods)): ?>
                                    <?php foreach ($shippingMethods as $index => $method): ?>
                                        <label class="flex items-center justify-between border rounded p-2 cursor-pointer hover:bg-gray-50">
                                            <div class="flex items-center">
                                                <input type="radio" name="shipping_method_id" value="<?php echo $method['id']; ?>" 
                                                       class="mr-2" <?php echo $index === 0 ? 'checked' : ''; ?>
                                                       data-cost="<?php echo $method['cost']; ?>"
                                                       data-cod-enabled="<?php echo ($method['cod_enabled'] ?? true) ? '1' : '0'; ?>"
                                                       data-cod-limit="<?php echo $method['cod_limit'] ?? 0; ?>">
                                                <span class="text-sm font-medium"><?php echo htmlspecialchars($method['name']); ?></span>
                                            </div>
                                            <span class="text-sm font-bold">
                                                <?php echo ($method['pricing_type'] ?? '') === 'free' || $method['cost'] == 0 ? 'Free' : '₹' . number_format($method['cost'], 2); ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-sm text-red-500">No shipping methods available for this location.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="border-t pt-4 space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>₹<?php echo number_format($total['total'] ?? 0, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-pink-600 font-medium">
                                <span>Shipping</span>
                                <span id="shipping-cost-display">
                                    <?php 
                                    // Default display for first method
                                    $defaultCost = 0;
                                    if (!empty($shippingMethods)) {
                                        $method = $shippingMethods[0];
                                        $defaultCost = (($method['pricing_type'] ?? '') === 'free') ? 0 : $method['cost'];
                                    }
                                    echo $defaultCost == 0 ? 'Free' : '₹' . number_format($defaultCost, 2);
                                    ?>
                                </span>
                            </div>
                        </div>
                    
                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>₹<?php echo number_format(($total['total'] ?? 0) + $defaultCost, 2); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" id="place-order-btn" class="w-full bg-pink-600 text-white py-3 rounded-lg hover:bg-pink-700 font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Address Modal Popup -->
<div id="address-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation();">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-800">Add New Address</h3>
            <button type="button" onclick="closeAddressModal();" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
        </div>
        
        <div class="p-6">
            <div id="address-form-container">
                <p class="text-gray-600 mb-4">Please fill in your shipping address details below:</p>
                <!-- Form will be loaded here via AJAX -->
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-pink-600"></div>
                    <p class="mt-2 text-gray-600">Loading form...</p>
                </div>
            </div>
        </div>
        
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
            <button type="button" onclick="closeAddressModal();" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-medium">
                Close
            </button>
            <button type="button" onclick="saveAddress(event);" class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-medium">
                Save Address
            </button>
        </div>
    </div>
</div>

<script>
    // Pass SITE_URL to JavaScript
    window.SITE_URL = '<?php echo SITE_URL; ?>';
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/checkout.js"></script>

