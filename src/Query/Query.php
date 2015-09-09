<?php
/**
 * Query.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class Query
 *
 * @package larryli\ipv4\Query
 */
abstract class Query implements \Countable, \Iterator
{
    /**
     * object names
     *
     * @var string[]
     */
    static public $classes = [
        'monipdb' => 'MonIPDBQuery',
        'qqwry' => 'QQWryQuery',
        'full' => 'FullQuery',
        'mini' => 'MiniQuery',
        'china' => 'ChinaQuery',
        'world' => 'WorldQuery',
        'freeipip' => 'FreeIPIPQuery',
        'taobao' => 'TaobaoQuery',
        'sina' => 'SinaQuery',
        'baidumap' => 'BaiduMapQuery',
    ];
    /**
     * factory objects
     *
     * @var Query[]
     */
    static protected $objects = [];

    /**
     * create query object with name
     *
     * if object exists, return directly
     *
     * @param $name
     * @param mixed $options object config
     * @return Query|FileQuery|DataBaseQuery|ApiQuery
     * @throws \Exception
     */
    static public function create($name, $options = null)
    {
        if (!isset(self::$objects[$name])) {
            if (isset(self::$classes[$name])) {
                $class = __NAMESPACE__ . '\\' . self::$classes[$name];
                $obj = new $class($options);
            } else {
                throw new \Exception("Unknown Query name \"{$name}\"");
            }
            self::$objects[$name] = $obj;
        }
        return self::$objects[$name];
    }

    /**
     * return objects config
     *
     * @param null|array $options config filename or array
     * @return array
     * @throws \Exception
     */
    static public function config($options = null)
    {
        if (empty($options)) {
            $config = dirname(dirname(__DIR__)) . '/config/query.php';
            if (file_exists($config)) {
                $options = $config;
            } else {
                $options = [
                    'monipdb',
                    'qqwry',
                    'full' => ['monipdb', 'qqwry'],
                    'mini' => 'full',
                    'china' => 'full',
                    'world' => 'full',
                ];
            }
        }
        if (is_string($options)) {
            $options = require($options . '');  // fix inspect
        }
        if (is_array($options)) {
            $result = [];
            foreach ($options as $query => $provider) {
                if (is_integer($query)) {
                    $query = $provider;
                    $provider = '';
                }
                $result[$query] = $provider;
            }
            return $result;
        } else {
            throw new \Exception("Error query factory {$options}");
        }
    }

    /**
     * return 0.1s
     *
     * @return int
     */
    static public function time()
    {
        return intval(microtime(true) * 10);
    }

    /**
     * name of the query
     *
     * @return string name string
     */
    abstract public function name();

    /**
     * check data is exists
     *
     * @return bool false meed the query need generate()
     */
    abstract public function exists();

    /**
     * initialize data with provider
     *
     * @param callback $func notify function
     * @param Query|null $provider main query provider
     * @param Query|null $provider_extra extra query provider
     * @return void
     */
    abstract public function init(callable $func, Query $provider = null, Query $provider_extra = null);

    /**
     * clean data
     *
     * @return void
     */
    abstract public function clean();

    /**
     * query ip address
     *
     * @param $ip
     * @return string
     */
    abstract public function division($ip);

    /**
     * query ip division id
     *
     * @param $ip
     * @return integer
     */
    abstract public function division_id($ip);

    /**
     * translate division_id to division
     *
     * @param integer $integer division_id
     * @return string division
     */
    abstract public function string($integer);

    /**
     * translate division to division_id
     *
     * @param string $string division
     * @return integer division_id
     */
    abstract public function integer($string);
}
