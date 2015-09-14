<?php
/**
 * web.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

Yii::setAlias('@ipv4', dirname(dirname(__DIR__)));
// Yii::setAlias('@ipv4', (dirname(__DIR__) . '/vendor/larryli/ipv4');
Yii::setAlias('larryli_ipv4_yii2_app', dirname(__DIR__));

$config = [
    'id' => 'ipv4',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'larryli_ipv4_yii2_app\controllers',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'ipv4',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'division/<type:\w+>/<id:\d+>' => 'division/indexes',
                'index/<type:\w+>' => 'index/index',
                'index/<type:\w+>/<id:\d+>' => 'index/view',
                '<controller:\w+>' => '<controller>/index',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
            ],
        ],
    ],
    'params' => [],
];
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
return $config;