<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午4:17
 */

namespace app\lib\exception;


class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;

}