<?php declare(strict_types=1);

/*
 * This file is a part of the Civ14 project.
 *
 * Copyright (c) 2025-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Civ14;

use \GdImage;
use \RuntimeException;

class SS13TilesetConverter
{
    public function __construct(
        protected string $baseFileName,
        protected string $inputDir,
        protected string $outputDir,
        protected bool $verbose = false
    ) {
        if (!extension_loaded('gd')) throw new RuntimeException("The GD extension is not enabled. Please enable it to use this script." . PHP_EOL);
    }

    /**
     * Executes the image conversion and JSON generation process.
     *
     * This method performs the following steps:
     * 1. Converts images using the specified base file name, input directory, and output directory.
     * 2. Generates a JSON file based on the converted images and stores it in the output directory.
     *
     * @return void
     *
     * @throws RuntimeException If any of the input images fail to load or if the image dimensions 
     *                          are not divisible by 4.
     *
     * The function performs the following steps:
     * - Loads three input images based on the base file name and input directory.
     * - Validates that the dimensions of the input images are divisible by 4.
     * - Calculates the quadrant size and final output dimensions based on the scaling factor.
     * - Generates and writes multiple output images using predefined transformation patterns.
     * - Cleans up resources by destroying the image resources after processing.
     *
     * Notes:
     * - The function assumes that the input images follow a naming convention of 
     *   "<baseFileName>0.png", "<baseFileName>7.png", and "<baseFileName>15.png".
     * - The output images are saved with specific names derived from the base file name.
     * - The function uses helper functions `generate()` and `write()` to create and save the images.
     */
    public function run(): void
    {
        self::convert_images($this->baseFileName, $this->inputDir, $this->outputDir, $this->verbose);
        self::generate_json($this->baseFileName, $this->outputDir, $this->verbose);
    }

    /**
     * Converts and processes a set of images based on a given base file name, input directory, 
     * and output directory. The function generates multiple output images by applying transformations 
     * and scaling to the input images.
     *
     * @param string $baseFileName The base name of the image files (e.g., "image" for "image0.png").
     * @param string $inputDir     The directory where the input images are located.
     * @param string $outputDir    The directory where the output images will be saved.
     * @param bool   $verbose      Whether to output verbose messages to the terminal.
     * @param int    $scaleFactor  The scaling factor to apply to the output images. Default is 1.
     *
     * @return void
     *
     * @throws RuntimeException If any of the input images fail to load or if the image dimensions 
     *                          are not divisible by 4.
     *
     * The function performs the following steps:
     * - Loads three input images based on the base file name and input directory.
     * - Validates that the dimensions of the input images are divisible by 4.
     * - Calculates the quadrant size and final output dimensions based on the scaling factor.
     * - Generates and writes multiple output images using predefined transformation patterns.
     * - Cleans up resources by destroying the image resources after processing.
     *
     * Notes:
     * - The function assumes that the input images follow a naming convention of 
     *   "<baseFileName>0.png", "<baseFileName>7.png", and "<baseFileName>15.png".
     * - The output images are saved with specific names derived from the base file name.
     * - The function uses helper functions `generate()` and `write()` to create and save the images.
     */
    private static function convert_images(
        string $baseFileName,
        string $inputDir,
        string $outputDir,
        bool $verbose = false,
        int $scaleFactor = 1
    ): void
    {
        // Load source images
        if (! $zerosource = imagecreatefrompng($inputPath = $inputDir . $baseFileName . '0.png')) throw new RuntimeException("Failed to load image: $inputPath");
        if (! $sevensource = imagecreatefrompng($inputPath = $inputDir . $baseFileName . '7.png')) throw new RuntimeException("Failed to load image: $inputPath");
        if (! $fifteensource = imagecreatefrompng($inputPath = $inputDir . $baseFileName . '15.png')) throw new RuntimeException("Failed to load image: $inputPath");
        assert($zerosource instanceof GdImage && $sevensource instanceof GdImage && $fifteensource instanceof GdImage);

        // Get original dimensions
        $width = imagesx($zerosource);
        $height = imagesy($zerosource);
        // Ensure dimensions are divisible by 4
        if ($width % 4 !== 0 || $height % 4 !== 0) throw new RuntimeException("Image dimensions must be divisible by 4.");
        // Calculate quadrant size (16x16 for a 32x32 image)
        $quadSize = (int)($width / 2);
        // Final output size (scaled up)
        $finalWidth = (int)(($quadSize * 2) * $scaleFactor);  // 64
        $finalHeight = (int)(($quadSize * 4) * $scaleFactor); // 128

        // Create output images
        copy($inputDir . $baseFileName . '0.png', $outputDir . 'full.png');
        if ($verbose) echo "Image saved as: full.png" . PHP_EOL;

        $output = self::generate(
            $zerosource,
            [
              /*[5, 0, 0, 0, 0, 0],*/ [1, 1, 0, 0, 0, 0],
                [4, 0, 1, 0, 0, 0], /*[6, 1, 1, 0, 0, 0],*/
                [2, 0, 2, 0, 0, 0], /*[7, 1, 2, 0, 0, 0],*/
              /*[8, 0, 3, 0, 0, 0],*/ [3, 1, 3, 0, 0, 0],
            ],
            $quadSize,
            $finalWidth,
            $finalHeight,
            $scaleFactor
        );
        self::write(
            $output,
            $outputDir,
            $verbose,
            $baseFileName . '0.png',
            $baseFileName . '2.png'
        );
        assert($output instanceof GdImage);
        imagedestroy($output);

        $output = self::generate(
            $sevensource,
            [
                /*[5, 0, 0, 0, 0, 0],*/ [1, 1, 0, 0, 0, 1],
                [1, 0, 1, 0, 0, 3],   /*[6, 1, 1, 0, 0, 0],*/
                [1, 0, 2, 0, 0, 2],   /*[7, 1, 2, 0, 0, 0],*/
                /*[8, 0, 3, 0, 0, 0],*/ [1, 1, 3, 1, 0, 2],
            ],
            $quadSize,
            $finalWidth,
            $finalHeight,
            $scaleFactor
        );
        self::write(
            $output,
            $outputDir,
            $verbose,
            $baseFileName . '1.png',
            $baseFileName . '3.png',
        );
        assert($output instanceof GdImage);
        imagedestroy($output);

        $output = self::generate(
            $sevensource,
            [
                /*[5, 0, 0, 0, 0, 0],*/ [1, 1, 0, 0, 0, 0],
                [1, 0, 1, 0, 0, 2],   /*[6, 1, 1, 0, 0, 0],*/
                [1, 0, 2, 0, 0, 1],   /*[7, 1, 2, 0, 0, 0],*/
                /*[8, 0, 3, 0, 0, 0],*/ [1, 1, 3, 0, 0, 3],
            ],
            $quadSize,
            $finalWidth,
            $finalHeight,
            $scaleFactor
        );
        self::write(
            $output,
            $outputDir,
            $verbose,
            $baseFileName . '4.png',
            $baseFileName . '6.png',
        );
        assert($output instanceof GdImage);
        imagedestroy($output);

        $output = self::generate(
            $sevensource,
            [
                /*[5, 0, 0, 0, 0, 0],*/ [2, 1, 0, 1, 0, 0],
                [4, 0, 1, 0, 0, 0],   /*[6, 1, 1, 0, 0, 0],*/
                [2, 0, 2, 0, 0, 0],   /*[7, 1, 2, 0, 0, 0],*/
                /*[8, 0, 3, 0, 0, 0],*/ [4, 1, 3, 1, 0, 0],
            ],
            $quadSize,
            $finalWidth,
            $finalHeight,
            $scaleFactor
        );
        self::write(
            $output,
            $outputDir,
            $verbose,
            $baseFileName . '5.png',
        );
        assert($output instanceof GdImage);
        imagedestroy($output);

        $output = self::generate(
            $fifteensource,
            [
                /*[5, 0, 0, 0, 0, 0],*/ [1, 1, 0, 1, 0, 0],
                [1, 0, 1, 0, 0, 0],   /*[6, 1, 1, 0, 0, 0],*/
                [1, 0, 2, 0, 0, 0],   /*[7, 1, 2, 0, 0, 0],*/
                /*[8, 0, 3, 0, 0, 0],*/ [1, 1, 3, 1, 0, 0],
            ],
            $quadSize,
            $finalWidth,
            $finalHeight,
            $scaleFactor
        );
        self::write(
            $output,
            $outputDir,
            $verbose,
            $baseFileName . '7.png',
        );

        imagedestroy($zerosource);
        imagedestroy($sevensource);
        imagedestroy($fifteensource);
    }

    /**
     * Generates a transformed image based on the provided source, mapping, and dimensions.
     *
     * @param $source The source image resource to be transformed.
     * @param array $mapping An array defining the mapping for the transformation.
     * @param int $quadSize The size of each quadrant in the transformation.
     * @param int $finalWidth The width of the final output image.
     * @param int $finalHeight The height of the final output image.
     * @param int $scaleFactor The scaling factor to be applied during the transformation.
     * 
     * @return resource The generated image resource with the applied transformations.
     */
    private static function generate(
        $source,
        array $mapping,
        int $quadSize,
        int $finalWidth,
        int $finalHeight,
        int $scaleFactor
    ): GdImage
    {
        imagesavealpha($output = imagecreatetruecolor($finalWidth, $finalHeight), true);
        imagefill($output, 0, 0, $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127));
        self::transform($source, $mapping, $output, $quadSize, $finalWidth, $finalHeight, $transparent, $finalWidth, $scaleFactor);
        return $output;
    }

    /**
     * Transforms an image by extracting, scaling, flipping, rotating, and repositioning quadrants
     * based on a given mapping, and outputs the final image.
     *
     * @param          $source       The source image resource to transform.
     * @param array    $mapping      An array of mappings, where each mapping is an array containing:
     *                               - int $quadNum: The quadrant number (1 to 4).
     *                               - int $destX: The destination X-coordinate in the output image.
     *                               - int $destY: The destination Y-coordinate in the output image.
     *                               - bool $flipX: Whether to flip the quadrant horizontally.
     *                               - bool $flipY: Whether to flip the quadrant vertically.
     *                               - int $rotate: The number of 90-degree counter-clockwise rotations to apply (0-3).
     * @param          &$output      The output image resource, passed by reference.
     * @param int      $quadSize     The size (width and height) of each quadrant in the source image.
     * @param int      $finalWidth   The width of the final output image.
     * @param int      $finalHeight  The height of the final output image.
     * @param int      $transparent  The color identifier for transparency in the output image.
     * @param int      $paddedWidth  Optional. The additional width to pad the output image. Default is 0.
     * @param int      $scaleFactor  Optional. The scaling factor for each quadrant. Default is 1.
     *
     * @return void
     */
    private static function transform(
        $source,
        array $mapping,
        GdImage &$output,
        int $quadSize,
        int $finalWidth,
        int $finalHeight,
        int $transparent,
        int $paddedWidth = 0,
        int $scaleFactor = 1
    ): void
    {
        // Create output image with adjusted width
        imagesavealpha($output = imagecreatetruecolor($finalWidth + $paddedWidth, $finalHeight), true);
        imagefill($output, 0, 0, $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127));

        // Extract and reposition quadrants
        foreach ($mapping as [$quadNum, $destX, $destY, $flipX, $flipY, $rotate]) {
            // Create a scaled quadrant image
            imagesavealpha($quadImage = imagecreatetruecolor((int)($quadSize * $scaleFactor), (int)($quadSize * $scaleFactor)), true);
            imagefill($quadImage, 0, 0, $transparent);

            // Copy and scale the quadrant
            imagecopyresampled(
                $quadImage, $source,
                0, 0, (int)((($quadNum - 1) % 2) * $quadSize), (int)(floor(($quadNum - 1) / 2) * $quadSize),
                (int)($quadSize * $scaleFactor), (int)($quadSize * $scaleFactor),
                $quadSize, $quadSize
            );

            // Apply flipping if needed
            if ($flipX || $flipY) imageflip($quadImage, ($flipX ? IMG_FLIP_HORIZONTAL : 0) | ($flipY ? IMG_FLIP_VERTICAL : 0));

            // Apply rotation if needed
            if ($rotate) $quadImage = imagerotate($quadImage, -90 * $rotate, $transparent);

            // Copy the final quadrant to the output image with padding offset
            imagecopy(
                $output, $quadImage,
                (int)(($destX * $quadSize * $scaleFactor) + ($paddedWidth / 2)), (int)($destY * $quadSize * $scaleFactor),
                0, 0,
                imagesx($quadImage), imagesy($quadImage)
            );
            imagedestroy($quadImage);
        }
    }

    /**
     * Saves an image resource to multiple file paths within a specified output directory.
     *
     * @param         $output    The image resource to be saved.
     * @param string  $outputDir The directory where the images will be saved.
     * @param bool    $verbose   Whether to output verbose messages to the terminal.
     * @param string  ...$paths  One or more file paths (relative to $outputDir) where the image will be saved.
     *
     * @return void
     */
    private static function write(
        &$output,
        string $outputDir,
        bool $verbose = false,
        string ...$paths
    ): void
    {
        foreach ($paths as $path) self::__write($output, $outputDir, $path, $verbose);
    }

    /**
     * Saves an image resource to multiple file paths within a specified output directory.
     *
     * @param $output    The image resource to be saved.
     * @param string  $outputDir The directory where the images will be saved.
     * @param bool    $verbose   Whether to output verbose messages to the terminal.
     * @param string  $path      A file paths (relative to $outputDir) where the image will be saved.
     *
     * @return void
     */
    private static function __write(
        &$output,
        string $outputDir,
        string $path,
        bool $verbose = false,
    ): void
    {
        imagepng($output, $outputDir . $path);
        if ($verbose) echo "Image saved as: $path" . PHP_EOL;
    }

    /**
     * Generates a JSON metadata file for a given base file name and output directory.
     *
     * The generated JSON file contains metadata including version, license, copyright,
     * size, and states with directional information. The states are dynamically created
     * based on the provided base file name.
     *
     * @param string $baseFileName The base name to be used for generating state names in the JSON.
     * @param string $outputDir The directory where the generated JSON file will be saved.
     * @param bool   $verbose   Whether to output verbose messages to the terminal.
     *
     * @return void
     */
    private static function generate_json(
        string $baseFileName,
        string $outputDir,
        bool $verbose
    ): string
    {
        $json = <<<JSON
        {
            "version": 1,
            "license": "CC-BY-SA-3.0",
            "copyright": "Created by Valithor for Space Station 14",
            "size": {
            "x": 32,
            "y": 32
            },
            "states": [
            {
                "name": "full"
            },
            {
                "name": "{$baseFileName}0",
                "directions": 4
            },
            {
                "name": "{$baseFileName}1",
                "directions": 4
            },
            {
                "name": "{$baseFileName}2",
                "directions": 4
            },
            {
                "name": "{$baseFileName}3",
                "directions": 4
            },
            {
                "name": "{$baseFileName}4",
                "directions": 4
            },
            {
                "name": "{$baseFileName}5",
                "directions": 4
            },
            {
                "name": "{$baseFileName}6",
                "directions": 4
            },
            {
                "name": "{$baseFileName}7",
                "directions": 4
            }
            ]
        }
        JSON;
        file_put_contents($outputDir . 'meta.json', $json);
        if ($verbose) echo "Meta file saved as: meta.json" . PHP_EOL;
        return $json;
    }
}