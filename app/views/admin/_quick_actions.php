<?php
/**
 * Admin Quick Actions Component
 * 
 * Renders modern, context-aware quick action buttons following UX best practices.
 * 
 * Usage:
 *   renderQuickActions([
 *       ['type' => 'primary', 'icon' => 'plus', 'label' => 'Add Category', 'url' => '/admin/categories/add', 'tooltip' => 'Creates a new parent category'],
 *       ['type' => 'secondary', 'icon' => 'download', 'label' => 'Import', 'url' => '/admin/categories/import'],
 *       ['type' => 'secondary', 'icon' => 'upload', 'label' => 'Export', 'url' => '/admin/categories/export']
 *   ], 'top-right');
 * 
 * @param array $actions Array of action items with: type, icon, label, url, tooltip (optional), confirm (optional)
 * @param string $placement 'top-right' (default), 'below-title', 'floating'
 * @param string $maxActions Maximum number of actions to show (default: 3)
 */

function renderQuickActions($actions = [], $placement = 'top-right', $maxActions = 3)
{
    if (empty($actions) || !is_array($actions)) {
        return;
    }

    // Limit to maxActions
    $visibleActions = array_slice($actions, 0, $maxActions);
    $hiddenActions = array_slice($actions, $maxActions);

    // Icon mapping (Heroicons outline style)
    $iconMap = [
        'plus' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>',
        'download' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>',
        'upload' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>',
        'eye' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>',
        'save' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3 3V4"></path></svg>',
        'trash' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
        'package' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
        'settings' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
        'refresh' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>',
    ];

    // Button style classes based on type
    $buttonStyles = [
        'primary' => 'bg-pink-600 text-white hover:bg-pink-700 hover:shadow-lg focus:ring-2 focus:ring-pink-500 focus:ring-offset-2',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:border-gray-400 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2',
        'tertiary' => 'bg-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 hover:shadow-lg focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
    ];

    // Container classes based on placement
    $containerClasses = [
        'top-right' => 'flex justify-end items-center gap-3 flex-wrap',
        'below-title' => 'flex items-center gap-3 flex-wrap mt-4',
        'floating' => 'fixed bottom-4 right-4 z-50 flex flex-col gap-2',
    ];

    $containerClass = $containerClasses[$placement] ?? $containerClasses['top-right'];

    echo '<div class="quick-actions ' . $containerClass . '">';

    foreach ($visibleActions as $action) {
        $type = $action['type'] ?? 'secondary';
        $icon = $action['icon'] ?? '';
        $label = $action['label'] ?? '';
        $url = $action['url'] ?? '#';
        $tooltip = $action['tooltip'] ?? '';
        $confirm = $action['confirm'] ?? null;
        $ariaLabel = $action['aria-label'] ?? $label;

        // Build full URL
        $fullUrl = (strpos($url, 'http') === 0 || strpos($url, '/') !== 0) ? $url : SITE_URL . $url;

        // Get icon SVG
        $iconSvg = $iconMap[$icon] ?? '';

        // Get button style
        $buttonClass = $buttonStyles[$type] ?? $buttonStyles['secondary'];

        // Build attributes
        $attrs = [];
        $attrs[] = 'class="quick-action-btn ' . $buttonClass . ' inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 min-h-[44px] focus:outline-none"';
        $attrs[] = 'href="' . htmlspecialchars($fullUrl) . '"';
        $attrs[] = 'aria-label="' . htmlspecialchars($ariaLabel) . '"';

        if ($tooltip) {
            $attrs[] = 'title="' . htmlspecialchars($tooltip) . '"';
        }

        if ($confirm) {
            $attrs[] = 'onclick="return confirm(\'' . htmlspecialchars($confirm) . '\');"';
        }

        echo '<a ' . implode(' ', $attrs) . '>';
        if ($iconSvg) {
            echo $iconSvg;
        }
        echo '<span>' . htmlspecialchars($label) . '</span>';
        echo '</a>';
    }

    // If there are hidden actions, show a dropdown (optional enhancement)
    if (!empty($hiddenActions) && $placement !== 'floating') {
        echo '<div class="relative quick-actions-dropdown">';
        echo '<button class="quick-action-btn ' . $buttonStyles['secondary'] . ' inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 min-h-[44px] focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" aria-label="More actions">';
        echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>';
        echo '<span>More</span>';
        echo '</button>';
        // Dropdown menu would go here (can be enhanced with JavaScript)
        echo '</div>';
    }

    echo '</div>';
}

// Add CSS styles for quick actions
function getQuickActionsStyles()
{
    return '
    <style>
        .quick-action-btn {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .quick-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .quick-action-btn:active {
            transform: translateY(0);
        }
        .quick-action-btn:focus-visible {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }
        @media (max-width: 640px) {
            .quick-actions {
                width: 100%;
                flex-direction: column;
            }
            .quick-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    ';
}
?>