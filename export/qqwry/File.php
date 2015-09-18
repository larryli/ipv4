<?php
/**
 * File.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export\qqwry;


/**
 * Class File
 * @package larryli\ipv4\export\qqwry
 */
class File
{
    /**
     * @var bool
     */
    protected $remove_ip_in_recode = false;
    /**
     * @var resource
     */
    protected $fp;
    /**
     * @var string
     */
    protected $index = '';
    /**
     * @var int
     */
    protected $size = 0;
    /**
     * @var array
     */
    protected $divisions = [];
    /**
     * @var array
     */
    protected $countries = [];

    /**
     * @param $filename
     * @param $remove_ip_in_recode
     * @throws \Exception
     */
    public function __construct($filename, $remove_ip_in_recode)
    {
        $this->remove_ip_in_recode = $remove_ip_in_recode;
        $this->fp = @fopen($filename, 'wb');
        if ($this->fp === false) {
            throw new \Exception("Invalid {$filename} file!");
        }
        fwrite($this->fp, str_pad('', 8, "\x00"), 8); // empty offset
    }

    /**
     *
     */
    public function __destruct()
    {
        if (!empty($this->fp)) {
            $position = ftell($this->fp);
            $len = $this->size * 7;
            fwrite($this->fp, $this->index, $len);
            rewind($this->fp);
            fwrite($this->fp, pack('V', $position), 4);
            fwrite($this->fp, pack('V', $position + $len - 7), 4);
            fclose($this->fp);
        }
        $this->fp = null;
    }

    /**
     * @param $ip
     * @param $country
     * @param $division
     */
    public function add($ip, $country, $division)
    {
        $ip = pack('V', $ip);
        $position = ftell($this->fp);
        if ($this->remove_ip_in_recode) {
            $position -= 4;
        }
        $position = substr(pack('V', $position), 0, 3);
        $this->index .= $ip . $position;
        $this->size++;
        if (!$this->remove_ip_in_recode) {
            fwrite($this->fp, $ip, 4);
        }
        if (isset($this->countries[$country]) && isset($this->countries[$country][$division])) {
            fwrite($this->fp, $this->countries[$country][$division], 4); // 0x01 . $offset
        } else if (isset($this->divisions[$country])) {
            $position = substr(pack('V', ftell($this->fp)), 0, 3);
            $this->countries[$country][$division] = "\x01" . $position;
            fwrite($this->fp, $this->divisions[$country], 4); // 0x02 . $offset
            if (isset($this->divisions[$division])) {
                fwrite($this->fp, $this->divisions[$division], 4); // 0x02 . $offset
            } else {
                $this->writeDivision($division);
            }
        } else if (isset($this->divisions[$division])) {
            $position = substr(pack('V', ftell($this->fp)), 0, 3);
            $this->countries[$country][$division] = "\x01" . $position;
            $this->writeDivision($country);
            fwrite($this->fp, $this->divisions[$division], 4); // 0x02 . $offset
        } else {
            $this->writeCountryDivision($country, $division);
        }
    }

    /**
     * @param $division
     * @return int|string
     */
    protected function writeDivision($division)
    {
        if (empty($division)) {
            fwrite($this->fp, "\x00", 1);
            return 0;
        } else {
            $position = substr(pack('V', ftell($this->fp)), 0, 3);
            fwrite($this->fp, $division . "\x00", strlen($division) + 1);
            $this->divisions[$division] = "\x02" . $position;
            return $position;
        }
    }

    /**
     * @param $country
     * @param $division
     */
    protected function writeCountryDivision($country, $division)
    {
        $position = $this->writeDivision($country);
        $this->writeDivision($division);
        if (!empty($position)) {
            $this->countries[$country][$division] = "\x01" . $position;
        }
    }
}
