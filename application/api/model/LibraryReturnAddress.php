<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2018/1/9
 * Time: 下午3:19
 */

namespace app\api\model;


class LibraryReturnAddress extends BaseModel
{

    public function getAddressDetailById($id){
        $res=self::where('id',$id)->find()->toArray();
        return $res;
    }


}