<?php

/**
 * QueryController.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli\ipv4\yii2\commands;

use Yii;
use yii\console\Controller;

/**
 * ipv4 command
 *
 * @package larryli\ipv4\yii2\commands
 */
class Ipv4Controller extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'query';
    /**
     * @var bool Force to initialize(download qqwry.dat & 17monipdb.dat if not exist & generate new database)
     */
    public $force = 0;
    /**
     * @var int number of times
     */
    public $times = 100000;

    public function actions()
    {
        return [
            'query' => 'larryli\ipv4\yii2\actions\QueryAction',
            'init' => 'larryli\ipv4\yii2\actions\InitAction',
            'dump' => 'larryli\ipv4\yii2\actions\DumpAction',
            'clean' => 'larryli\ipv4\yii2\actions\CleanAction',
            'benchmark' => 'larryli\ipv4\yii2\actions\BenchmarkAction',
        ];
    }

    public function options($actionID)
    {
        $options = [];
        switch ($actionID) {
            case 'init':
                $options = [
                    'force',
                ];
                break;
            case 'benchmark':
                $options = [
                    'times',
                ];
                break;
        }
        return array_merge($options, parent::options($actionID));
    }
}
