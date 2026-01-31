<?php
$subsection = $_GET['subsection'] ?? 'zones';

// Handle routing for shipping subsections
if ($subsection === 'delivery') {
    include __DIR__ . '/shipping/delivery.php';
} else {
    // Default to zones
    include __DIR__ . '/shipping/zones.php';
}
?>

