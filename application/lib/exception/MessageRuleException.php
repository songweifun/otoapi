<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午4:56
 */

namespace app\lib\exception;


class MessageRuleException extends BaseException
{
    public $code=404;
    public $msg='请求的消息规则不存在';
    public $errorCode=40000;

}