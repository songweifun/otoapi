<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/22
 * Time: 下午6:31
 */

namespace app\lib\exception;


class ThemeException extends BaseException
{
    public $code=404;
    public $msg='指定主题不存在，请检查主题Id';
    public $errorCode=30000;


}