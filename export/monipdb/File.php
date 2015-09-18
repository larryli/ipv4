<?php
/**
 * File.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export\monipdb;


/**
 * Class File
 * @package larryli\ipv4\export\monipdb
 */
class File
{
    /**
     * @var resource
     */
    protected $fp;
    /**
     * @var bool
     */
    protected $last_invalid;
    /**
     * @var int
     */
    protected $last_ip;
    /**
     * @var
     */
    protected $version;
    /**
     * @var Indexes
     */
    protected $idx;
    /**
     * @var Strings
     */
    protected $str;
    /**
     * @var int
     */
    protected $n;

    /**
     * @param $filename
     * @param $version
     * @throws \Exception
     */
    public function __construct($filename, $version)
    {
        $this->fp = @fopen($filename, 'wb');
        if ($this->fp === false) {
            throw new \Exception("Invalid {$filename} file!");
        }
        $this->last_invalid = true;
        $this->last_ip = ip2long('255.255.255.255');
        $this->version = $version;
        $this->idx = new Indexes();
        $this->str = new Strings();
        $this->n = 0;
        $size = 4 + 1024;
        fwrite($this->fp, str_pad('', $size, "\x00"), $size); // write empty offset & index
    }

    /**
     *
     */
    public function __destruct()
    {
        if (!empty($this->fp)) {
            if ($this->last_invalid || $this->idx->invalid()) {
                $this->idx->set($this->last_ip, $this->n);
                $this->str->set($this->fp, $this->last_ip, $this->version);
            }
            $offset = ftell($this->fp) + 1024;
            $this->str->write($this->fp);
            rewind($this->fp);
            fwrite($this->fp, pack('N', $offset), 4);
            $this->idx->write($this->fp);
            fclose($this->fp);
        }
        $this->fp = null;
    }

    /**
     * @param $ip
     * @param $division
     */
    public function add($ip, $division)
    {
        if ($ip == $this->last_ip) {
            $this->last_invalid = false;
            $division = $this->version;
        }
        $this->idx->set($ip, $this->n);
        $this->str->set($this->fp, $ip, $division);
        $this->n++;
    }
}