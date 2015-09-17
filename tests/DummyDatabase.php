<?php
/**
 * DummyDatabase.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


use larryli\ipv4\Database;

/**
 * Class DummyDatabase
 * @package larryli\ipv4\tests
 */
class DummyDatabase extends Database
{
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        return isset($this->data[$table]);
    }

    /**
     * @param string $table
     */
    public function createDivisionsTable($table)
    {
        $this->data[$table] = [];
    }

    /**
     * @param string $table
     */
    public function createIndexTable($table)
    {
        $this->data[$table] = [];
    }

    /**
     * @param string $table
     */
    public function cleanTable($table)
    {
        $this->data[$table] = [];
    }

    /**
     * @param string $table
     */
    public function dropTable($table)
    {
        unset($this->data[$table]);
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertDivisions($table, $data)
    {
        $this->insertArray($table, 'id', $data);
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertIndexes($table, $data)
    {
        $this->insertArray($table, 'id', $data);
    }

    /**
     * @param $table
     * @return int
     */
    public function count($table)
    {
        return isset($this->data[$table]) ? count($this->data[$table]) : 0;
    }

    /**
     * @param $table
     * @return int
     */
    public function size($table)
    {
        return 1;
    }

    /**
     * @param string $table
     * @param int $id
     * @return array|null
     */
    public function getDivision($table, $id)
    {
        if (isset($this->data[$table])) {
            foreach ($this->data[$table] as $pk => $data) {
                if ($id == $pk) {
                    return $data;
                }
            }
        }
        return null;
    }

    /**
     * @param string $table
     * @param int $ip
     * @return array|null
     */
    public function getIndex($table, $ip)
    {
        if (isset($this->data[$table])) {
            foreach ($this->data[$table] as $pk => $data) {
                if ($pk >= $ip) {
                    return $data['division_id'];
                }
            }
        }
        return null;
    }

    /**
     * @param string $table
     * @param int $start
     * @param int $size
     * @return array
     */
    public function getIndexes($table, $start, $size)
    {
        if (isset($this->data[$table])) {
            if ($start < 0) {
                $start = 0;
            }
            $count = count($this->data[$table]);
            if ($start >= $count) {
                return [];
            }
            return array_slice($this->data[$table], $start, $size);
        }
        return [];
    }

    /**
     * @param $table
     * @param $pk
     * @param $data
     */
    protected function insertArray($table, $pk, $data)
    {
        foreach ($data as $d) {
            $this->data[$table][$d[$pk]] = $d;
        }
        ksort($this->data[$table]);
    }
}
