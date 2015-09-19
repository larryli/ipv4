<?php
/**
 * StringsTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests\export\monipdb;

use larryli\ipv4\export\monipdb\Strings;

/**
 * Class StringsTest
 * @package export\monipdb
 */
class StringsTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testSet()
    {
        $fp = fopen($this->filename(), 'rb');
        $a = $this->read($fp);
        $b = $this->read($fp);
        $c = $this->read($fp);
        fclose($fp);
        $this->assertEquals(1, $a['ip']);
        $this->assertEquals(strlen("abc\tabc\t\t"), $a['len']);
        $this->assertEquals(2, $b['ip']);
        $this->assertEquals(strlen("foobar\tfoobar\t\t"), $b['len']);
        $this->assertEquals(3, $c['ip']);
        $this->assertEquals(strlen("abc\tabc\t\t"), $c['len']);
        $this->assertEquals($a['offset'], $c['offset']);
    }

    /**
     *
     */
    public function testWrite()
    {
        $fp = fopen($this->filename(), 'rb');
        $a = $this->read($fp);
        $b = $this->read($fp);
        $c = $this->read($fp);
        $position = ftell($fp);
        fseek($fp, $position + $a['offset']);
        $a = fread($fp, $a['len']);
        fseek($fp, $position + $b['offset']);
        $b = fread($fp, $b['len']);
        fseek($fp, $position + $c['offset']);
        $c = fread($fp, $c['len']);
        fclose($fp);
        $this->assertEquals("abc\tabc\t\t", $a);
        $this->assertEquals("foobar\tfoobar\t\t", $b);
        $this->assertEquals("abc\tabc\t\t", $c);
    }

    /**
     * @return string
     */
    protected function filename()
    {
        return __DIR__ . '/test.dat';
    }

    /**
     *
     */
    protected function setUp()
    {
        $fp = fopen($this->filename(), 'wb');
        $str = new Strings();
        $str->set($fp, 1, 'abc');
        $str->set($fp, 2, 'foobar');
        $str->set($fp, 3, 'abc');
        $str->write($fp);
        fclose($fp);
    }

    /**
     *
     */
    protected function tearDown()
    {
        unlink($this->filename());
    }

    /**
     * @param $fp
     * @return array
     */
    protected function read($fp)
    {
        $ip = unpack('Nlen', fread($fp, 4));
        $offset = unpack('Vlen', fread($fp, 3) . "\x00");
        $len = unpack('Clen', fread($fp, 1));
        return [
            'ip' => $ip['len'],
            'offset' => $offset['len'],
            'len' => $len['len'],
        ];
    }
}
