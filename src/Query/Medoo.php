<?php
/**
 * Medoo.php
 *
 * Author: Larry Li <larryli@qq.com>
 * @see: https://github.com/catfan/Medoo/commit/319a37a859e9f3e44f849e631862ee2eb1043033
 */

namespace larryli\ipv4\Query;

/**
 * Class Medoo
 * @package larryli\ipv4\Query
 */
class Medoo extends \medoo
{
    /**
     * @var
     */
    protected $prefix;

    /**
     * @param null $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
    }

    /**
     * @param $table
     * @param $join
     * @param null $columns
     * @param null $where
     * @param null $column_fn
     * @return string
     */
    protected function select_context($table, $join, &$columns = null, $where = null, $column_fn = null)
    {
        return parent::select_context($this->prefix . $table, $join, $columns, $where, $column_fn);
    }

    /**
     * @param $table
     * @param $datas
     * @return array
     */
    public function insert($table, $datas)
    {
        return parent::insert($this->prefix . $table, $datas);
    }

    /**
     * @param $table
     * @param $data
     * @param null $where
     * @return bool|int
     */
    public function update($table, $data, $where = null)
    {
        return parent::update($this->prefix . $table, $data, $where);
    }

    /**
     * @param $table
     * @param $where
     * @return bool|int
     */
    public function delete($table, $where)
    {
        return parent::delete($this->prefix . $table, $where);
    }

    /**
     * @param $table
     * @param $columns
     * @param null $search
     * @param null $replace
     * @param null $where
     * @return bool|int
     */
    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        return parent::replace($this->prefix . $table, $columns, $search, $replace, $where);
    }

    /**
     * @param $table
     * @return bool
     * @throws \Exception
     */
    public function table_exists($table)
    {
        switch ($this->database_type) {
            case 'sqlite':
                $query = $this->query('SELECT count(*) FROM sqlite_master WHERE type='
                    . $this->quote('table') . ' AND name='
                    . $this->quote($this->prefix . $table));
                return 0 + $query->fetchColumn() ? true : false;
                break;
            case 'mysql':
                $query = $this->query('SHOW TABLES LIKE ' . $this->quote($this->prefix . $table));
//                echo $query->queryString;
//                die($query->fetchColumn());
                return ($query->fetchColumn() === false) ? false : true;
                break;
            default:
                throw new \Exception("check table exists: unsupported type {$this->database_type}");
        }
    }

    /**
     * @param $table
     * @param $columns
     * @param null $options
     * @return mixed
     */
    public function create_table($table, $columns, $options = null)
    {
        $cols = [];
        foreach ($columns as $name => $type) {
            if (is_string($name)) {
                $cols[] = "\t" . $this->column_quote($name) . ' ' . $type;
            } else {
                $cols[] = "\t" . $type;
            }
        }
        $sql = 'CREATE TABLE ' . $this->column_quote($this->prefix . $table) . " (\n" . implode(",\n", $cols) . "\n)";
        $query = $this->query($options === null ? $sql : $sql . ' ' . $options);
        return $query->fetch();
    }

    /**
     * @param $name
     * @param $table
     * @param $columns
     * @param bool|false $unique
     * @return mixed
     */
    public function create_index($name, $table, $columns, $unique = false)
    {
        $query = $this->query(($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
            . $this->column_quote($name) . ' ON '
            . $this->column_quote($this->prefix . $table)
            . ' (' . (is_array($columns) ? $this->array_quote($columns) : $columns) . ')');
        return $query->fetch();
    }

    /**
     * @param $table
     * @return mixed
     */
    public function clean_table($table)
    {
        switch ($this->database_type) {
            case 'sqlite':
                $sql = 'DELETE FROM ';
                break;
            case 'mysql':
                $sql = 'TRUNCATE TABLE ';
                break;
            default:
                throw new \Exception("clean table: unsupported type {$this->database_type}");
        }

        $query = $this->query($sql . $this->column_quote($this->prefix . $table));
        return $query->fetch();
    }

    /**
     * @param $table
     * @return mixed
     */
    public function drop_table($table)
    {
        $query = $this->query('DROP TABLE ' . $this->column_quote($this->prefix . $table));
        return $query->fetch();
    }

    /**
     * @param bool|true $ai
     * @param bool|false $big
     * @return string
     * @throws \Exception
     */
    public function pk_type($ai = true, $big = false)
    {
        switch ($this->database_type) {
            case 'sqlite':
                return 'integer PRIMARY KEY' . ($ai ? ' AUTOINCREMENT' : '') . ' NOT NULL';
            case 'mysql':
                return ($big ? 'bigint(20)' : 'int(11)') . ' NOT NULL' . ($ai ? ' AUTO_INCREMENT' : '') . ' PRIMARY KEY';
            default:
                throw new \Exception("return pk type: unsupported type {$this->database_type}");
        }
    }

    /**
     * @param bool|false $big
     * @return string
     * @throws \Exception
     */
    public function int_type($big = false)
    {
        switch ($this->database_type) {
            case 'sqlite':
                return $big ? 'bigint' : 'integer';
            case 'mysql':
                return $big ? 'bigint(20)' : 'int(11)';
            default:
                throw new \Exception("return int type: unsupported type {$this->database_type}");
        }
    }

}
