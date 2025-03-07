<?php

declare(strict_types=1);

namespace Inspirum\Balikobot\Tests\Unit;

use Inspirum\Balikobot\Model\Aggregates\PackageTransportCostCollection;
use Inspirum\Balikobot\Model\Values\PackageTransportCost;
use Inspirum\Balikobot\Tests\AbstractTestCase;
use InvalidArgumentException;
use RuntimeException;

class PackageTransportCostCollectionTest extends AbstractTestCase
{
    public function testGetters(): void
    {
        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->add(new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->add(new PackageTransportCost('78923', 'toptrans', 20, 'CZK'));

        $this->assertEquals('toptrans', $transportCosts->getShipper());
        $this->assertEquals(2, $transportCosts->count());
        $this->assertEquals(['34567', '78923'], $transportCosts->getBatchIds());
        $this->assertEquals(520, $transportCosts->getTotalCost());
        $this->assertEquals('CZK', $transportCosts->getCurrencyCode());
    }

    public function testThrowsErrorOnMissingShipper(): void
    {
        $this->expectException(RuntimeException::class);

        $packages = new PackageTransportCostCollection();

        $packages->getShipper();
    }

    public function testThrowsErrorOnMissingShipperForCurrencyGetter(): void
    {
        $this->expectException(RuntimeException::class);

        $packages = new PackageTransportCostCollection();

        $packages->getCurrencyCode();
    }

    public function testThrowsErrorOnDefferentCurrencies(): void
    {
        $this->expectException(RuntimeException::class);

        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->add(new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->add(new PackageTransportCost('78923', 'toptrans', 2.1, 'EUR'));

        $transportCosts->getTotalCost();
    }

    public function testCollectionShipperFromFirstPackage(): void
    {
        $transportCosts = new PackageTransportCostCollection();

        $transportCosts->add(new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));

        $this->assertEquals('toptrans', $transportCosts->getShipper());
    }

    public function testDiffShipperThrowsError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Package is from different shipper ("ppl" instead of "toptrans")');

        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->add(new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->add(new PackageTransportCost('78923', 'ppl', 20, 'CZK'));
    }

    public function testDiffShipperThrowsErrorWithOffsetSetMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Package is from different shipper ("ppl" instead of "toptrans")');

        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->offsetSet(1, new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->offsetSet(2, new PackageTransportCost('78923', 'ppl', 20, 'CZK'));
    }

    public function testSupportArrayAccess(): void
    {
        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->offsetSet(1, new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->offsetSet(4, new PackageTransportCost('78923', 'toptrans', 20, 'CZK'));

        $this->assertEquals('78923', $transportCosts->offsetGet(4)->getBatchId());
        $this->assertEquals(2, $transportCosts->count());
        $this->assertTrue($transportCosts->offsetExists(1));

        $transportCosts->offsetUnset(1);

        $this->assertFalse($transportCosts->offsetExists(1));
    }

    public function testSupportIteratorAggregate(): void
    {
        $transportCosts = new PackageTransportCostCollection('toptrans');

        $transportCosts->add(new PackageTransportCost('34567', 'toptrans', 500, 'CZK'));
        $transportCosts->add(new PackageTransportCost('78923', 'toptrans', 20, 'CZK'));

        $iterator = $transportCosts->getIterator();

        $this->assertEquals(2, $iterator->count());
        $this->assertEquals('34567', $iterator->current()->getBatchId());
    }
}
