<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\Data\SimpleBag;

class ConfigStrategyTest extends TestCase
{
    public function testGetId()
    {
        $strategy = new ConfigStrategy([
            'name' => null,
            'team' => 'avengers',
        ]);

        $bag = new SimpleBag([
            'id' => 0,
            'name' => 'Hulk',
        ]);

        $this->assertEquals([
            'name' => 'Hulk',
            'team' => 'avengers',
        ], $strategy->getRecordIdentifier($bag));
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\InvalidConfigException
     */
    public function testMissingRecord()
    {
        $strategy = new ConfigStrategy([
            'name' => null,
            'team' => 'avengers',
        ]);

        $bag = new SimpleBag([
            'id' => 0,
            'nickname' => 'Hulk',
        ]);

        $strategy->getRecordIdentifier($bag);
    }
}
