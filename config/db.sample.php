<?php
/**
 * db.sample.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

return [
    // required
    'database_type' => 'mysql',
    'database_name' => 'ipv4',
    'server' => 'localhost',
    'username' => 'homestead',
    'password' => 'secret',
    'charset' => 'utf8',

    // optional
    'port' => 3306,
    'prefix' => 'ipv4_',
    // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
];
