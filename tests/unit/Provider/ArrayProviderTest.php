<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use PHPUnit\Framework\TestCase;

class ArrayProviderTest extends TestCase
{
    /**
     * @expectedException \SlayerBirden\DataFlow\Provider\InvalidDataException
     * @expectedExceptionMessage Row #(0) is not an array
     */
    public function testWrongArray()
    {
        new ArrayProvider('test1', [1,2,3]);
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Provider\InvalidDataException
     * @expectedExceptionMessage Row #(1) has different structure than the 1st element
     */
    public function testInconsistentArray()
    {
        new ArrayProvider('test2', [
            [
                'id' => 1,
                'name' => 'Bob',
            ],
            [
                'id' => 2,
                'gender' => 'male',
            ]
        ]);
    }

    public function testGetEstimatedSize()
    {
        $provider = new ArrayProvider('test2', [
            [
                'id' => 1,
                'name' => 'Bob',
            ],
            [
                'name' => 'Peter',
                'id' => 2,
            ]
        ]);

        $this->assertSame(2, $provider->getEstimatedSize());
    }
}
