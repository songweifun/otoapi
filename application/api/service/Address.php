<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/27
 * Time: 下午7:06
 */

namespace app\api\service;
use app\api\model\UserAddress as UserAddressModel;
use app\lib\exception\AddressException;


class Address
{
    public function getAddressDetailById($id){
        $address=new UserAddressModel();
        $res=$address->where('address_id',$id)->find();
        if(!$res){
            throw new AddressException();
        }

        return $res->toArray();
    }

}