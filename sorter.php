<?php

namespace Civ14;

if (! $autoloader = require file_exists(__DIR__.'/vendor/autoload.php') ? __DIR__.'/vendor/autoload.php' : __DIR__.'/../../autoload.php')
    throw new \Exception('Composer autoloader not found. Run `composer install` and try again.');

// Example usage:
// $directoryToSort = "in/walls.rsi/"; // Replace with the actual directory
// sortFilesIntoPairs($directoryToSort);

// Example usage with command line argument
if (! isset($argv[1]) || ! $directory = $argv[1]) die("Usage: php sorter.php <directory_to_sort>");
$sorter = new Sorter($directory);
$sorter->run();
echo "Finished sorting files in $directory." . PHP_EOL;