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
     * @var
     */
    static protected $db;
    /**
     * @var
     */
    static private $data;
    /**
     * @var array
     */
    private $saved = [];
    /**
     * @var array
     */
    private $last_saved = [];

    /**
     * @param $id
     * @return mixed
     */
    abstract public function translateId($id);

    /**
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        if (self::$db === null) {
            self::initDatabase($options);
        }
    }

    /**
     * @throws \Exception
     */
    static public function initDatabase($options)
    {
        if (is_a($options, Database)) {
            self::$db = $options;
        } else {
            self::$db = new MedooDatabase($options);
        }
    }

    /**
     * @param $func
     * @throws \Exception
     */
    static public function initDivision($func)
    {
        if (self::$db === null) {
            self::initDatabase(null);
        }
        if (!self::$db->tableExists(self::DIVISION)) {
            self::$db->createDivisionsTable(self::DIVISION);
        }
        if (self::$db->count(self::DIVISION) == 0) {
            $divisions = require('divisions.php');
            $func(0, count($divisions));
            foreach (array_chunk($divisions, self::SIZE) as $n => $data) {
                $func(1, self::SIZE * $n);
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
     * @param $id
     * @return string
     */
    static private function _getData($id)
    {
        if (empty($id)) {
            return '';
        }
        $data = self::$db->getDivision(self::DIVISION, $id);
        if (empty($data['parent_id'])) {
            return $data['name'];
        }
        return self::_getData($data['parent_id']) . "\t" . $data['name'];
    }

    /**
     * @param $id
     * @return mixed
     */
    static protected function getData($id)
    {
        if (!isset(self::$data[$id])) {
            self::$data[$id] = self::_getData($id);
        }
        return self::$data[$id];
    }

    /**
     * @param $medoo
     * @param $table
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
     * @return mixed
     */
    public function exists()
    {
        return self::$db->tableExists($this->name());
    }

    /**
     * @param $func
     * @param $translateId
     */
    protected function dumpFunc($func, $translateId)
    {
        $total = $this->getTotal();
        for ($i = 0; $i < $total; $i += self::SIZE) {
            $data = self::$db->getIndexes($this->name(), $i, self::SIZE);
            foreach ($data as $row) {
                $func($row['id'], $translateId($row['division_id']));
            }
        }
    }

    /**
     * @param $func
     */
    public function dumpId($func)
    {
        $this->dumpFunc($func, function ($id) {
            return $id;
        });
    }

    /**
     * @param $func
     */
    public function dump($func)
    {
        $this->dumpFunc($func, self::getData);
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function query($ip)
    {
        $id = self::$db->getIndex($this->name(), $ip);
        return self::getData($id);
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return self::$db->count($this->name());
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
     * @param $func
     * @param $db1
     * @param $db2
     * @throws \Exception
     */
    public function generate($func, $db1, $db2 = null)
    {
        if (is_object($db2) && method_exists($db2, 'guess')) {
            $translateId = function ($ip, $id) use ($db2) {
                if (empty($id)) {
                    list($id, $_) = $db2->guess($db2->query($ip));
                }
                return $id;
            };
        } else {
            $translateId = function ($_, $id) {
                return $id;
            };
        }

        $this->startSave();
        $func(0, $db1->getTotal());
        $db1->dumpId(function ($ip, $id) use ($func, $translateId) {
            static $n = 0;
            $n++;
            $id = $this->translateId($translateId($ip, $id));
            if ($this->saveTo($ip, $id)) {
                $func(1, $n);
            }
        });
        $this->endSave();
        $func(2, 0);
    }

    /**
     *
     */
    protected function startSave()
    {
        $this->initTable();
        $this->saved = [];
        $this->last_saved = [];
    }

    /**
     * @param $ip
     * @param $id
     * @return bool
     */
    protected function saveTo($ip, $id)
    {
        $flush = false;
        if (!empty($this->last_saved) && $id != $this->last_saved['division_id']) {
            if (count($this->saved) > self::SIZE) {
                $this->endSave();
                $flush = true;
            } else {
                $this->saved[] = $this->last_saved;
            }
        }
        $this->last_saved = ['id' => $ip, 'division_id' => $id];
        return $flush;
    }

    /**
     *
     */
    protected function endSave()
    {
        $this->saved[] = $this->last_saved;
        self::$db->insertIndexes($this->name(), $this->saved);
        $this->saved = [];
    }

}
