<?php
/**
 * InitAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use larryli\ipv4\query\Query;
use Yii;
use yii\helpers\Console;

/**
 * Class QueryAction
 * @package larryli\ipv4\yii2\actions
 */
class QueryAction extends Action
{
    /**
     * query ip
     *
     * @param string $ip ip v4 address
     *
     * @throws \Exception
     */
    public function run($ip)
    {
        $this->stdout('query ', Console::FG_GREEN);
        $this->stdout("{$ip}\n", Console::FG_YELLOW);
        $ip = ip2long($ip);
        foreach ($this->ipv4->getQueries() as $query) {
            $this->query($query, $ip);
        }
    }

    /**
     * @param Query $query
     * @param integer $ip
     * @throws \Exception
     */
    private function query(Query $query, $ip)
    {
        $address = $query->find($ip);
        $name = $query->name();
        $this->stdout("\t{$name}: ", Console::FG_YELLOW);
        $this->stdout("{$address}\n");
    }

}