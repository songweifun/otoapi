<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/24
 * Time: 下午10:11
 */

namespace app\lib\exception;


class ForbiddenException extends BaseException
{
    public $code = 403;
    public $msg = '权限不够';
    public $errorCode = 10001;

}