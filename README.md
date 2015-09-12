# IP v4 中国城市地址库

整理 [IPIP.net](https://www.ipip.net) 和 [QQ IP 数据库纯真版](http://www.cz88.net/down/76250/) 为符合[中华人民共和国行政区划代码](http://www.stats.gov.cn/tjsj/tjbz/xzqhdm/)的国家与地区、省或直辖市、地级市或省管县级市地址。

## 目标

计划在 IPIP.net 库的基础上去掉 IDC/ISP 数据补上纯真 IP 库的数据，然后生成下列四个库：

* ```mini```：**迷你库**用于快速识别中华人民共和国境内 IP（不含台澎金马、香港、澳门）；
* ```china```: **国内城市库**用于定位中华人民共和国第一级和第二级行政区划（含部分省管第三级行政区划），即俗称的城市定位（含台湾、香港、澳门，作为第一级行政区划）；
* ```world```: **国家库**用于定位国家与地区（含台湾、香港、澳门地区）；
* ```full```: **完整库**是国内城市库与国家地区库的合集（台湾、香港、澳门作为中华人民共和国第一级行政区划）；

## 通过 composer 安装

```shell
composer require larryli/ipv4
```

## 可选 IP 库

```shell
cp config/query.sample.php config/query.php
```

配置选项请参见该文件的代码注释。

默认会下载 **IPIP.net**（```monipdb```）和 **QQ IP 数据库纯真版**（```qqwry```）。

然后使用 **IPIP.net**（```monipdb```）配合 **QQ IP 数据库纯真版**（```qqwry```）生成**完整库**（```full```）。

再使用**完整库**（```full```）直接生成**迷你库**（```mini```）、**国内城市库**（```china```）和**国家库**（```world```）。

## 可选配置 MySQL 数据库

```shell
cp config/db.sample.php config/db.php
```

配置选项请参见该文件的代码注释或 [Medoo 文档](http://medoo.in/api/new)，当前仅支持 SQLite 和 MySQL（MariaDB）。

默认会使用 ```runtime``` 目录下的 ```ipv4.sqlite``` 数据库。

## 可选查询库配置

```shell
cp config/providers.sample.php config/providers.php
```

配置选项请参见该文件的代码注释。

## 初始化

```shell
bin/ipv4 init
```

可以使用 ```--force``` 更新覆盖现有数据。

## 查询

```shell
bin/ipv4 query 127.0.0.1
```

## 杂项

```shell
bin/ipv4 benchmark        # 性能测试
bin/ipv4 clean            # 清除全部数据
bin/ipv4 clean file       # 清除下载的文件数据
bin/ipv4 clean database   # 清除生成的数据库数据
bin/ipv4 dump             # 导出原始数据
bin/ipv4 dump division    # 导出排序好的全部地址列表
bin/ipv4 dump division_id # 导出排序好的全部地址和猜测行政区域代码列表
bin/ipv4 dump count       # 导出纪录统计数据
```

注意：```dump``` 命令会耗费大量内存，请配置 PHP ```memory_limit``` 至少为 ```128M``` 或更多。

## Yii2 组件

请参见 [yii2 代码目录文档](src/yii2/README.md)