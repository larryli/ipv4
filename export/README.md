# IPv4 导出

## 导出为 17monipdb.dat 格式

```php
$qqwry = new \larryli\ipv4\QqwryQuery('qqwry.dat');
if (!$qqwry->exists()) {
    $qqwry->init();
}

$export = new \larryli\ipv4\export\MonipdbQuery('17monipdb.dat');
$export->setEcdz(false);    // 17monipdb4ecdz.dat remove tab in the string.
$export->setEncoding('UTF-8');  // GBK/GB2312/GB18030/BIG5
$export->setProviders([$qqwry]);
$export->init();

$monipdb = new \larryli\ipv4\MonipdbQuery('17monipdb.dat');
var_dump($monipdb->find(ip2long('202.103.24.68')));

// or
include 'IP.class.php';
var_dump(IP::find('202.103.24.68'));
```

方法 ```setEcdz(true)``` 会删除地址字符串中的制表符分隔，默认为 ```false```。
方法 ```setEncoding``` 可以设置导出数据的编码，默认为 ```UTF-8```。

## 导出为 qqwry.dat 格式

```php
$monipdb = new \larryli\ipv4\MonipdbQuery('17monipdb.dat');
if (!$monipdb->exists()) {
    $monipdb->init();
}

$export = new \larryli\ipv4\export\QqwryQuery('qqwry.dat');
$export->setRemoveIpInRecode(false); // remove redundant ip data in the recode data (4 char per a recode)
$export->setEncoding('GBK');  // GBK/GB2312/GB18030/BIG5
$export->setProviders([$qqwry]);
$export->init();

$qqwry = new \larryli\ipv4\QqwryQuery('qqwry.dat');
var_dump($qqwry->find(ip2long('202.103.24.68')));
```

方法 ```setRemoveIpInRecode(true)``` 会删除纪录区冗余的 IP 数据（每条纪录 4 个字节），默认为 ```false```。

一般来说，网上流行的 qqwry.dat 处理代码都会直接跳过这 4 个字节的数据。如果要保持最大兼容，请不要去掉。但文件会增加许多。

方法 ```setEncoding``` 可以设置导出数据的编码，默认为 ```GBK```。
