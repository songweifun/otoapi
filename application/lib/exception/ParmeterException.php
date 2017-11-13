<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/20
 * Time: 下午11:38
 */

namespace app\lib\exception;


class ParmeterException extends BaseException
{
//这些相当于重写基类的成员变量 throw这个类的事例时抛到全局异常处理类处理
    public $code=400;
    public $msg='参数错误';
    public $errorCode=10000;

}