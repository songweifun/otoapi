<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午7:52
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\UserAddress;
use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;
use think\Controller;

class Address extends BaseController
{
    //前置方法 继承think/controller类
    protected $beforeActionList=[
      'checkPrimaryScope'=>['only'=>'createOrUpdateAddress,getUserAddress']
    ];


    public function createOrUpdateAddress(){
        $validate = new AddressNew();
        $validate->goCheck();
        //根据token来获取uid
        //如果uid 在user表中不存在抛出异常
        //如果存在 获得用户发送的地址数据
        //根据uid查询地址表看看存不存在 如果存在则为更新 否则为添加
        $uid=TokenService::getCurrentUid();

        $user=UserModel::get($uid);
        if(!$user){
            throw new UserException();
        }

        $dataArray=$validate->getDataByRule(input('post.'));//通过验证器基类的方法获取参数防止多余的参数覆盖数据表
        $userAddress=$user->address;
        if (!$userAddress )
        {
            // 关联属性不存在，则新建
            $user->address()
                ->save($dataArray);
        }
        else
        {
            // 存在则更新
            // fromArrayToModel($user->address, $data);
            // 新增的save方法和更新的save方法并不一样
            // 新增的save来自于关联关系
            // 更新的save来自于模型
            $user->address->save($dataArray);
        }
        return json(new SuccessMessage(),201);
    }


    /**
     * 获取用户地址信息
     * @return UserAddress
     * @throws UserException
     */
    public function getUserAddress(){
        $uid = TokenService::getCurrentUid();
        $userAddress = UserAddress::where('user_id', $uid)
            ->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;
    }


}