<?php

namespace Operation\Log\orm\think;

use Operation\Log\facades\OperationLog;
use think\db\connector\Mysql;

class MySqlConnection extends Mysql
{
    public function startTrans(): void
    {
        OperationLog::beginTransaction();
        parent::startTrans();
    }

    public function rollback(): void
    {
        OperationLog::rollBackTransaction();
        parent::rollback();
    }
}