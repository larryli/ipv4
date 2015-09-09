<?php
/**
 * FreeIPIPQuery.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\Query;


/**
 * Class FreeIPIPQuery
 * @package larryli\ipv4\Query
 * @see https://www.ipip.net/api.html
 */
class FreeIPIPQuery extends ApiQuery
{
    /**
     * @return string
     */
    public function name()
    {
        return 'freeapi.ipip.net';
    }

    /**
     * @param $ip
     * @return string
     */
    public function division($ip)
    {
        $url = 'http://freeapi.ipip.net/' . long2ip($ip);
        $content = json_decode(@file_get_contents($url), true);
        if (empty($content)) {
            return '';
        }
        return implode("\t", $content);
    }
}