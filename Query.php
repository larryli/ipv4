<?php
/**
 * Query.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class Query
 *
 * @package larryli\ipv4\query
 */
abstract class Query implements \Countable, \Iterator
{
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
     * @param callback $func notify function
     * @param Query|null $provider main query provider
     * @param Query|null $provider_extra extra query provider
     * @return void
     */
    abstract public function init(callable $func, Query $provider = null, Query $provider_extra = null);

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
}
