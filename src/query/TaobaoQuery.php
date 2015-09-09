<?php
/**
 * TaobaoQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\query;


/**
 * Class TaobaoQuery
 * @package larryli\ipv4\query
 * @see http://ip.taobao.com/instructions.php
 */
class TaobaoQuery extends ApiQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'ip.taobao.com';
    }

    /**
     * @param $ip
     * @return string
     */
    public function find($ip)
    {
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . long2ip($ip);
        $content = json_decode(@file_get_contents($url), true);
        if (empty($content)) {
            return '';
        }
        if (isset($content['code']) && empty($content['code'])) {
            return @$content['data']['country']
            . "\t" . @$content['data']['area']
            . "\t" . @$content['data']['region']
            . "\t" . @$content['data']['city']
            . "\t" . @$content['data']['county']
            . "\t" . @$content['data']['isp'];
        }
        return '';
    }
}