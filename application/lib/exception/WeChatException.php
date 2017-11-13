<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午3:59
 */

namespace app\lib\exception;


class WeChatException extends BaseException
{
    public $code = 400;
    public $msg = 'wechat unknown error';
    public $errorCode = 999;

}