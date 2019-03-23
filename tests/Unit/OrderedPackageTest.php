<?php

namespace Inspirum\Balikobot\Tests\Integration\Balikobot;

use Inspirum\Balikobot\Model\Values\OrderedPackage;
use Inspirum\Balikobot\Tests\AbstractTestCase;

class OrderedPackageTest extends AbstractTestCase
{
    public function testStaticConstructor()
    {
        $orderedPackage = OrderedPackage::newInstanceFromData('cp', '0001', [
            'package_id'      => 1234,
            'carrier_id'      => "02IID",
            'track_url'       => "/track",
            'label_url'       => "/labels",
            'carrier_id_swap' => 23,
            'pieces'          => [1, 2],
        ]);

        $this->assertEquals('cp', $orderedPackage->getShipper());
        $this->assertEquals('0001', $orderedPackage->getBatchId());
        $this->assertEquals(1234, $orderedPackage->getPackageId());
        $this->assertEquals("02IID", $orderedPackage->getCarrierId());
        $this->assertEquals("/track", $orderedPackage->getTrackUrl());
        $this->assertEquals("/labels", $orderedPackage->getLabelUrl());
        $this->assertEquals(23, $orderedPackage->getCarrierIdSwap());
        $this->assertEquals([1, 2], $orderedPackage->getPieces());
    }

    public function testStaticConstructorWithMissingData()
    {
        $orderedPackage = OrderedPackage::newInstanceFromData('cp', '0001', [
            'package_id' => 1234,
            'carrier_id' => "02IID",
            'label_url'  => "/labels",
        ]);

        $this->assertEquals('cp', $orderedPackage->getShipper());
        $this->assertEquals('0001', $orderedPackage->getBatchId());
        $this->assertEquals(1234, $orderedPackage->getPackageId());
        $this->assertEquals("02IID", $orderedPackage->getCarrierId());
        $this->assertEquals(null, $orderedPackage->getTrackUrl());
        $this->assertEquals("/labels", $orderedPackage->getLabelUrl());
        $this->assertEquals(null, $orderedPackage->getCarrierIdSwap());
        $this->assertEquals([], $orderedPackage->getPieces());
    }
}
