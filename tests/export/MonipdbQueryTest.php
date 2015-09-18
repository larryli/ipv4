<?php
/**
 * MonipdbQueryTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests\export;

use larryli\ipv4\export\MonipdbQuery as Export;
use larryli\ipv4\MonipdbQuery as Query;
use larryli\ipv4\tests\DummyQuery;

/**
 * Class MonipdbQueryTest
 * @package larryli\ipv4\tests\export
 */
class MonipdbQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testInit()
    {
        $filename = __DIR__ . '/test.dat';
        $dummy = new DummyQuery();
        $dummy->init();
        $export = new Export($filename);
        $export->setProviders([$dummy]);
        $export->init();
        $query = new Query($filename);
        $this->assertEquals("4\t4\t\t", $query->find(ip2long('0.0.0.0')));
        $this->assertEquals("4\t4\t\t", $query->find(ip2long('0.255.255.254')));
        $this->assertEquals("4\t4\t\t", $query->find(ip2long('0.255.255.255')));
        $this->assertEquals("0\t0\t\t", $query->find(ip2long('3.4.5.6')));
        $this->assertEquals("2\t2\t\t", $query->find(ip2long('127.0.0.1')));
        $this->assertEquals("3\t3\t\t", $query->find(ip2long('192.168.10.1')));
        $this->assertEquals("5\t5\t\t", $query->find(ip2long('169.254.1.1')));
        $this->assertStringStartsWith('ipv4.larryli.cn', $query->find(ip2long('255.255.255.254')));
        $query->clean();
    }
}
