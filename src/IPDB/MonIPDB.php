<?php
/**
 * MonIPDB.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\IPDB;


class MonIPDB extends IPDB
{
    const URL = 'http://s.qdcdn.com/17mon/17monipdb.zip';
    private $fp = null;
    private $offset = 0;
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
        $file = $func(self::URL);
        $zip_file = dirname($this->filename) . '/17monipdb.zip';
        file_put_contents($zip_file, $file);
        $zip = new \ZipArchive;
        $res = $zip->open($zip_file);
        if ($res === TRUE) {
            $zip->extractTo(dirname($this->filename), basename($this->filename));
            $zip->close();
            unlink($zip_file);
        } else {
            throw new \Exception("Unzip {$zip_file} error!");
        }
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
        list($_, $this->offset) = unpack('N', fread($this->fp, 4));
        if ($this->offset < 4) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        $this->end = $this->offset - 1024 - 4;
        $this->index = fread($this->fp, $this->offset - 4);
        return true;
    }

    public function dump($func)
    {
        $this->init();
        for ($start = 1024; $start < $this->end; $start += 8) {
            list($_, $ip) = unpack('N', $this->index{$start} . $this->index{$start + 1} . $this->index{$start + 2} . $this->index{$start + 3});
            list($_, $offset) = unpack('V', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
            list($_, $length) = unpack('C', $this->index{$start + 7});
            $func($ip, $this->readOffset($offset, $length));
        }
    }

    public function getTotal()
    {
        $this->init();
        return intval(($this->end - 1024) / 8);
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
        $nip = pack('N', $ip);
        $tmp_offset = $ip_start * 4;
        list($_, $start) = unpack('V', $this->index[$tmp_offset] . $this->index[$tmp_offset + 1] . $this->index[$tmp_offset + 2] . $this->index[$tmp_offset + 3]);
        $start = $start * 8 + 1024;
        if ($ip_start == 255) {
            $end = $this->end - 8;
        } else {
            list($_, $end) = unpack('V', $this->index[$tmp_offset + 4] . $this->index[$tmp_offset + 5] . $this->index[$tmp_offset + 6] . $this->index[$tmp_offset + 7]);
            $end = $end * 8 + 1024 - 8;
        }
        $start = $this->find2($nip, $start, $end);
        if ($start === null) {
            $this->cached[$ip] = '';
        } else {
            list($_, $offset) = unpack('V', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
            list($_, $length) = unpack('C', $this->index{$start + 7});
            $this->cached[$ip] = $this->readOffset($offset, $length);
        }
        return $this->cached[$ip];
    }

    public function guess($address)
    {
        list($country, $province, $city, $_) = explode("\t", $address);

        foreach (self::$divisions as $country_name => $country_data) {
            if (strncmp($country, $country_name, strlen($country_name)) == 0) {
                if (empty($province) || empty($country_data['divisions'])) {
                    return [$country_data['id'], $country_name];
                }
                $provincies = $country_data['divisions'];
                foreach ($provincies as $province_name => $province_data) {
                    if (strncmp($province, $province_name, strlen($province_name)) == 0) {
                        if (empty($city) || empty($province_data['divisions'])) {
                            return [$province_data['id'], $province_name];
                        }
                        $cities = $province_data['divisions'];
                        foreach ($cities as $city_name => $city_data) {
                            if (strncmp($city, $city_name, strlen($city_name)) == 0) {
                                return [$city_data['id'], $city_name];
                            }
                        }
                        throw new \Exception("\"{$address}\" cannot found \"{$city}\" at \"{$country_name}\" \"{$province_name}\"");
                    }
                }
                throw new \Exception("\"{$address}\" cannot found \"{$province}\" at \"{$country_name}\"");
            }
        }
        return [0, ''];
    }

    private function find1($ip, $l, $r)
    {
        for ($m = $l; $m <= $r; $m += 8) {
            if ($this->index{$m} . $this->index{$m + 1} . $this->index{$m + 2} . $this->index{$m + 3} >= $ip) {
                return $m;
            }
        }
        return null;
    }

    private function find2($ip, $l, $r)
    {
        if ($l + 8 >= $r) {
            return $r;
        }
        $m = intval(($l + $r) / 16) * 8;
        $mip = $this->index{$m} . $this->index{$m + 1} . $this->index{$m + 2} . $this->index{$m + 3};
        if ($ip == $mip) {
            return $m;
        } else if ($ip < $mip) {
            return $this->find2($ip, $l, $m);
        } else {
            return $this->find2($ip, $m, $r);
        }
    }

    private function readOffset($offset, $len)
    {
        if (!isset($this->data[$offset])) {
            fseek($this->fp, $this->offset + $offset - 1024);
            $this->data[$offset] = fread($this->fp, $len);
        }
        return $this->data[$offset];
    }

}