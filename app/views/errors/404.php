<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-pink-600 mb-4">404</h1>
            <h2 class="text-2xl font-bold mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-8">The page you are looking for doesn't exist.</p>
            <a href="<?php echo SITE_URL; ?>" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                Go Home
            </a>
        </div>
    </div>
</body>
</html>

