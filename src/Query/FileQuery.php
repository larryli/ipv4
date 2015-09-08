<?php
/**
 * FileQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class FileQuery
 * @package larryli\ipv4\Query
 */
abstract class FileQuery extends Query
{
    /**
     * @var string
     */
    protected $filename = '';
    /**
     * @var bool
     */
    protected $is_init = false;
    /**
     * @var array
     */
    static protected $divisions = [];

    /**
     * @param $address
     * @return mixed
     */
    abstract public function guess($address);

    /**
     * @param $func
     * @param $translate
     * @return mixed
     */
    abstract protected function traverse(callable $func, callable $translate);

    /**
     * @param $filename
     * @param bool|false $is_init
     * @throws \Exception
     */
    public function __construct($filename)
    {
        self::initGuess();
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
    }

    /**
     *
     */
    static protected function initGuess()
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
    }

    /**
     * @param string $filename
     * @return string
     * @throws \Exception
     */
    static protected function getRuntime($filename = '')
    {
        $runtime = realpath(dirname(dirname(__DIR__)) . '/runtime');
        if (empty($runtime)) {
            throw new \Exception('larryli\\ipv4 runtime must not empty!');
        }
        return $runtime . '/' . $filename;
    }

    /**
     * @return bool
     */
    public function init()
    {
        if ($this->is_init) {
            return true;
        }
        $this->is_init = true;
        return false;
    }

    /**
     * @return string
     */
    public function name()
    {
        return basename($this->filename);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filename);
    }

    /**
     *
     */
    public function clean()
    {
        if ($this->exists()) {
            unlink($this->filename);
        }
    }

    /**
     * @param $func
     * @throws \Exception
     */
    public function dump(callable $func)
    {
        $this->traverse($func, function ($str) {
            return $str;
        });
    }

    /**
     * @param $func
     * @throws \Exception
     */
    public function each(callable $func)
    {
        $this->traverse($func, function ($str) {
            list($id, $_) = $this->guess($str);
            return $id;
        });
    }

    /**
     * @param $ip
     * @return mixed
     * @throws \Exception
     */
    public function id($ip)
    {
        list($id, $_) = $this->guess($this->address($ip));
        return $id;
    }
}