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
     * @param string $table
     * @return bool
     */
    abstract public function tableExists($table);

    /**
     * @param string $table
     */
    abstract public function createDivisionsTable($table);

    /**
     * @param string $table
     */
    abstract public function createIndexTable($table);

    /**
     * @param string $table
     */
    abstract public function cleanTable($table);

    /**
     * @param string $table
     */
    abstract public function dropTable($table);

    /**
     * @param string $table
     * @param array $data
     */
    abstract function insertDivisions($table, $data);

    /**
     * @param string $table
     * @param array $data
     */
    abstract public function insertIndexes($table, $data);

    /**
     * begin transaction
     *
     * @return mixed
     */
    abstract public function startCommit();

    /**
     * commit transaction
     *
     * @return mixed
     */
    abstract public function endCommit();

    /**
     * @param $table
     * @return int
     */
    abstract public function count($table);

    /**
     * @param string $table
     * @param integer $id
     * @return mixed
     */
    abstract public function getDivision($table, $id);

    /**
     * @param string $table
     * @param integer $ip
     * @return mixed
     */
    abstract public function getIndex($table, $ip);

    /**
     * @param string $table
     * @param integer $start
     * @param integer $size
     * @return mixed
     */
    abstract public function getIndexes($table, $start, $size);
}