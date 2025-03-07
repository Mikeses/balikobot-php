<?php

declare(strict_types=1);

namespace Inspirum\Balikobot\Tests\Unit\Balikobot;

use DateTime;
use Inspirum\Balikobot\Exceptions\BadRequestException;
use Inspirum\Balikobot\Model\Aggregates\OrderedPackageCollection;
use Inspirum\Balikobot\Model\Values\OrderedPackage;
use Inspirum\Balikobot\Services\Balikobot;
use function count;

class TrackPackagesMethodTest extends AbstractBalikobotTestCase
{
    public function testMakeRequest(): void
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status'   => 200,
            'packages' => [
                0 => [
                    'carrier_id' => '1234',
                    'status'     => 200,
                    'states'     => [
                        [
                            'date'           => '2018-11-07 14:15:01',
                            'name'           => 'Doručování zásilky',
                            'status_id'      => 2,
                            'status_id_v2'   => 2.2,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka je v přepravě.',
                        ],
                    ],
                ],
            ],
        ]);

        $service = new Balikobot($requester);

        $package = new OrderedPackage('1', 'ppl', '0001', '1234');

        $service->trackPackage($package);

        $requester->shouldHaveReceived(
            'request',
            [
                'https://apiv2.balikobot.cz/v2/ppl/track',
                [
                    'carrier_ids' => [
                        '1234',
                    ],
                ],
            ]
        );

        $this->assertTrue(true);
    }

    public function testResponseData(): void
    {
        $service = $this->newMockedBalikobot(200, [
            'status'   => 200,
            'packages' => [
                0 => [
                    'carrier_id' => '1234',
                    'status'     => 200,
                    'states'     => [
                        [
                            'date'           => '2018-11-07 14:15:01',
                            'name'           => 'Doručování zásilky',
                            'status_id'      => 2,
                            'status_id_v2'   => 2.2,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka je v přepravě.',
                        ],
                        [
                            'date'           => '2018-11-08 18:00:00',
                            'name'           => 'Dodání zásilky. (77072 - Depo Olomouc 72)',
                            'status_id'      => 1,
                            'status_id_v2'   => 1.2,
                            'type'           => 'notification',
                            'name_balikobot' => 'Zásilka byla doručena příjemci.',
                        ],
                        [
                            'date'           => '2020-11-06 21=>00=>00',
                            'name'           => 'Obdrženy údaje k zásilce.',
                            'status_id'      => -1,
                            'status_id_v2'   => -1,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka zatím nebyla předána dopravci.',
                        ],
                    ],
                ],
            ],
        ]);

        $package = new OrderedPackage('1', 'ppl', '0001', '1234');

        $statuses = $service->trackPackage($package);

        $this->assertEquals(3, count($statuses));
        $this->assertEquals(2, $statuses[0]->getGroupId());
        $this->assertEquals(2.2, $statuses[0]->getId());
        $this->assertEquals(new DateTime('2018-11-08 18:00:00'), $statuses[1]->getDate());
        $this->assertEquals('Obdrženy údaje k zásilce.', $statuses[2]->getDescription());
        $this->assertEquals('Zásilka zatím nebyla předána dopravci.', $statuses[2]->getName());
        $this->assertEquals('notification', $statuses[1]->getType());
        $this->assertEquals(1.2, $statuses[1]->getId());
    }

    public function testMakeRequestWithMultiplePackages(): void
    {
        $requester = $this->newRequesterWithMockedRequestMethod(200, [
            'status'   => 200,
            'packages' => [
                0 => [
                    'carrier_id' => '1236',
                    'status'     => 200,
                    'states'     => [
                        [
                            'date'           => '2018-11-07 14:15:01',
                            'name'           => 'Doručování zásilky',
                            'status_id'      => 2,
                            'status_id_v2'   => 2.2,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka je v přepravě.',
                        ],
                    ],
                ],
                1 => [
                    'carrier_id' => '1234',
                    'status'     => 200,
                    'states'     => [
                        [
                            'date'           => '2018-11-08 18:00:00',
                            'name'           => 'Dodání zásilky. (77072 - Depo Olomouc 72)',
                            'status_id'      => 1,
                            'status_id_v2'   => 1.2,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka byla doručena příjemci.',
                        ],
                    ],
                ],
            ],
        ]);

        $service = new Balikobot($requester);

        $packages = new OrderedPackageCollection();
        $packages->add(new OrderedPackage('1', 'ppl', '0001', '1236'));
        $packages->add(new OrderedPackage('2', 'ppl', '0001', '1234'));

        $service->trackPackages($packages);

        $requester->shouldHaveReceived(
            'request',
            [
                'https://apiv2.balikobot.cz/v2/ppl/track',
                [
                    'carrier_ids' => [
                        '1236',
                        '1234',
                    ],
                ],
            ]
        );

        $this->assertTrue(true);
    }

    public function testResponseDataWithMultiplePackages(): void
    {
        $service = $this->newMockedBalikobot(200, [
            'status'   => 200,
            'packages' => [
                0 => [
                    'carrier_id' => '1236',
                    'status'     => 200,
                    'states'     => [
                        [
                            'date'           => '2018-11-07 14:15:01',
                            'name'           => 'Doručování zásilky',
                            'status_id'      => 2,
                            'status_id_v2'   => 2.2,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka je v přepravě.',
                        ],
                        [
                            'date'           => '2018-11-08 18:00:00',
                            'name'           => 'Dodání zásilky. (77072 - Depo Olomouc 72)',
                            'status_id'      => 1,
                            'status_id_v2'   => 1.2,
                            'type'           => 'notification',
                            'name_balikobot' => 'Zásilka byla doručena příjemci.',
                        ],
                        [
                            'date'           => '2020-11-06 21=>00=>00',
                            'name'           => 'Obdrženy údaje k zásilce.',
                            'status_id'      => -1,
                            'status_id_v2'   => -1,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka zatím nebyla předána dopravci.',
                        ],
                    ],
                ],
                1 => [
                    'carrier_id' => '1234',
                    'status'     => 200,
                    'states'     => [
                        [

                            'date'           => '2018-11-08 14:18:06',
                            'name'           => 'Dodání zásilky. (77072 - Depo Olomouc 72)',
                            'status_id'      => 1,
                            'status_id_v2'   => 1.1,
                            'type'           => 'event',
                            'name_balikobot' => 'Zásilka byla doručena příjemci.',
                        ],
                    ],
                ],
            ],
        ]);

        $packages = new OrderedPackageCollection();
        $packages->add(new OrderedPackage('1', 'ppl', '0001', '1236'));
        $packages->add(new OrderedPackage('2', 'ppl', '0001', '1234'));

        $statuses = $service->trackPackages($packages);

        $this->assertCount(2, $statuses);
        $this->assertCount(3, $statuses[0]);
        $this->assertEquals(2, $statuses[0][0]->getGroupId());
        $this->assertEquals(2.2, $statuses[0][0]->getId());
        $this->assertEquals(new DateTime('2018-11-08 18:00:00'), $statuses[0][1]->getDate());
        $this->assertEquals('Obdrženy údaje k zásilce.', $statuses[0][2]->getDescription());
        $this->assertEquals('Zásilka zatím nebyla předána dopravci.', $statuses[0][2]->getName());
        $this->assertEquals(1.2, $statuses[0][1]->getId());
        $this->assertEquals(1.1, $statuses[1][0]->getId());
        $this->assertEquals(new DateTime('2018-11-08 14:18:06'), $statuses[1][0]->getDate());
    }

    public function testThrowsExceptionWhenNoReturnStatus(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Technologie dopravce není dostupná');

        $service = $this->newMockedBalikobot(200, [
            'packages' => [
                0 => [
                    'carrier_id'     => '1234',
                    'status'         => 503,
                    'status_message' => 'Technologie dopravce je momentálně nedostupná. Zopakujte dotaz později.',
                ],
            ],
        ]);

        $package = new OrderedPackage('1', 'gls', '0001', '1234');

        $service->trackPackage($package);
    }
}
