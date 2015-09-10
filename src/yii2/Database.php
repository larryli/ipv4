<?php
/**
 * Database.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2;

use larryli\ipv4\query\Database as BaseDatabase;
use Yii;
use yii\db\Query;

/**
 * Class MedooDatabase
 * @package larryli\ipv4\query
 */
class Database extends BaseDatabase
{
    /**
     * @var \yii\db\Connection yii2 database connection
     */
    protected $db;
    /**
     * @var string table prefix
     */
    protected $prefix = '';
    /**
     * @var \yii\db\Transaction
     */
    protected $transaction;

    /**
     * @param null $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        $this->db = Yii::$app->db;
        if (is_array($options)) {
            if (isset($options['prefix'])) {
                $this->prefix = $options['prefix'];
            }
        }
    }

    /**
     * @param $table
     * @return bool
     * @throws \Exception
     */
    public function tableExists($table)
    {
        return true;    // migrate
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function createDivisionsTable($table)
    {
        // do nothing
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function createIndexTable($table)
    {
        // do nothing
    }

    /**
     * @param $table
     * @throws \Exception
     */
    public function cleanTable($table)
    {
        $this->db->createCommand()->truncateTable($this->getTableName($table))->execute();
    }

    /**
     * @param $name
     * @return string
     */
    protected function getTableName($name)
    {
        return $this->prefix . $name;
    }

    /**
     * @param $table
     */
    public function dropTable($table)
    {
        $this->cleanTable($table);  // just clean
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertDivisions($table, $data)
    {
        $this->batchInsert($table, $data);
    }

    /**
     * @param $table
     * @param $data
     * @throws \yii\db\Exception
     */
    private function batchInsert($table, $data)
    {
        $rows = [];
        foreach ($data as $row) {
            if (!isset($columns)) {
                $columns = array_keys($row);
            }
            $rows[] = array_values($row);
        }
        if (isset($columns)) {
            $this->db->createCommand()->batchInsert($this->getTableName($table), $columns, $rows)->execute();
        }
    }

    /**
     * @param string $table
     * @param array $data
     */
    public function insertIndexes($table, $data)
    {
        $this->batchInsert($table, $data);
    }

    /**
     *
     */
    public function startCommit()
    {
        $this->transaction = $this->db->beginTransaction();
    }

    /**
     *
     */
    public function endCommit()
    {
        $this->transaction->commit();
    }

    /**
     * @param $table
     * @return bool|int
     */
    public function count($table)
    {
        return (new Query())->from($this->getTableName($table))->count();
    }

    /**
     * @param $table
     * @param $id
     * @return mixed
     */
    public function getDivision($table, $id)
    {
        return (new Query())->from($this->getTableName($table))
            ->select(['name', 'parent_id'])
            ->where(['id' => $id])
            ->one();
    }

    /**
     * @param $table
     * @param $ip
     * @return mixed
     */
    public function getIndex($table, $ip)
    {
        $data = (new Query())->from($this->getTableName($table))
            ->select(['division_id'])
            ->where(['>=', 'id', $ip])
            ->orderBy('id ASC')
            ->one();
        if ($data === false) {
            return 0;
        }
        return $data['division_id'];
    }

    /**
     * @param $table
     * @param $start
     * @param $size
     * @return mixed
     */
    public function getIndexes($table, $start, $size)
    {
        return (new Query())->from($this->getTableName($table))
            ->select(['id', 'division_id'])
            ->orderBy('id ASC')
            ->offset($start)
            ->limit($size)
            ->all();
    }
}