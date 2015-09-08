<?php
/**
 * WorldQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class WorldQuery
 * @package larryli\ipv4\Query
 */
class WorldQuery extends DatabaseQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'world';
    }

    /**
     * @param int $id
     * @return int
     */
    public function translateId($id)
    {
        if ($id > 100000 && $id < 700000) {  // 中国内地
            $id = 1;
        }
        return $id;
    }
}