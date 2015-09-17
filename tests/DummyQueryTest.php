<?php
/**
 * DummyQueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


/**
 * Class DummyQueryTest
 * @package larryli\ipv4\tests
 */
class DummyQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testCurrent()
    {
        $query = new DummyQuery();
        $this->assertEmpty($query->current());
        $query->init();
        $this->assertEquals(4, $query->current());
        $query->next();
        $this->assertEquals(4, $query->current());
        $query->next();
        $this->assertEquals(0, $query->current());
    }

    /**
     *
     */
    public function testNext()
    {
        $query = new DummyQuery();
        $query->init();
        $this->assertEquals(0, $query->key());
        $this->assertEquals(4, $query->current());
        $query->next();
        $this->assertEquals(ip2long('0.255.255.255'), $query->key());
        $this->assertEquals(4, $query->current());
    }

    /**
     *
     */
    public function testKey()
    {
        $query = new DummyQuery();
        $this->assertEmpty($query->key());
        $query->init();
        $this->assertEquals(0, $query->key());
        $query->next();
        $this->assertEquals(ip2long('0.255.255.255'), $query->key());
    }

    /**
     *
     */
    public function testValid()
    {
        $query = new DummyQuery();
        $this->assertFalse($query->valid());
        $query->init();
        $this->assertTrue($query->valid());
    }

    /**
     *
     */
    public function testRewind()
    {
        $query = new DummyQuery();
        $query->init();
        $this->assertEquals(0, $query->key());
        $this->assertEquals(4, $query->current());
        $query->next();
        $this->assertEquals(ip2long('0.255.255.255'), $query->key());
        $this->assertEquals(4, $query->current());
        $query->rewind();
        $this->assertEquals(0, $query->key());
        $this->assertEquals(4, $query->current());
    }

    /**
     *
     */
    public function testName()
    {
        $this->assertEquals('dummy', new DummyQuery());
    }

    /**
     *
     */
    public function testExists()
    {
        $query = new DummyQuery();
        $this->assertFalse($query->exists());
        $query->init();
        $this->assertTrue($query->exists());
    }

    /**
     *
     */
    public function testInit()
    {
        $query1 = new DummyQuery();
        $query1->init(function () {
            return [
                ip2long('0.0.0.0') => 1,
                ip2long('126.255.255.255') => 2,
                ip2long('127.0.0.0') => 3,
                ip2long('127.0.0.1') => 4,
                ip2long('255.255.255.255') => 5,
            ];
        });
        $this->assertEquals(1, $query1->find(ip2long('0.0.0.0')));
        $this->assertEquals(2, $query1->find(ip2long('0.0.0.1')));
        $this->assertEquals(2, $query1->find(ip2long('126.255.255.255')));
        $this->assertEquals(3, $query1->find(ip2long('127.0.0.0')));
        $this->assertEquals(4, $query1->find(ip2long('127.0.0.1')));
        $this->assertEquals(5, $query1->find(ip2long('127.0.0.2')));
        $this->assertEquals(5, $query1->find(ip2long('255.255.255.254')));
        $this->assertEquals(5, $query1->find(ip2long('255.255.255.255')));
        $query2 = new DummyQuery();
        $query2->init();
        $this->assertEquals(2, $query2->find(ip2long('127.0.0.1')));
        $query3 = new DummyQuery();
        $query3->setProviders([
            $query1,
            $query2,
        ]);
        $query3->init();
        $this->assertEquals(4, $query3->find(ip2long('127.0.0.1')));
        $query4 = new DummyQuery();
        $query4->setProviders([
            $query2,
            $query1,
        ]);
        $query4->init();
        $this->assertEquals(2, $query4->find(ip2long('127.0.0.1')));
    }

    /**
     *
     */
    public function testClean()
    {
        $query = new DummyQuery();
        $this->assertFalse($query->exists());
        $query->init();
        $this->assertTrue($query->exists());
        $query->clean();
        $this->assertFalse($query->exists());
    }

    /**
     *
     */
    public function testFind()
    {
        $query = new DummyQuery();
        $this->assertEmpty($query->find(12345678));
        $query->init();
        $this->assertEquals(2, $query->find(ip2long('127.0.0.1')));
        $this->assertEquals(3, $query->find(ip2long('192.168.10.1')));
        $this->assertEquals(4, $query->find(ip2long('0.0.0.0')));
        $this->assertEquals(4, $query->find(ip2long('255.255.255.254')));
        $this->assertEquals(4, $query->find(ip2long('255.255.255.255')));
        $this->assertEquals(5, $query->find(ip2long('169.254.1.1')));
    }

    /**
     *
     */
    public function testFindId()
    {
        $query = new DummyQuery();
        $this->assertEmpty($query->findId(12345678));
        $query->init();
        $this->assertEquals(2, $query->findId(ip2long('127.0.0.1')));
        $this->assertEquals(3, $query->findId(ip2long('192.168.10.1')));
        $this->assertEquals(4, $query->findId(ip2long('0.0.0.0')));
        $this->assertEquals(4, $query->findId(ip2long('255.255.255.254')));
        $this->assertEquals(4, $query->findId(ip2long('255.255.255.255')));
        $this->assertEquals(5, $query->findId(ip2long('169.254.1.1')));
    }

    /**
     *
     */
    public function testDivisionById()
    {
        $query = new DummyQuery();
        $this->assertInternalType('string', $query->divisionById(0));
    }

    /**
     *
     */
    public function testIdByDivision()
    {
        $query = new DummyQuery();
        $this->assertInternalType('int', $query->idByDivision('abc'));
    }

    /**
     *
     */
    public function testCount()
    {
        $query = new DummyQuery();
        $this->assertEmpty($query->count());
        $query->init(function () {
            return [
                0 => 0,
                ip2long('255.255.255.255') => 1,
            ];
        });
        $this->assertEquals(2, $query->count());
    }
}