<?php
/**
 * MiniQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class MiniQuery
 * @package larryli\ipv4\query
 */
class MiniQuery extends DatabaseQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'mini';
    }

    /**
     * @param $id
     * @return int
     */
    public function translateId($id)
    {
        if ($id > 700000) { // 港澳台
            $id = 0;
        } else if ($id > 100000) {  // 中国内地
            $id = 1;
        } else if ($id > 10) {  // 国外
            $id = 0;
        }
        return $id;
    }
}