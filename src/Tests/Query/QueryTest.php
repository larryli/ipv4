<?php
/**
 * QueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Tests\Query;

use larryli\ipv4\Query\Query;

/**
 * Class QueryTest
 * @package larryli\ipv4\Tests\Query
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testCreate()
    {
        foreach (Query::$classes as $name => $class) {
            $query1 = Query::create($name);
            $query2 = Query::create($name);
            $this->assertEquals($query1, $query2);
        }
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $config = Query::config(['a', 'b', 'c']);
        foreach ($config as $name => $provider) {
            $this->assertEmpty($provider);
            $this->assertRegExp('/[abc]/', $name);
        }
    }

    /**
     *
     */
    public function testTime()
    {
        $this->assertEquals(time(), intval(Query::time() / 10));
    }
}
