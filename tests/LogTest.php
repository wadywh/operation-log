<?php

namespace Operation\Log\Test;

use Operation\Log\facades\OperationLog;
use Operation\Log\Test\model\illuminate\User as IUser;
use Operation\Log\Test\model\think\User as TUser;

class LogTest extends Base
{
    public function testCreateI()
    {
        $user = new IUser();
        $user->name = "Create";
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $this->assertEquals("创建 用户 (id:$id)：姓名：Create，性别：男", OperationLog::getLog());
        return $id;
    }

    /**
     * @depends testCreateI
     */
    public function testUpdateI($id)
    {
        $user = IUser::find($id);
        $user->name = "Update";
        $user->sex = 0;
        $user->save();
        $this->assertEquals("修改 用户 (id:$id)：姓名由：Create 改为：Update，性别由：男 改为：女", OperationLog::getLog());
        return $id;
    }

    /**
     * @depends testUpdateI
     */
    public function testDeleteI($id)
    {
        IUser::destroy($id);
        $this->assertEquals("删除 用户 (id:$id)：姓名：Update，性别：女，json：", OperationLog::getLog());
    }

    public function testCreateT()
    {
        $user = new TUser();
        $user->name = "Create";
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $this->assertEquals("创建 用户 (id:$id)：姓名：Create，性别：男", OperationLog::getLog());
        return $id;
    }

    /**
     * @depends testCreateT
     */
    public function testUpdateT($id)
    {
        $user = TUser::find($id);
        $user->name = "Update";
        $user->sex = 0;
        $user->save();
        $this->assertEquals("修改 用户 (id:$id)：姓名由：Create 改为：Update，性别由：男 改为：女", OperationLog::getLog());
        return $id;
    }

    /**
     * @depends testUpdateT
     */
    public function testDeleteT($id)
    {
        TUser::destroy($id);
        $this->assertEquals("删除 用户 (id:$id)：姓名：Update，性别：女，json：", OperationLog::getLog());
    }
}
