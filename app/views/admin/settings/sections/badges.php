<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Badge Configuration</h3>
        <p class="text-gray-500 text-sm">Manage how product badges are displayed and prioritized on product cards.</p>
    </div>

    <!-- General Settings -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Badges per Card</label>
            <input type="number" name="settings[badge_max_count]" 
                   value="<?php echo $currentSettings['badge_max_count']['value'] ?? 2; ?>" 
                   min="1" max="5" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Maximum number of badges to show on a single product card.</p>
        </div>

        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">High Discount Threshold (%)</label>
            <input type="number" name="settings[badge_high_discount_threshold]" 
                   value="<?php echo $currentSettings['badge_high_discount_threshold']['value'] ?? 40; ?>" 
                   min="1" max="100" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Discounts above this percentage can override other badges.</p>
        </div>

        <!-- Top Selling Rules -->
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">Top Selling Min Orders (30 Days)</label>
            <input type="number" name="settings[badge_top_selling_min_orders]" 
                   value="<?php echo $currentSettings['badge_top_selling_min_orders']['value'] ?? 50; ?>" 
                   min="0" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Minimum sold quantity to qualify for Top Selling badge automatically.</p>
        </div>

        <!-- Low Stock Rules -->
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">Low Stock Threshold</label>
            <input type="number" name="settings[badge_low_stock_threshold]" 
                   value="<?php echo $currentSettings['badge_low_stock_threshold']['value'] ?? 5; ?>" 
                   min="1" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Stock quantity below which the Low Stock badge appears.</p>
        </div>

        <!-- Rating Rules -->
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating Min Value</label>
            <input type="number" name="settings[badge_rating_min_val]" step="0.1"
                   value="<?php echo $currentSettings['badge_rating_min_val']['value'] ?? 4.0; ?>" 
                   min="1" max="5" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Minimum average rating required.</p>
        </div>

        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating Min Reviews</label>
            <input type="number" name="settings[badge_rating_min_count]" 
                   value="<?php echo $currentSettings['badge_rating_min_count']['value'] ?? 10; ?>" 
                   min="1" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
            <p class="text-xs text-gray-500 mt-1">Minimum number of reviews required for Rating badge.</p>
        </div>
    </div>

    <!-- Badge Priorities -->
    <div class="mb-6">
        <h4 class="font-semibold text-gray-800 mb-4">Badge Status & Priority</h4>
        <p class="text-sm text-gray-500 mb-4">Lower priority number means higher importance (1 is highest).</p>
        
        <!-- Hidden Inputs for JSON serialization -->
        <input type="hidden" name="settings[badge_priorities]" id="input_badge_priorities">
        <input type="hidden" name="settings[disabled_badges]" id="input_disabled_badges">

        <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Badge Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enable/Disable</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    // Defaults
                    $defaults = [
                        'top_selling' => 1,
                        'discount' => 2,
                        'rating' => 3,
                        'low_stock' => 4,
                        'new' => 5
                    ];
                    
                    // Get saved priorities if they exist
                    $savedPriorities = isset($currentSettings['badge_priorities']['value']) 
                        ? json_decode($currentSettings['badge_priorities']['value'], true) 
                        : [];
                        
                    $priorities = array_merge($defaults, is_array($savedPriorities) ? $savedPriorities : []);
                    
                    // Get disabled badges
                    $disabledBadges = isset($currentSettings['disabled_badges']['value']) 
                        ? json_decode($currentSettings['disabled_badges']['value'], true) 
                        : [];
                    if (!is_array($disabledBadges)) $disabledBadges = [];

                    $badges = [
                        'top_selling' => ['label' => '🔥 Top Selling', 'desc' => 'Performance based'],
                        'discount' => ['label' => '💸 Discount %', 'desc' => 'Pricing based'],
                        'rating' => ['label' => '⭐ Rating', 'desc' => 'Trust based'],
                        'low_stock' => ['label' => '⚠️ Low Stock', 'desc' => 'Urgency based'],
                        'new' => ['label' => '🆕 New Arrival', 'desc' => 'Time based']
                    ];
                    ?>

                    <?php foreach ($badges as $key => $info): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900"><?php echo $info['label']; ?></span>
                                <span class="text-xs text-gray-500"><?php echo $info['desc']; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap w-32">
                            <input type="number" 
                                   data-priority-key="<?php echo $key; ?>"
                                   value="<?php echo $priorities[$key]; ?>" 
                                   min="1" max="10" 
                                   class="w-20 px-3 py-1 border border-gray-300 rounded focus:ring-pink-500 focus:border-pink-500 text-sm priority-input">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       data-badge-key="<?php echo $key; ?>"
                                       value="1" 
                                       class="sr-only peer badge-toggle"
                                       <?php echo !in_array($key, $disabledBadges) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                            </label>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial serialization
        updateJsonData();

        // Add listeners
        document.querySelectorAll('.priority-input, .badge-toggle').forEach(el => {
            el.addEventListener('change', updateJsonData);
        });

        function updateJsonData() {
            const priorities = {};
            const disabled = [];

            // Collect priorities
            document.querySelectorAll('.priority-input').forEach(input => {
                const key = input.dataset.priorityKey;
                priorities[key] = parseInt(input.value) || 99;
            });

            // Collect disabled badges
            document.querySelectorAll('.badge-toggle').forEach(input => {
                const key = input.dataset.badgeKey;
                if (!input.checked) {
                    disabled.push(key);
                }
            });

            // Update hidden inputs
            document.getElementById('input_badge_priorities').value = JSON.stringify(priorities);
            document.getElementById('input_disabled_badges').value = JSON.stringify(disabled);
        }
    });
</script>
