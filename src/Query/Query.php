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
    abstract public function total();

    /**
     * @return mixed
     */
    abstract public function clean();

    /**
     * @param callback $func
     * @param Query|null $provider
     * @param Query|null $provider_extra
     * @return mixed
     */
    abstract public function generate(callable $func, Query $provider = null, Query $provider_extra = null);

    /**
     * @param callback $func
     * @return mixed
     */
    abstract public function dump(callable $func);

    /**
     * @param callback $func
     * @return mixed
     */
    abstract public function each(callable $func);

    /**
     * @param $ip
     * @return string
     */
    abstract public function address($ip);

    /**
     * @param $ip
     * @return integer
     */
    abstract public function id($ip);

    /**
     * @var array
     */
    static protected $objects = [];

    /**
     * @var array
     */
    static public $classes = [
        'monipdb' => MonIPDBQuery,
        'qqwry' => QQWryQuery,
        'full' => FullQuery,
        'mini' => MiniQuery,
        'china' => ChinaQuery,
        'world' => WorldQuery,
        'freeipip' => FreeIPIPQuery,
        'taobao' => TaobaoQuery,
        'sina' => SinaQuery,
        'baidumap' => BaiduMapQuery,
    ];

    /**
     * @param $name
     * @param null $options
     * @return Query
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
     * @param null $options
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
            $options = require($options);
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
}
