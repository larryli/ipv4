<?php
/**
 * QueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;

use larryli\ipv4\BaidumapQuery;
use larryli\ipv4\ChinaQuery;
use larryli\ipv4\FreeipipQuery;
use larryli\ipv4\FullQuery;
use larryli\ipv4\MiniQuery;
use larryli\ipv4\MonipdbQuery;
use larryli\ipv4\QqwryQuery;
use larryli\ipv4\Query;
use larryli\ipv4\SinaQuery;
use larryli\ipv4\TaobaoQuery;
use larryli\ipv4\WorldQuery;

/**
 * Class QueryTest
 * @package larryli\ipv4\tests\query
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testTime()
    {
        $this->assertEquals(time(), intval(Query::time() / 10));
    }

    /**
     * @throws \Exception
     */
    public function testCreate()
    {
        $dummy = new DummyDatabase();
        $this->assertTrue(MonipdbQuery::is_a(Query::create('monipdb', __DIR__ . '/17monipdb.dat')));
        $this->assertTrue(QqwryQuery::is_a(Query::create('qqwry', __DIR__ . '/qqwry.dat')));
        $this->assertTrue(FullQuery::is_a(Query::create('full', $dummy)));
        $this->assertTrue(MiniQuery::is_a(Query::create('mini', $dummy)));
        $this->assertTrue(ChinaQuery::is_a(Query::create('china', $dummy)));
        $this->assertTrue(WorldQuery::is_a(Query::create('world', $dummy)));
        $this->assertTrue(FreeipipQuery::is_a(Query::create('freeipip', null)));
        $this->assertTrue(SinaQuery::is_a(Query::create('sina', null)));
        $this->assertTrue(TaobaoQuery::is_a(Query::create('taobao', null)));
        $this->assertTrue(BaidumapQuery::is_a(Query::create('baidumap', null)));
        $this->assertTrue(DummyQuery::is_a(Query::create('qqwry', [
            'class' => DummyQuery::className(),
        ])));
    }

    /**
     *
     */
    public function testGetProviders()
    {
        $dummy = new DummyQuery();
        $this->assertEmpty($dummy->getProviders());
    }

    /**
     *
     */
    public function testSetProviders()
    {
        $dummy = new DummyQuery();
        $this->assertEmpty($dummy->getProviders());
        $dummy->setProviders([
            new FreeipipQuery(),
        ]);
        $this->assertCount(1, $dummy->getProviders());
    }
}
