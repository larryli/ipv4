<?php
/**
 * IPDB.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\IPv4\IPDB;


abstract class IPDB
{
    public $filename = '';
    protected $is_init = false;
    static protected $divisions = [];

    abstract public function download($func = file_get_contents);

    abstract public function dump($func);

    abstract public function getTotal();

    abstract public function query($ip);

    abstract public function guess($address);

    public function __construct($filename, $is_init = false)
    {
        if (empty(self::$divisions)) {
            self::$divisions = [
                '中国' => ['id' => 1],
                '本机地址' => ['id' => 2],
                '局域网' => ['id' => 3],
                '保留地址' => ['id' => 4],
                'IPIP.NET' => ['id' => 4],    // alias
                'IANA机构' => ['id' => 4],    // alias
                'CZ88.NET' => ['id' => 4],    // alias
                '纯真网络' => ['id' => 4],    // alias
                '本地链路' => ['id' => 5],
            ];
            self::$divisions = array_merge(self::$divisions, require('guess_world.php'));
            self::$divisions = array_merge(self::$divisions, require('guess_china.php'));
            self::$divisions = array_merge(self::$divisions, require('guess_college.php'));
        }

        if (empty($filename)) {
            throw new \Exception("must have a database file.");
        }
        if (file_exists($filename)) {
            if (!is_writable($filename)) {
                throw new \Exception("{$filename} is not writable.");
            }
        } else {
            $dir = dirname($filename);
            if (!is_dir($dir)) {
                throw new \Exception("{$dir} is not a directory.");
            }
            if (!is_writable($dir)) {
                throw new \Exception("{$dir} is not writable.");
            }
        }
        $this->filename = $filename;
        if ($is_init) {
            $this->init();
        }
    }

    public function init()
    {
        if ($this->is_init) {
            return true;
        }
        $this->is_init = true;
        return false;
    }

    public static function getClass()
    {
        return get_called_class();
    }

}