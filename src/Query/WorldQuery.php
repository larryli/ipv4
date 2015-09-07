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
     * @param $func
     * @param $db1
     * @param $db2
     */
    public function generate($func, $db1, $db2)
    {
        $this->startSave();
        $func(0, $db1->getTotal());
        $db1->dumpId(function ($ip, $id) use ($func, $db1) {
            static $n = 0;
            $n++;
            if ($id > 100000 && $id < 700000) {  // 中国内地
                $id = 1;
            }
            if ($this->saveTo($ip, $id)) {
                $func(1, $n);
            }
        });
        $this->endSave();
        $func(2, 0);
    }

}