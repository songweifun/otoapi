<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午6:40
 */

namespace app\lib\exception;



class MessageException extends BaseException
{
    public $code=404;
    public $msg='查找的消息不存在';
    public $errorCode=50000;

}