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

$renamer = new SS13TilesetRenamer(
    rtrim($options['I'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
    rtrim($options['O'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
);

$renamer->run();
echo "Conversion complete: $baseFileName in $currentInputDir to $currentOutputDir" . PHP_EOL;