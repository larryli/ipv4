<?php
/**
 * QQWryQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class QQWryQuery
 * @package larryli\ipv4\Query
 */
class QQWryQuery extends FileQuery
{
    /**
     *
     */
    const COPYWRITE_URL = 'http://update.cz88.net/ip/copywrite.rar';
    /**
     *
     */
    const QQWRY_URL = 'http://update.cz88.net/ip/qqwry.rar';
    /**
     * @var null
     */
    private $fp = null;
    /**
     * @var int
     */
    private $end = 0;
    /**
     * index cache
     * @var array
     */
    private $index;
    /**
     * record cache
     * @var array
     */
    private $data;
    /**
     * query address/division id cache
     * @var array
     */
    private $cached = [];

    /**
     * @param string $filename
     * @throws \Exception
     */
    function __construct($filename = '')
    {
        if (empty($filename)) {
            $filename = self::getRuntime('qqwry.dat');
        }
        return parent::__construct($filename);
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->fp != null) {
            fclose($this->fp);
        }
    }

    /**
     * @param callable $func
     * @param Query|null $provider
     * @param Query|null $provider_extra
     * @throws \Exception
     */
    public function generate(callable $func = null, Query $provider = null, Query $provider_extra = null)
    {
        if (empty($func)) {
            $copywrite = file_get_contents(self::COPYWRITE_URL);
            $qqwry = file_get_contents(self::QQWRY_URL);
        } else {
            $copywrite = $func(self::COPYWRITE_URL);
            $qqwry = $func(self::QQWRY_URL);
        }
        $key = unpack("V6", $copywrite)[6];
        for ($i = 0; $i < 0x200; $i++) {
            $key *= 0x805;
            $key++;
            $key = $key & 0xFF;
            $qqwry[$i] = chr(ord($qqwry[$i]) ^ $key);
        }
        $qqwry = gzuncompress($qqwry);
        $fp = fopen($this->filename, 'wb');
        if (!$fp) {
            throw new \Exception("write \"{$this->filename}\" error");
        }
        fwrite($fp, $qqwry);
        fclose($fp);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function init()
    {
        if (parent::init()) {
            return true;
        }
        $this->fp = fopen($this->filename, 'rb');
        if ($this->fp === FALSE) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        $offset = unpack('Llen', fread($this->fp, 4));
        $end = unpack('Llen', fread($this->fp, 4));
        $this->end = $end['len'] - $offset['len'] + 7;
        if ($offset['len'] < 4) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        if ($this->end < 7) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        fseek($this->fp, $offset['len']);
        $this->index = fread($this->fp, $this->end);
        return true;
    }

    /**
     * @param callable $func
     * @param callable $translate
     * @throws \Exception
     */
    protected function traverse(callable $func, callable $translate)
    {
        $this->init();
        $this->initData();
        $start = 0;
        while (1) {
            $offset = unpack('Llen', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
            $data = $translate($this->readRecode($offset['len']));
            $start += 7;
            if ($start < $this->end) {
                $ip = unpack('Llen', $this->index{$start} . $this->index{$start + 1} . $this->index{$start + 2} . $this->index{$start + 3});
                $func($ip['len'] - 1, $data);
            } else {
                $func(4294967295, $data);  // last
                break;
            }
        }
    }

    /**
     *
     */
    private function initData()
    {
        fseek($this->fp, 0);
        $offset = unpack('Llen', fread($this->fp, 4));
        fseek($this->fp, 0);
        $this->data = fread($this->fp, $offset['len']);
        fclose($this->fp);
        $this->fp = null;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function total()
    {
        $this->init();
        return intval($this->end / 7);
    }

    /**
     * @param $ip
     * @return mixed
     * @throws \Exception
     */
    public function address($ip)
    {
        $ip_start = intval(floor($ip / (256 * 256 * 256)));

        if ($ip_start < 0 || $ip_start > 255) {
            throw new \Exception("{$ip} is not valid.");
        }
        if (isset($this->cached[$ip]) === TRUE) {
            return $this->cached[$ip];
        }

        $this->init();
        $offset = $this->find($ip, 0, $this->end);
        $offset = unpack('Llen', $this->index{$offset + 4} . $this->index{$offset + 5} . $this->index{$offset + 6} . "\x0");
        $this->cached[$ip] = $this->readRecode($offset['len']);
        return $this->cached[$ip];
    }

    /**
     * @param $address
     * @return array
     */
    public function guess($address)
    {
        foreach (self::$divisions as $country_name => $country_data) {
            if (strncmp($address, $country_name, strlen($country_name)) == 0) {
                if (empty($country_data['divisions'])) {
                    return [$country_data['id'], $country_name];
                }
                $pos = strlen($country_name);
                $provincies = $country_data['divisions'];
                foreach ($provincies as $province_name => $province_data) {
                    $pos1 = strpos($address, $province_name, $pos);
                    if ($pos1 !== FALSE) {
                        if (empty($province_data['divisions'])) {
                            return [$province_data['id'], $province_name];
                        }
                        $pos1 += strlen($province_name);
                        $cities = $province_data['divisions'];
                        foreach ($cities as $city_name => $city_data) {
                            $pos2 = strpos($address, $city_name, $pos1);
                            if ($pos2 !== FALSE) {
                                return [$city_data['id'], $city_name];
                            }
                        }
                    }
                }
            }
        }
        $provincies = self::$divisions['中国']['divisions'];
        foreach ($provincies as $province_name => $province_data) {
            if (strncmp($address, $province_name, strlen($province_name)) == 0) {
                if (empty($province_data['divisions'])) {
                    return [$province_data['id'], $province_name];
                }
                $pos1 = strlen($province_name);
                $cities = $province_data['divisions'];
                foreach ($cities as $city_name => $city_data) {
                    $pos2 = strpos($address, $city_name, $pos1);
                    if ($pos2 !== FALSE) {
                        return [$city_data['id'], $city_name];
                    }
                }
                return [$province_data['id'], $province_name];
            }
        }
        return [0, ''];
    }

    /**
     * @param $ip
     * @param $l
     * @param $r
     * @return int
     */
    private function find($ip, $l, $r)
    {
        if ($l + 7 >= $r) {
            return $l;
        }
        $m = intval(($l + $r) / 14) * 7;
        $mip = unpack('Llen', $this->index{$m} . $this->index{$m + 1} . $this->index{$m + 2} . $this->index{$m + 3});
        if ($ip < $mip['len']) {
            return $this->find($ip, $l, $m);
        } else {
            return $this->find($ip, $m, $r);
        }
    }

    /**
     * @param $offset
     * @return string
     */
    private function readRecode($offset)
    {
        $record = array('', '');
        $offset = $offset + 4;
        $flag = ord($this->readOffset(1, $offset));
        if ($flag == 1) {
            $location_offset = $this->readOffset(3, $offset + 1);
            $location_offset = unpack('Llen', $location_offset . "\x0");
            $sub_flag = ord($this->readOffset(1, $location_offset['len']));
            if ($sub_flag == 2) {
                // 国家
                $country_offset = $this->readOffset(3, $location_offset['len'] + 1);
                $country_offset = unpack('Llen', $country_offset . "\x0");
                $record[0] = $this->readLocation($country_offset['len']);
                // 地区
                $record[1] = $this->readLocation($location_offset['len'] + 4);
            } else {
                $record[0] = $this->readLocation($location_offset['len']);
                $record[1] = $this->readLocation($location_offset['len'] + strlen($record[0]) + 1);
            }
        } else if ($flag == 2) {
            // 地区
            // offset + 1(flag) + 3(country offset)
            $record[1] = $this->readLocation($offset + 4);
            // offset + 1(flag)
            $country_offset = $this->readOffset(3, $offset + 1);
            $country_offset = unpack('Llen', $country_offset . "\x0");
            $record[0] = $this->readLocation($country_offset['len']);
        } else {
            $record[0] = $this->readLocation($offset);
            $record[1] = $this->readLocation($offset + strlen($record[0]) + 1);
        }
        return @iconv('GBK', 'UTF-8//IGNORE', implode("\t", $record));
    }

    /**
     * @param $offset
     * @return string
     */
    private function readLocation($offset)
    {
        if ($offset == 0) {
            return '';
        }
        $flag = ord($this->readOffset(1, $offset));
        // 出错
        if ($flag == 0) {
            return '';
        }
        // 仍然为重定向
        if ($flag == 2) {
            $offset = $this->readOffset(3, $offset + 1);
            $offset = unpack('Llen', $offset . "\x0");
            return $this->readLocation($offset['len']);
        }
        $location = '';
        $chr = $this->readOffset(1, $offset);
        while (ord($chr) != 0) {
            $location .= $chr;
            $offset++;
            $chr = $this->readOffset(1, $offset);
        }
        return $location;
    }

    /**
     * @param $len
     * @param $offset
     * @return string
     */
    private function readOffset($len, $offset)
    {
        if (empty($this->fp)) {
            return substr($this->data, $offset, $len);
        }
        fseek($this->fp, $offset);
        return fread($this->fp, $len);
    }
}
