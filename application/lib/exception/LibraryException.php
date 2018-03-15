<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/22
 * Time: 下午2:02
 */

namespace app\lib\exception;


class LibraryException extends BaseException
{
    public $code=404;
    public $msg='请求的学校信息不存在';
    public $errorCode=30000;

}