<?php
/**
 * IndexesTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests\export\monipdb;

use larryli\ipv4\export\monipdb\Indexes;

/**
 * Class IndexesTest
 * @package larryli\ipv4\tests\export\monipdb
 */
class IndexesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $answer = [];

    /**
     *
     */
    public function testSet()
    {
        $fp = fopen($this->filename(), 'rb');
        $data = [];
        for ($i = 0; $i < 256; $i++) {
            $d = unpack('Vlen', fread($fp, 4));
            $data[$i] = $d['len'];
        }
        fclose($fp);
        foreach ($data as $i => $d) {
            $this->assertEquals($this->answer[$i], $d, "{$i}.0.0.0");
        }
    }

    /**
     *
     */
    public function testInvalid()
    {
        $idx = new Indexes();
        $idx->set(0, 1);
        $this->assertTrue($idx->invalid());
        $idx->set(ip2long('254.255.255.255'), 1);
        $this->assertTrue($idx->invalid());
        $idx->set(ip2long('255.0.0.0'), 1);
        $this->assertFalse($idx->invalid());
    }

    /**
     *
     */
    public function testWrite()
    {
        $this->assertEquals(1024, filesize($this->filename()));
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
        $ips = [
            ip2long('0.0.0.0'), // 0
            ip2long('10.20.255.255'), // 1
            ip2long('125.0.255.255'), // 2
            ip2long('180.20.255.255'), // 3
            ip2long('180.120.255.255'), // 4
            ip2long('185.120.255.255'), // 5
            ip2long('201.0.255.255'), // 6
            ip2long('221.255.255.255'), // 7
            ip2long('231.0.255.255'), // 8
            ip2long('255.255.255.255'), // 9
        ];
        $this->answer = [
            0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, // 0
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 16
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 32
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 48
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 64
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 80
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, // 96
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, // 112
            3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, // 128
            3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, // 144
            3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, // 160
            3, 3, 3, 3, 3, 5, 5, 5, 5, 5, 6, 6, 6, 6, 6, 6, // 176
            6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 7, 7, 7, 7, 7, 7, // 192
            7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 8, 8, // 208
            8, 8, 8, 8, 8, 8, 8, 8, 9, 9, 9, 9, 9, 9, 9, 9, // 224
            9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, // 240
        ];
        $fp = fopen($this->filename(), 'wb');
        $idx = new Indexes();
        foreach ($ips as $i => $ip) {
            $idx->set($ip, $i);
        }
        $idx->write($fp);
        fclose($fp);
    }

    /**
     *
     */
    protected function tearDown()
    {
        unlink($this->filename());
    }
}
