<?php
/**
 * ExportQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export;


use larryli\ipv4\Query;

/**
 * Class ExportQuery
 * @package larryli\ipv4\export
 */
abstract class ExportQuery extends Query
{
    /**
     * data filename
     *
     * @var string
     */
    protected $filename = '';
    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @param Query $query
     * @param callable $func
     * @return
     */
    abstract public function export(Query $query, callable $func);

    /**
     * @param string $filename
     * @throws \Exception
     */
    public function __construct($filename)
    {
        if (empty($filename)) {
            throw new \Exception("must have a export database file.");
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
     * @param $encoding
     * @return string
     */
    public function setEncoding($encoding)
    {
        $encoding = strtoupper($encoding);
        if (in_array($encoding, ['UTF-8', 'GB2312', 'GBK', 'GB18030', 'BIG5'])) {
            $this->encoding = $encoding;
        }
        return $this->encoding;
    }

    /**
     * @param callable|null $func
     * @throws \Exception
     */
    public function init(callable $func = null)
    {
        if (count($this->providers) <= 0) {
            throw new \Exception("Invalid provider: must need one!");
        }
        if (count($this->providers[0]) <= 0) {
            throw new \Exception("Invalid provider {$this->providers[0]}: is empty!");
        }
        if ($func == null) {
            $func = function () {
                // do nothing
            };
        }
        $this->export($this->providers[0], $func);
    }

    /**
     * @return bool
     */
    public function current()
    {
        return false;
    }

    /**
     *
     */
    public function next()
    {
        // do nothing
    }

    /**
     * @return null
     */
    public function key()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return false;   // always false
    }

    /**
     *
     */
    public function rewind()
    {
        // do nothing
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
        return false;   // always false
    }

    /**
     *
     */
    public function clean()
    {
        @unlink($this->filename);
    }

    /**
     * @param $ip
     * @return string
     */
    public function find($ip)
    {
        return '';
    }

    /**
     * @param $ip
     * @return int
     */
    public function findId($ip)
    {
        return 0;
    }

    /**
     * @param int $integer
     * @return string
     */
    public function divisionById($integer)
    {
        return '';
    }

    /**
     * @param string $string
     * @return int
     */
    public function idByDivision($string)
    {
        return 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return 0;
    }

    /**
     * @param Query $query
     * @return array
     */
    protected function version(Query $query)
    {
        return [
            'ipv4.larryli.cn',
            date('Ymd'),
            $query->className(),
            $query->name(),
        ];
    }
}
