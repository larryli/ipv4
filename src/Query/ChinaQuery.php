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
            if ($id > 10 && $id < 100000) {  // 国外
                $id = 0;
            }
            if ($this->saveTo($ip, $id)) {
                $func(1, $n);
            }
        });
        $this->endSave();
        $func(2, 0);
    }

}