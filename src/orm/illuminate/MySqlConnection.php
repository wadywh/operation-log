<?php

namespace Operation\Log\orm\illuminate;

use Operation\Log\facades\OperationLog;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function query(): Builder
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    public function beginTransaction()
    {
        OperationLog::beginTransaction();
        parent::beginTransaction();
    }

    public function rollBack($toLevel = null)
    {
        OperationLog::rollBackTransaction();
        parent::rollBack($toLevel);
    }
}