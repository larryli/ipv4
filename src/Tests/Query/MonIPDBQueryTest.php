<?php
/**
 * MonIPDBQueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Tests\Query;

use larryli\ipv4\Query\MonIPDBQuery;

/**
 * Class MonIPDBQueryTest
 * @package larryli\ipv4\Tests\Query
 */
class MonIPDBQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MonIPDBQuery
     */
    static protected $query;

    public static function setUpBeforeClass()
    {
        self::$query = new MonIPDBQuery(__DIR__ . '/test.dat');
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
        $query = new MonIPDBQuery();
        $this->assertEquals('17monipdb.dat', $query->name());
    }

    public function testName()
    {
        $this->assertEquals('test.dat', self::$query->name());
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
        $this->assertTrue(self::$query->exists());
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

    public function testInteger()
    {
        $this->assertEquals(202, self::$query->integer("美国\t美国\t\t"));
        $this->assertEquals(420100, self::$query->integer("中国\t湖北\t武汉\t"));
        $this->assertEquals(440300, self::$query->integer("中国\t广东\t深圳\t"));
    }

    /**
     * @depends testInit
     */
    public function testDivision()
    {
        $this->assertContains('深圳', self::$query->division(ip2long('202.96.134.133'))); // 深圳
        $this->assertContains('武汉', self::$query->division(ip2long('202.103.24.68')));  // 武汉
    }

    /**
     * @depends testInit
     */
    public function testDivision_id()
    {
        $this->assertEquals(4, self::$query->division_id(ip2long('0.0.0.0')));
        $this->assertEquals(2, self::$query->division_id(ip2long('127.0.0.1')));
        $this->assertEquals(3, self::$query->division_id(ip2long('192.168.1.1')));
        $this->assertEquals(3, self::$query->division_id(ip2long('10.0.0.1')));
        $this->assertEquals(4, self::$query->division_id(ip2long('255.255.255.255')));
        $this->assertEquals(440300, self::$query->division_id(ip2long('202.96.134.133'))); // 深圳
        $this->assertEquals(420100, self::$query->division_id(ip2long('202.103.24.68')));  // 武汉
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
            $this->assertEquals($division, self::$query->division($ip));
        }
    }

}
