<?php

/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/8/4
 * Time: 下午3:50
 */
namespace app\api\behavior;
use think\Response;

class CORS
{
    public function appInit(&$params)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: POST,GET,DELETE,PUT');
        if(request()->isOptions()){
            exit();
        }
    }

}