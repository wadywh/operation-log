<?php


namespace Operation\Log\Test;


use Operation\Log\facades\OperationLog;

/**
 * 操作日志信息
 * Class CurrentOperationLogTest
 * @package Operation\Log\Test
 */
class OperationLogTest
{
    // 最终操作日志
    public function finalLog()
    {
        echo '最终操作日志：' . PHP_EOL;
        echo OperationLog::getLog();
    }
}