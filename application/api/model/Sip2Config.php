<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/1
 * Time: 下午2:17
 */

namespace app\api\model;


use app\lib\exception\Sip2ConfigException;

class Sip2Config extends BaseModel
{
    public static function getSip2ConfigBySchoolId($sid){
        $result=self::where('school_id','=',$sid)->where('is_default','=',1)->find();
        if(!$result){
            throw new Sip2ConfigException();

        }
        return $result->toArray();
    }

}