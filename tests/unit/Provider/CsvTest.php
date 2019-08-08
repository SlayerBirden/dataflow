<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Required to have this override for testing with realpath
 * Inconsistent return to comply with the original PHP function http://php.net/manual/ru/function.realpath.php
 *
 * @param string $path
 * @return string|false
 */
function realpath(string $path)
{
    if (CsvTest::getRoot()->hasChild(basename($path))) {
        return $path;
    }
    return false;
}

class CsvTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private static $root;

    public static function setUpBeforeClass()
    {
        self::$root = vfsStream::setup();
    }

    public static function getRoot(): vfsStreamDirectory
    {
        return self::$root;
    }

    protected function setUp()
    {
        $file = fopen(self::$root->url() . '/users.csv', 'w');
        fwrite($file, "firstname,lastname\n");
        fwrite($file, "John,Doe\n");
        fclose($file);
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Provider\Exception\FileDoesNotExist
     */
    public function testNonExistingFile()
    {
        new Csv('testId', self::$root->url() . '/fictional.file', true);
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Provider\Exception\HeaderMissing
     */
    public function testNoHeader()
    {
        new Csv('testId', self::$root->getChild('users.csv')->url(), false);
    }

    public function testGetCask()
    {
        $csv = new Csv('testId', self::$root->getChild('users.csv')->url());

        $cask = $csv->getCask();
        $actual = [];
        foreach ($cask as $row) {
            $actual[] = $row->toArray();
        }

        $this->assertEquals([
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]
        ], $actual);
    }

    /**
     * @expectedException \SlayerBirden\DataFlow\Provider\Exception\RowInvalid
     */
    public function testInvalidHeader()
    {
        $csv = new Csv('testId', self::$root->getChild('users.csv')->url(), true, [
            'firstname',
            'lastname',
            'age'
        ]);
        $cask = $csv->getCask();
        // trigger generator
        foreach ($cask as $row) {
            #pass
        }
    }

    public function testGetCaskWithOverridenHeader()
    {
        $csv = new Csv('testId', self::$root->getChild('users.csv')->url(), true, [
            'first',
            'last',
        ]);
        $cask = $csv->getCask();

        $actual = [];
        foreach ($cask as $row) {
            $actual[] = $row->toArray();
        }

        $this->assertEquals([
            [
                'first' => 'John',
                'last' => 'Doe',
            ]
        ], $actual);
    }

    public function testGetEstimatedSize()
    {
        $csv = new Csv('testId', self::$root->getChild('users.csv')->url());

        $this->assertSame(1, $csv->getEstimatedSize());
    }
}
