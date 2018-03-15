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
use app\api\validate\IdMustBePositiveInt;
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
        $postArr=input('post.');

        if(!array_key_exists('flag',$postArr)){
            $postArr['flag']=0;
        }

        $dataArray=$validate->getDataByRule($postArr);//通过验证器基类的方法获取参数防止多余的参数覆盖数据表
        //return json($dataArray);
        $userAddress=$user->address;
//        //print_r($user->toArray());die;
//        if (!$userAddress )
//        {
//            // 关联属性不存在，则新建
//            $user->address()
//                ->save($dataArray);
//        }
//        else
//        {
//            // 存在则更新
//            // fromArrayToModel($user->address, $data);
//            // 新增的save方法和更新的save方法并不一样
//            // 新增的save来自于关联关系
//            // 更新的save来自于模型
//            $user->address->save($dataArray);
//        }

        //print_r($dataArray);die;
        $address=new UserAddress();
        if(input('post.address_id')){


            if($dataArray['flag']==1){
                $address->where('user_id',$uid)->setField('flag',0);
            }
            $res=$address->where('address_id',input('post.address_id'))->update($dataArray);
            if(!$res){
                throw new UserException([
                    'msg'=>'用户地址更新失败',
                    'code'=>401,
                    'errorCode'=>20007
                ]);
            }
        }else{
            if(!$userAddress){
                $dataArray['flag']=1;//第一个地址设置为默认地址
            }
            if($dataArray['flag']==1){
                $address->where('user_id',$uid)->setField('flag',0);
            }
            $res=$user->address()->save($dataArray);
            if(!$res){
                throw new UserException([
                    'msg'=>'用户地址添加失败',
                    'code'=>401,
                    'errorCode'=>20006
                ]);
            }
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
        $userAddress = UserAddress::where('user_id', $uid)->order('flag desc')
            ->select();
        if(empty($userAddress->toArray())){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 20008
            ]);
        }
        return json($userAddress);
    }

    /**
     * 获取用户地址信息
     * @return UserAddress
     * @throws UserException
     */
    public function getUserAddressDefault(){
        $uid = TokenService::getCurrentUid();
        $userAddress = UserAddress::where('user_id', $uid)->where('flag','=',1)
            ->find();
        if(empty($userAddress)){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 20008
            ]);
        }
        return json($userAddress);
    }


    public function deleteUserAddress($id){
       // $uid = TokenService::getCurrentUid();
        (new IdMustBePositiveInt())->goCheck();
        $address=UserAddress::get($id);
        if(!$address){
            throw new UserException([
                'msg'=>'地址不存在',
                'errorCode'=>20008
            ]);
        }
        $res=$address->delete();
        if($res){
            return json($address);
        }else{
            throw new UserException([
                'msg'=>"地址删除失败",
                'code'=>401,
                'errorCode'=>20009
            ]);
        }
    }


}