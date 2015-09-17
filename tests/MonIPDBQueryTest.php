<?php
/**
 * MonIPDBQueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;

use larryli\ipv4\MonIPDBQuery;

/**
 * Class MonIPDBQueryTest
 * @package larryli\ipv4\tests\query
 */
class MonIPDBQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MonIPDBQuery
     */
    static protected $query;

    public static function setUpBeforeClass()
    {
        self::$query = new MonIPDBQuery(__DIR__ . '/17monipdb.dat');
    }

    public static function tearDownAfterClass()
    {
        if (self::$query->exists()) {
            self::$query->clean();
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructException()
    {
        new MonIPDBQuery('/some filename');
    }

    public function testConstruct()
    {
        $filename = 'test.dat';
        $query = new MonIPDBQuery(__DIR__ . '/' . $filename);
        $this->assertEquals($filename, $query->name());
    }

    public function testName()
    {
        $this->assertEquals('17monipdb.dat', self::$query->name());
    }

    public function testClean()
    {
        self::$query->clean();
        $this->assertFalse(self::$query->exists());
    }

    /**
     * @depends testClean
     */
    public function testInit()
    {
        if (!self::$query->exists()) {
            self::$query->init();
        }
        $this->assertNotEmpty(self::$query->find(ip2long('0.0.0.0')));
    }

    /**
     * @depends testInit
     */
    public function testExists()
    {
        $this->assertTrue(self::$query->exists());
    }

    /**
     * @depends testInit
     */
    public function testCount()
    {
        $this->assertGreaterThan(10000, count(self::$query));
    }

    public function testIdByDivision()
    {
        $this->assertEquals(202, self::$query->idByDivision("美国\t美国\t\t"));
        $this->assertEquals(420100, self::$query->idByDivision("中国\t湖北\t武汉\t"));
        $this->assertEquals(440300, self::$query->idByDivision("中国\t广东\t深圳\t"));
    }

    /**
     * @depends testInit
     */
    public function testFind()
    {
        $this->assertContains('深圳', self::$query->find(ip2long('202.96.134.133'))); // 深圳
        $this->assertContains('武汉', self::$query->find(ip2long('202.103.24.68')));  // 武汉
    }

    /**
     * @depends testInit
     */
    public function testFindId()
    {
        $this->assertEquals(4, self::$query->findId(ip2long('0.0.0.0')));
        $this->assertEquals(2, self::$query->findId(ip2long('127.0.0.1')));
        $this->assertEquals(3, self::$query->findId(ip2long('192.168.1.1')));
        $this->assertEquals(3, self::$query->findId(ip2long('10.0.0.1')));
        $this->assertEquals(4, self::$query->findId(ip2long('255.255.255.255')));
        $this->assertEquals(440300, self::$query->findId(ip2long('202.96.134.133'))); // 深圳
        $this->assertEquals(420100, self::$query->findId(ip2long('202.103.24.68')));  // 武汉
    }

    /**
     * @depends testInit
     */
    public function testForeach()
    {
        $tests = [];
        $count = 0;
        foreach (self::$query as $ip => $division) {
            $count++;
            if ($count % 1000 == 0) {
                $tests[$ip] = $division;
            }
        }
        $this->assertEquals($count, count(self::$query));
        foreach ($tests as $ip => $division) {
            $this->assertEquals($division, self::$query->find($ip));
        }
    }
}
