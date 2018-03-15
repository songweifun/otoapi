<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 下午3:25
 */

namespace app\lib\exception;


class CarException extends BaseException
{
    public $code=404;
    public $msg='借书车空的';
    public $errorCode=70000;


}