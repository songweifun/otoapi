<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/27
 * Time: 下午7:09
 */

namespace app\lib\exception;


class AddressException extends BaseException
{
    public $code=404;
    public $msg='地址不存在';
    public $errorCode=120000;

}