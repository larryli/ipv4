<?php
/**
 * QqwryQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export;


use larryli\ipv4\export\qqwry\File;
use larryli\ipv4\Query;

class QqwryQuery extends ExportQuery
{
    /**
     * @var string export dat encoding
     */
    protected $encoding = 'GBK';
    /**
     * @var bool remove recode data prefix ip (unused)
     */
    protected $remove_ip_in_recode = false;

    public function setRemoveIpInRecode($remove)
    {
        $this->remove_ip_in_recode = boolval($remove);
    }
    /**
     * @param Query $query
     * @param callable $func
     * @throws \Exception
     */
    public function export(Query $query, callable $func)
    {
        $func(0, count($query));
        $file = new File($this->filename, $this->remove_ip_in_recode);
        $n = 0;
        $time = self::time();
        $last = -1;
        foreach ($query as $ip => $id) {
            $n++;
            if ($time < self::time()) {
                $time = self::time();
                $func(1, $n);
            }
            if (is_integer($id)) {
                $id = $query->divisionById($id);
            }
            if ($this->encoding != 'UTF-8') {
                $id = @iconv('UTF-8', $this->encoding . '//IGNORE', $id);
            }
            $a = explode("\t", $id);
            $division = '';
            switch (count($a)) {
                case 2: // qqwry
                    $country = $a[0];
                    $division = $a[1];
                    break;
                case 4: // monipdb
                    if ($a[0] == $a[1] && empty($a[2]) && empty($a[3])) {
                        $country = $a[0];
                    } else if (!empty($a[3])) {
                        $country = $a[0] . $a[1] .$a[2];
                        $division = $a[3];
                    } else {
                        $country = implode('', $a);
                    }
                    break;
                default: // larryli/ipv4
                    $country = implode('', $a);
                    break;
            }
            $file->add($last + 1, $country, $division);
            $last = $ip;
        }
        $version = $this->version($query);
        $file->add($last, $version[0], $version[1]);
        $func(2, 0);
    }
}
