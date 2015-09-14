<?php
/**
 * BenchmarkAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use Yii;
use yii\helpers\Console;

/**
 * Class BenchmarkAction
 * @package larryli\ipv4\yii2\actions
 */
class BenchmarkAction extends Action
{
    /**
     * @var \larryli\ipv4\yii2\commands\Ipv4Controller
     */
    public $controller;

    /**
     * benchmark
     *
     * @param string $type file or database
     *
     * @throws \Exception
     */
    public function run($type = 'all')
    {
        $times = $this->controller->times;
        if ($times < 1) {
            $this->stderr("benchmark {$times} is too small\n", Console::FG_GREY, Console::BG_RED);
            return;
        }
        $this->stdout("benchmark {$type}:", Console::FG_GREEN);
        $this->stdout("\t{$times} times\n", Console::FG_YELLOW);
        switch ($type) {
            case 'all':
                foreach ($this->ipv4->providers as $name => $provider) {
                    $this->benchmark($name, $times);
                }
                break;
            case 'file':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (empty($provider)) {
                        $this->benchmark($name, $times);
                    }
                }
                break;
            case 'database':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (!empty($provider)) {
                        $this->benchmark($name, $times);
                    }
                }
                break;
            default:
                $this->stderr("Unknown type \"{$type}\".\n", Console::FG_GREY, Console::BG_RED);
                break;
        }
    }

    /**
     * @param string $name
     * @param integer $times
     * @throws \Exception
     */
    private function benchmark($name, $times)
    {
        $query = $this->ipv4->createQuery($name);
        if (count($query) > 0) {
            $this->stdout("\t benchmark {$name}: \t", Console::FG_GREEN);
            $start = microtime(true);
            for ($i = 0; $i < $times; $i++) {
                $ip = mt_rand(0, 4294967295);
                $query->find($ip);
            }
            $time = microtime(true) - $start;
            $this->stdout("{$time} secs\n", Console::FG_YELLOW);
        }
    }
}
