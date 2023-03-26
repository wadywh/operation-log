<?php

namespace Operation\Log\Test;

use Illuminate\Database\Capsule\Manager;
use Operation\Log\facades\OperationLog;
use Operation\Log\Test\model\illuminate\User;
use Operation\Log\Test\model\illuminate\UserType;
use Exception;

class LogTest extends Base
{
    // ORM单数据新增
    // 可查询information_schema库
    // 配置表注释和每个字段注释
    public function testCreate1()
    {
        $user = new User();
        $user->name = "zhangsan";
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $log = OperationLog::getLog();
        $this->assertEquals("创建 用户 (id:$id)：姓名：zhangsan，性别：男", $log);
        echo $log;
    }

    // ORM单数据新增
    // 可查询information_schema库
    // 配置表注释和部分字段注释
    public function testCreate2()
    {
        // 配置了columnComment就不会再去查information_schema库
        $user = new User();
        $user->name = "zhangsan";
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $log = OperationLog::getLog();
        $this->assertEquals("创建 用户 (id:$id)：姓名：zhangsan，sex：男", $log);
        echo $log;
    }

    // ORM单数据新增
    // 可查询information_schema库
    // 不配置表注释和字段注释
    public function testCreate3()
    {
        $user = new User();
        $user->name = "zhangsan";
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $log = OperationLog::getLog();
        $this->assertEquals("创建 用户 (id:$id)：姓名：zhangsan，性别：男", $log);
        echo $log;
    }

    // ORM单数据新增
    // 不可查询information_schema库
    // 不配置表注释和字段注释
    public function testCreate4()
    {
        OperationLog::setExecInfoSchema(false);
        $user = new User();
        $user->name = "zhangsan";
        $user->sex = 0;
        $user->save();
        $id = $user->id;
        $log = OperationLog::getLog();
        $this->assertEquals("创建 tb_user (id:$id)：name：zhangsan，sex：女", $log);
        echo $log;
    }

    // ORM关联模型批量数据创建
    public function testCreate5()
    {
        // createMany底层逻辑会将每个新记录单独创建，而不是一次性插入多个记录
        $user = User::query()->first();
        $user->types()->createMany([
            ['user_id' => $user->id, 'type_code' => 'TEST' . rand(1, 10)],
            ['user_id' => $user->id, 'type_code' => 'TEST' . rand(1, 10)],
        ]);
        $log = OperationLog::getLog();
        $this->assertTrue(true);
        echo $log;
    }

    // truncate清空数据表
    public function testTruncate()
    {
        User::query()->truncate();
        UserType::query()->truncate();
        $log = OperationLog::getLog();
        $this->assertTrue(true);
        echo $log;
    }

    // ORM模型自动加解密字段新增、修改、删除
    // 自定义记录类型
    public function testFieldEncryption1()
    {
        // OperationLog::setRecordTypes(['updated', 'batch_updated', 'deleted', 'batch_deleted']);
        // 单数据
        $user = new User();
        $user->name = "zhangsan";
        $user->sex = 2;
        $user->price = 100;
        $user->save();
        $user->name = 'lisi';
        $user->sex = 0;
        $user->price = '';
        $user->save();
        $user->delete();

        // 批量数据
        $time = date('Y-m-d H:i:s');
        User::query()->insert([
            ['name' => 'zhangsan', 'sex' => 0, 'price' => 100, 'create_time' => $time, 'update_time' => $time],
            ['name' => 'lisi', 'sex' => 1, 'price' => 101, 'create_time' => $time, 'update_time' => $time],
            ['name' => 'wangwu', 'sex' => 2, 'price' => 102, 'create_time' => $time, 'update_time' => $time],
        ]);
        $ids = User::query()->orderByDesc('id')->limit(3)->pluck('id');
        User::query()->whereIn('id', $ids)->update([
            'name' => 'test',
            'sex' => 1,
            'price' => 103,
            'update_time' => date('Y-m-d H:i:s', time() + 100)
        ]);
        User::query()->whereIn('id', $ids)->delete();

        $log = OperationLog::getLog();
        $this->assertTrue(true);
        echo $log;
    }

    // 查询构造器模型自动加解密字段新增、修改、删除
    public function testFieldEncryption2()
    {
        $time = date('Y-m-d H:i:s');
        $id = Manager::table('user')->insertGetId([
            'name' => 'zhangsan',
            'sex' => 0,
            'price' => 100,
            'create_time' => $time,
            'update_time' => $time
        ]);
        Manager::table('user')->where('id', $id)->update([
            'name' => 'lisi',
            'sex' => 1,
            'price' => 101,
            'update_time' => date('Y-m-d H:i:s', time() + 100)
        ]);
        Manager::table('user')->where('id', $id)->delete();

        $log = OperationLog::getLog();
        $this->assertTrue(true);
        echo $log;
    }

    // 模拟单事务提交
    public function testWorkCommit()
    {
        Manager::beginTransaction();
        try {
            $user = new User();
            $user->name = 'zhangsan';
            $user->sex = 1;
            $user->price = 100;
            $user->save();

            Manager::table('user')->where('id', $user->id)->update(['sex' => 0, 'price' => 101]);

            $user->delete();
            Manager::commit();
        } catch (Exception $e) {
            Manager::rollBack();
            throw new Exception($e->getMessage());
        }
        $this->assertTrue(true);
        echo OperationLog::getLog();
    }

