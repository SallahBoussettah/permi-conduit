<?php
// This script provides a direct CSS link for when Vite assets fail to load

// Get the manifest file
$manifestPath = __DIR__ . '/build/manifest.json';
$cssPath = '';

if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    
    // Get CSS file path
    if (isset($manifest['resources/css/app.css']['file'])) {
        $cssPath = '/build/assets/' . basename($manifest['resources/css/app.css']['file']);
    }
}

// Output a direct link to the CSS file
header('Content-Type: text/html');
echo '<!DOCTYPE html>
<html>
<head>
    <title>Direct CSS Link</title>
</head>
<body>
    <h1>Direct CSS Link</h1>
    <p>Copy the following code and add it to your layout file just before the closing &lt;/head&gt; tag:</p>
    <pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">
    &lt;!-- Fallback for Vite CSS loading issues --&gt;
    &lt;link rel="stylesheet" href="' . htmlspecialchars($cssPath) . '"&gt;
    </pre>
    
    <h2>Steps to fix:</h2>
    <ol>
        <li>Add the above line to your layout files (resources/views/layouts/app.blade.php and resources/views/layouts/main.blade.php)</li>
        <li>Make sure the /build directory and all assets are uploaded to your server</li>
        <li>Check file permissions (should be 644 for files, 755 for directories)</li>
        <li>Clear Laravel cache by running the artisan commands mentioned in the documentation</li>
    </ol>
    
    <h2>Alternative solution:</h2>
    <p>You can also try uploading the CSS file directly and linking to it with an absolute URL:</p>
    <pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">
    &lt;link rel="stylesheet" href="https://your-domain.com/build/assets/app-[hash].css"&gt;
    </pre>
    <p>Replace [hash] with the actual filename hash shown above.</p>
</body>
</html>'; 