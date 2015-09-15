<?php
/**
 * index.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

require(dirname(__DIR__) . '/bootstrap.php');
$config = require(__DIR__ . '/../config/web.php');
(new yii\web\Application($config))->run();
