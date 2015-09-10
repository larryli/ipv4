<?php
/**
 * CleanAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use larryli\ipv4\query\DatabaseQuery;
use Yii;
use yii\helpers\Console;

/**
 * Class CleanAction
 * @package larryli\ipv4\yii2\actions
 */
class CleanAction extends Action
{
    /**
     * benchmark
     *
     * @param string $type file or database
     *
     * @throws \Exception
     */
    public function run($type = 'all')
    {
        $cleanDivision = false;
        $this->stdout("clean {$type}:\n", Console::FG_GREEN);
        switch ($type) {
            case 'all':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                    }
                    $this->clean($name);
                }
                break;
            case 'file':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (empty($provider)) {
                        $this->clean($name);
                    }
                }
                break;
            case 'database':
                foreach ($this->ipv4->providers as $name => $provider) {
                    if (!empty($provider)) {
                        $cleanDivision = true;
                        $this->clean($name);
                    }
                }
                break;
            default:
                $this->stderr("Unknown type \"{$type}\".", Console::FG_GREY, Console::BG_RED);
                break;
        }
        if ($cleanDivision) {
            $this->cleanDivision();
        }
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    private function clean($name)
    {
        $this->stdout("clean {$name}:", Console::FG_GREEN);
        $query = $this->ipv4->createQuery($name);
        $query->clean();
        $this->stdout(" completed!\n", Console::FG_GREEN);
    }

    /**
     *
     */
    private function cleanDivision()
    {
        $this->stdout("clean divisions:", Console::FG_GREEN);
        DatabaseQuery::cleanDivision();
        $this->stdout(" completed!\n", Console::FG_GREEN);
    }
}
