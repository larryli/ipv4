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
     * @param $func
     * @param $db1
     * @param $db2
     */
    public function generate($func, $db1, $db2)
    {
        $this->startSave();
        $func(0, $db1->getTotal());
        $db1->dump(function ($ip, $address) use ($func, $db1, $db2) {
            static $n = 0;
            $n++;
            list($id, $_) = $db1->guess($address);
            if (empty($id)) {
                list($id, $_) = $db2->guess($db2->query($ip));
            }
            if ($this->saveTo($ip, $id)) {
                $func(1, $n);
            }
        });
        $this->endSave();
        $func(2, 0);
    }

}
