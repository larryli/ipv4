<?php
/**
 * DatabaseQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class DatabaseQuery
 * @package larryli\ipv4\query
 */
abstract class DatabaseQuery extends Query
{
    /**
     *
     */
    const DIVISION = 'divisions';
    /**
     * @var Database
     */
    static protected $db;
    /**
     * @var string[]
     */
    static private $divisions = [];
    /**
     * @var int
     */
    protected $position = 0;
    /**
     * @var
     */
    protected $buffer;
    /**
     * @var
     */
    protected $buffer_position;
    /**
     * @var array
     */
    private $lasted = [];
    /**
     * @var array
     */
    private $saved = [];

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        if (self::$db === null) {
            self::initDatabase($database);
        }
    }

    /**
     * @param Database $database
     * @throws \Exception
     */
    static public function initDatabase(Database $database)
    {
        if (Database::is_a($database)) {
            self::$db = $database;
        } else {
            $className = Database::className();
            throw new \Exception("{$database} is not a {$className} object.");
        }
    }

    /**
     * @param null|callable $func
     * @param bool $has_extra
     * @throws \Exception
     */
    static public function initDivision(callable $func = null, $has_extra = false)
    {
        if (self::$db === null) {
            self::initDatabase(null);
        }
        if (!self::$db->tableExists(self::DIVISION)) {
            self::$db->createDivisionsTable(self::DIVISION);
        }
        if ($func == null) {
            $func = function () {
            };
        }
        if (self::$db->count(self::DIVISION) == 0) {
            $divisions = require('divisions_zh_CN.php');
            if ($has_extra) {
                $divisions = array_merge($divisions, require('divisions_extra_zh_CN.php'));
            }
            $data = [];
            $size = self::$db->size(self::DIVISION);
            $func(0, count($divisions));
            $time = Query::time();
            foreach ($divisions as $n => $d) {
                if ($time < Query::time()) {
                    $func(1, $n);
                    $time = Query::time();
                }
                $data[] = $d;
                if (count($data) >= $size) {
                    self::$db->insertDivisions(self::DIVISION, $data);
                    $data = [];
                }
            }
            if (count($data) > 0) {
                self::$db->insertDivisions(self::DIVISION, $data);
            }
            $func(2, 0);
        }
    }

    /**
     * @throws \Exception
     */
    static public function cleanDivision()
    {
        if (self::$db === null) {
            self::initDatabase(null);
        }
        if (self::$db->tableExists(self::DIVISION)) {
            self::$db->cleanTable(self::DIVISION);
        }
    }

    /**
     * @param callable $func
     * @throws \Exception
     */
    public function init(callable $func = null)
    {
        $count = count($this->providers);
        if (empty($count)) {
            return;
        }
        if (empty($func)) {
            $func = function ($code, $n) {
                // do nothing
            };
        }
        $provider = $this->providers[0];
        $func(0, count($provider));
        $this->startInsertIndex();
        $n = 0;
        $time = Query::time();
        foreach ($provider as $ip => $id) {
            if (is_string($id)) {
                $id = $provider->idByDivision($id);
            }
            for ($i = 1; $i < $count && $id == 0; $i++) {
                $id = $this->providers[$i]->findId($ip);
            }
            $this->insertIndex($ip, $id);
            $n++;
            if ($time < Query::time()) {
                $time = Query::time();
                $func(1, $n);
            }
        }
        $this->endInsertIndex();
        $func(2, 0);
        $this->rewind();    // init \Iterator offset & buffer
    }

    /**
     *
     */
    protected function startInsertIndex()
    {
        $this->initTable();
        $this->lasted = [];
        $this->saved = [];
    }

    /**
     */
    protected function initTable()
    {
        if (!self::$db->tableExists($this->name())) {
            self::$db->createIndexTable($this->name());
        } else {
            self::$db->cleanTable($this->name());
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    abstract public function translateId($id);

    /**
     * @param $ip
     * @param $id
     */
    protected function insertIndex($ip, $id)
    {
        $size = self::$db->size($this->name());
        if (!empty($this->lasted) && $id != $this->lasted['division_id']) {
            $this->saved[] = $this->lasted;
            if (count($this->saved) >= $size) {
                $this->endInsertIndex();
            }
        }
        $this->lasted = ['id' => $ip, 'division_id' => $id];
    }

    /**
     *
     */
    protected function endInsertIndex()
    {
        self::$db->insertIndexes($this->name(), $this->saved);
        $this->saved = [];
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
        $this->buffer_position = -self::$db->size($this->name()); // active read buffer
        $this->buffer = [];
    }

    /**
     *
     */
    public function clean()
    {
        if ($this->exists()) {
            self::$db->dropTable($this->name());
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return self::$db->tableExists($this->name());
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function find($ip)
    {
        return $this->divisionById($this->findId($ip));
    }

    /**
     * @param int $integer
     * @return string
     */
    public function divisionById($integer)
    {
        return self::getDivision($integer);
    }

    /**
     * @param $id
     * @return mixed
     */
    static protected function getDivision($id)
    {
        if (!isset(self::$divisions[$id])) {
            if (empty($id)) {
                $division = '';
            } else {
                $division = self::$db->getDivision(self::DIVISION, $id);
                if (empty($division['parent_id'])) {
                    $division = $division['name'];
                } else {
                    $division = self::getDivision($division['parent_id']) . "\t" . $division['name'];
                }
            }
            self::$divisions[$id] = $division;
        }
        return self::$divisions[$id];
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function findId($ip)
    {
        return self::$db->getIndex($this->name(), $ip);
    }

    /**
     * @param string $string
     * @return int
     */
    public function idByDivision($string)
    {
        return 0;
    }

    /**
     * @return integer
     */
    public function count()
    {
        return self::$db->count($this->name());
    }

    /**
     * @return integer
     */
    public function current()
    {
        return intval($this->buffer[$this->position - $this->buffer_position]['division_id']);
    }

    /**
     *
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @return integer
     */
    public function key()
    {
        return intval($this->buffer[$this->position - $this->buffer_position]['id']);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $size = self::$db->size($this->name());
        if ($this->position < $this->buffer_position || $this->position >= $this->buffer_position + $size) {
            $this->buffer_position = intval($this->position / $size) * $size;
            $this->buffer = self::$db->getIndexes($this->name(), $this->buffer_position, $size);
        }
        return isset($this->buffer[$this->position - $this->buffer_position]);
    }
}
