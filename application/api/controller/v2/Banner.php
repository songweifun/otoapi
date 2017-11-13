<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/18
 * Time: 下午11:29
 */

namespace app\api\controller\v2;


use app\api\validate\IdMustBePositiveInt;
use app\api\model\Banner as BannerModel;
use app\lib\exception\BannerMissException;
use think\Exception;

class Banner
{
    /**
     * 获得指定banner的所有子项
     * http GET
     * url banner/:id
     * @id bannerid
     */
    public function getBanner($id){

        return 'This is v2 Version';

    }

}