    // 模拟单事务回滚
    public function testWorkBack()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('模拟单事务回滚');
        Manager::beginTransaction();
        try {
            $user = new User();
            $user->name = 'zhangsan';
            $user->sex = 1;
            $user->price = 100;
            $user->save();

            Manager::table('user')->where('id', $user->id)->update(['sex' => 0, 'price' => 101]);

            $user->delete();
            throw new Exception('模拟单事务回滚');
            Manager::commit();
        } catch (Exception $e) {
            Manager::rollBack();
            echo OperationLog::getLog();
            throw new Exception($e->getMessage());
        }
    }

    // 模拟嵌套事务提交
    public function testWorkNestCommit()
    {
        Manager::beginTransaction();
        try {
            $user = new User();
            $user->name = 'zhangsan';
            $user->sex = 1;
            $user->price = 100;
            $user->save();

            Manager::beginTransaction();
            try {
                Manager::table('user')->where('id', $user->id)->update(['sex' => 0, 'price' => 101]);

                Manager::beginTransaction();
                try {
                    $user->delete();
                    Manager::commit();
                } catch (Exception $e) {
                    Manager::rollBack();
                    throw new Exception($e->getMessage());
                }

                Manager::commit();
            } catch (Exception $e) {
                Manager::rollBack();
                throw new Exception($e->getMessage());
            }
            Manager::commit();
        } catch (Exception $e) {
            Manager::rollBack();
            throw new Exception($e->getMessage());
        }
        $this->assertTrue(true);
        echo OperationLog::getLog();
    }

    // 模拟嵌套事务部分回滚
    public function testWorkNestBackPart()
    {
        Manager::beginTransaction();
        try {
            $user = new User();
            $user->name = 'zhangsan';
            $user->sex = 1;
            $user->price = 100;
            $user->save();

            Manager::beginTransaction();
            try {
                Manager::table('user')->where('id', $user->id)->update(['sex' => 0, 'price' => 101]);

                Manager::beginTransaction();
                try {
                    $user->delete();
                    $user = new User();
                    $user->name = 'test';
                    $user->sex = 1;
                    $user->price = 100;
                    $user->save();
                    // 这里开始抛异常
                    throw new Exception('模拟嵌套事务部分回滚');
                    Manager::commit();
                } catch (Exception $e) {
                    Manager::rollBack();
                    throw new Exception($e->getMessage());
                }

                Manager::commit();
            } catch (Exception $e) {
                Manager::rollBack();
                // 这里不继续抛异常
                echo $e->getMessage() . PHP_EOL;
            }
            Manager::commit();
        } catch (Exception $e) {
            Manager::rollBack();
            throw new Exception($e->getMessage());
        }
        $this->assertTrue(true);
        echo OperationLog::getLog();
    }

    // 模拟嵌套事务全部回滚
    public function testWorkNestBackAll()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('模拟嵌套事务全部回滚');
        Manager::beginTransaction();
        try {
            $user = new User();
            $user->name = 'zhangsan';
            $user->sex = 1;
            $user->price = 100;
            $user->save();

            Manager::beginTransaction();
            try {
                Manager::table('user')->where('id', $user->id)->update(['sex' => 0, 'price' => 101]);

                Manager::beginTransaction();
                try {
                    $user->delete();
                    // 这里开始抛异常
                    throw new Exception('模拟嵌套事务全部回滚');
                    Manager::commit();
                } catch (Exception $e) {
                    Manager::rollBack();
                    throw new Exception($e->getMessage());
                }

                Manager::commit();
            } catch (Exception $e) {
                Manager::rollBack();
                throw new Exception($e->getMessage());
            }
            Manager::commit();
        } catch (Exception $e) {
            Manager::rollBack();
            throw new Exception($e->getMessage());
        }
        echo OperationLog::getLog();
    }

    // 当前操作表、当前操作对象、当前操作类型、当前操作日志
    // 最终操作日志信息
    public function testCurrentLog()
    {
        // 最终操作日志获取，该函数会在PHP程序运行结束后自动调用
        OperationLog::setShutdownFunction([new OperationLogTest(), 'finalLog']);

        // 单数据新增
        $user = new User();
        $user->name = 'zhangsan';
        $user->sex = 0;
        $user->price = 100;
        $user->save();

        // 批量数据新增
        User::query()->insert([
            ['name' => 'lisi', 'sex' => 0, 'price' => '998'],
            ['name' => 'wangwu', 'sex' => 1, 'price' => '998'],
        ]);

        // 单数据修改
        Manager::table('user')->where('id', $user->id)->update(['sex' => 2, 'price' => 101]);

        // 批量数据修改
        User::query()->where('price', '998')->update(['price' => '996']);

        // 单数据删除
        $user->delete();

        // 批量数据删除
        User::query()->where('price', '996')->delete();

        $this->assertTrue(true);
    }

}
