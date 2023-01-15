支持 Laravel 的 ORM 与 ThinkPHP 的 ORM 。可以生成增、删、改，包括批量增、删、改，以及 使用 DB 操作的日志。

因为批量操作没有触发模型事件，使用模型事件无法覆盖所有模型对数据库的操作以及 DB 操作，所以通过获取器，自动生成可读性高的操作日志。

### 安装

> composer require wadywh/operation-log

### Laravel 使用

首先在数据库的配置文件 `config/database.php` 中增加两个配置项 `modelNamespace` 和 `logKey`，如果项目通过注入表模型映射关系来确定模型命名空间，可不配置 `modelNamespace` 

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    ...
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            ...
            ...
            // 模型所在的命名空间
            "modelNamespace" => "Operation\Log\Test\model",
            // 日志记录的主键
            "logKey" => "id",
        ],
        ...
    ]
    ...
];
```

然后注册 MySQL 数据库连接的解析器。

```php
\Illuminate\Database\Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
    return new \Operation\Log\orm\illuminate\MySqlConnection($connection, $database, $prefix, $config);
});
```

### ThinkPHP 使用

在数据库的配置文件 config/database.php 中增加三个配置项 `query`、`modelNamespace` 和 `logKey`。

```php
<?php

return [
    'default'         => env('database.driver', 'mysql'),
    ...
    'connections'     => [
        'mysql' => [
            // 服务器地址
            'hostname'        => env('database.hostname', '127.0.0.1'),
            // 数据库名
            'database'        => env('database.database', ''),
            // 用户名
            'username'        => env('database.username', 'root'),
            // 密码
            'password'        => env('database.password', ''),
            // 端口
            'hostport'        => env('database.hostport', '3306'),
            ...
            ...
            // 数据库类型
            'type'            => \Operation\Log\orm\think\MySqlConnection::class,
            // 指定查询对象
            "query"           => \Operation\Log\orm\think\Query::class,
            // Builder类
            "builder"         => \think\db\builder\Mysql::class,
            // 模型所在的命名空间
            "modelNamespace"  => "Operation\Log\Test\model",
            // 日志记录的主键
            "logKey"          => "id",
        ],
        // 更多的数据库配置信息
        ...
    ],
    ...
];
```

### 日志主键

可在模型中设置`$logKey`属性修改需要记录的主键名称。

```php
<?php

namespace Operation\Log\Test\model;

class User extends BaseModel
{
    // 日志记录的主键名称
    public string $logKey = 'id';
}
```

### 可读性设置

通过表注释、字段注释与获取器来生成可读性的日志。

**表注释与字段注释**

可使用表自身注释和字段自身注释（前提是允许查询information_schema库且有查询权限），也可以在模型中通过`$tableComment`与`$columnComment`设置表注释与字段注释（优先级最高）。

**获取器**

设置一个名为`字段名_text`的获取器。

```php
<?php

namespace Operation\Log\Test\model;

class User extends BaseModel
{
    // 日志记录的主键名称
    public string $logKey = 'id';
    // 表注释
    public $tableComment = '用户';
    // 字段注释
    public $columnComment = [
        'name' => '姓名',
        'sex' => '性别',
    ];
    // 日志记录忽略的字段
    public $ignoreLogFields = [
        'create_time',
        'update_time',
    ];

    // Laravel ORM 获取器设置方法
    public function getSexTextAttribute($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }

    // ThinkPHP ORM 获取器设置方法
    public function getSexTextAttr($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}
```

### 模型单独设置不记录日志

可在模型中设置`$notRecordLog = false`属性，该数据表的变更则不会生成操作日志。

```php
<?php

namespace Operation\Log\Test\model;

class User extends BaseModel
{
    // 不生成操作日志
    public bool $notRecordLog = false;
}
```

### 获取日志信息

```php
\Operation\Log\facades\OperationLog::getLog();
```

### 清除日志信息

```php
\Operation\Log\facades\OperationLog::clearLog();
```

### 注入表模型命名空间映射关系

```php
\Operation\Log\facades\OperationLog::setTableModelMapping($map);
```