<?php
/**
 * MonipdbQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export;

use larryli\ipv4\export\monipdb\File;
use larryli\ipv4\Query;


/**
 * Class MonipdbQuery
 * @package larryli\ipv4\export
 */
class MonipdbQuery extends ExportQuery
{
    /**
     * @var bool
     */
    protected $ecdz = false;

    /**
     * @param $ecdz
     */
    public function setEcdz($ecdz)
    {
        $this->ecdz = boolval($ecdz);
    }

    /**
     * @param Query $query
     * @param callable $func
     * @throws \Exception
     */
    public function export(Query $query, callable $func)
    {
        $func(0, count($query));
        $file = new File($this->filename, implode("\t", $this->version($query)));
        $n = 0;
        $time = self::time();
        foreach ($query as $ip => $id) {
            if (is_integer($id)) {
                $id = $query->divisionById($id);
            }
            if ($this->ecdz) {
                $id = implode('', explode("\t", $id));
            }
            if ($this->encoding != 'UTF-8') {
                $id = @iconv('UTF-8', $this->encoding . '//IGNORE', $id);
            }
            $file->add($ip, $id);
            $n++;
            if ($time < self::time()) {
                $time = self::time();
                $func(1, $n);
            }
        }
        $func(2, 0);
    }
}
