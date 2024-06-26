<?php

namespace GPXTest\UpgradeFees;

use GPX\Model\Credit;
use GPX\Model\UnitType;
use PHPUnit\Framework\TestCase;

class UpgradeFeeTest extends TestCase {

    /**
     * @dataProvider unitTypeProvider
     */
    public function test_getting_number_of_bedrooms(string $unit_type, string $expected) {
        $this->assertEquals($expected, UnitType::getNumberOfBedrooms($unit_type));
    }

    /**
     * @dataProvider upgradeFeeProvider
     */
    public function test_calculate_upgrade_fee(string $credit, string $booking, int $expected) {
        $this->assertEquals($expected, Credit::calculateUpgradeFee($booking, $credit));
    }

    public function unitTypeProvider(): array {
        return [
            ['1b/4', '1'],
            ['2b/6', '2'],
            ['3b/8', '3'],
            ['2b/8', '2'],
            ['St/4', 'studio'],
            ['St/2', 'studio'],
            ['1b/2', '1'],
            ['1b/6', '1'],
            ['2br/6', '2'],
            ['1b/3', '1'],
            ['HR/2', 'studio'],
            ['2BLOFT/8', '2'],
            ['2B VIL/6', '2'],
            ['4b/12', '4'],
            ['HR/4', 'studio'],
            ['4b/10', '4'],
            ['3b/12', '3'],
            ['2b/10', '2'],
            ['2b/4', '2'],
            ['HR/2+2', 'studio'],
            ['2b/7', '2'],
            ['St/2+2', 'studio'],
            ['1b/2+2', '1'],
            ['1b/5', '1'],
            ['2b/6+2', '2'],
            ['1BMINI/4', '1'],
            ['1B DLX/4', '1'],
            ['1BTWN/4', '1'],
            ['St/3', 'studio'],
            ['HR/3', 'studio'],
            ['1b/2+3', '1'],
            ['1b/4+2', '1'],
            ['St/4+2', 'studio'],
            ['3b/10', '3'],
            ['2BCAB/6', '2'],
            ['3b/1', '3'],
            ['1b/1', '1'],
            ['1b/', '1'],
            ['HDLX/2', 'studio'],
            ['1B OCN/4', '1'],
            ['St/1', 'studio'],
            ['St/', 'studio'],
            ['1b/8', '1'],
            ['2BLOFT/6', '2'],
            ['2b/2', '2'],
            ['3b/7', '3'],
            ['HDLX/4', 'studio'],
            ['3b/9', '3'],
            ['1br/4', '1'],
            ['1b-Loft/6', '1'],
            ['Std', 'studio'],
            ['3b/6', '3'],
            ['Std/2', 'studio'],
            ['HTL/2', 'studio'],
            ['1br/6', '1'],
            ['1b/4 Partial Kitchen', '1'],
            ['1br/3', '1'],
            ['Std/4', 'studio'],
        ];
    }

    public function upgradeFeeProvider(): array {
        return [
            ['studio', 'studio', 0],
            ['studio', '1', 85],
            ['studio', '2', 185],
            ['studio', '3', 185],
            ['studio', '4', 185],

            ['1', 'studio', 0],
            ['1', '1', 0],
            ['1', '2', 185],
            ['1', '3', 185],
            ['1', '4', 185],

            ['2', 'studio', 0],
            ['2', '1', 0],
            ['2', '2', 0],
            ['2', '3', 185],
            ['2', '4', 185],

            ['3', 'studio', 0],
            ['3', '1', 0],
            ['3', '2', 0],
            ['3', '3', 0],
            ['3', '4', 0],

            ['4', 'studio', 0],
            ['4', '1', 0],
            ['4', '2', 0],
            ['4', '3', 0],
            ['4', '4', 0],
        ];
    }
}
