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
    public static function check($ac, $se)
    {
        $app = self::where('app_id','=',$ac)
            ->where('app_secret', '=',$se)
            ->find();
        return $app;

    }

}