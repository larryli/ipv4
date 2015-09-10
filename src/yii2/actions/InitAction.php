<?php
/**
 * InitAction.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\actions;

use larryli\ipv4\query\DatabaseQuery;
use Yii;
use yii\helpers\Console;

/**
 * Class InitAction
 * @package larryli\ipv4\yii2\actions
 */
class InitAction extends Action
{
    /**
     * @var \larryli\ipv4\yii2\commands\Ipv4Controller
     */
    public $controller;

    /**
     * initialize ip database
     */
    public function run()
    {
        $force = $this->controller->force;
        $this->stdout("initialize ip database:\n", Console::FG_GREEN);
        foreach ($this->ipv4->providers as $name => $provider) {
            if (empty($provider)) {
                $this->download($name, $force);
            } else {
                $this->division();
                $this->generate($name, $force, $provider);
            }
        }
    }

    /**
     * @param string $name
     * @param bool $force
     * @return void
     * @throws \Exception
     */
    protected function download($name, $force)
    {
        $query = $this->ipv4->createQuery($name);
        $name = $query->name();
        if (!$force && $query->exists()) {
            $this->stdout("use exist {$name} file or api.\n", Console::FG_YELLOW);
        } else {
            $this->stdout("download {$name} file:\n", Console::FG_GREEN);
            $query->init(function ($url) {
                return file_get_contents($url, false, $this->createStreamContext());
            });
            $this->stdout(" completed!\n", Console::FG_GREEN);
        }
    }

    /**
     *
     * @return resource
     */
    protected function createStreamContext()
    {
        $ctx = stream_context_create([], [
            'notification' => function ($code, $severity, $message, $message_code, $bytesTransferred, $bytesMax) {
                switch ($code) {
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        Console::startProgress(0, $bytesMax);
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        Console::updateProgress($bytesTransferred, $bytesMax);
                        if ($bytesTransferred == $bytesMax) {
                            Console::updateProgress($bytesMax, $bytesMax);
                            Console::endProgress();
                        }
                        break;
                    case STREAM_NOTIFY_COMPLETED:
                        Console::updateProgress($bytesMax, $bytesMax);
                        Console::endProgress();
                        break;
                }
            }
        ]);
        return $ctx;
    }

    /**
     *
     */
    protected function division()
    {
        DatabaseQuery::initDivision(function ($code, $n) {
            static $total = 0;
            switch ($code) {
                case 0:
                    $this->stdout("generate divisions table:\n", Console::FG_GREEN);
                    Console::startProgress(0, $n);
                    $total = $n;
                    break;
                case 1:
                    Console::updateProgress($n, $total);
                    break;
                case 2:
                    Console::updateProgress($total, $total);
                    Console::endProgress();
                    $this->stdout(" completed!\n", Console::FG_GREEN);
                    break;
            }
        }, true);
    }

    /**
     * @param string $name
     * @param bool $force
     * @param string|array $provider
     * @return void
     * @throws \Exception
     */
    protected function generate($name, $force, $provider)
    {
        $query = $this->ipv4->createQuery($name);
        if (is_string($provider)) {
            $provider = $this->ipv4->createQuery($provider);
            $use = $provider->name();
            $provider_extra = null;
        } else if (is_array($provider)) {
            $provider_extra = $this->ipv4->createQuery($provider[1]);
            $provider = $this->ipv4->createQuery($provider[0]);
            $use = $provider->name() . ' and ' . $provider_extra->name();
        } else {
            throw new \Exception("Error generate options {$provider}");
        }
        $name = $query->name();
        if (!$force && count($query) > 0) {
            $this->stdout("use exist {$name} table.\n", Console::FG_YELLOW);
        } else {
            $this->stdout("generate {$name} table with {$use}:\n", Console::FG_GREEN);
            $query->init(function ($code, $n) {
                static $total = 0;
                switch ($code) {
                    case 0:
                        Console::startProgress(0, $n);
                        $total = $n;
                        break;
                    case 1:
                        Console::updateProgress($n, $total);
                        break;
                    case 2:
                        Console::updateProgress($total, $total);
                        Console::endProgress();
                        break;
                }
            }, $provider, $provider_extra);
            $this->stdout(" completed!\n", Console::FG_GREEN);
        }
    }
}