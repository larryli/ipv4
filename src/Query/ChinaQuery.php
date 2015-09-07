<?php
/**
 * ChinaQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class ChinaQuery
 * @package larryli\ipv4\Query
 */
class ChinaQuery extends DatabaseQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'china';
    }

    /**
     * @param $id
     * @return int
     */
    public function translateId($id)
    {
        if ($id > 10 && $id < 100000) {  // 国外
            $id = 0;
        }
        return $id;
    }
}