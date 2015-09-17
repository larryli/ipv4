<?php
/**
 * Query.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class Query
 * @package larryli\ipv4
 */
abstract class Query extends Object implements \Countable, \Iterator
{
    /**
     * @var Query[]
     */
    protected $providers = [];

    /**
     * return 0.1s
     *
     * @return int
     */
    static public function time()
    {
        return intval(microtime(true) * 10);
    }

    /**
     * @param $name
     * @param $options
     * @return Query
     * @throws \Exception
     */
    public static function create($name, $options)
    {
        if (is_array($options) && isset($options['class'])) {
            $class = $options['class'];
            $options = @$options['options'];
        } else {
            $class = __NAMESPACE__ . "\\" . ucfirst($name) . 'Query';
        }
        if (!class_exists($class)) {
            throw new \Exception("{$class} not found");
        }
        return new $class($options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name();
    }

    /**
     * name of the query
     *
     * @return string name string
     */
    abstract public function name();

    /**
     * check data is exists
     *
     * @return bool false meed the query need generate()
     */
    abstract public function exists();

    /**
     * initialize data with provider
     *
     * @param null|callback $func notify function
     * @return void
     */
    abstract public function init(callable $func = null);

    /**
     * clean data
     *
     * @return void
     */
    abstract public function clean();

    /**
     * query ip division
     *
     * @param $ip
     * @return string
     */
    abstract public function find($ip);

    /**
     * query ip division id
     *
     * @param $ip
     * @return integer
     */
    abstract public function findId($ip);

    /**
     * translate division id to division
     *
     * @param integer $integer division id
     * @return string division
     */
    abstract public function divisionById($integer);

    /**
     * translate division to division id
     *
     * @param string $string division
     * @return integer division id
     */
    abstract public function idByDivision($string);

    /**
     * @return Query[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param Query[] $providers
     * @return mixed
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];
        foreach ($providers as $provider) {
            if (Query::is_a($provider)) {
                $this->providers[] = $provider;
            }
        }
    }
}
