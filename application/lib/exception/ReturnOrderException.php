<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2018/1/9
 * Time: 下午6:19
 */

namespace app\lib\exception;


class ReturnOrderException extends BaseException
{
    public $code = 404;
    public $msg = '还书订单不存在';
    public $errorCode = 130000;

}