<?php
/**
 * console.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

Yii::setAlias('@ipv4', dirname(dirname(__DIR__)));
// Yii::setAlias('@ipv4', (dirname(__DIR__) . '/vendor/larryli/ipv4');
Yii::setAlias('larryli_ipv4_yii2_app', dirname(__DIR__));

$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'ipv4-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'larryli_ipv4_yii2_app\commands',
    'controllerMap' => [
        // ipv4 command
        'ipv4' => [
            'class' => 'larryli\ipv4\yii2\commands\Ipv4Controller',
        ],
        // you must copy the migrate files on @ipv4/Yii/Migrations to @app/migrations manually
        // or see [improve migrate command](https://github.com/yiisoft/yii2/issues/384)
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@ipv4/src/yii2/migrations',
        ],
    ],
    'vendorPath' => VENDOR_PATH,
    'components' => [
        // ipv4 component
        'ipv4' => [
            'class' => 'larryli\ipv4\yii2\IPv4',
            'runtime' => '@runtime',
            'database' => 'larryli\ipv4\yii2\Database',
            // query config
            'providers' => [
                'monipdb',    // empty
                'qqwry',
                'full' => ['monipdb', 'qqwry'], // ex. 'monipdb', 'qqwry', ['qqwry', 'monipdb']
                'mini' => 'full',   // ex. ['monipdb', 'qqwry'], 'monipdb', 'qqwry', ['qqwry', 'monipdb']
                'china' => 'full',
                'world' => 'full',
                'freeipip',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => [],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
