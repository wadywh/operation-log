<?php

namespace Operation\Log\facades;

use Operation\Log\Facade;

/**
 * @method static getLog()
 * @method static clearLog()
 * @method static setTableModelMapping(array $map)
 * @method static setExecInfoSchema(bool $exec)
 * @method static setRecordTypes(array $types)
 * @method static setShutdownFunction($function, ...$parameters)
 * @method static beginTransaction()
 * @method static rollBackTransaction()
 */
class OperationLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Operation\Log\OperationLog::class;
    }
}