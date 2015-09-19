<?php
/**
 * Strings.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export\monipdb;


/**
 * Class Strings
 * @package larryli\ipv4\export\monipdb
 */
class Strings
{
    /**
     *
     */
    const SIZE = 4;
    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var string
     */
    protected $buffer = '';
    /**
     * @var array
     */
    protected $exists = [];
    /**
     * @var bool
     */
    protected $ecdz;

    /**
     * @param bool|false $ecdz
     */
    public function __construct($ecdz = false)
    {
        $this->ecdz = $ecdz;
    }

    /**
     * @param $fp
     * @param $ip
     * @param $str
     */
    public function set($fp, $ip, $str)
    {
        if (!isset($this->exists[$str])) {
            if ($this->ecdz) {
                $pad = $str;
            } else {
                $pad = $this->pad($str);
            }
            $offset = $this->offset;
            $len = strlen($pad);
            $this->buffer .= $pad;
            $this->offset += $len;
            $this->exists[$str] = [
                'offset' => $offset,
                'len' => $len,
            ];
        }
        fwrite($fp, pack('N', $ip), 4);
        fwrite($fp, pack('V', $this->exists[$str]['offset']), 3);
        fwrite($fp, pack('C', $this->exists[$str]['len']), 1);
    }

    /**
     * @param $str
     * @return string
     */
    protected function pad($str)
    {
        $array = explode("\t", $str);
        $array = array_pad($array, self::SIZE, '');
        if (empty($array[1])) {
            $array[1] = $array[0];
        }
        $array = array_slice($array, 0, self::SIZE);
        return implode("\t", $array);
    }

    /**
     * @param $fp
     */
    public function write($fp)
    {
        fwrite($fp, $this->buffer, $this->offset);
    }
}
