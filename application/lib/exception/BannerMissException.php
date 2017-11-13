<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/20
 * Time: 下午8:56
 */

namespace app\lib\exception;


class BannerMissException extends BaseException
{

    //这些相当于重写基类的成员变量 throw这个类的事例时抛到全局异常处理类处理
    public $code=404;
    public $msg='请求的banner不存在';
    public $errorCode=40000;

}