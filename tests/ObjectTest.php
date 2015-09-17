<?php
/**
 * ObjectTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


use larryli\ipv4\Object;

/**
 * Class ObjectTest
 * @package larryli\ipv4\tests
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testClassName()
    {
        $this->assertEquals("larryli\\ipv4\\Object", Object::className());
        $this->assertEquals(__NAMESPACE__ . "\\DummyObject", DummyObject::className());
    }

    /**
     *
     */
    public function testIs_a()
    {
        $this->assertTrue(DummyObject::is_a(new DummyObject()));
        $this->assertFalse(DummyObject::is_a(new FakeObject()));
        $this->assertTrue(Object::is_a(new Object()));
        $this->assertTrue(Object::is_a(new DummyObject()));
        $this->assertFalse(Object::is_a(new FakeObject()));
    }
}

/**
 * Class Dummy
 * @package larryli\ipv4\tests
 */
class DummyObject extends Object
{
}

/**
 * Class Fake
 * @package larryli\ipv4\tests
 */
class FakeObject
{
}