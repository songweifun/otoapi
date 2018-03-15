<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/8/2
 * Time: 下午8:05
 */

namespace app\api\model;


class ThirdApp extends BaseModel
{
    protected $table='noto_admin_user';


    public function libraryDetail(){
        return $this->belongsTo('Library','library_id');
    }


    public static function check($ac, $se)
    {
        $app = self::with('libraryDetail')->where('user_name','=',$ac)
            ->where('password', '=',md5($se))
            ->find();
        return $app;

    }

}