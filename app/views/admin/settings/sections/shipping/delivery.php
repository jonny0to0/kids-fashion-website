<?php
// Delivery Methods View
$shippingZoneModel = new ShippingZone();
$deliveryMethodModel = new DeliveryMethod();

$zones = $shippingZoneModel->getAllZones();
$methods = []; 
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-4 mb-4 gap-4 md:gap-0">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Delivery Configuration</h3>
            <p class="text-sm text-gray-500">Manage delivery methods, pricing rules, and logistics partners.</p>
        </div>
        <button onclick="openMethodModal()" class="btn-pink-gradient px-4 py-2 rounded-lg font-medium text-sm w-full md:w-auto">
            Add Delivery Method
        </button>
    </div>

    <?php if (empty($zones)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        You need to create a Shipping Zone first before adding delivery methods.
                        <a href="<?php echo SITE_URL; ?>/admin/settings?section=shipping&subsection=zones" class="font-medium underline hover:text-yellow-600">Go to Zones</a>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($zones as $zone): ?>
                <?php $zoneMethods = $deliveryMethodModel->getMethodsByZone($zone['id']); ?>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 md:gap-0">
                        <h4 class="font-medium text-gray-700 flex items-center gap-2">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <?php echo htmlspecialchars($zone['zone_name']); ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo empty($zoneMethods) ? '0 methods' : count($zoneMethods) . ' methods'; ?>
                            </span>
                        </h4>
                        <button onclick="openMethodModal(<?php echo $zone['id']; ?>)" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Method
                        </button>
                    </div>
                    
                    <?php if (empty($zoneMethods)): ?>
                        <div class="p-8 text-center text-sm text-gray-500 bg-white">
                            <div class="mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            No delivery methods configured for this zone.
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($zoneMethods as $method): ?>
                                <div class="p-4 flex flex-col md:flex-row items-start md:items-center justify-between hover:bg-gray-50 transition-colors gap-4 md:gap-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($method['name']); ?></span>
                                            <?php if (!$method['is_active']): ?>
                                                <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                                            <?php endif; ?>
                                            <?php if ($method['badge_text']): ?>
                                                 <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full border border-green-200"><?php echo htmlspecialchars($method['badge_text']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1 flex gap-3">
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <?php echo ucfirst(str_replace('_', ' ', $method['pricing_type'] ?? 'flat')); ?>
                                            </span>
                                            <?php if ($method['estimated_days']): ?>
                                                <span class="flex items-center gap-1">
                                                     <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Est. <?php echo htmlspecialchars($method['estimated_days']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($method['delivery_type']): ?>
                                                 <span class="flex items-center gap-1">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                                    <?php echo ucfirst(str_replace('_', ' ', $method['delivery_type'])); ?>
                                                 </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end mt-4 md:mt-0">
                                        <div class="text-right">
                                            <div class="font-bold text-gray-900">
                                                <?php if (($method['pricing_type'] ?? '') === 'free'): ?>
                                                    <span class="text-green-600">Free</span>
                                                <?php else: ?>
                                                    ₹<?php echo number_format($method['cost'], 2); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php if ($method['cod_charge'] > 0): ?>
                                                    + ₹<?php echo $method['cod_charge']; ?> COD
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick='editMethod(<?php echo json_encode($method); ?>)' class="p-1.5 hover:bg-blue-50 text-blue-600 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            <button onclick="deleteMethod(<?php echo $method['id']; ?>)" class="p-1.5 hover:bg-red-50 text-red-600 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Advanced Tabbed Modal -->
<!-- Advanced Tabbed Modal -->
<div id="methodModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="methodModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeMethodModal()"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="methodModalTitle">
                        Add Delivery Method
                    </h3>
                    <button type="button" onclick="closeMethodModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="methodForm" onsubmit="saveMethod(event)">
                <input type="hidden" name="id" id="methodId">
                
                <!-- Wizard Steps Indicator -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <!-- Step 1: Basic -->
                        <div class="flex flex-col items-center step-indicator" data-step="0">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm mb-1 transition-colors duration-200 step-circle">1</div>
                            <span class="text-xs font-medium text-blue-600 step-label">Basic</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-2 rounded relative">
                             <div class="absolute top-0 left-0 h-full bg-blue-600 rounded transition-all duration-300 step-line" data-step="0" style="width: 0%"></div>
                        </div>
                        
                        <!-- Step 2: Pricing -->
                        <div class="flex flex-col items-center step-indicator" data-step="1">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold text-sm mb-1 transition-colors duration-200 step-circle">2</div>
                            <span class="text-xs font-medium text-gray-500 step-label">Pricing</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-2 rounded relative">
                             <div class="absolute top-0 left-0 h-full bg-blue-600 rounded transition-all duration-300 step-line" data-step="1" style="width: 0%"></div>
                        </div>

                        <!-- Step 3: Timing -->
                        <div class="flex flex-col items-center step-indicator" data-step="2">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold text-sm mb-1 transition-colors duration-200 step-circle">3</div>
                            <span class="text-xs font-medium text-gray-500 step-label">Timing</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-2 rounded relative">
                             <div class="absolute top-0 left-0 h-full bg-blue-600 rounded transition-all duration-300 step-line" data-step="2" style="width: 0%"></div>
                        </div>

                        <!-- Step 4: Advanced -->
                        <div class="flex flex-col items-center step-indicator" data-step="3">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold text-sm mb-1 transition-colors duration-200 step-circle">4</div>
                            <span class="text-xs font-medium text-gray-500 step-label">Adv.</span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                <!-- Tab: Basic Info -->
                <div id="content-basic" class="tab-content space-y-4">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
                            <select name="zone_id" id="methodZoneId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?php echo $zone['id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Type</label>
                            <select name="delivery_type" id="methodDeliveryType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="home">Home Delivery</option>
                                <option value="pickup_point">Pickup Point</option>
                                <option value="store_pickup">Store Pickup</option>
                                <option value="courier">Courier Partner</option>
                            </select>
                        </div>
                     </div>
                     
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method Name</label>
                        <input type="text" name="name" id="methodName" placeholder="e.g. Standard Shipping"
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 shadow-sm transition-shadow duration-200">
                        <div id="methodNameSuggestions" class="absolute z-10 w-full bg-white mt-1 rounded-md shadow-lg border border-gray-100 hidden overflow-hidden">
                            <ul class="py-1 text-sm text-gray-700 max-h-48 overflow-y-auto">
                                <!-- Suggestions will be populated by JS -->
                            </ul>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Select a suggestion or enter a custom delivery method name.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="methodDescription" rows="2" placeholder="Brief explanation for customers"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Courier Partner (Optional)</label>
                        <input type="text" name="courier_partner" id="methodCourier" placeholder="e.g. Delhivery, BlueDart"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="is_active" id="methodActive" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="methodActive" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>

                <!-- Tab: Pricing & Cost -->
                <div id="content-pricing" class="tab-content hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pricing Model</label>
                        <select name="pricing_type" id="methodPricingType" onchange="togglePricingFields()" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="flat">Flat Rate</option>
                            <option value="free">Free Delivery</option>
                            <option value="price">Based on Order Value</option>
                            <option value="weight">Based on Total Weight</option>
                            <option value="distance">Based on Distance</option>
                        </select>
                    </div>

                    <div id="costInputGroup" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Base Cost (₹)</label>
                            <input type="number" name="cost" id="methodCost" step="0.01" min="0" value="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taxable?</label>
                            <div class="mt-2 text-sm">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_taxable" id="methodTaxable" class="form-checkbox h-4 w-4 text-blue-600">
                                    <span class="ml-2">Apply Tax</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="conditionsGroup" class="hidden p-3 bg-gray-50 rounded border border-gray-200 space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Conditions</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label id="labelMinInfo" class="block text-xs font-medium text-gray-700 mb-1">Min Value</label>
                                <input type="number" name="condition_min" id="methodMin" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label id="labelMaxInfo" class="block text-xs font-medium text-gray-700 mb-1">Max Value</label>
                                <input type="number" name="condition_max" id="methodMax" step="0.01" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">COD Extra Charge (₹)</label>
                            <input type="number" name="cod_charge" id="methodCodCharge" step="0.01" min="0" value="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Surcharge (₹)</label>
                            <input type="number" name="fuel_surcharge" id="methodFuelCharge" step="0.01" min="0" value="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Tab: Timing & Logistics -->
                <div id="content-delivery" class="tab-content hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Est. Delivery Time</label>
                            <input type="text" name="estimated_days" id="methodDays" placeholder="e.g. 2-3 Days"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cut-off Time</label>
                            <input type="time" name="cutoff_time" id="methodCutoff"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="has_slots" id="methodSlots" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Enable Delivery Slots</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="has_same_day" id="methodSameDay" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Supports Same-Day Delivery</span>
                        </label>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Courier Integration</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="api_enabled" id="methodApiEnabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Enable API Integration</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_assign" id="methodAutoAssign" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Auto-assign Courier</span>
                            </label>
                        </div>
                        <div class="mt-3">
                             <label class="block text-sm font-medium text-gray-700 mb-1">Tracking URL Format</label>
                             <input type="text" name="tracking_url" id="methodTrackingUrl" placeholder="https://tracker.com?id={AWB}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Tab: Advanced -->
                <div id="content-advanced" class="tab-content hidden space-y-4">
                     <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Checkout Display</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge / Label</label>
                                <input type="text" name="badge_text" id="methodBadge" placeholder="e.g. Fastest"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" autocomplete="off">
                                <div id="methodBadgeSuggestions" class="absolute z-10 w-full bg-white mt-1 rounded-md shadow-lg border border-gray-100 hidden overflow-hidden">
                                    <ul class="py-1 text-sm text-gray-700 max-h-48 overflow-y-auto">
                                        <!-- Suggestions will be populated by JS -->
                                    </ul>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Select a suggestion or enter a custom delivery Badge / Label.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" id="methodSort" value="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="show_at_checkout" id="methodShowCheckout" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Show at Checkout</span>
                            </label>
                        </div>
                     </div>

                     <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Weight & Dimensions Constraints</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Min Weight (kg)</label>
                                <input type="number" name="min_weight" id="methodMinWeight" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Max Weight (kg)</label>
                                <input type="number" name="max_weight" id="methodMaxWeight" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Dimensions (LxWxH)</label>
                            <input type="text" name="max_dimensions" id="methodDimensions" placeholder="e.g. 100x100x100 cm"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="mt-2">
                             <label class="flex items-center">
                                <input type="checkbox" name="is_fragile" id="methodFragile" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Suitable for Fragile Items</span>
                            </label>
                        </div>
                     </div>

                     <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">COD Settings</h4>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="enable_cod" id="methodEnableCod" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="methodEnableCod" class="ml-2 block text-sm text-gray-700">Enable Cash on Delivery</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max COD Amount</label>
                            <input type="number" name="max_cod_amount" id="methodMaxCod" step="0.01" placeholder="Leave empty for no limit"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                     </div>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                <button type="button" onclick="closeMethodModal()" class="text-gray-500 hover:text-gray-700 text-sm font-medium focus:outline-none hover:underline">
                    Cancel
                </button>
                <div class="flex gap-3">
                    <button type="button" id="btnBack" onclick="prevStep()" class="hidden px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Back
                    </button>
                    <button type="button" id="btnNext" onclick="nextStep()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        Next
                    </button>
                    <button type="submit" id="btnSubmit" class="hidden px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm">
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Wizard State
let currentStep = 0;
const totalSteps = 4;
const steps = ['basic', 'pricing', 'delivery', 'advanced'];

window.updateWizardUI = function() {
    // Show current step content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('content-' + steps[currentStep]).classList.remove('hidden');

    // Update Step Indicators
    document.querySelectorAll('.step-indicator').forEach(el => {
        const step = parseInt(el.dataset.step);
        const circle = el.querySelector('.step-circle');
        const label = el.querySelector('.step-label');
        
        if (step < currentStep) {
            // Completed
            circle.classList.remove('bg-gray-200', 'text-gray-500', 'bg-blue-600', 'text-white');
            circle.classList.add('bg-green-500', 'text-white');
            circle.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            label.classList.remove('text-gray-500', 'text-blue-600');
            label.classList.add('text-green-600');
        } else if (step === currentStep) {
            // Active
            circle.classList.remove('bg-gray-200', 'text-gray-500', 'bg-green-500');
            circle.classList.add('bg-blue-600', 'text-white');
            circle.innerHTML = step + 1;
            label.classList.remove('text-gray-500', 'text-green-600');
            label.classList.add('text-blue-600');
        } else {
            // Future
            circle.classList.remove('bg-blue-600', 'text-white', 'bg-green-500');
            circle.classList.add('bg-gray-200', 'text-gray-500');
            circle.innerHTML = step + 1;
            label.classList.remove('text-blue-600', 'text-green-600');
            label.classList.add('text-gray-500');
        }
    });

    // Update Step Lines
    document.querySelectorAll('.step-line').forEach(el => {
        const step = parseInt(el.dataset.step);
        if (step < currentStep) {
            el.style.width = '100%';
        } else {
            el.style.width = '0%';
        }
    });

    // Update Buttons
    const btnBack = document.getElementById('btnBack');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');

    if (currentStep === 0) {
        btnBack.classList.add('hidden');
    } else {
        btnBack.classList.remove('hidden');
    }

    if (currentStep === totalSteps - 1) {
        btnNext.classList.add('hidden');
        btnSubmit.classList.remove('hidden');
    } else {
        btnNext.classList.remove('hidden');
        btnSubmit.classList.add('hidden');
    }
}

window.validateStep = function(step) {
    let isValid = true;
    
    // Helper to show error
    const showError = (id) => {
        const el = document.getElementById(id);
        if(el) {
            el.classList.add('border-red-500', 'ring-1', 'ring-red-500');
            el.addEventListener('input', () => {
                el.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            }, { once: true });
        }
        isValid = false;
    };

    if (step === 0) {
        const name = document.getElementById('methodName');
        if (!name.value.trim()) showError('methodName');
        
        const zone = document.getElementById('methodZoneId');
        if (!zone.value) showError('methodZoneId');
    }
    
    if (step === 1) {
        const pricingType = document.getElementById('methodPricingType').value;
        const cost = document.getElementById('methodCost');
        if (pricingType !== 'free' && parseFloat(cost.value) < 0) {
            showError('methodCost');
        }
    }

    return isValid;
}

window.nextStep = function() {
    if (validateStep(currentStep)) {
        if (currentStep < totalSteps - 1) {
            currentStep++;
            updateWizardUI();
        }
    }
}

window.prevStep = function() {
    if (currentStep > 0) {
        currentStep--;
        updateWizardUI();
    }
}

window.togglePricingFields = function() {
    const type = document.getElementById('methodPricingType').value;
    const costInputGroup = document.getElementById('costInputGroup');
    const conditionsGroup = document.getElementById('conditionsGroup');
    const labelMin = document.getElementById('labelMinInfo');
    const labelMax = document.getElementById('labelMaxInfo');

    if (type === 'free') {
        costInputGroup.classList.add('opacity-50', 'pointer-events-none');
        document.getElementById('methodCost').value = '0';
    } else {
        costInputGroup.classList.remove('opacity-50', 'pointer-events-none');
    }

    if (type === 'price') {
        conditionsGroup.classList.remove('hidden');
        labelMin.textContent = 'Min Order Amount (₹)';
        labelMax.textContent = 'Max Order Amount (₹)';
    } else if (type === 'weight') {
        conditionsGroup.classList.remove('hidden');
        labelMin.textContent = 'Min Weight (kg)';
        labelMax.textContent = 'Max Weight (kg)';
    } else if (type === 'distance') {
        conditionsGroup.classList.remove('hidden');
        labelMin.textContent = 'Min Distance (km)';
        labelMax.textContent = 'Max Distance (km)';
    } else {
        conditionsGroup.classList.add('hidden');
    }
}

// Move modal to body
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('methodModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
});

window.openMethodModal = function(zoneId = null) {
    document.getElementById('methodModalTitle').textContent = 'Add Delivery Method';
    document.getElementById('methodForm').reset();
    document.getElementById('methodId').value = '';
    
    // Reset Wizard
    currentStep = 0;
    updateWizardUI();
    
    if (zoneId) {
        document.getElementById('methodZoneId').value = zoneId;
    }
    
    togglePricingFields();
    document.getElementById('methodModal').classList.remove('hidden');
}

window.closeMethodModal = function() {
    document.getElementById('methodModal').classList.add('hidden');
}

window.editMethod = function(method) {
    document.getElementById('methodModalTitle').textContent = 'Edit Delivery Method';
    document.getElementById('methodId').value = method.id;
    document.getElementById('methodZoneId').value = method.zone_id;
    
    // Basic
    document.getElementById('methodName').value = method.name;
    document.getElementById('methodDescription').value = method.description || '';
    document.getElementById('methodDeliveryType').value = method.delivery_type || 'home';
    document.getElementById('methodCourier').value = method.courier_partner || '';
    document.getElementById('methodActive').checked = method.is_active == 1;
    
    // Pricing
    document.getElementById('methodPricingType').value = method.pricing_type || 'flat';
    document.getElementById('methodCost').value = method.cost;
    document.getElementById('methodTaxable').checked = method.is_taxable == 1;
    document.getElementById('methodMin').value = method.condition_min;
    document.getElementById('methodMax').value = method.condition_max;
    document.getElementById('methodCodCharge').value = method.cod_charge || 0;
    document.getElementById('methodFuelCharge').value = method.fuel_surcharge || 0;
    
    // Delivery
    document.getElementById('methodDays').value = method.estimated_days;
    document.getElementById('methodCutoff').value = method.cutoff_time || '';
    document.getElementById('methodSlots').checked = method.has_slots == 1;
    document.getElementById('methodSameDay').checked = method.has_same_day == 1;
    
    // Integration JSON parsing
    let integration = {};
    if (typeof method.integration_settings === 'string') {
        try { integration = JSON.parse(method.integration_settings); } catch(e){}
    } else if (typeof method.integration_settings === 'object') {
        integration = method.integration_settings;
    }
    document.getElementById('methodApiEnabled').checked = integration.api_enabled == 1;
    document.getElementById('methodAutoAssign').checked = integration.auto_assign == 1;
    document.getElementById('methodTrackingUrl').value = integration.tracking_url || '';

    // Advanced & COD
    document.getElementById('methodBadge').value = method.badge_text || '';
    document.getElementById('methodSort').value = method.sort_order || 0;
    document.getElementById('methodShowCheckout').checked = method.show_at_checkout == 1;
    
    document.getElementById('methodMinWeight').value = method.min_weight;
    document.getElementById('methodMaxWeight').value = method.max_weight;
    document.getElementById('methodDimensions').value = method.max_dimensions || '';
    document.getElementById('methodFragile').checked = method.is_fragile == 1;

    let codSettings = {};
    if (typeof method.cod_settings === 'string') {
        try { codSettings = JSON.parse(method.cod_settings); } catch(e){}
    } else if (typeof method.cod_settings === 'object') {
        codSettings = method.cod_settings;
    }
    document.getElementById('methodEnableCod').checked = codSettings.enable_cod !== undefined ? codSettings.enable_cod == 1 : true;
    document.getElementById('methodMaxCod').value = codSettings.max_cod_amount || '';

    togglePricingFields();
    // Start at Step 1
    currentStep = 0;
    updateWizardUI();
    document.getElementById('methodModal').classList.remove('hidden');
}

window.saveMethod = async function(e) {
    e.preventDefault();
    
    // Manual Validation
    const nameInput = document.getElementById('methodName');
    if (!nameInput.value.trim()) {
        currentStep = 0;
        updateWizardUI();
        nameInput.focus();
        nameInput.classList.add('border-red-500', 'ring-1', 'ring-red-500');
        // Remove error style on input
        nameInput.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        }, { once: true });
        
        // Optional: Show a toast or alert
        alert('Method Name is required.');
        return;
    }

    const formData = new FormData(e.target);
    
    // Helper to add unchecked checkboxes
    const checkboxes = ['is_active', 'is_taxable', 'has_slots', 'has_same_day', 'api_enabled', 'auto_assign', 'show_at_checkout', 'is_fragile', 'enable_cod'];
    checkboxes.forEach(id => {
        if (!e.target.querySelector(`[name="${id}"]`).checked) {
            // formData.append(id, '0'); // PHP might expect missing key as false, or we handle it in controller. 
            // My controller uses isset() so missing is fine, but for explicit '0' vs '1' usually fine to just let isset fail.
        }
    });
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/shipping/saveMethod', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error saving method');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}

