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
}
