<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/1
 * Time: 下午2:40
 */

namespace app\lib\exception;


class Sip2ConfigException extends BaseException
{
    public $code=404;
    public $msg='请求的sip2配置信息不存在';
    public $errorCode=30000;

}