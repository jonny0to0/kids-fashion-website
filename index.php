<?php
/**
 * Root Index File - Fallback Entry Point
 * This file serves as a fallback if .htaccess rewrite fails
 * It internally loads the public/index.php without redirecting
 */

// Include the public index.php directly (internal, no redirect)
require_once __DIR__ . '/public/index.php';

