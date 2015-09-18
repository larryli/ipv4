# IPv4 导出

## 导出为 17monipdb.dat 格式

```php
$qqwry = new \larryli\ipv4\QqwryQuery('qqwry.dat');
if (!$qqwry->exists()) {
    $qqwry->init();
}

$export = new \larryli\ipv4\export\MonipdbQuery('17monipdb.dat');
$export->setProviders([$qqwry]);
$export->init();

$monipdb = new \larryli\ipv4\MonipdbQuery('17monipdb.dat');
var_dump($monipdb->find(ip2long('202.103.24.68')));

// or
include 'IP.class.php';
var_dump(IP::find('202.103.24.68'));
```
