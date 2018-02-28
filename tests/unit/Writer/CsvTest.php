<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\Data\SimpleBag;

class CsvTest extends TestCase
{
    public function testWrite()
    {
        $root = vfsStream::setup();
        $header = [
            'firstname',
            'lastname',
        ];
        $url = $root->url() . '/users.csv';
        $file = new \SplFileObject($url, 'w');
        $file->fputcsv($header);
        $csv = new Csv('testId', $file, $header);

        $bag = new SimpleBag([
            'firstname' => 'Bob',
            'lastname' => 'Dawson',
        ]);

        $csv->pass($bag);

        $expected = <<<FILE
firstname,lastname
Bob,Dawson

FILE;

        $this->assertEquals($expected, file_get_contents($url));
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Writer\Exception\WriteErrorException
     */
    public function testFailedWrite()
    {
        $root = vfsStream::setup();
        $header = [
            'firstname',
            'lastname',
        ];
        // create file
        $url = $root->url() . '/users.csv';
        $h = fopen($url, 'w');
        fclose($h);

        $file = new \SplFileObject($url, 'r');
        $csv = new Csv('testId', $file, $header);
        $bag = new SimpleBag([
            'firstname' => 'Bob',
            'lastname' => 'Dawson',
        ]);
        $csv->pass($bag);
    }
}
