<div class="container mx-auto px-6 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Support Tickets</h1>
            <p class="text-gray-600">Manage customer inquiries and complaints</p>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0">
            <a href="<?php echo SITE_URL; ?>/admin/support/settings" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </a>
        </div>
    </div>

    <!-- Stats / Alerts -->
    <?php if (!$supportEnabled): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Support system is currently disabled for customers. <a href="<?php echo SITE_URL; ?>/admin/support/settings" class="font-medium underline hover:text-yellow-600">Enable it in settings</a>.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Search subject, ticket ID..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="open" <?php echo ($filters['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="in_progress" <?php echo ($filters['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo ($filters['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="w-full md:w-48">
                <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500" onchange="this.form.submit()">
                    <option value="all">All Priorities</option>
                    <option value="low" <?php echo ($filters['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($filters['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($filters['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="urgent" <?php echo ($filters['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Ticket List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No tickets found matching your criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo $ticket['ticket_id']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full border border-gray-200"><?php echo ucfirst($ticket['category']); ?></span>
                                <?php if($ticket['order_id']): ?>
                                <span class="ml-1 bg-blue-50 text-blue-600 text-xs px-2 py-0.5 rounded-full border border-blue-100">Order #<?php echo $ticket['order_id']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-pink-100 rounded-full flex items-center justify-center text-pink-600 font-bold text-xs">
                                        <?php echo substr($ticket['first_name'], 0, 1) . substr($ticket['last_name'], 0, 1); ?>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ticket['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $priorityClass = 'bg-gray-100 text-gray-800';
                                if ($ticket['priority'] === 'low') $priorityClass = 'bg-green-100 text-green-800';
                                if ($ticket['priority'] === 'medium') $priorityClass = 'bg-blue-100 text-blue-800';
                                if ($ticket['priority'] === 'high') $priorityClass = 'bg-orange-100 text-orange-800';
                                if ($ticket['priority'] === 'urgent') $priorityClass = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $priorityClass; ?>">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
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
                                <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?><br>
                                <span class="text-xs text-gray-400"><?php echo date('h:i A', strtotime($ticket['updated_at'])); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo SITE_URL; ?>/admin/support/view/<?php echo $ticket['ticket_id']; ?>" class="text-pink-600 hover:text-pink-900">View / Reply</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($tickets)): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo $pagination->render(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
