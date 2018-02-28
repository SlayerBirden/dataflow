<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\Data\SimpleBag;

class PipeLineTest extends TestCase
{
    /**
     * @var PipeLine
     */
    private $pipeline;

    protected function setUp()
    {
        $this->pipeline = new PipeLine();
    }

    public function testCount()
    {
        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler0';
            }
        }, 0);

        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler100';
            }
        }, 100);

        $this->assertCount(2, $this->pipeline);
    }

    public function testOrder()
    {
        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler0';
            }
        }, 0);

        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler100';
            }
        }, 100);

        $handler = $this->pipeline->current();
        $this->assertSame('handler100', $handler->getIdentifier());
    }

    public function testRewind()
    {
        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler0';
            }
        }, 0);

        $this->pipeline->insert(new class implements HandlerInterface
        {
            public function handle(DataBagInterface $dataBag): DataBagInterface
            {
                return new SimpleBag();
            }

            public function getIdentifier(): string
            {
                return 'handler100';
            }
        }, 100);

        $this->pipeline->next();

        $handler = $this->pipeline->current();
        $this->assertSame('handler0', $handler->getIdentifier());

        $this->pipeline->rewind();
        $handler = $this->pipeline->current();
        $this->assertSame('handler100', $handler->getIdentifier());
    }
}
