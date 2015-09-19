<?php
/**
 * DummyDatabaseTest.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


/**
 * Class DummyDatabaseTest
 * @package larryli\ipv4\tests
 */
class DummyDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testTableExists()
    {
        $database = new DummyDatabase();
        $this->assertFalse($database->tableExists('test'));
        $database->createDivisionsTable('test');
        $this->assertTrue($database->tableExists('test'));
        $database->dropTable('test');
        $this->assertFalse($database->tableExists('test'));
    }

    /**
     *
     */
    public function testCreateDivisionsTable()
    {
        $database = new DummyDatabase();
        $this->assertFalse($database->tableExists('test'));
        $database->createDivisionsTable('test');
        $this->assertTrue($database->tableExists('test'));
    }

    /**
     *
     */
    public function testCreateIndexTable()
    {
        $database = new DummyDatabase();
        $this->assertFalse($database->tableExists('test'));
        $database->createIndexTable('test');
        $this->assertTrue($database->tableExists('test'));
    }

    /**
     *
     */
    public function testCleanTable()
    {
        $database = new DummyDatabase();
        $database->createDivisionsTable('test');
        $database->insertDivisions('test', [
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
            ['id' => 3, 'name' => 'c'],
        ]);
        $this->assertEquals(3, $database->count('test'));
        $database->cleanTable('test');
        $this->assertEquals(0, $database->count('test'));
    }

    /**
     *
     */
    public function testDropTable()
    {
        $database = new DummyDatabase();
        $this->assertFalse($database->tableExists('test'));
        $database->createIndexTable('test');
        $this->assertTrue($database->tableExists('test'));
        $database->dropTable('test');
        $this->assertFalse($database->tableExists('test'));
    }

    /**
     *
     */
    public function testInsertDivisions()
    {
        $database = new DummyDatabase();
        $database->createDivisionsTable('test');
        $database->insertDivisions('test', [
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
            ['id' => 3, 'name' => 'c'],
        ]);
        $this->assertEquals(3, $database->count('test'));
        $division = $database->getDivision('test', 2);
        $this->assertEquals('b', $division['name']);
        $database->insertDivisions('test', [
            ['id' => 4, 'name' => 'e'],
        ]);
        $this->assertEquals(4, $database->count('test'));
        $division = $database->getDivision('test', 2);
        $this->assertEquals('b', $division['name']);
        $division = $database->getDivision('test', 4);
        $this->assertEquals('e', $division['name']);
    }

    /**
     *
     */
    public function testInsertIndexes()
    {
        $database = new DummyDatabase();
        $database->createIndexTable('test');
        $database->insertIndexes('test', [
            ['id' => 0, 'division_id' => 0],
            ['id' => 1, 'division_id' => 1],
            ['id' => 100, 'division_id' => 100],
        ]);
        $this->assertEquals(3, $database->count('test'));
        $test = $database->getIndex('test', 1);
        $this->assertEquals(1, $test);
        $database->insertIndexes('test', [
            ['id' => 10, 'division_id' => 10],
        ]);
        $this->assertEquals(4, $database->count('test'));
        $test = $database->getIndex('test', 1);
        $this->assertEquals(1, $test);
        $test = $database->getIndex('test', 10);
        $this->assertEquals(10, $test);
    }

    /**
     *
     */
    public function testCount()
    {
        $database = new DummyDatabase();
        $database->createDivisionsTable('test');
        $database->insertDivisions('test', [
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
            ['id' => 3, 'name' => 'c'],
        ]);
        $this->assertEquals(3, $database->count('test'));
        $database->insertDivisions('test', [
            ['id' => 3, 'name' => 'e'],
            ['id' => 4, 'name' => 'f'],
        ]);
        $this->assertEquals(4, $database->count('test'));
        $database->insertDivisions('test', [
            ['id' => 5, 'name' => 'g'],
        ]);
        $this->assertEquals(5, $database->count('test'));
    }

    /**
     *
     */
    public function testSize()
    {
        $database = new DummyDatabase();
        $this->assertInternalType('int', $database->size('test'));
    }

    /**
     *
     */
    public function testGetDivision()
    {
        $database = new DummyDatabase();
        $database->createDivisionsTable('test');
        $database->insertDivisions('test', [
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
            ['id' => 3, 'name' => 'c'],
        ]);
        $division = $database->getDivision('test', 1);
        $this->assertEquals('a', $division['name']);
        $division = $database->getDivision('test', 2);
        $this->assertEquals('b', $division['name']);
        $division = $database->getDivision('test', 3);
        $this->assertEquals('c', $division['name']);
        $this->assertEmpty($database->getDivision('test', 4));
    }

    /**
     *
     */
    public function testGetIndex()
    {
        $database = new DummyDatabase();
        $database->createIndexTable('test');
        $database->insertIndexes('test', [
            ['id' => 0, 'division_id' => 0],
            ['id' => 1, 'division_id' => 1],
            ['id' => 10, 'division_id' => 10],
            ['id' => 100, 'division_id' => 100],
        ]);
        $test = $database->getIndex('test', 0);
        $this->assertEquals(0, $test);
        $test = $database->getIndex('test', 1);
        $this->assertEquals(1, $test);
        $test = $database->getIndex('test', 4);
        $this->assertEquals(10, $test);
        $test = $database->getIndex('test', 100);
        $this->assertEquals(100, $test);
    }

    /**
     *
     */
    public function testGetIndexes()
    {
        $database = new DummyDatabase();
        $database->createIndexTable('test');
        $database->insertIndexes('test', [
            ['id' => 0, 'division_id' => 0],
            ['id' => 1, 'division_id' => 1],
            ['id' => 2, 'division_id' => 2],
            ['id' => 3, 'division_id' => 3],
            ['id' => 4, 'division_id' => 4],
            ['id' => 5, 'division_id' => 5],
        ]);
        $this->assertCount(5, $database->getIndexes('test', 1, 5));
        $this->assertCount(4, $database->getIndexes('test', 2, 5));
        $test = $database->getIndexes('test', 2, 3);
        $this->assertEquals(2, $test[0]['division_id']);
        $this->assertEquals(3, $test[1]['division_id']);
    }
}
