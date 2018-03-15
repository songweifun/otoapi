<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/27
 * Time: 下午3:22
 */

namespace app\lib\exception;


class OrderBookListException extends BaseException
{
    public $code = 404;
    public $msg = '订单图书不存在';
    public $errorCode = 110000;

}