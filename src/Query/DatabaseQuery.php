<?php
/**
 * DatabaseQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class DatabaseQuery
 * @package larryli\ipv4\Query
 */
abstract class DatabaseQuery extends Query
{
    /**
     *
     */
    const SIZE = 100;
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
    static private $divisons = [];
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
     * @param null|string|array|Database $options
     */
    public function __construct($options = null)
    {
        if (self::$db === null) {
            self::initDatabase($options);
        }
    }

    /**
     * @param string|array|Database $options
     * @throws \Exception
     */
    static public function initDatabase($options)
    {
        if (is_a($options, __NAMESPACE__ . '\\Database')) {
            self::$db = $options;
        } else {
            self::$db = new MedooDatabase($options);
        }
    }

    /**
     * @param callable $func
     * @param bool $has_extra
     * @throws \Exception
     */
    static public function initDivision(callable $func, $has_extra = false)
    {
        if (self::$db === null) {
            self::initDatabase(null);
        }
        if (!self::$db->tableExists(self::DIVISION)) {
            self::$db->createDivisionsTable(self::DIVISION);
        }
        if (self::$db->count(self::DIVISION) == 0) {
            $divisions = require('divisions.php');
            if ($has_extra) {
                $divisions = array_merge($divisions, require('divisions_extra.php'));
            }
            $func(0, count($divisions));
            self::$db->startCommit();
            $time = Query::time();
            foreach (array_chunk($divisions, self::SIZE) as $n => $data) {
                if ($time < Query::time()) {
                    $func(1, self::SIZE * $n);
                    $time = Query::time();
                }
                self::$db->insertDivisions(self::DIVISION, $data);
            }
            self::$db->endCommit();
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
     * @param Query|null $provider
     * @param Query|null $provider_extra
     * @throws \Exception
     */
    public function init(callable $func, Query $provider = null, Query $provider_extra = null)
    {
        if (!empty($provider_extra)) {
            $translateId = function ($ip, $id) use ($provider_extra) {
                if (empty($id)) {
                    $id = $provider_extra->division_id($ip);
                }
                return $id;
            };
        } else {
            $translateId = function ($_, $id) {
                return $id;
            };
        }

        $func(0, count($provider));
        $this->startInsertIndex();
        $n = 0;
        $time = Query::time();
        foreach ($provider as $ip => $id) {
            if (is_string($id)) {
                $id = $provider->integer($id);
            }
            $id = $this->translateId($translateId($ip, $id));
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
        if (!empty($this->lasted) && $id != $this->lasted['division_id']) {
            if (count($this->saved) > 499) {
                $this->endInsertIndex();
            } else {
                $this->saved[] = $this->lasted;
            }
        }
        $this->lasted = ['id' => $ip, 'division_id' => $id];
    }

    /**
     *
     */
    protected function endInsertIndex()
    {
        $this->saved[] = $this->lasted;
        self::$db->startCommit();
        self::$db->insertIndexes($this->name(), $this->saved);
        self::$db->endCommit();
        $this->saved = [];
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
        $this->buffer_position = -self::SIZE; // active read buffer
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
    public function division($ip)
    {
        return $this->string($this->division_id($ip));
    }

    /**
     * @param int $integer
     * @return string
     */
    public function string($integer)
    {
        return self::getDivision($integer);
    }

    /**
     * @param $id
     * @return mixed
     */
    static protected function getDivision($id)
    {
        if (!isset(self::$divisons[$id])) {
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
            self::$divisons[$id] = $division;
        }
        return self::$divisons[$id];
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function division_id($ip)
    {
        return self::$db->getIndex($this->name(), $ip);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function integer($string)
    {
        return '';
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
//        return $this->position;
//        $data = $this->getData();
        return intval($this->buffer[$this->position - $this->buffer_position]['division_id']);
    }

    /**
     * @return mixed
     */
//    protected function getData()
//    {
////        echo "{$this->position}\n";
//        if ($this->position < $this->buffer_position || $this->position >= $this->buffer_position + self::SIZE) {
//            $this->buffer_position = intval($this->position / self::SIZE) * self::SIZE;
//            echo "{$this->position}, {$this->buffer_position}\n";
//            $this->buffer = self::$db->getIndexes($this->name(), $this->buffer_position, self::SIZE);
//        }
//        return $this->buffer[$this->position - $this->buffer_position];
//    }

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
        //$data = $this->getData();
        return intval($this->buffer[$this->position - $this->buffer_position]['id']);
//        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        if ($this->position < $this->buffer_position || $this->position >= $this->buffer_position + self::SIZE) {
            $this->buffer_position = intval($this->position / self::SIZE) * self::SIZE;
//            echo "{$this->position}, {$this->buffer_position}\n";
            $this->buffer = self::$db->getIndexes($this->name(), $this->buffer_position, self::SIZE);
        }
        return isset($this->buffer[$this->position - $this->buffer_position]);
        //return $this->position < count($this);
    }

}
