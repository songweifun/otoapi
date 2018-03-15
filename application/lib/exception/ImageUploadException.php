<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 上午11:54
 */

namespace app\lib\exception;


class ImageUploadException extends BaseException
{
    public $code=401;
    public $msg='图片上传失败';
    public $errorCode=60000;

}