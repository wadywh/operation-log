<?php

namespace Operation\Log\orm\illuminate;

use Illuminate\Database\ConnectionInterface as Query;
use Illuminate\Database\Eloquent\Model;

class DbModel extends Model
{
    // 日志记录的主键名称
    public $logKey = "id";

    /** @var Query $query */
    private $query;

    public function setQuery(Query $query)
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * Get a fresh instance of the model's attributes.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array|mixed
     */
    public function getRawOriginal($key = null, $default = null)
    {
        if (!is_null($key)) {
            return $this->getOriginal($key, $default);
        }

        return $this->original;
    }
}