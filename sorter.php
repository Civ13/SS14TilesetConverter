<?php

/**
 * Sorts files in a directory into subfolders based on their names.
 * Files are expected to have the format: string_number.extension
 * where number is between 0 and 15. Files with the same string prefix
 * and numbers that sum to 15 are considered a pair and moved to a
 * subfolder.
 */

function sortFilesIntoPairs(string $directory): void
{
    // Validate directory
    if (!is_dir($directory)) {
        die("Error: '$directory' is not a valid directory." . PHP_EOL);
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
    echo "Finished sorting files." . PHP_EOL;
}

// Example usage:
// $directoryToSort = "in/walls.rsi/"; // Replace with the actual directory
// sortFilesIntoPairs($directoryToSort);

// Example usage with command line argument
if (isset($argv[1])) {
    $directoryToSort = $argv[1];
    sortFilesIntoPairs($directoryToSort);
} else {
    echo "Usage: php sorter.php <directory_to_sort>" . PHP_EOL;
}
?>
