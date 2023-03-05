<?php


namespace Operation\Log;


interface OperationLogRecordInterface
{
    /**
     * 执行记录当前日志
     * @return mixed
     */
    public function execRecordLog();
}