<?php

/**
 * IPv4.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2;

use larryli\ipv4\query\Query;
use Yii;
use yii\base\Component;

/**
 * Class IPv4
 * @package larryli\ipv4\yii2
 */
class IPv4 extends Component
{
    /**
     * @var string table prefix
     */
    public $prefix = 'ipv4_';
    /**
     * @var string runtime path
     */
    public $runtime = '';
    /**
     * @var string larryli\ipv4\query\Database class
     */
    public $database = '';
    /**
     * @var array
     */
    public $providers = [];
    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->providers = Query::config($this->providers);
        foreach ($this->providers as $name => $provider) {
            $this->createQuery($name);
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function createQuery($name)
    {
        $options = null;
        switch ($name) {
            case 'monipdb':
            case 'qqwry':
                $options = $this->getFileOptions($name);
                break;
            case 'full':
            case 'mini':
            case 'china':
            case 'world':
                $options = $this->getDatabaseOptions($name);
                break;
        }
        $this->objects[$name] = Query::create($name, $options);
        return $this->objects[$name];
    }

    /**
     * @param $name
     * @return bool|null|string
     */
    private function getFileOptions($name)
    {
        $options = null;
        if (!empty($this->runtime)) {
            $options = Yii::getAlias($this->runtime);
            switch ($name) {
                case 'monipdb':
                    $options .= '/17monipdb.dat';
                    break;
                case 'qqwry':
                    $options .= '/qqwry.dat';
                    break;
            }
        }
        return $options;
    }

    /**
     * @param $name
     * @return array|null
     */
    private function getDatabaseOptions($name)
    {
        $options = null;
        if (!empty($this->database)) {
            $options = new $this->database(['prefix' => $this->prefix]);
        } else if (!empty($this->runtime)) {
            $options = [
                'database_type' => 'sqlite',
                'database_file' => Yii::getAlias($this->runtime) . '/ipv4.sqlite',
            ];
        }
        return $options;
    }

    /**
     * @return Query[]
     */
    public function getQueries()
    {
        return $this->objects;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->objects)) {
            return $this->objects[$name];
        }
        return parent::__get($name);
    }

    /**
     * @param string $name
     * @return bool|mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->objects)) {
            return true;
        }
        return parent::__get($name);
    }

}