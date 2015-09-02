<?php
/**
 * QQWry.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\IPv4\IPDB;


class QQWry extends IPDB
{
    const COPYWRITE_URL = 'http://update.cz88.net/ip/copywrite.rar';
    const QQWRY_URL = 'http://update.cz88.net/ip/qqwry.rar';
    private $fp = null;
    private $end = 0;
    private $index = [];
    private $data = [];
    private $cached = [];

    public function __destruct()
    {
        if ($this->fp !== null) {
            fclose($this->fp);
        }
    }

    public function download($func = file_get_contents)
    {
        $copywrite = $func(self::COPYWRITE_URL);
        $qqwry = $func(self::QQWRY_URL);
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

    public function init()
    {
        if (parent::init()) {
            return true;
        }
        $this->fp = fopen($this->filename, 'rb');
        if ($this->fp === FALSE) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        list($_, $offset) = unpack('L', fread($this->fp, 4));
        list($_, $end) = unpack('L', fread($this->fp, 4));
        $this->end = $end - $offset + 7;
        if ($offset < 4) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        if ($this->end < 7) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        fseek($this->fp, $offset);
        $this->index = fread($this->fp, $this->end);
        return true;
    }

    public function dump($func)
    {
        $this->init();
        for ($start = 0; $start < $this->end; $start += 7) {
            list($_, $ip) = unpack('L', $this->index{$start} . $this->index{$start + 1} . $this->index{$start + 2} . $this->index{$start + 3});
            list($_, $offset) = unpack('L', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
            $func($ip, $this->readRecode($offset));
        }
    }

    public function getTotal()
    {
        $this->init();
        return intval($this->end / 7);
    }

    public function query($ip)
    {
        $ip_start = floor($ip / (256 * 256 * 256));

        if ($ip_start < 0 || $ip_start > 255) {
            throw new \Exception("{$ip} is not valid.");
        }
        if (isset($this->cached[$ip]) === TRUE) {
            return $this->cached[$ip];
        }

        $this->init();
        $offset = $this->find($ip, 0, $this->end);
        list($_, $offset) = unpack('L', $this->index{$offset + 4} . $this->index{$offset + 5} . $this->index{$offset + 6} . "\x0");
        $this->cached[$ip] = $this->readRecode($offset);
        return $this->cached[$ip];
    }

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

    private function find($ip, $l, $r)
    {
        if ($l + 7 >= $r) {
            return $l;
        }
        $m = intval(($l + $r) / 14) * 7;
        list($_, $mip) = unpack('L', $this->index{$m} . $this->index{$m + 1} . $this->index{$m + 2} . $this->index{$m + 3});
        if ($ip == $mip) {
            return $m;
        } else if ($ip < $mip) {
            return $this->find($ip, $l, $m);
        } else {
            return $this->find($ip, $m, $r);
        }
    }

    private function readRecode($offset)
    {
        if (!isset($this->data[$offset])) {
            $record = array('', '');
            $offset = $offset + 4;
            $flag = ord($this->readOffset(1, $offset));
            if ($flag == 1) {
                $location_offset = $this->readOffset(3, $offset + 1);
                list($_, $location_offset) = unpack('L', $location_offset . "\x0");
                $sub_flag = ord($this->readOffset(1, $location_offset));
                if ($sub_flag == 2) {
                    // 国家
                    $country_offset = $this->readOffset(3, $location_offset + 1);
                    list($_, $country_offset) = unpack('L', $country_offset . "\x0");
                    $record[0] = $this->readLocation($country_offset);
                    // 地区
                    $record[1] = $this->readLocation($location_offset + 4);
                } else {
                    $record[0] = $this->readLocation($location_offset);
                    $record[1] = $this->readLocation($location_offset + strlen($record[0]) + 1);
                }
            } else if ($flag == 2) {
                // 地区
                // offset + 1(flag) + 3(country offset)
                $record[1] = $this->readLocation($offset + 4);
                // offset + 1(flag)
                $country_offset = $this->readOffset(3, $offset + 1);
                list($_, $country_offset) = unpack('L', $country_offset . "\x0");
                $record[0] = $this->readLocation($country_offset);
            } else {
                $record[0] = $this->readLocation($offset);
                $record[1] = $this->readLocation($offset + strlen($record[0]) + 1);
            }
            $this->data[$offset] = @iconv('GBK', 'UTF-8//IGNORE', implode("\t", $record));
        }
        return $this->data[$offset];
    }

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
            list($_, $offset) = unpack('L', $offset . "\x0");
            return $this->readLocation($offset);
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

    private function readOffset($len, $offset)
    {
        fseek($this->fp, $offset);
        return fread($this->fp, $len);
    }
}
