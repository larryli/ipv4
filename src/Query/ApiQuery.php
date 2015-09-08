<?php
/**
 * ApiQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class ApiQuery
 * @package larryli\ipv4\Query
 */
abstract class ApiQuery extends Query
{
    /**
     * @return bool
     */
    public function exists()
    {
        return true;
    }

    /**
     * @return int
     */
    public function total()
    {
        return 0;
    }

    /**
     *
     */
    public function clean()
    {
        // do nothing.
    }

    /**
     *
     */
    public function generate(callable $func, Query $provider = null, Query $provider_extra = null)
    {
        // do nothing.
    }

    /**
     * @param $func
     */
    public function dump(callable $func)
    {
        // do nothing.
    }

    /**
     * @param $func
     */
    public function each(callable $func)
    {
        // do nothing.
    }

    /**
     * @param $ip
     * @return int
     */
    public function id($ip)
    {
        return 0;
    }
}
