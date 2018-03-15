<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/22
 * Time: 下午3:22
 */

namespace app\api\validate;


use app\lib\exception\ParmeterException;

class PasswordReset extends BaseValidate
{
    protected $rule = [
        'phone' => 'require|isMobile',
        'code' => 'require|isPositiveInteger',
        'password' => 'require|checkPasswords',
    ];

    protected $singleRule=[
        'password'=>'require|min:6',
        'pconfirm'=>'require|min:6'
    ];


    public function checkPasswords($values){
        if(!is_array($values)){
            throw new ParmeterException([
                'msg'=>'密码参数错误1'
            ]);
        }
        if(empty($values)){
            throw new ParmeterException([
                'msg'=>'密码不能为空'
            ]);
        }

        if($values['password']!==$values['pconfirm']){
            throw new ParmeterException([
                'msg'=>'两次密码不一致'
            ]);
        }


        $this->checkPassword($values);

        return true;
    }

    //验证子数组
    private function checkPassword($value)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            throw new ParmeterException([
                'msg' => '密码格式错误,密码不能小于6位',
            ]);
        }
    }

}