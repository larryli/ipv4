<?php
/**
 * Database.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class Database
 * @package larryli\ipv4\query
 */
abstract class Database extends Object
{
    /**
     * test table is exists
     *
     * @param string $table
     * @return bool
     */
    abstract public function tableExists($table);

    /**
     * create divisions table
     *
     * @param string $table
     */
    abstract public function createDivisionsTable($table);

    /**
     * create index table
     *
     * @param string $table
     */
    abstract public function createIndexTable($table);

    /**
     * clean table
     *
     * @param string $table
     */
    abstract public function cleanTable($table);

    /**
     * drop table
     *
     * @param string $table
     */
    abstract public function dropTable($table);

    /**
     * insert division data to table
     *
     * @param string $table
     * @param array $data
     */
    abstract public function insertDivisions($table, $data);

    /**
     * insert division data to table
     *
     * @param string $table
     * @param array $data
     */
    abstract public function insertIndexes($table, $data);

    /**
     * count table data
     *
     * @param $table
     * @return int
     */
    abstract public function count($table);

    /**
     * insert or select page size
     *
     * @param $table
     * @return int
     */
    abstract public function size($table);

    /**
     * get division data by id
     *
     * @param string $table
     * @param integer $id
     * @return mixed
     */
    abstract public function getDivision($table, $id);

    /**
     * get index data by ip integer
     *
     * @param string $table
     * @param integer $ip
     * @return mixed
     */
    abstract public function getIndex($table, $ip);

    /**
     * gets index data
     *
     * @param string $table
     * @param integer $start
     * @param integer $size
     * @return mixed
     */
    abstract public function getIndexes($table, $start, $size);
}