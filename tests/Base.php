<?php

namespace Operation\Log\Test;

use Operation\Log\orm\illuminate\MySqlConnection;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;

class Base extends TestCase
{
    public function __construct(string $name = '')
    {
        parent::__construct($name);
        $this->connectionI();
        $this->createTable();
    }

    // 连接数据库 illuminate orm
    protected function connectionI()
    {
        $capsule = new Manager();
        $capsule->addConnection([
            "driver" => "mysql",
            "host" => "127.0.0.1",
            "database" => "log_test",
            "username" => "root",
            "password" => "root",
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "tb_",
            "modelNamespace" => "Operation\Log\Test\model\illuminate",
            "logKey" => "id",
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }

    // 创建表
    protected function createTable()
    {
        if (!Manager::select("show tables like 'tb_user';")) {
            Manager::select("
                create table tb_user
                (
                    id          int auto_increment primary key,
                    name        varchar(20)       null comment '姓名',
                    sex         tinyint default 0 null comment '性别',
                    price       varchar(512)      null comment '金额',
                    create_time datetime,
                    update_time datetime
                )
                    comment '用户';
            ");
        }
        if (!Manager::select("show tables like 'tb_user_type';")) {
            Manager::select("
                create table tb_user_type
                (
                    id          int auto_increment primary key,
                    user_id     int default 0 not null comment '用户id',
                    type_code   varchar(32) null comment '用户类型'
                )
                    comment '用户类型';
            ");
        }
    }
}