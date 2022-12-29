<?php

namespace Operation\Log\facades;

use Operation\Log\Facade;
use Operation\Log\orm\illuminate\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static created(Model $model, array $data)
 * @method static updated(Model $model, array $oldData, array $data)
 * @method static deleted(Model $model, array $data)
 * @method static batchCreated(Model $model, array $data)
 * @method static batchUpdated(Model $model, array $oldData, array $data)
 * @method static batchDeleted(Model $model, array $data)
 */
class IlluminateOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Log::class;
    }
}