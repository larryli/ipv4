<?php
/**
 * MonipdbQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export;

use larryli\ipv4\export\monipdb\Indexes;
use larryli\ipv4\export\monipdb\Strings;
use larryli\ipv4\Query;


/**
 * Class MonipdbQuery
 * @package larryli\ipv4\export
 */
class MonipdbQuery extends ExportQuery
{
    /**
     * @param callable|null $func
     * @throws \Exception
     */
    public function init(callable $func = null)
    {
        if (count($this->providers) <= 0) {
            throw new \Exception("Invalid provider: must need one!");
        }
        $provider = $this->providers[0];
        $total = count($provider);
        if ($total <= 0) {
            throw new \Exception("Invalid provider {$provider}: is empty!");
        }
        if ($func == null) {
            $func = function () {
                // do nothing
            };
        }
        $fp = @fopen($this->filename, 'wb');
        if ($fp === false) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        $size = 4 + 1024;
        fwrite($fp, str_pad('', $size), $size); // write empty offset & index
        $func(0, $total);
        $last_invalid = true;
        $last_ip = ip2long('255.255.255.255');
        $idx = new Indexes();
        $str = new Strings();
        $n = 0;
        $time = self::time();
        foreach ($provider as $ip => $id) {
            $idx->set($ip, $n);
            if ($ip == $last_ip) {
                $last_invalid = false;
                $id = $this->copyright($provider);
            } else if (is_integer($id)) {
                $id = $provider->divisionById($id);
            }
            $str->set($fp, $ip, $id);
            $n++;
            if ($time < self::time()) {
                $time = self::time();
                $func(1, $n);
            }
        }
        if ($last_invalid || $idx->invalid()) {
            $idx->set($last_ip, $n);
            $str->set($fp, $last_ip, $this->copyright($provider));
        }
        $offset = ftell($fp) + 1024;
        $str->write($fp);
        rewind($fp);
        fwrite($fp, pack('N', $offset), 4);
        $idx->write($fp);
        fclose($fp);
        $func(2, 0);
    }

    /**
     * @param Query $provider
     * @return string
     */
    protected function copyright(Query $provider)
    {
        return implode("\t", [
            'ipv4.larryli.cn',
            date('Ymd'),
            $provider->className(),
            $provider->name(),
        ]);
    }
}
