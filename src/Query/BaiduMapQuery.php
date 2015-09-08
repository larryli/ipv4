<?php
/**
 * BaiduMapQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class BaiduMapQuery
 * @package larryli\ipv4\Query
 * @see http://api.map.baidu.com/lbsapi/cloud/ip-location-api.htm
 */
class BaiduMapQuery extends ApiQuery
{
    protected $key = 'F454f8a5efe5e577997931cc01de3974';

    function __construct($key = '')
    {
        if (!empty($key)) {
            $this->key = $key;
        }
    }

    /**
     * @return string
     */
    public function name()
    {
        return 'api.map.baidu.com';
    }

    /**
     * @param $ip
     * @return string
     */
    public function address($ip)
    {
        $url = 'http://api.map.baidu.com/location/ip?ak=' . $this->key . '&ip=' . long2ip($ip);
        $content = json_decode(file_get_contents($url), true);
        if (empty($content)) {
            return '';
        }
        if (isset($content['status']) && empty($content['status'])) {
            return @$content['content']['address_detail']['province']
            . "\t" . @$content['content']['address_detail']['city']
            . "\t" . @$content['content']['address_detail']['district']
            . "\t" . @$content['content']['address_detail']['street']
            . "\t" . @$content['content']['address_detail']['street_number'];
        }
        return '';
    }
}