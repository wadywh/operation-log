<?php

namespace Operation\Log\facades;

use Operation\Log\Facade;

/**
 * @method static getLog()
 * @method static clearLog()
 * @method static setTableModelMapping()
 * @method static getTableModelMapping()
 * @method static setExecInfoSchema()
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