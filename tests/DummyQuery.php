<?php
/**
 * DummyQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\tests;


use larryli\ipv4\Query;

/**
 * Class DummyQuery
 * @package larryli\ipv4\tests
 */
class DummyQuery extends Query
{
    /**
     * @var array
     */
    protected $indexes = [];
    /**
     * @return null
     */
    public function current()
    {
        return current($this->indexes);
    }

    /**
     *
     */
    public function next()
    {
        next($this->indexes);
    }

    /**
     * @return null
     */
    public function key()
    {
        return key($this->indexes);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->key() !== null;   // $this->current !== false;
    }

    /**
     *
     */
    public function rewind()
    {
        reset($this->indexes);
    }

    /**
     * @return string
     */
    public function name()
    {
        return 'dummy';
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * @param callable|null $func
     */
    public function init(callable $func = null)
    {
        $this->indexes = [];
        if (count($this->providers) > 0) {
            $provider = $this->providers[0];
            foreach ($provider as $ip => $id) {
                $this->indexes[$ip] = $id;
            }
        } else if ($func == null) {
            $this->indexes[ip2long('0.0.0.0')] = 4;
            $this->indexes[ip2long('0.255.255.255')] = 4;
            $this->indexes[ip2long('1.255.255.255')] = 0;
            $this->indexes[ip2long('2.255.255.255')] = 0;
            $this->indexes[ip2long('3.255.255.255')] = 0;
            $this->indexes[ip2long('4.255.255.255')] = 0;
            $this->indexes[ip2long('5.255.255.255')] = 0;
            $this->indexes[ip2long('6.255.255.255')] = 0;
            $this->indexes[ip2long('10.255.255.255')] = 3;
            $this->indexes[ip2long('126.255.255.255')] = 0;
            $this->indexes[ip2long('127.0.0.0')] = 3;
            $this->indexes[ip2long('127.0.0.1')] = 2;
            $this->indexes[ip2long('127.255.255.255')] = 3;
            $this->indexes[ip2long('169.253.255.255')] = 0;
            $this->indexes[ip2long('169.254.255.255')] = 5;
            $this->indexes[ip2long('172.15.255.255')] = 0;
            $this->indexes[ip2long('172.31.255.255')] = 3;
            $this->indexes[ip2long('192.167.255.255')] = 0;
            $this->indexes[ip2long('192.168.255.255')] = 3;
            $this->indexes[ip2long('243.255.255.255')] = 0;
            $this->indexes[ip2long('255.255.255.255')] = 4;
        } else {
            $this->indexes = $func();
        }
        $this->rewind();
    }

    /**
     *
     */
    public function clean()
    {
        $this->indexes = [];
    }

    /**
     * @param $ip
     * @return string
     */
    public function find($ip)
    {
        foreach ($this->indexes as $i => $id) {
            if ($i >= $ip) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * @param $ip
     * @return int
     */
    public function findId($ip)
    {
        return $this->idByDivision($this->find($ip));
    }

    /**
     * @param int $integer
     * @return string
     */
    public function divisionById($integer)
    {
        return (string)$integer;
    }

    /**
     * @param string $string
     * @return int
     */
    public function idByDivision($string)
    {
        return intval($string);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->indexes);
    }
}
