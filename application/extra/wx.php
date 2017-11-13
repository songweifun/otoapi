<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 下午12:01
 */

return [
    // 小程序app_id
    'app_id'=>'wx56328ae4ca9194f1',
    // 小程序app_secret
    'app_secret'=>'312340af18de463d7c50ffa01d040adb',
    // 微信使用code换取用户openid及session_key的url地址
    'login_url'=>'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?"."grant_type=client_credential&appid=%s&secret=%s",
];