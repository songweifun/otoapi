<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/13
 * Time: 下午6:34
 */

namespace app\lib\exception;


class BookException extends BaseException
{
    public $code=404;
    public $msg='图书不存在';
    public $errorCode=90000;

}