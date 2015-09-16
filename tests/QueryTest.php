<?php
/**
 * QueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;

use larryli\ipv4\Query;

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
}
