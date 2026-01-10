<?php
$pageTitle = 'Support / Queries';
?>

<div class="admin-card mb-6">
    <div class="text-center py-12">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Support / Queries</h2>
        <p class="text-gray-600 mb-6">Customer support and query management system</p>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 max-w-2xl mx-auto">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Feature Coming Soon</h3>
            <p class="text-blue-700 mb-4">
                The support and query management system is currently under development. 
                This feature will allow you to manage customer inquiries, support tickets, and queries in one centralized location.
            </p>
            <div class="text-left space-y-2 text-sm text-blue-600">
                <p>✓ Ticket management system</p>
                <p>✓ Customer inquiry tracking</p>
                <p>✓ Response templates</p>
                <p>✓ Support analytics</p>
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-3">
        <a href="<?php echo SITE_URL; ?>/admin/customers" class="btn-pink-gradient px-6 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
            View Customers
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/orders" class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center gap-2">
            View Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/admin" class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center gap-2">
            Back to Dashboard
        </a>
    </div>
</div>

