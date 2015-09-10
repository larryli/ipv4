<?php
/**
 * QueryAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

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
        foreach ($this->ipv4->providers as $name => $provider) {
            $this->query($name, $ip);
        }
    }

    /**
     * @param string $name
     * @param integer $ip
     * @throws \Exception
     */
    private function query($name, $ip)
    {
        $query = $this->ipv4->createQuery($name);
        $address = $query->find($ip);
        $this->stdout("\t{$name}: ", Console::FG_YELLOW);
        $this->stdout("{$address}\n");
    }

}