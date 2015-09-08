<?php
/**
 * Database.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class Database
 * @package larryli\ipv4\Query
 */
abstract class Database
{
    /**
     * @param $table
     * @return bool
     * @throws \Exception
     */
    abstract public function tableExists($table);

    /**
     * @param $table
     * @throws \Exception
     */
    abstract public function createDivisionsTable($table);

    /**
     * @param $table
     * @throws \Exception
     */
    abstract public function createIndexTable($table);

    /**
     * @param $table
     * @throws \Exception
     */
    abstract public function cleanTable($table);

    /**
     * @param $table
     */
    abstract public function dropTable($table);

    /**
     * @param $table
     * @param $data
     * @return mixed
     */
    abstract function insertDivisions($table, $data);

    /**
     * @param $table
     * @param $data
     */
    abstract public function insertIndexes($table, $data);

    /**
     * @param $table
     * @return bool|int
     */
    abstract public function count($table);


    /**
     * @param $table
     * @param $id
     * @return bool
     */
    abstract public function getDivision($table, $id);


    /**
     * @param $table
     * @param $ip
     * @return bool
     */
    abstract public function getIndex($table, $ip);

    /**
     * @param $table
     * @param $start
     * @param $size
     * @return array|bool
     */
    abstract public function getIndexes($table, $start, $size);
}