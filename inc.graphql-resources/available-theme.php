<?php

/**
 * Scans the assets/themes directory and returns a JSON list of available themes.
 * Each theme object contains the directory name and a title-cased version of the name.
 */

// Set the content type to JSON
header('Content-Type: application/json');
$cacheTime = 86400; // 24 jam
header('Cache-Control: public, max-age=' . $cacheTime);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');

// Define the path to the themes directory
$themesPath = __DIR__ . '/assets/themes';

// Initialize an array to hold the theme data
$themes = [];

// Get all subdirectories in the themes directory
$directories = glob($themesPath . '/*', GLOB_ONLYDIR);

if ($directories !== false) {
    foreach ($directories as $dir) {
        if(file_exists($dir . '/style.min.css'))
        {
            $themeName = basename($dir);
            // Convert snake_case or kebab-case to Title Case (e.g., "dark-blue" becomes "Dark Blue")
            $themes[] = ['name' => $themeName, 'title' => ucwords(str_replace(['-', '_'], ' ', $themeName))];
        }
    }
}

// Output the themes as a JSON array
echo json_encode($themes, JSON_PRETTY_PRINT);