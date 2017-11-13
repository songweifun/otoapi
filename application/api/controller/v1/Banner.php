<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/18
 * Time: 下午11:29
 */

namespace app\api\controller\v1;


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
//        $data=[
//            'name'=>'fansongweifefefefe',
//            'email'=>'fansongwei163.com'
//        ];
//        $validate=new TestValidate();
//        $result=$validate->batch()->check($data);
//        $data=[
//            'id'=>$id
//        ];
//        $validate=new IdMustBePositiveInt();
//        $validate->check($data);
//        var_dump($validate->getError());
        (new IdMustBePositiveInt())->goCheck(); //

        //$banner=BannerModel::getBannerById($id);
        //with 可以是数组所以是多个 或者嵌套
        $banner=BannerModel::with(['items','items.img'])->find($id);
        //$banner->hidden(['update_time','delete_time','items.update_time']);
        if(!$banner){
            throw  new BannerMissException();
        }
        //throw new Exception('内部错误');
        //$c=config('setting.img_prefix');
        return json($banner);

    }

}