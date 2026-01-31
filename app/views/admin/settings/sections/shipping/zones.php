<?php
// Shipping Zones View
$shippingZoneModel = new ShippingZone();
$zones = $shippingZoneModel->getAllZones();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0 border-b border-gray-200 pb-4 mb-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Shipping Zones</h3>
            <p class="text-sm text-gray-500">Configure shipping rules based on geographic regions.</p>
        </div>
        <button onclick="openZoneModal()" class="btn-pink-gradient px-4 py-2 rounded-lg font-medium text-sm w-full md:w-auto">
            Add New Zone
        </button>
    </div>

    <?php if (empty($zones)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Zones Configured</h3>
            <p class="text-gray-500 max-w-md mx-auto mb-6">Create zones to apply specific shipping rates to different countries, states, or regions.</p>
            
            <button onclick="openZoneModal()" class="btn-pink-gradient px-4 py-2 rounded-lg font-medium text-sm">
                Add New Zone
            </button>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h4 class="font-medium text-gray-700">Active Zones</h4>
                <div class="text-xs text-gray-500">Sorted by Priority (High to Low)</div>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($zones as $zone): ?>
                    <div class="p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-0 hover:bg-gray-50 group">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($zone['zone_name']); ?></span>
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full border border-blue-200 uppercase">
                                    <?php echo htmlspecialchars($zone['zone_type'] ?? 'Country'); ?>
                                </span>
                                <?php if (($zone['priority'] ?? 0) > 0): ?>
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full border border-yellow-200" title="Priority">
                                        P: <?php echo $zone['priority']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                <?php 
                                $locs = json_decode($zone['locations'] ?? $zone['regions'] ?? '[]', true);
                                if (empty($locs)) {
                                    echo 'All locations';
                                } else {
                                    $count = count($locs);
                                    $firstFew = array_slice($locs, 0, 3);
                                    echo implode(', ', $firstFew);
                                    if ($count > 3) echo ' + ' . ($count - 3) . ' more';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-xs font-medium <?php echo $zone['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> rounded-full">
                                <?php echo $zone['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <div class="flex gap-2">
                                <button onclick='editZone(<?php echo json_encode($zone); ?>)' class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
                                <button onclick="deleteZone(<?php echo $zone['id']; ?>)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Zone Modal -->
<div id="zoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg mx-4 md:mx-auto shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Add New Zone</h3>
            <form id="zoneForm" onsubmit="saveZone(event)">
                <input type="hidden" name="id" id="zoneId">
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zone Name</label>
                        <input type="text" name="zone_name" id="zoneName" required placeholder="e.g. South India, Mumbai Remote"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zone Type</label>
                            <select name="zone_type" id="zoneType" onchange="updateLocationInput()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="country">Country Level</option>
                                <option value="state">State Level</option>
                                <option value="district">District Level</option>
                                <option value="pin">PIN Code Specific</option>
                                <option value="pin_range">PIN Code Range</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority (0-100)</label>
                            <input type="number" name="priority" id="zonePriority" value="0" min="0" max="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                             <p class="text-xs text-gray-500 mt-1">Higher number = First match</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" id="locationLabel">Included Locations</label>
                        <textarea name="locations" id="zoneLocations" rows="4" placeholder="Enter locations..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1" id="locationHint">Enter one per line or comma separated.</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="zoneActive" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="zoneActive" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Zone
                    </button>
                    <button type="button" onclick="closeZoneModal()" class="px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Move modal to body
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('zoneModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
});

window.updateLocationInput = function() {
    const type = document.getElementById('zoneType').value;
    const label = document.getElementById('locationLabel');
    const hint = document.getElementById('locationHint');
    const input = document.getElementById('zoneLocations');
    
    if (type === 'country') {
        label.textContent = 'Country Code';
        input.placeholder = 'IN';
        input.value = 'IN'; // Auto-set defaults
        hint.textContent = 'Use ISO 2-letter country code (e.g., IN, US).';
    } else if (type === 'state') {
        label.textContent = 'States';
        input.placeholder = 'Maharashtra\nDelhi\nKarnataka';
        hint.textContent = 'Enter full state names, one per line.';
    } else if (type === 'district') {
        label.textContent = 'Districts / Cities';
        input.placeholder = 'Mumbai\nThane\nPune';
        hint.textContent = 'Enter district or city names.';
    } else if (type === 'pin') {
        label.textContent = 'PIN Codes';
        input.placeholder = '400001\n400002\n400003';
        hint.textContent = 'Enter specific PIN codes.';
    } else if (type === 'pin_range') {
        label.textContent = 'PIN Ranges (Not fully supported in UI text mode yet)';
        input.placeholder = 'TO BE IMPLEMENTED';
        hint.textContent = 'Use standard PIN type for now or specific JSON format.';
    }
}

window.openZoneModal = function() {
    document.getElementById('modalTitle').textContent = 'Add New Zone';
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneId').value = '';
    document.getElementById('zoneActive').checked = true;
    document.getElementById('zoneType').value = 'country';
    document.getElementById('zonePriority').value = '0';
    updateLocationInput();
    document.getElementById('zoneModal').classList.remove('hidden');
}

window.closeZoneModal = function() {
    document.getElementById('zoneModal').classList.add('hidden');
}

window.editZone = function(zone) {
    document.getElementById('modalTitle').textContent = 'Edit Zone';
    document.getElementById('zoneId').value = zone.id;
    document.getElementById('zoneName').value = zone.zone_name;
    document.getElementById('zoneActive').checked = zone.is_active == 1;
    document.getElementById('zoneType').value = zone.zone_type || 'country';
    document.getElementById('zonePriority').value = zone.priority || 0;
    
    updateLocationInput();

    // Parse locations
    let locations = '';
    try {
        // Try locations first, fall back to regions
        const rawLoc = zone.locations || zone.regions || '[]';
        const locData = JSON.parse(rawLoc);
        if (Array.isArray(locData)) {
            locations = locData.join('\n');
        }
    } catch(e) {
        // use raw if not json
        locations = zone.locations || zone.regions || '';
    }
    document.getElementById('zoneLocations').value = locations;
    
    document.getElementById('zoneModal').classList.remove('hidden');
}

window.saveZone = async function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Add is_active manually if unchecked (checkboxes don't send value if unchecked)
    if (!document.getElementById('zoneActive').checked) {
        formData.append('is_active', '0');
    }
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/shipping/saveZone', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error saving zone');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}

window.deleteZone = async function(id) {
    if (!confirm('Are you sure you want to delete this zone? All associated delivery methods will also be deleted.')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/shipping/deleteZone', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error deleting zone');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}
</script>
