<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 上午11:11
 */

namespace app\api\controller\v1;


use app\api\service\UserToken;
use app\api\validate\AppTokenGet;
use app\api\validate\CodeGet;
use app\api\validate\TokenGet;
use app\lib\exception\ParmeterException;
use app\api\service\Token as TokenService;
use app\api\service\AppToken as AppTokenService;
use app\lib\exception\TokenException;
use think\Exception;

class Token
{
    /**
     * 第三方应用获取令牌
     * @url /app_token?
     * @POST ac=:ac se=:secret
     */
    public function getToken($code){
        (new TokenGet())->goCheck();
        $ut=new UserToken($code);
        $token=$ut->get();
        return json([
            'token' => $token
        ]);
    }
    //令牌有效性验证
    public function verifyToken($token=''){
        if(!$token){
            throw new ParmeterException([
                'msg'=>'token不允许为空'
            ]);
        }
        $valid=TokenService::verifyToken($token);
        return json([
            'isValid' => $valid
        ]);

    }

    /**
     * 第三方应用获取令牌
     * @url /app_token?
     * @POST ac=:ac se=:secret
     */
    public function getAppToken($ac='', $se='')
    {
       //return $ac;
//        header('Access-Control-Allow-Origin: *');
//        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
//        header('Access-Control-Allow-Methods: GET');
        (new AppTokenGet())->goCheck();
        $app = new AppTokenService();
        $token = $app->get($ac, $se);
        return json([
            'token' => $token
        ]);
    }


    public function getCheckCode($phone){
        (new CodeGet())->goCheck();

        $url=config('setting.code_url').$phone;
        //return $url;

        $result=curl_get($url);
        //print_r($result);die;
        //echo $result['state'];die;

        $arr=array();
        preg_match_all( '/\[state\] => ([0-9]+)[\s\r\n]+\[content\] => ([0-9]+)?/',$result, $arr );
        //print_r($arr);die;

        if($arr[1][0]==0){
            throw new TokenException([
                //"code"=>304,
                'msg'=>"获取验证码过于频繁，请稍后!",
                //"errorCode"=>10001
            ]);
        }else if($arr[1][0]==200){
           cache($phone,$arr[2][0],config('setting.code_expire_in'));
           return json(array('code'=>$arr[2][0]));


        }

        //return $result;




    }

}