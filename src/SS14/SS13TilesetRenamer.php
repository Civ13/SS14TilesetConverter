<?php declare(strict_types=1);

/*
 * This file is a part of the Civ14 project.
 *
 * Copyright (c) 2025-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Civ14;

use \RuntimeException;

class SS13TilesetRenamer
{
    public function __construct(
        protected string $inputDir,
        protected string $outputDir,
        protected bool $verbose = false
    ) {
        if (!is_dir($inputDir)) throw new RuntimeException("Input directory does not exist: $inputDir");
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) throw new RuntimeException("Failed to create output directory: $outputDir");
        if (realpath($inputDir) === realpath($outputDir)) throw new RuntimeException("Directories for input and output must not be the same.");
    }

    public function run(): void
    {
        // Find all base files in the input directory and its subdirectories
        if (empty($allBaseFiles = self::findBaseFiles($this->inputDir))) throw new RuntimeException("Base file matching *0.png not found in input directory or its subdirectories.");

        // Process each base file found
        foreach ($allBaseFiles as $baseFile) {
            $currentInputDir = dirname($baseFile) . DIRECTORY_SEPARATOR;
            $currentOutputDir = $this->outputDir . str_replace($this->inputDir, "", $currentInputDir);
            if (!is_dir($currentOutputDir) && !mkdir($currentOutputDir, 0777, true)) die("Failed to create output directory: $currentOutputDir" . PHP_EOL);

            // Generate SS14 images using SS13 images
            $SS14ImageConverter = new SS13TilesetConverter(
                substr(pathinfo($baseFile, PATHINFO_FILENAME), 0, -1),
                $currentInputDir,
                $currentOutputDir
            );
            $SS14ImageConverter->run();
        }
    }

    // Function to recursively find files matching *0.png in subdirectories
    public static function findBaseFiles(string $directory): array
    {
        return array_reduce(
            array_diff(scandir($directory), ['.', '..']),
            fn($files, $item) => is_dir($path = $directory . $item)
                ? array_merge($files, self::findBaseFiles($path . DIRECTORY_SEPARATOR))
                : (is_file($path) && preg_match('/^[^0-9]+(?=0\.png$)/', basename($path)) && str_ends_with($path, "0.png") ? [...$files, $path] : $files),
            []
        );
    }
}