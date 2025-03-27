<?php

/*
 * This file is a part of the Civ14 project.
 *
 * Copyright (c) 2025-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Civ14;

if (! $autoloader = require file_exists(__DIR__.'/vendor/autoload.php') ? __DIR__.'/vendor/autoload.php' : __DIR__.'/../../autoload.php')
    throw new \Exception('Composer autoloader not found. Run `composer install` and try again.');

$options = getopt("I:O:");
if (!isset($options['I']) || !isset($options['O'])) die("Usage: php script.php -I \"input_directory\" -O \"output_directory\"" . PHP_EOL );

$inputDir = rtrim($options['I'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$outputDir = rtrim($options['O'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!is_dir($inputDir)) die("Input directory does not exist: $inputDir"  . PHP_EOL);
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) die("Failed to create output directory: $outputDir" . PHP_EOL);
if (realpath($inputDir) === realpath($outputDir)) die("Directories for input and output must not be the same."  . PHP_EOL);

// Function to recursively find files matching *0.png in subdirectories
function findBaseFiles(string $directory): array {
    $files = [];
    $items = scandir($directory);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $directory . $item;
        if (is_dir($path)) {
            // Recurse into subdirectories
            $files = array_merge($files, findBaseFiles($path . DIRECTORY_SEPARATOR));
        } elseif (is_file($path) && preg_match('/^[^0-9]+(?=0\.png$)/', basename($path))) {
            // Check if file matches the pattern
            if (str_ends_with($path, "0.png"))
            $files[] = $path;
        }
    }
    return $files;
}

// Find all base files in the input directory and its subdirectories
$allBaseFiles = findBaseFiles($inputDir);

if (empty($allBaseFiles)) die("Base file matching *0.png not found in input directory or its subdirectories.");

// Process each base file found
foreach ($allBaseFiles as $baseFile) {
    $baseFileName = substr(pathinfo($baseFile, PATHINFO_FILENAME), 0, -1);
    $currentInputDir = dirname($baseFile) . DIRECTORY_SEPARATOR;
    $currentOutputDir = $outputDir . str_replace($inputDir, "", $currentInputDir);

    if (!is_dir($currentOutputDir) && !mkdir($currentOutputDir, 0777, true)) die("Failed to create output directory: $currentOutputDir" . PHP_EOL);

    // Generate SS14 images using SS13 images
    $SS14ImageConverter = new SS13TilesetConverter($baseFileName, $currentInputDir, $currentOutputDir);
    $SS14ImageConverter->run();

    echo "Conversion complete: $baseFileName in $currentInputDir to $currentOutputDir" . PHP_EOL;
}

echo "All conversions complete." . PHP_EOL;
