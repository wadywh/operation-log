<?php

namespace Operation\Log\orm\think;

use Operation\Log\facades\OperationLog;
use Operation\Log\facades\ThinkOrmLog;
use Operation\Log\Singleton;
use think\helper\Str;
use think\Model;

class Query extends \think\db\Query
{
    public function insert(array $data = [], bool $getLastInsID = false)
    {
        $result = parent::insert($data, $getLastInsID);

        if ($getLastInsID) {
            $id = $result;
        } else {
            $id = $this->getLastInsID();
        }

        $model = $this->generateModel();
        $pk = $this->getPk();
        $data = $data ?: $this->getOptions("data");
        $data[$pk] = $id;
        ThinkOrmLog::created($model, $data);

        return $result;
    }

    public function insertAll(array $dataSet = [], int $limit = 0): int
    {
        $result = parent::insertAll($dataSet, $limit);

        $model = $this->generateModel();
        ThinkOrmLog::batchCreated($model, $dataSet);

        return $result;
    }

    public function update(array $data = []): int
    {
        $model = $this->generateModel();
        $newData = $data ?: $this->getOptions("data");
        $field = array_keys($newData);
        $field[] = $model->logKey ?? $model->getPk();

        $pk = $model->getPk();
        if (isset($data[$pk])) {
            // 包含主键只更新一条
            $oldData = $this->find($data[$pk]);
            if (!empty($oldData)) {
                $oldData = [is_array($oldData) ? $oldData : $oldData->toArray()];
            }
        } else {
            // 条件查询或许是多条
            $oldData = $this->field($field)->select()->toArray();
        }
        if (!empty($oldData)) {
            if (count($oldData) > 1) {
                ThinkOrmLog::batchUpdated($model, $oldData, $newData);
            } else {
                ThinkOrmLog::updated($model, $oldData[0], $newData);
            }
        }

        return parent::update($data);
    }

    public function delete($data = null): int
    {
        $model = $this->generateModel();
        if (!empty($data)) {
            $pk = $model->getPk();
            $delData = $this->whereIn($pk, $data)->select()->toArray();
        } else {
            $delData = $this->select()->toArray();
        }

        if (!empty($delData)) {
            if (count($delData) > 1) {
                ThinkOrmLog::batchDeleted($model, $delData);
            } else {
                ThinkOrmLog::deleted($model, $delData[0]);
            }
        }


        return parent::delete($data);
    }

    /**
     * 生成Model对象
     * @return Model
     */
    private function generateModel(): Model
    {
        if ($this->getModel()) {
            return $this->getModel();
        }
        $name = $this->getName();
        $tableMap = Singleton::getInstance()->getTableModelMapping();
        if (isset($tableMap[$name])) {
            $modelNamespace = $tableMap[$name];
            $className = trim($modelNamespace, "\\");
        } else {
            $modelNamespace = $this->getConfig("modelNamespace") ?: "app\model";;
            $className = trim($modelNamespace, "\\") . "\\" . Str::studly($name);
        }
        if (class_exists($className)) {
            $model = new $className;
        } else {
            $model = new DbModel($name);
            $model->table($this->getTable());
            $model->setQuery($this);
            $model->logKey = $this->getConfig("logKey") ?: $model->getPk();
            $model->pk($this->getPk());
        }
        return $model;
    }
}