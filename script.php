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

$files = array_filter(glob($inputDir . '*0.png'), fn($file) => preg_match('/^[^0-9]+(?=0\.png$)/', basename($file)));
if (empty($files)) die("Base file matching *0.png not found in input directory.");
$baseFileName = substr(pathinfo($files[0], PATHINFO_FILENAME), 0, -1);

// Generate SS14 images using SS13 images
$SS14ImageConverter = new SS13TilesetConverter($baseFileName, $inputDir, $outputDir);
$SS14ImageConverter->run();

echo "Conversion complete: $baseFileName" . PHP_EOL;