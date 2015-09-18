<?php
/**
 * MonipdbQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4;


/**
 * Class MonipdbQuery
 * @package larryli\ipv4\query
 */
class MonipdbQuery extends FileQuery
{
    /**
     *
     */
    const URL = 'http://s.qdcdn.com/17mon/17monipdb.zip';
    /**
     * @var
     */
    protected $position;
    /**
     * @var null
     */
    private $fp = null;
    /**
     * @var int
     */
    private $offset = 0;
    /**
     * @var int
     */
    private $end = 0;
    /**
     * @var array
     */
    private $index = [];
    /**
     * @var array
     */
    private $data = [];
    /**
     * @var string[]
     */
    private $cached = [];

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
     * @return int
     * @throws \Exception
     */
    public function count()
    {
        $this->initFile();
        return intval(($this->end - 1024) / 8);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function initFile()
    {
        if (parent::initFile()) {
            return true;
        }
        $this->fp = @fopen($this->filename, 'rb');
        if ($this->fp === FALSE) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        $offset = unpack('Nlen', fread($this->fp, 4));
        $this->offset = $offset['len'] - 1024;
        if ($this->offset < 4) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        $this->end = $this->offset - 4;
        $this->index = fread($this->fp, $this->end);
        $this->rewind();
        return true;
    }

    /**
     * @param $ip
     * @return string
     * @throws \Exception
     */
    public function find($ip)
    {
        $ip_start = intval(floor($ip / (256 * 256 * 256)));

        if ($ip_start < 0 || $ip_start > 255) {
            throw new \Exception("{$ip} is not valid.");
        }
        if (isset($this->cached[$ip]) === TRUE) {
            return $this->cached[$ip];
        }

        $this->initFile();
        $nip = pack('N', $ip);
        $tmp_offset = $ip_start * 4;
        $start = unpack('Vlen', $this->index{$tmp_offset} . $this->index{$tmp_offset + 1} . $this->index{$tmp_offset + 2} . $this->index{$tmp_offset + 3});
        $start = $start['len'] * 8 + 1024;
        if ($ip_start == 255) {
            $end = $this->end - 8;
        } else {
            $end = unpack('Vlen', $this->index{$tmp_offset + 4} . $this->index{$tmp_offset + 5} . $this->index{$tmp_offset + 6} . $this->index{$tmp_offset + 7});
            $end = $end['len'] * 8 + 1024 - 8;
        }
        $start = $this->findIndex($nip, $start, $end);
        if ($start === null) {
            $this->cached[$ip] = '';
        } else {
            $offset = unpack('Vlen', $this->index{$start + 4} . $this->index{$start + 5} . $this->index{$start + 6} . "\x0");
            $length = unpack('Clen', $this->index{$start + 7});
            $this->cached[$ip] = $this->readOffset($offset['len'], $length['len']);
        }
        return $this->cached[$ip];
    }

    /**
     * @param callable $func
     * @throws \Exception
     */
    public function init(callable $func = null)
    {
        if (empty($func)) {
            $file = file_get_contents(self::URL);
        } else {
            $file = $func(self::URL);
        }
        $zip_file = dirname($this->filename) . '/17monipdb.zip';
        file_put_contents($zip_file, $file);
        $zip = new \ZipArchive;
        $res = $zip->open($zip_file);
        if ($res === TRUE) {
            $data_filename = dirname($this->filename) . '/17monipdb.dat';
            $zip->extractTo(dirname($this->filename), basename($data_filename));
            $zip->close();
            unlink($zip_file);
            if (strcmp($data_filename, $this->filename)) {
                rename($data_filename, $this->filename);
            }
        } else {
            throw new \Exception("Unzip {$zip_file} error!");
        }
    }

    /**
     * @param $ip
     * @param $l
     * @param $r
     * @return int|null
     */
    private function findIndex($ip, $l, $r)
    {
        for ($m = $l; $m <= $r; $m += 8) {
            if ($this->index{$m} . $this->index{$m + 1} . $this->index{$m + 2} . $this->index{$m + 3} >= $ip) {
                return $m;
            }
        }
        return null;
    }

    /**
     * @param $offset
     * @param $len
     * @return mixed
     */
    private function readOffset($offset, $len)
    {
        if (!isset($this->data[$offset])) {
            fseek($this->fp, $this->offset + $offset);
            $this->data[$offset] = fread($this->fp, $len);
        }
        return $this->data[$offset];
    }

    /**
     * @param $address
     * @return array
     * @throws \Exception
     */
    public function idByDivision($address)
    {
        list($country, $province, $city, $_) = explode("\t", $address);

        foreach (self::$divisions as $country_name => $country_data) {
            if (strncmp($country, $country_name, strlen($country_name)) == 0) {
                if (empty($province) || empty($country_data['divisions'])) {
                    return $country_data['id'];
                }
                $provinces = $country_data['divisions'];
                foreach ($provinces as $province_name => $province_data) {
                    if (strncmp($province, $province_name, strlen($province_name)) == 0) {
                        if (empty($city) || empty($province_data['divisions'])) {
                            return $province_data['id'];
                        }
                        $cities = $province_data['divisions'];
                        foreach ($cities as $city_name => $city_data) {
                            if (strncmp($city, $city_name, strlen($city_name)) == 0) {
                                return $city_data['id'];
                            }
                        }
                        throw new \Exception("\"{$address}\" cannot found \"{$city}\" at \"{$country_name}\" \"{$province_name}\"");
                    }
                }
                throw new \Exception("\"{$address}\" cannot found \"{$province}\" at \"{$country_name}\"");
            }
        }
        return 0;
    }

    /**
     * @return string
     */
    public function current()
    {
        $offset = unpack('Vlen', $this->index{$this->position + 4} . $this->index{$this->position + 5} . $this->index{$this->position + 6} . "\x0");
        $length = unpack('Clen', $this->index{$this->position + 7});
        return $this->readOffset($offset['len'], $length['len']);
    }

    /**
     *
     */
    public function next()
    {
        $this->position += 8;
    }

    /**
     * @return integer
     */
    public function key()
    {
        $ip = unpack('Nlen', $this->index{$this->position} . $this->index{$this->position + 1} . $this->index{$this->position + 2} . $this->index{$this->position + 3});
        return intval($ip['len']);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function valid()
    {
        $this->initFile();
        return $this->position < $this->end;
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 1024;
    }

    /**
     * @throws \Exception
     */
    public function yaml()
    {
        $fp = @fopen($this->filename, 'rb');
        if ($fp === FALSE) {
            throw new \Exception("Invalid {$this->filename} file!");
        }
        echo "---\n";
        $offset = unpack('Nlen', fread($fp, 4));
        echo "offset: {$offset['len']}\n";
        $size = $offset['len'] - 2048 - 4;
        $total = $size / 8;
        echo "total: {$total}\n";
        $index = fread($fp, 1024);
        echo "index:\n";
        for ($n = 0; $n < 256; $n++) {
            $i = $n * 4;
            $ip = long2ip($n * 256 * 256 * 256);
            $offset = unpack('Vlen', $index{$i} . $index{$i + 1} . $index{$i + 2} . $index{$i + 3});
            echo "\t- ip: {$ip}\n\t  no: {$offset['len']}\n";
        }
        $string = [];
        $data = fread($fp, $size);
        echo "data:\n";
        for ($n = 0; $n < $total; $n++) {
            $i = $n * 8;
            $ip = unpack('Nlen', $data{$i} . $data{$i + 1} . $data{$i + 2} . $data{$i + 3});
            $ip = long2ip($ip['len']);
            $offset = unpack('Vlen', $data{$i + 4} . $data{$i + 5} . $data{$i + 6} . "\x0");
            $len = unpack('Clen', $data{$i + 7});
            echo "\t- no: {$n}\n\t  ip: {$ip}\n\t  offset: {$offset['len']}\n\t  len: {$len['len']}\n";
            $string[$offset['len']] = $len['len'];
        }
        echo "string:\n";
        while (true) {
            $position = ftell($fp);
            $offset = $position - $size - 1024 - 4;
            if (array_key_exists($offset, $string)) {
                $len = $string[$offset];
                $str = fread($fp, $len);
                echo "\t- position: {$position}\n\t  offset: {$offset}\n\t  len: {$len}\n\t  string: \"{$str}\"\n";
            } else {
                break;
            }
        }
        fclose($fp);
    }
}