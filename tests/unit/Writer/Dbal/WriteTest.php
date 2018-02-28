<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SlayerBirden\DataFlow\Data\SimpleBag;
use SlayerBirden\DataFlow\EmitterInterface;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\ConfigStrategy;

class WriteTest extends TestCase
{
    /**
     * @var Write
     */
    private $write;
    /**
     * @var ObjectProphecy
     */
    private $strategy;
    /**
     * @var ObjectProphecy
     */
    private $emitter;
    /**
     * @var ObjectProphecy
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = $this->prophesize(Connection::class);
        $utility = $this->prophesize(WriterUtilityInterface::class);
        $utility->getColumns(Argument::any())->willReturn([]);
        $this->strategy = $this->prophesize(ConfigStrategy::class);
        $this->emitter = $this->prophesize(EmitterInterface::class);
        $this->write = new Write(
            'test',
            $this->connection->reveal(),
            'heroes',
            $utility->reveal(),
            $this->strategy->reveal(),
            $this->emitter->reveal()
        );
    }

    public function testNoId()
    {
        $this->strategy->getRecordIdentifier(Argument::any())->willReturn([]);
        $this->emitter->emit(Argument::exact('record_insert'), Argument::any(), Argument::any())->shouldBeCalled();

        $this->write->pass(new SimpleBag([]));
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Writer\Dbal\InvalidIdentificationException
     *
     */
    public function testMultipleMatches()
    {
        $this->strategy->getRecordIdentifier(Argument::any())->willReturn([
            'id' => 1,
        ]);

        $statement = $this->prophesize(\Doctrine\DBAL\Statement::class);
        $statement->execute(Argument::any())->willReturn();
        $statement->fetchColumn()->willReturn(2);

        $builder = $this->prophesize(\Doctrine\DBAL\Query\QueryBuilder::class);
        $builder->select(Argument::any())->willReturn($builder->reveal());
        $builder->from(Argument::any())->willReturn($builder->reveal());
        $builder->setParameters(Argument::any())->willReturn();
        $builder->andWhere(Argument::any())->willReturn();
        $builder->getSQL()->willReturn('');

        $this->connection->createQueryBuilder()->willReturn($builder->reveal());
        $this->connection->prepare(Argument::any())->willReturn($statement->reveal());

        $this->write->pass(new SimpleBag([]));
    }
}
