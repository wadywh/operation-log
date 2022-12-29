<?php

namespace Operation\Log\facades;

use Operation\Log\Facade;
use Operation\Log\orm\think\Log;
use think\Model;

/**
 * @method static created(Model $model, array $data)
 * @method static updated(Model $model, array $oldData, array $data)
 * @method static deleted(Model $model, array $data)
 * @method static batchCreated(Model $param, array $data)
 * @method static batchUpdated(Model $model, $oldData, array $data)
 * @method static batchDeleted(Model $model, array $data)
 */
class ThinkOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Log::class;
    }
}