<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 上午11:49
 */

namespace app\api\model;


class Images extends BaseModel
{
    protected $autoWriteTimestamp=true; //自动写入时间戳
    //隐藏字段
    protected $hidden=['update_time','delete_time','create_time','id'];

    public function getUrlAttr($value)
    {
        return config('setting.img_prefix').'/'.$value;
    }

}