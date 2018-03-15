<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/14
 * Time: 上午11:29
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\CodeGet;
use app\api\validate\Login;
use app\api\validate\PasswordReset;
use app\api\validate\UserNew;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Request;
use app\api\model\User as UserModel;
use app\api\service\Upload as UploadService;
use app\api\service\Token as TokenService;


class User extends BaseController
{
    /**
     * 注册
     * @return \think\response\Json
     * @throws UserException
     */
    public function register(){
        //print_r(request()->post());die;
        (new UserNew())->goCheck();
        //$postData=request()->post();
        $phone=Request::instance()->post('phone','htmlspecialchars');
        $user_one=UserModel::get(['phone' => $phone]);

        if($user_one){
            throw new UserException([
                'code'=>403,
                'msg'=>'此手机已被注册',
                'errorCode'=>20001
            ]);
        }

        $code=Request::instance()->post('code','htmlspecialchars');
        $cacheCode=cache($phone);
        if($cacheCode!==$code){
            throw new UserException([
                'code'=>403,
                'msg'=>'验证码错误,或者已经过期！',
                'errorCode'=>20002
            ]);
        }

        $library_id=Request::instance()->post('library_id','interval');
        $card=Request::instance()->post('card','htmlspecialchars');
        $user_name=Request::instance()->post('user_name','htmlspecialchars');
        $password=Request::instance()->post('password/a','htmlspecialchars');


        $user = UserModel::create([
            'card' =>  $card,
            'user_name' =>  $user_name,
            'password' =>  md5($password['password']),
            'phone' =>  $phone,
        ]);

        if(!$user){
            throw new UserException([
                'msg'=>"注册失败",
                'code'=>400,
                'errorCode'=>20003,
            ]);
        }
        //var_dump($user);die;
        $user->profile()->save(['library_id' => $library_id]);

        return json(UserModel::with('profile')->find($user->id));
        //return $phone;
    }

    //检查用户是否已注册
    public function checkUser($phone){
        (new CodeGet())->goCheck();
        $user_one=UserModel::where('phone' , $phone)->find();
        //print_r($user_one);die;
        if(!$user_one){
            throw new UserException();
        }

        //echo 11111;die;

        return json($user_one);
    }

    public function resetPassword(){

        (new PasswordReset())->goCheck();
        $phone=Request::instance()->post('phone','htmlspecialchars');
        $code=Request::instance()->post('code','htmlspecialchars');
        $cacheCode=cache($phone);
        if($cacheCode!==$code){
            throw new UserException([
                'code'=>403,
                'msg'=>'验证码错误,或者已经过期！',
                'errorCode'=>20002
            ]);
        }
        $password=Request::instance()->post('password/a','htmlspecialchars');

        $user = UserModel::where('phone',$phone)->update(['password' => md5($password['password'])]);

        if(!$user){
            throw new UserException([
                'msg'=>"更新失败",
                'code'=>403,
                'errorCode'=>20004
            ]);
        }

        return json(new SuccessMessage(),201);


    }

    public function login(){
        (new Login())->goCheck();
        $phone=Request::instance()->post('phone','htmlspecialchars');
        $password=Request::instance()->post('password','htmlspecialchars');

        $user=UserModel::where('phone','=',$phone)->where('password','=',md5($password))->find();

        if(!$user){
            throw new UserException([
               'msg'=>'登录失败',
               'errorCode'=>20005
            ]);
        }

        return json($user->toArray());

    }


    public function uploadAvatar(){
        $upload=new UploadService();
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('avatar');
        if($file){
            $res=$upload->uploadImg($file);
            $uid=TokenService::getCurrentUid();
            $result=UserModel::where('id','=',$uid)->update(['headimg'=>$res['id']]);
            if(!$result){
                throw new UserException([
                    'msg'=>"上传头像失败"
                ]);
            }

            return json($res);
        }else{
            throw new UserException([
                'msg'=>"没有上传任何头像文件!"
            ]);

        }



    }



}