<?php
/**
 * bootstrap.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

/**
 * @param $path
 * @return bool|mixed
 */
function requireVendor($path)
{
    $path = realpath($path);
    $file1 = $path . '/autoload.php';
    $file2 = $path . '/yiisoft/yii2/Yii.php';
    if (file_exists($file1) && file_exists($file2)) {
        require($file1 . ''); // fix inspect
        require($file2 . ''); // fix inspect
        defined('VENDOR_PATH') or define('VENDOR_PATH', $path);
        return true;
    }
    return false;
}

if (getenv('YII_DEBUG')) {
    define('YII_DEBUG', getenv('YII_DEBUG'));
} else {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
}
if (getenv('YII_ENV')) {
    define('YII_ENV', getenv('YII_ENV'));
} else {
    defined('YII_ENV') or define('YII_ENV', 'dev');
}

if ((!$loader = requireVendor(__DIR__ . '/../vendor')) && (!$loader = requireVendor(__DIR__ . '/../../..'))) {
    if (PHP_SAPI !== 'cli') {
        echo '<pre>';
    }
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -sS https://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    exit(1);
}
return $loader;
