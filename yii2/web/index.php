<?php
/**
 * index.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
require(dirname(__DIR__) . '/bootstrap.php');
$config = require(__DIR__ . '/../config/web.php');
(new yii\web\Application($config))->run();
