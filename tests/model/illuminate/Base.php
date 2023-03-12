<?php

namespace Operation\Log\Test\model\illuminate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;

class Base extends Model
{
    // 加密
    protected function opensslEncrypt($data)
    {
        return (new Encrypter(md5('operation_log'), 'AES-256-CBC'))->encrypt($data);
    }

    // 解密
    protected function opensslDecrypt($data)
    {
        return (new Encrypter(md5('operation_log'), 'AES-256-CBC'))->decrypt($data);
    }
}