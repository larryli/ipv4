<?php
/**
 * Query.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class Query
 * @package larryli\ipv4\Query
 */
abstract class Query
{
    /**
     * @return mixed
     */
    abstract public function name();

    /**
     * @return mixed
     */
    abstract public function exists();

    /**
     * @return mixed
     */
    abstract public function clean();

    /**
     * @param $func
     * @return mixed
     */
    abstract public function dump($func);

    /**
     * @param $ip
     * @return mixed
     */
    abstract public function query($ip);

    /**
     * @return mixed
     */
    abstract public function getTotal();

}
