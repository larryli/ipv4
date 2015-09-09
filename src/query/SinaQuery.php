<?php
/**
 * SinaQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\query;


/**
 * Class SinaQuery
 * @package larryli\ipv4\query
 */
class SinaQuery extends ApiQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'int.dpool.sina.com.cn';
    }

    /**
     * @param $ip
     * @return string
     */
    public function find($ip)
    {
        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . long2ip($ip);
        $content = json_decode(@file_get_contents($url), true);
        if (empty($content)) {
            return '';
        }
        if (isset($content['ret']) && !empty($content['ret'])) {
            return @$content['country']
            . "\t" . @$content['province']
            . "\t" . @$content['city']
            . "\t" . @$content['district']
            . "\t" . @$content['isp']
            . "\t" . @$content['type']
            . "\t" . @$content['desc'];
        }
        return '';
    }
}