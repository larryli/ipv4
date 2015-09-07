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
    static protected $medoo;
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
     * @param $func
     * @param $db1
     * @param $db2
     */
    abstract public function generate($func, $db1, $db2);

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (self::$medoo === null) {
            self::initMedoo();
        }
    }

    /**
     * @throws \Exception
     */
    static protected function initMedoo()
    {
        $config = dirname(dirname(__DIR__)) . '/config/db.php';
        if (!file_exists($config)) {
            $data = <<<EOT
<?php
return [
    'database_type' => 'sqlite',
    'database_file' => __DIR__ . '/../runtime/ipv4.sqlite',
];
EOT;
            if (file_put_contents($config, $data) === FALSE) {
                throw new \Exception("write config file \"{$config}\" error");
            }
        }
        self::$medoo = new Medoo(require($config));
    }

    /**
     * @param $func
     * @throws \Exception
     */
    static public function initDivision($func)
    {
        if (self::$medoo === null) {
            self::initMedoo();
        }
        if (!self::$medoo->table_exists(self::DIVISION)) {
            self::$medoo->create_table(self::DIVISION, [
                'id' => self::$medoo->pk_type(true, false),
                'name' => 'varchar(255)',
                'title' => 'varchar(255)',
                'is_city' => 'boolean',
                'parent_id' => self::$medoo->int_type(false),
            ]);
            self::$medoo->create_index('is_city', self::DIVISION, 'is_city');
            self::$medoo->create_index('parent_id', self::DIVISION, 'parent_id');
        }
        if (self::$medoo->count(self::DIVISION) == 0) {
            $divisions = require('divisions.php');
            $func(0, count($divisions));
            foreach (array_chunk($divisions, self::SIZE) as $n => $data) {
                $func(1, self::SIZE * $n);
                self::$medoo->insert(self::DIVISION, $data);
            }
            $func(2, 0);
        }
    }

    /**
     * @throws \Exception
     */
    static public function cleanDivision()
    {
        if (self::$medoo === null) {
            self::initMedoo();
        }
        if (self::$medoo->table_exists(self::DIVISION)) {
            self::$medoo->drop_table(self::DIVISION);
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
        $data = self::$medoo->get('divisions', ['name', 'parent_id'], ['id' => $id]);
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
        if (!self::$medoo->table_exists($this->name())) {
            self::$medoo->create_table($this->name(), [
                'id' => self::$medoo->pk_type(false, true),
                'division_id' => self::$medoo->int_type(false),
            ]);
        } else {
            self::$medoo->clean_table($this->name());
        }
    }

    /**
     * @return mixed
     */
    public function exists()
    {
        return self::$medoo->table_exists($this->name());
    }

    /**
     * @param $func
     */
    public function dumpId($func)
    {
        $total = $this->getTotal();
        for ($i = 0; $i < $total; $i += self::SIZE) {
            $data = self::$medoo->select($this->name(), ['id', 'division_id'], [
                'ORDER' => 'id ASC',
                'LIMIT' => [$i, self::SIZE],
            ]);
            foreach ($data as $row) {
                $func($row['id'], $row['division_id']);
            }
        }
    }

    /**
     * @param $func
     */
    public function dump($func)
    {
        $total = $this->getTotal();
        for ($i = 0; $i < $total; $i += self::SIZE) {
            $data = self::$medoo->select($this->name(), ['id', 'division_id'], [
                'ORDER' => 'id ASC',
                'LIMIT' => [$i, self::SIZE],
            ]);
            foreach ($data as $row) {
                $func($row['id'], self::getData($row['division_id']));
            }
        }
    }

    /**
     * @param $ip
     * @return mixed
     */
    public function query($ip)
    {
        $id = self::$medoo->get($this->name(), 'division_id', [
            'id[>=]' => $ip,
            'ORDER' => 'id ASC',
            'LIMIT' => 1,
        ]);
        return self::getData($id);
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return self::$medoo->count($this->name());
    }

    /**
     *
     */
    public function clean()
    {
        if ($this->exists()) {
            self::$medoo->drop_table($this->name());
        }
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
        self::$medoo->insert($this->name(), $this->saved);
        $this->saved = [];
    }

}
