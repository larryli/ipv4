<?php
/**
 * FullQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class FullQuery
 * @package larryli\ipv4\Query
 */
class FullQuery extends DatabaseQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'full';
    }

    /**
     * @param $id
     * @return mixed
     */
    public function translateId($id)
    {
        return $id;
    }
}
