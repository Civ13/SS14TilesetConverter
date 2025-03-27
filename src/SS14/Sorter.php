<?php declare(strict_types=1);

/*
 * This file is a part of the Civ14 project.
 *
 * Copyright (c) 2025-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Civ14;

/**
 * Class Sorter
 *
 * The `Sorter` class is responsible for organizing files within a specified directory
 * into pairs based on specific criteria. It processes files by matching them according
 * to their numeric suffixes and performs operations such as pairing, moving, and renaming.
 *
 * Features:
 * - Validates the provided directory.
 * - Scans the directory for files and filters unnecessary entries.
 * - Pairs files based on their names and numeric suffixes.
 * - Handles unpaired files by categorizing them into a separate folder.
 * - Creates subfolders for paired files and moves them accordingly.
 *
 * Usage:
 * - Instantiate the class with a valid directory path.
 * - Call the `run()` method to execute the sorting and processing logic.
 *
 * Exceptions:
 * - Throws `\RuntimeException` if the directory is invalid or cannot be read.
 * - Throws `\Exception` if file operations (e.g., moving files) fail.
 *
 * Example:
 * ```php
 * $sorter = new Sorter('/path/to/directory');
 * $sorter->run();
 * ```
 */
class Sorter {
    private array $filePairs = [];
    private array $processedFiles = [];
    public function __construct(
        private string $directory
    ){
        if (!is_dir($this->directory)) {
            throw new \RuntimeException("'$this->directory' is not a valid directory.");
        }
    }

    /**
     * Executes the main logic of the sorter.
     *
     * This method scans the specified directory for files, filters out
     * unnecessary entries (e.g., '.' and '..'), and processes the files
     * by sorting them into pairs and performing operations on those pairs.
     *
     * @throws \RuntimeException If the directory cannot be read.
     *
     * @return void
     */
    public function run(): void
    {
        if (! $files = scandir($this->directory)) {
            throw new \RuntimeException("Failed to read directory '$this->directory'.");
        }
        $this->sortFilesIntoPairs(array_diff($files, ['.', '..']));
        $this->processFilePairs();
    }

    /**
     * Sorts files into pairs based on their filenames and specific criteria.
     *
     * This method processes a list of file pairs and attempts to match files
     * based on their names. It uses a regular expression to extract the base
     * name and a numeric suffix from the filename. Files are paired if their
     * numeric suffixes add up to 15. If a pair is found, the files are marked
     * as paired; otherwise, they are marked as unpaired.
     *
     * Criteria for pairing:
     * - The file must exist in the specified directory.
     * - The file must not have been processed already.
     * - The filename must match the pattern `^(.+?)(\d+)$`, where the numeric
     *   suffix is extracted.
     * - The numeric suffix must be between 0 and 15 (inclusive).
     * - A corresponding pair file must exist, with the same base name and a
     *   numeric suffix such that the two suffixes add up to 15.
     *
     * If a file does not meet the criteria or its pair is not found, it is
     * marked as unpaired.
     *
     * @return void
     */
    private function sortFilesIntoPairs(array $files): void
    {
        foreach ($files as $file) {
            if (!is_file($this->directory . DIRECTORY_SEPARATOR . $file) || in_array($file, $this->processedFiles)) {
                continue;
            }
            $pathInfo = pathinfo($file);
            // TODO: Modified regex to handle names without underscores and numbers at the end
            if (! preg_match('/^(.+?)(\d+)$/', $pathInfo['filename'], $matches)) {
                continue;
            }
            if (! isset($matches[2]) || ! is_numeric($matches[2])) {
                continue;
            }
            $number = (int)$matches[2];
            if ($number < 0 || $number > 15) {
                continue;
            }

            // Check for pairs
            $pairFileName = $matches[1] . (15 - $number) . "." . ($pathInfo['extension'] ?? '.png');
            (!in_array($pairFileName, $this->filePairs) || in_array($pairFileName, $this->processedFiles))
                ? $this->__unpaired($file)
                : $this->__paired($file, $matches[1], $pairFileName);
                continue;
        }
    }

    /**
     * Handles pairing of files by associating a base name with its corresponding files.
     *
     * This method ensures that the provided file and its pair are grouped together
     * under the same base name in the `$filePairs` array. It also marks both files
     * as processed by adding them to the `$processedFiles` array.
     *
     * @param string $file         The primary file to be paired.
     * @param string $baseName     The base name used as the key for grouping paired files.
     * @param string $pairFileName The secondary file to be paired with the primary file.
     *
     * @return void
     */
    private function __paired(string $file, string $baseName, string $pairFileName): void
    {
        if (!isset($this->filePairs[$baseName])) $this->filePairs[$baseName] = [];
        $this->filePairs[$baseName][] = $file;
        $this->filePairs[$baseName][] = $pairFileName;
        $this->processedFiles[] = $file;
        $this->processedFiles[] = $pairFileName;
    }

    /**
     * Handles files that do not have a matching pair by adding them to a folder named "unpaired".
     *
     * @param  string $file The file to be marked as unpaired.
     * @return void
     */
    private function __unpaired(string $file): void
    {
        $this->processedFiles[] = $file;
        $this->filePairs["unpaired"] = isset($this->filePairs["unpaired"])
            ? $file
            : [];
    }

    /**
     * Processes file pairs by creating subfolders and renaming files.
     *
     * This method iterates over the file pairs stored in the `$filePairs` property.
     * For each pair, it creates a subfolder named after the pair's key within the
     * specified directory. Then, it renames the files in the pair and moves them
     * into the created subfolder.
     *
     * @return void
     */
    private function processFilePairs(): void
    {
        foreach ($this->filePairs as $pairName => $pairFiles) {
            $this::createFolders($subfolderPath = $this->directory . DIRECTORY_SEPARATOR . $pairName);
            $this->renameFiles(
                $subfolderPath,
                ...$pairFiles
            );
        }
    }
    
    /**
     * Creates the specified folders if they do not already exist.
     *
     * @param string ...$folders A list of folder paths to create.
     * 
     * @throws \RuntimeException If a folder cannot be created.
     */
    private static function createFolders(string ...$folders): void
    {
        foreach ($folders as $folder) if (!is_dir($folder) && !mkdir($folder)) {
            throw new \RuntimeException("Failed to create directory '$folder'.");
        }
    }

    /**
     * Renames (moves) a list of files to a specified subfolder.
     *
     * @param string $subfolderPath The destination subfolder path where the files will be moved.
     * @param string ...$pairFiles A variadic list of file names to be moved.
     *
     * @throws \Exception If a file cannot be moved to the specified subfolder.
     * 
     * @return void
     */
    private function renameFiles(string $subfolderPath, string ...$pairFiles): void
    {
        foreach ($pairFiles as $file) if (!rename(
            $this->directory . DIRECTORY_SEPARATOR . $file,
            $subfolderPath . DIRECTORY_SEPARATOR . $file
        )) throw new \Exception("Failed to move '$file' to '$subfolderPath'.");
    }
}