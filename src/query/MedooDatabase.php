<?php
/**
 * MedooDatabase.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\query;


/**
 * Class MedooDatabase
 * @package larryli\ipv4\query
 */
class MedooDatabase extends Database
{
    /**
     * @var Medoo
     */
    protected $medoo;

    /**
     * @param null $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        if (empty($options)) {
            $options = $this->initConfig();
        } else if (is_string($options)) {
            $options = require($options . '');  // fix inspect
        }
        if (is_array($options) && isset($options['database_type'])) {
            $this->medoo = new Medoo($options);
        } else {
            throw new \Exception("medoo options is not a array or have not type");
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function initConfig()
    {
        $config = dirname(dirname(__DIR__)) . '/config/db.php';
        if (file_exists($config)) {
            return require($config . '');  // fix inspect
        } else {
            return [
                'database_type' => 'sqlite',
                'database_file' => dirname(dirname(__DIR__)) . '/runtime/ipv4.sqlite',
            ];
        }
    }

    /**
     * @param $table
     * @return bool
     * @throws \Exception
     */
    public function tableExists($table)
    {
        return $this->medoo->table_exists($table);
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function createDivisionsTable($table)
    {
        $this->medoo->create_table($table, [
            'id' => $this->medoo->pk_type(true, false),
            'name' => 'varchar(255)',
            'title' => 'varchar(255)',
            'is_city' => 'boolean',
            'parent_id' => $this->medoo->int_type(false),
        ]);
        $this->medoo->create_index('is_city', $table, 'is_city');
        $this->medoo->create_index('parent_id', $table, 'parent_id');
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function createIndexTable($table)
    {
        $this->medoo->create_table($table, [
            'id' => $this->medoo->pk_type(false, true),
            'division_id' => $this->medoo->int_type(false),
        ]);
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function cleanTable($table)
    {
        $this->medoo->clean_table($table);
    }

    /**
     * @param $table
     */
    public function dropTable($table)
    {
        $this->medoo->drop_table($table);
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertDivisions($table, $data)
    {
        $this->medoo->insert($table, $data);
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertIndexes($table, $data)
    {
        $this->medoo->insert($table, $data);
    }

    /**
     *
     */
    public function startCommit()
    {
        $this->medoo->pdo->beginTransaction();
    }

    /**
     *
     */
    public function endCommit()
    {
        $this->medoo->pdo->commit();
    }

    /**
     * @param $table
     * @return bool|int
     */
    public function count($table)
    {
        return $this->medoo->count($table);
    }

    /**
     * @param $table
     * @param $id
     * @return mixed
     */
    public function getDivision($table, $id)
    {
        return $this->medoo->get($table, ['name', 'parent_id'], ['id' => $id]);
    }

    /**
     * @param $table
     * @param $ip
     * @return mixed
     */
    public function getIndex($table, $ip)
    {
        return $this->medoo->get($table, 'division_id', [
            'id[>=]' => $ip,
            'ORDER' => 'id ASC',
            'LIMIT' => 1,
        ]);
    }

    /**
     * @param $table
     * @param $start
     * @param $size
     * @return mixed
     */
    public function getIndexes($table, $start, $size)
    {
        return $this->medoo->select($table, ['id', 'division_id'], [
            'ORDER' => 'id ASC',
            'LIMIT' => [$start, $size],
        ]);
    }
}