window.deleteMethod = async function(id) {
    if (!confirm('Are you sure you want to delete this delivery method?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/shipping/deleteMethod', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error deleting method');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}
</script>

<script>
// Delivery Method Suggestion Logic
document.addEventListener('DOMContentLoaded', function() {
    const methodInput = document.getElementById('methodName');
    const suggestionsBox = document.getElementById('methodNameSuggestions');
    const suggestionsList = suggestionsBox.querySelector('ul');
    
    // Static Suggestion Data
    const suggestions = [
        "Standard Delivery",
        "Express Delivery",
        "Same Day Delivery",
        "Free Shipping",
        "Local Courier",
        "Village Delivery"
    ];

    function showSuggestions(filterText = '') {
        const lowerFilter = filterText.toLowerCase();
        const filtered = suggestions.filter(item => 
            item.toLowerCase().includes(lowerFilter)
        );

        if (filtered.length === 0) {
            suggestionsBox.classList.add('hidden');
            return;
        }

        suggestionsList.innerHTML = filtered.map(item => `
            <li class="px-4 py-2 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors duration-150 flex items-center justify-between group" onclick="selectSuggestion('${item}')">
                <span>${highlightMatch(item, filterText)}</span>
                <span class="opacity-0 group-hover:opacity-100 text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
            </li>
        `).join('');
        
        suggestionsBox.classList.remove('hidden');
    }

    function highlightMatch(text, filter) {
        if (!filter) return text;
        const regex = new RegExp(`(${filter})`, 'gi');
        return text.replace(regex, '<span class="font-bold text-blue-600">$1</span>');
    }

    window.selectSuggestion = function(value) {
        methodInput.value = value;
        suggestionsBox.classList.add('hidden');
        methodInput.focus();
    };

    // Input Event Listeners
    methodInput.addEventListener('focus', () => showSuggestions(methodInput.value));
    
    methodInput.addEventListener('input', () => showSuggestions(methodInput.value));

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!methodInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.add('hidden');
        }
    });

    // Keyboard navigation support (optional but nice)
    methodInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            suggestionsBox.classList.add('hidden');
        }
    });

    // Badge / Label Suggestion Logic
    const badgeInput = document.getElementById('methodBadge');
    const badgeSuggestionsBox = document.getElementById('methodBadgeSuggestions');
    const badgeSuggestionsList = badgeSuggestionsBox.querySelector('ul');
    
    // Static Suggestion Data for Badge
    const badgeSuggestions = [
        "Fastest",
        "Best Value",
        "Recommended",
        "Free",
        "Popular",
        "New",
        "Limited Area",
        "Same Day"
    ];

    function showBadgeSuggestions(filterText = '') {
        const lowerFilter = filterText.toLowerCase();
        const filtered = badgeSuggestions.filter(item => 
            item.toLowerCase().includes(lowerFilter)
        );

        if (filtered.length === 0) {
            badgeSuggestionsBox.classList.add('hidden');
            return;
        }

        badgeSuggestionsList.innerHTML = filtered.map(item => `
            <li class="px-4 py-2 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors duration-150 flex items-center justify-between group" onclick="selectBadgeSuggestion('${item}')">
                <span>${highlightMatch(item, filterText)}</span>
                <span class="opacity-0 group-hover:opacity-100 text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
            </li>
        `).join('');
        
        badgeSuggestionsBox.classList.remove('hidden');
    }

    window.selectBadgeSuggestion = function(value) {
        badgeInput.value = value;
        badgeSuggestionsBox.classList.add('hidden');
        badgeInput.focus();
    };

    // Input Event Listeners for Badge
    badgeInput.addEventListener('focus', () => showBadgeSuggestions(badgeInput.value));
    
    badgeInput.addEventListener('input', () => showBadgeSuggestions(badgeInput.value));

    // Close on outside click for Badge
    document.addEventListener('click', function(e) {
        if (!badgeInput.contains(e.target) && !badgeSuggestionsBox.contains(e.target)) {
            badgeSuggestionsBox.classList.add('hidden');
        }
    });

    // Keyboard navigation support for Badge
    badgeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            badgeSuggestionsBox.classList.add('hidden');
        }
    });
});
</script>
