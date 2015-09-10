# IPv4 Yii2 组件

## 配置 ipv4 组件和命令

### 别名

可以在 ```config``` 文件先定义一个别名：

```php
Yii::setAlias('@ipv4', (dirname(__DIR__) . '/vendor/larryli/ipv4/src');
```

### 组件

在 ```components``` 中增加：

```php
// ipv4 component
'ipv4' => [
    'class' => "larryli\\ipv4\\yii2\\IPv4",
    'runtime' => '@app/runtime',
    'database' => "larryli\\ipv4\\yii2\\Database",
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
```

其中：

* ```class``` 指向组件自身；
* ```runtime``` 如果为空，表示使用 ipv4 自己的 ```runtime```；
* ```database``` 指向特定的 ```Database``` 类，为空表示使用 ipv4 默认的 Medoo，建议使用 ```larryli\ipv4\yii2\Database``` 集成使用 yii2 的数据库配置；
* ```prefix``` 为数据库表前缀，默认为 ```ipv4_```，仅在 ```database``` 为 ```larryli\ipv4\yii2\Database``` 有效；
* ```providers``` 配置可用的 ```larryli\ipv4\query\Query``` 和其生成规则；

### 命令

在 ```config``` 数组中增加 ```controllerMap``` 配置内容：

```php
// ipv4 command
'ipv4' => [
    'class' => "larryli\\ipv4\\yii2\\commands\\Ipv4Controller",
],
```

使用：

```shell
./yii help ipv4
```

可以查看 ipv4 命令列表。

## 数据库迁移

复制数据库迁移脚本到当前 ```@app/migrations``` 下：

```shell
cp vendor/larryli/ipv4/src/yii2/migrations/*.php migrations/
```

或者参见[此页面的说明](https://github.com/yiisoft/yii2/issues/384)使用其他的方式处理。

然后，执行迁移：

```shell
./yii migrate/up
```

## 初始化

```shell
./yii ipv4/init
```

## 查询

```shell
./yii ipv4/query 127.0.0.1
```

## 杂项

```shell
./yii ipv4/benchmark        # 性能测试
./yii ipv4/clean            # 清除全部数据
./yii ipv4/clean file       # 清除下载的文件数据
./yii ipv4/clean database   # 清除生成的数据库数据
./yii ipv4/dump             # 导出原始数据
./yii ipv4/dump division    # 导出排序好的全部地址列表
./yii ipv4/dump division_id # 导出排序好的全部地址和猜测行政区域代码列表
```

注意：```dump``` 命令会耗费大量内存，请配置 PHP ```memory_limit``` 至少为 ```256M``` 或更多。

## 代码调用

### 使用组件

```php
use Yii;
Yii::$app->get('ipv4')->get('full')->find(ip2long('127.0.0.1'));
```

### 使用模型

仅支持生成的数据库 ```larryli\ipv4\query\DatabaseQuery```。

待补充。
