<?php
/**
 * DatabaseQueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


use larryli\ipv4\DatabaseQuery;

/**
 * Class DatabaseQueryTest
 * @package larryli\ipv4\tests
 */
class DatabaseQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstruct()
    {
        $this->assertTrue(DatabaseQuery::is_a(new DummyDatabaseQuery(new DummyDatabase())));
    }

    /**
     *
     */
    public function testInit()
    {
        $query0 = new DummyQuery();
        $query0->init();

        $query1 = new DummyQuery();
        $query1->init(function (){
            return [
                ip2long('1.255.255.255') => 1,
                ip2long('255.255.255.255') => 0,
            ];
        });

        $query2 = new DummyQuery();
        $query2->init(function (){
            return [
                ip2long('2.255.255.255') => 2,
                ip2long('255.255.255.255') => 0,
            ];
        });

        $query3 = new DummyQuery();
        $query3->init(function (){
            return [
                ip2long('3.255.255.255') => 3,
                ip2long('255.255.255.255') => 0,
            ];
        });

        $query4 = new DummyQuery();
        $query4->init(function (){
            return [
                ip2long('4.255.255.255') => 4,
                ip2long('255.255.255.255') => 0,
            ];
        });

        $query5 = new DummyQuery();
        $query5->init(function (){
            return [
                ip2long('5.255.255.255') => 5,
                ip2long('255.255.255.255') => 0,
            ];
        });

        $query = new DummyDatabaseQuery(new DummyDatabase());
        DatabaseQuery::initDivision();
        $this->assertFalse($query->exists());
        $query->setProviders([
            $query0,
        ]);
        $query->init();
        $this->assertEquals(4, $query->findId(ip2long('0.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('1.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('2.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('3.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('4.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('5.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('6.0.0.0')));
        $this->assertEquals(3, $query->findId(ip2long('10.0.0.0')));
        $this->assertEquals(2, $query->findId(ip2long('127.0.0.1')));
        $this->assertEquals(3, $query->findId(ip2long('127.0.0.2')));
        $query->setProviders([
            $query0,
            $query1,
            $query2,
            $query3,
            $query4,
            $query5,
        ]);
        $query->init();
        $this->assertEquals(4, $query->findId(ip2long('0.0.0.0')));
        $this->assertEquals(1, $query->findId(ip2long('1.0.0.0')));
        $this->assertEquals(2, $query->findId(ip2long('2.0.0.0')));
        $this->assertEquals(3, $query->findId(ip2long('3.0.0.0')));
        $this->assertEquals(4, $query->findId(ip2long('4.0.0.0')));
        $this->assertEquals(5, $query->findId(ip2long('5.0.0.0')));
        $this->assertEquals(0, $query->findId(ip2long('6.0.0.0')));
        $this->assertEquals(3, $query->findId(ip2long('10.0.0.0')));
        $this->assertEquals(2, $query->findId(ip2long('127.0.0.1')));
        $this->assertEquals(3, $query->findId(ip2long('127.0.0.2')));
    }
}

/**
 * Class DummyDatabaseQuery
 * @package larryli\ipv4\tests
 */
class DummyDatabaseQuery extends DatabaseQuery
{

    /**
     * @param $id
     * @return mixed
     */
    public function translateId($id)
    {
        return $id;
    }

    /**
     * name of the query
     *
     * @return string name string
     */
    public function name()
    {
        return 'dummy';
    }
}