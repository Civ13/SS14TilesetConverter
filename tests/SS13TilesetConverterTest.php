<?php

/*
 * This file is a part of the Civ14 project.
 *
 * Copyright (c) 2025-present Valithor Obsidion <valithor@valzargaming.com>
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

use Civ14\SS13TilesetConverter;

class SS13TilesetConverterTest extends TestCase
{
    public function testMethod1()
    {
        $converter = new SS13TilesetConverter(
            'aztec',
            __DIR__ . '\ss13\\',
            __DIR__ . '\ss14\\',
        );
        $converter->run();
        $this->assertTrue(true);
    }
}