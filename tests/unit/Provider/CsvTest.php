<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\Provider\Exception\RowInvalid;

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
     * @expectedException \SlayerBirden\DataFlow\Provider\Exception\HeaderMissing
     */
    public function testNoHeader()
    {
        $file = new \SplFileObject(self::$root->getChild('users.csv')->url());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
        new Csv('testId', $file, false);
    }

    public function testGetCask()
    {
        $file = new \SplFileObject(self::$root->getChild('users.csv')->url());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
        $csv = new Csv('testId', $file);

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

    public function testInvalidHeader()
    {
        $file = new \SplFileObject(self::$root->getChild('users.csv')->url());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
        $csv = new Csv('testId', $file, true, [
            'firstname',
            'lastname',
            'age'
        ]);
        $cask = $csv->getCask();
        // trigger generator
        foreach ($cask as $row) {
            $this->assertInstanceOf(RowInvalid::class, $row);
        }
    }

    public function testGetCaskWithOverridenHeader()
    {
        $file = new \SplFileObject(self::$root->getChild('users.csv')->url());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
        $csv = new Csv('testId', $file, true, [
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
        $file = new \SplFileObject(self::$root->getChild('users.csv')->url());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
        $csv = new Csv('testId', $file);

        $this->assertSame(1, $csv->getEstimatedSize());
    }
}
