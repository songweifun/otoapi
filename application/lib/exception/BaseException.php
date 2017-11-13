<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/20
 * Time: 下午8:53
 */

namespace app\lib\exception;


use think\Exception;
//继承异常类否则throw 抛出时会报错
class BaseException extends Exception
{
    //http 状态码 404 200
    public $code=400;
    //错误描述
    public $msg='参数错误';
    //自定义的错误码
    public $errorCode=10000;

//构造函数改写属性更符合面向对象的初衷
    public function __construct($parms=[])
    {
        if(!is_array($parms)){
            return ;
        }

        if(array_key_exists('code',$parms)){
            $this->code=$parms['code'];
        }
        if(array_key_exists('msg',$parms)){
            $this->msg=$parms['msg'];
        }
        if(array_key_exists('errorCode',$parms)){
            $this->errorCode=$parms['errorCode'];
        }
    }

}