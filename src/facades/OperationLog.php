<?php

namespace Operation\Log\facades;

use Operation\Log\Facade;

/**
 * @method static getLog()
 * @method static clearLog()
 * @method static getCurrentLog()
 * @method static clearCurrentLog()
 * @method static setTableModelMapping(array $map)
 * @method static getTableModelMapping()
 * @method static setExecInfoSchema(bool $exec)
 * @method static getExecInfoSchema()
 * @method static setRecordTypes(array $types)
 * @method static getRecordTypes()
 * @method static getOperationType()
 * @method static setRecordClass($class)
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