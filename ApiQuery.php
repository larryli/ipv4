<?php
/**
 * ApiQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class ApiQuery
 * @package larryli\ipv4\query
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
     * @param callable $func
     * @param Query|null $provider
     * @param Query|null $provider_extra
     */
    public function init(callable $func, Query $provider = null, Query $provider_extra = null)
    {
        // do nothing.
    }

    /**
     *
     */
    public function clean()
    {
        // do nothing.
    }

    /**
     * @return int
     */
    public function count()
    {
        return 0;
    }

    /**
     * @param $ip
     * @return int
     */
    public function findId($ip)
    {
        return 0;
    }


    public function divisionById($integer)
    {
        return '';
    }

    public function idByDivision($string)
    {
        return 0;
    }


    public function current()
    {
        return null;
    }

    public function next()
    {
        // do nothing
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return false;
    }

    public function rewind()
    {
        // do nothing
    }
}
