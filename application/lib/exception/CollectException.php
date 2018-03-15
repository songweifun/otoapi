<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/13
 * Time: 下午2:38
 */

namespace app\lib\exception;


class CollectException extends BaseException
{
    public $code=404;
    public $msg='收藏夹是空的';
    public $errorCode=70000;

}