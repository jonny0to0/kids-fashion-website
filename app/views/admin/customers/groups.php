<?php
/**
 * Customer Groups Management Page
 */

require_once __DIR__ . '/../_breadcrumb.php';

renderBreadcrumb([
    ['label' => 'Home', 'url' => '/admin'],
    ['label' => 'Customers', 'url' => '/admin/customers'],
    ['label' => 'Groups']
]);
?>

<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Customer Groups</h1>
    </div>
    <p class="text-gray-600 mb-6">Manage customer groups and segments for targeted marketing.</p>

    <div class="admin-card">
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Customer Groups Feature</h3>
            <p class="text-gray-500 mb-8 max-w-lg mx-auto">This feature will allow you to organize customers into groups for targeted marketing campaigns, special pricing tiers, and personalized content delivery.</p>
            <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                Coming Soon
            </div>
        </div>
    </div>
</div>

