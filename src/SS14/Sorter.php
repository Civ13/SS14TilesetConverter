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
 * This class provides functionality to sort files in a directory into subfolders
 * based on their names. Files are expected to follow a specific naming convention
 * and are grouped into pairs based on their numeric suffixes.
 * 
 * Files Naming Convention:
 * - Files should have the format: `string_number.extension`
 * - The `string` is a common prefix for related files.
 * - The `number` is an integer between 0 and 15.
 * - Files with the same `string` prefix and numbers that sum to 15 are considered a pair.
 * 
 * Unpaired Files:
 * - Files that do not have a matching pair are moved to a subfolder named `unpaired`.
 * 
 * Method:
 * - `sortFilesIntoPairs(string $directory): void`
 *   - Sorts files in the specified directory into subfolders based on the pairing logic.
 *   - Creates subfolders for each pair and moves the files into their respective subfolders.
 *   - Unpaired files are moved to the `unpaired` subfolder.
 * 
 * Example Usage:
 * - Run the script with a directory path as a command-line argument:
 *   `php sorter.php <directory_to_sort>`
 * - Alternatively, call the `sortFilesIntoPairs` method directly in your code.
 * 
 * Error Handling:
 * - If the provided directory is invalid, the script terminates with an error message.
 * 
 * Output:
 * - Displays messages indicating the movement of files and the completion of the sorting process.
 */
class Sorter {
    public static function sortFilesIntoPairs(string $directory): void
    {
        // Validate directory
        if (!is_dir($directory)) {
            throw new \Exception("Error: '$directory' is not a valid directory." . PHP_EOL);
        }

        $files = scandir($directory);
        $filePairs = [];
        $processedFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (!is_file($filePath)) {
                continue;
            }

            // Extract base name and number
            $parts = explode('.', $file);
            $extension = array_pop($parts);
            $baseNameWithNumber = implode('.', $parts);

            // Modified regex to handle names without underscores and numbers at the end
            if (preg_match('/^(.+?)(\d+)$/', $baseNameWithNumber, $matches)) {
                $baseName = $matches[1];
                $number = (int)$matches[2];

                if ($number >= 0 && $number <= 15) {
                    // Check for pairs
                    $pairNumber = 15 - $number;
                    $pairFileName = $baseName . $pairNumber . "." . $extension;
                    $pairFilePath = $directory . DIRECTORY_SEPARATOR . $pairFileName;

                    if (in_array($pairFileName, $files) && !in_array($pairFileName, $processedFiles) && !in_array($file, $processedFiles)) {
                        // Found a pair
                        $pairName = $baseName;
                        if (!isset($filePairs[$pairName])) {
                            $filePairs[$pairName] = [];
                        }
                        $filePairs[$pairName][] = $file;
                        $filePairs[$pairName][] = $pairFileName;
                        $processedFiles[] = $file;
                        $processedFiles[] = $pairFileName;
                    } else if (!in_array($file, $processedFiles)){
                        //no pair found, add to a folder named "unpaired"
                        $pairName = "unpaired";
                        if (!isset($filePairs[$pairName])) {
                            $filePairs[$pairName] = [];
                        }
                        $filePairs[$pairName][] = $file;
                        $processedFiles[] = $file;
                    }
                }
            }
        }

        // Create subfolders and move files
        foreach ($filePairs as $pairName => $pairFiles) {
            $subfolderPath = $directory . DIRECTORY_SEPARATOR . $pairName;
            if (!is_dir($subfolderPath)) {
                mkdir($subfolderPath);
            }

            foreach ($pairFiles as $file) {
                $sourcePath = $directory . DIRECTORY_SEPARATOR . $file;
                $destinationPath = $subfolderPath . DIRECTORY_SEPARATOR . $file;
                rename($sourcePath, $destinationPath);
                echo "Moved '$file' to '$pairName' folder." . PHP_EOL;
            }
        }
    }
}