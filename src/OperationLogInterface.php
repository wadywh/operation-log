<?php

namespace Operation\Log;

interface OperationLogInterface
{
    /**
     * 获取主键
     * @param $model
     * @return string
     */
    public function getPk($model): string;

    /**
     * 获取表名
     * @param $model
     * @return string
     */
    public function getTableName($model): string;

    /**
     * 获取数据库名
     * @param $model
     * @return string
     */
    public function getDatabaseName($model): string;

    /**
     * 执行SQL
     * @param $model
     * @param string $sql
     * @return mixed
     */
    public function executeSQL($model, string $sql);

    /**
     * 获取模型上当前所有的属性
     * @param $model
     * @return array
     */
    public function getAttributes($model): array;

    /**
     * 获取模型上当前修改的属性
     * @param $model
     * @return array
     */
    public function getChangedAttributes($model): array;

    public function getValue($model, string $key): string;

    public function getOldValue($model, string $key): string;

    public function created($model, array $data);

    public function updated($model, array $oldData, array $data);

    public function deleted($model, array $data);

    public function batchCreated($model, array $data);

    public function batchUpdated($model, array $oldData, array $data);

    public function batchDeleted($model, array $data);

}