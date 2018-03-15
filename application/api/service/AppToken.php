<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/8/2
 * Time: 下午8:02
 */

namespace app\api\service;


use app\api\model\ThirdApp;
use app\lib\exception\TokenException;

class AppToken extends Token
{
    public function get($ac, $se){
        $app = ThirdApp::check($ac, $se);
        //print_r($app->toArray());die;
        if(!$app)
        {
            throw new TokenException([
                'msg' => '授权失败',
                'errorCode' => 10004
            ]);
        }
        else{
            //$scope = $app->scope;
            $scope = 32;
            $uid = $app->id;
            //$uid = $app->id;
            $sid = $app->library_id;
            $hw_lib_code = $app->library_detail['hw_lib_code'];
            //echo $hw_lib_code;die;
            $values = [
                'scope' => $scope,
                'uid' => $uid,
                'sid'=>$sid,
                'hw_lib_code'=>$hw_lib_code,
            ];
            $token = $this->saveToCache($values);
            return $token;
        }

    }

    private function saveToCache($values){
        $token = self::generateToken();
        $expire_in = config('setting.token_expire_in');
        $result = cache($token, json_encode($values), $expire_in);
        if(!$result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $token;
    }

}