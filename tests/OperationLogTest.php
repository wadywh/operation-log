<?php


namespace Operation\Log\Test;


use Operation\Log\facades\OperationLog;
use Operation\Log\OperationLogRecordInterface;

/**
 * 操作日志信息
 * Class CurrentOperationLogTest
 * @package Operation\Log\Test
 */
class OperationLogTest implements OperationLogRecordInterface
{
    // 当前操作日志
    public function execRecordLog()
    {
        echo '当前操作表：' . OperationLog::getLogModel() . PHP_EOL;
        echo '当前操作对象：' . OperationLog::getLogKey() . PHP_EOL;
        echo '当前操作类型：' . OperationLog::getOperationType() . PHP_EOL;
        echo '当前操作日志：' . OperationLog::getCurrentLog() . PHP_EOL;
        echo PHP_EOL;
    }

    // 最终操作日志
    public function finalLog()
    {
        echo '最终操作日志：' . PHP_EOL;
        echo OperationLog::getLog();
    }
}