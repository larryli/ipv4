<?php
/**
 * Query.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class Query
 * @package larryli\ipv4\Query
 */
abstract class Query
{
    /**
     * @return mixed
     */
    abstract public function name();

    /**
     * @return mixed
     */
    abstract public function exists();

    /**
     * @return mixed
     */
    abstract public function clean();

    /**
     * @param $func
     * @return mixed
     */
    abstract public function dump($func);

    /**
     * @param $ip
     * @return mixed
     */
    abstract public function query($ip);

    /**
     * @return mixed
     */
    abstract public function getTotal();

    /**
     * @var array
     */
    static protected $objects = [];

    /**
     * @param $name
     * @param null $options
     */
    static public function factory($name, $options = null)
    {
        if (!isset(self::$objects[$name])) {
            switch ($name) {
                case 'monipdb':
                    $obj = new MonIPDBQuery($options);
                    break;
                case 'qqwry':
                    $obj = new QQWryQuery($options);
                    break;
                case 'full':
                    $obj = new FullQuery($options);
                    break;
                case 'mini':
                    $obj = new MiniQuery($options);
                    break;
                case 'china':
                    $obj = new ChinaQuery($options);
                    break;
                case 'world':
                    $obj = new WorldQuery($options);
                    break;
                default:
                    throw new \Exception("Unknown Query name \"{$name}\"");
            }
            self::$objects[$name] = $obj;
        }
        return self::$objects[$name];
    }
}
