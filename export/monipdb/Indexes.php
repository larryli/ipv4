<?php
/**
 * Indexes.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\export\monipdb;


/**
 * Class Indexes
 * @package larryli\ipv4\export\monipdb
 */
class Indexes
{
    /**
     * @var string
     */
    protected $buffer = '';
    /**
     * @var int
     */
    protected $ip = 0;

    /**
     * @param $ip
     * @param $n
     */
    public function set($ip, $n)
    {
        for (; $ip >= $this->ip; $this->ip += 256 * 256 * 256) {
            $this->buffer .= pack('V', $n);
        }
    }

    /**
     * @return bool
     */
    public function invalid()
    {
        return $this->ip < ip2long('255.255.255.255');
    }

    /**
     * @param $fp
     */
    public function write($fp)
    {
        fwrite($fp, $this->buffer, 1024);
    }
}
