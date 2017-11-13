<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/26
 * Time: 下午8:19
 */

namespace app\lib\exception;


class OrderException extends BaseException
{
    public $code = 404;
    public $msg = '订单不存在，请检查ID';
    public $errorCode = 80000;

}