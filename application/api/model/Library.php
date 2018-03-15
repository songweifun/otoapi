<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/22
 * Time: 下午1:57
 */

namespace app\api\model;


use app\lib\exception\LibraryException;

class Library extends BaseModel
{
    public function getSchoolInfoById($sid){
        $res=self::where('id',$sid)->find();
        if(!$res){
            throw new LibraryException();
        }

        return $res;
    }



    public function getSchoolIdBySchoolName($name){
        $res=self::where('lname',$name)->find();
        if(!$res){
            throw new LibraryException();
        }

        return $res;
    }

    public function getSchoolIdByHwLibCode($libCode){
        $res=self::where('hw_lib_code',$libCode)->find();
        if(!$res){
            throw new LibraryException([]);
        }

        return $res;
    }


    public function getSchoolNameByHwLibCode($libCode){
        $res=self::where('hw_lib_code',$libCode)->find();
        if(!$res){
            throw new LibraryException([]);
        }

        return $res;
    }

}