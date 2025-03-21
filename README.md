# SS14 Image Converter

This project provides a PHP library for converting images specifically for the SS14 game. It includes functionality to transform images and generate corresponding JSON metadata.

## Installation

To install the project, you can use Composer. Run the following command in your terminal:

```
composer install
```

## Usage

To use the `SS13TilesetConverter`, you need to provide the input and output directories along with the base file name of the images you want to convert. 

Here is an example of how to use the converter:

```php
$options = getopt("I:O:");
if (!isset($options['I']) || !isset($options['O'])) {
    die("Usage: php script.php -I \"input_directory\" -O \"output_directory\"" . PHP_EOL);
}

$inputDir = rtrim($options['I'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$outputDir = rtrim($options['O'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

$converter = new Civ14\SS13TilesetConverter($baseFileName, $inputDir, $outputDir);
$converter->run();
```

## Requirements

- PHP 7.4 or higher
- GD extension enabled

## License

This project is licensed under the MIT License. See the LICENSE file for more details.