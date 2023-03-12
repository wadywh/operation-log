<?php

namespace Operation\Log\orm\illuminate;

use Operation\Log\OperationLog;
use Operation\Log\OperationLogInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Log extends OperationLog implements OperationLogInterface
{
    /**
     * @param Model $model
     * @return string
     */
    public function getPk($model): string
    {
        return $model->getKeyName();
    }

    /**
     * @param Model $model
     * @return string
     */
    public function getTableName($model): string
    {
        return $model->getConnection()->getTablePrefix() . $model->getTable();
    }

    /**
     * @param Model $model
     * @return string
     */
    public function getDatabaseName($model): string
    {
        if (method_exists($model, "getQuery")) {
            return $model->getQuery()->getDatabaseName();
        }
        return $model->getConnection()->getDatabaseName();
    }

    /**
     * @param Model $model
     * @param string $sql
     * @return array
     */
    public function executeSQL($model, string $sql): array
    {
        if (method_exists($model, "getQuery")) {
            return $model->getQuery()->select($sql);
        }
        return $model->getConnection()->select($sql);
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getAttributes($model): array
    {
        return $model->getAttributes();
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getChangedAttributes($model): array
    {
        return $model->getChanges();
    }

    /**
     * @param Model $model
     * @param string $key
     * @param $value
     * @return string
     */
    public function getValue($model, string $key, $value): string
    {
        $keyText = $key . "_text";
        $value = $model->$keyText ?? $value;

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            return (string)$value;
        }
    }

    /**
     * @param Model $model
     * @param string $key
     * @param $value
     * @return string
     */
    public function getOldValue($model, string $key, $value): string
    {
        $keyText = $key . "_text";
        $attributeFun = "get" . Str::studly(Str::lower($keyText)) . "Attribute";
        return (string)(method_exists($model, $attributeFun) ? $model->$attributeFun($model->getRawOriginal($key)) : $model->getRawOriginal($key));
    }

    /**
     * @param Model $model
     * @param array $data
     */
    public function created($model, array $data)
    {
        $model->setRawAttributes($data);
        $this->generateLog($model, self::CREATED);
    }

    /**
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function updated($model, array $oldData, array $data)
    {
        $model->setRawAttributes($oldData, true);
        $model->setRawAttributes(array_merge($oldData, $data));
        $model->syncChanges();
        $this->generateLog($model, self::UPDATED);
    }

    /**
     * @param Model $model
     * @param array $data
     */
    public function deleted($model, array $data)
    {
        $model->setRawAttributes($data);
        $this->generateLog($model, self::DELETED);
    }

    /**
     * @param Model $model
     * @param array $data
     */
    public function batchCreated($model, array $data)
    {
        foreach ($data as $item) {
            $model->setRawAttributes($item);
            $this->generateLog($model, self::BATCH_CREATED);
        }
    }

    /**
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function batchUpdated($model, array $oldData, array $data)
    {
        foreach ($oldData as $item) {
            $model->setRawAttributes((array)$item, true);
            $model->setRawAttributes(array_merge((array)$item, $data));
            $model->syncChanges();
            $this->generateLog($model, self::BATCH_UPDATED);
        }
    }

    /**
     * @param Model $model
     * @param array $data
     */
    public function batchDeleted($model, array $data)
    {
        foreach ($data as $item) {
            $model->setRawAttributes((array)$item);
            $this->generateLog($model, self::BATCH_DELETED);
        }
    }
}