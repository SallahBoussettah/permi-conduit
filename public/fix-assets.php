<?php
// Diagnostic script for Vite assets

// Display PHP info for debugging
echo "<h1>Asset Path Diagnostics</h1>";

// Check if the manifest file exists
$manifestPath = __DIR__ . '/build/manifest.json';
echo "<p>Checking for manifest file at: " . $manifestPath . "</p>";

if (file_exists($manifestPath)) {
    echo "<p style='color:green;'>✓ Manifest file exists</p>";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    echo "<pre>";
    print_r($manifest);
    echo "</pre>";
    
    // Check CSS file
    if (isset($manifest['resources/css/app.css']['file'])) {
        $cssFile = __DIR__ . '/build/assets/' . basename($manifest['resources/css/app.css']['file']);
        echo "<p>Checking for CSS file at: " . $cssFile . "</p>";
        
        if (file_exists($cssFile)) {
            echo "<p style='color:green;'>✓ CSS file exists</p>";
            $cssUrl = '/build/assets/' . basename($manifest['resources/css/app.css']['file']);
            echo "<p>CSS URL should be: " . $cssUrl . "</p>";
        } else {
            echo "<p style='color:red;'>✗ CSS file not found!</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ CSS entry not found in manifest!</p>";
    }
    
    // Check JS file
    if (isset($manifest['resources/js/app.js']['file'])) {
        $jsFile = __DIR__ . '/build/assets/' . basename($manifest['resources/js/app.js']['file']);
        echo "<p>Checking for JS file at: " . $jsFile . "</p>";
        
        if (file_exists($jsFile)) {
            echo "<p style='color:green;'>✓ JS file exists</p>";
            $jsUrl = '/build/assets/' . basename($manifest['resources/js/app.js']['file']);
            echo "<p>JS URL should be: " . $jsUrl . "</p>";
        } else {
            echo "<p style='color:red;'>✗ JS file not found!</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ JS entry not found in manifest!</p>";
    }
} else {
    echo "<p style='color:red;'>✗ Manifest file not found!</p>";
}

// List all files in the build directory
echo "<h2>Files in build directory:</h2>";
if (is_dir(__DIR__ . '/build')) {
    $buildFiles = scandir(__DIR__ . '/build');
    echo "<ul>";
    foreach ($buildFiles as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
    
    // Check assets directory
    if (is_dir(__DIR__ . '/build/assets')) {
        $assetFiles = scandir(__DIR__ . '/build/assets');
        echo "<h2>Files in build/assets directory:</h2>";
        echo "<ul>";
        foreach ($assetFiles as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li>" . $file . "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>✗ build/assets directory not found!</p>";
    }
} else {
    echo "<p style='color:red;'>✗ build directory not found!</p>";
} 