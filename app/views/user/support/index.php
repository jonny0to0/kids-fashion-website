<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Customer Support</h1>

        <?php if (!$enabled): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Our support system is currently unavailable. Please check back later.
                    </p>
                </div>
            </div>
        </div>
        <?php else: ?>

        <!-- Contact Cards -->
        <?php if (!empty($contact['phone']) || !empty($contact['email'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <?php if (!empty($contact['phone'])): ?>
            <div class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
                <div class="bg-pink-100 p-3 rounded-full text-pink-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Call Us</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($contact['phone']); ?></p>
                    <?php if (!empty($contact['hours'])): ?>
                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($contact['hours']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($contact['email'])): ?>
            <div class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Email Us</h3>
                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-pink-600 hover:underline"><?php echo htmlspecialchars($contact['email']); ?></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">My Tickets</h2>
            <a href="<?php echo SITE_URL; ?>/support/create" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors font-medium shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Ticket
            </a>
        </div>

        <!-- Ticket List -->
        <?php if (empty($tickets)): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="inline-block p-4 bg-gray-100 rounded-full text-gray-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets yet</h3>
            <p class="text-gray-500 mb-6">Need help with an order or account issue?</p>
            <a href="<?php echo SITE_URL; ?>/support/create" class="text-pink-600 hover:text-pink-700 font-medium">Create your first ticket &rarr;</a>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo $ticket['ticket_id']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full border border-gray-200"><?php echo ucfirst($ticket['category']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClass = 'bg-gray-100 text-gray-800';
                                if ($ticket['status'] === 'open') $statusClass = 'bg-green-100 text-green-800';
                                if ($ticket['status'] === 'in_progress') $statusClass = 'bg-yellow-100 text-yellow-800';
                                if ($ticket['status'] === 'resolved') $statusClass = 'bg-blue-100 text-blue-800';
                                if ($ticket['status'] === 'closed') $statusClass = 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($ticket['status'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($ticket['order_id']): ?>
                                #<?php echo $ticket['order_id']; ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo SITE_URL; ?>/support/view/<?php echo $ticket['ticket_id']; ?>" class="text-pink-600 hover:text-pink-900">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
