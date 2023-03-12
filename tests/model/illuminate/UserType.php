<?php

namespace Operation\Log\Test\model\illuminate;

/**
 * @property mixed $id
 * @property mixed|int $user_id
 * @property mixed $type_code
 */
class UserType extends Base
{
    protected $table = "user_type";
    public $timestamps = false;
    public $tableComment = "用户类型";
    public $columnComment = [
        "user_id" => "用户id",
        "type_code" => "类型编码",
    ];
    public $fillable = [
        'user_id', 'type_code'
    ];

}