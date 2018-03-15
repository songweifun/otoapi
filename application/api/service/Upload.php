<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/7
 * Time: 上午10:46
 */

namespace app\api\service;
use app\api\model\Images as ImagesModel;
use app\lib\exception\ImageUploadException;


class Upload
{
    public function uploadImg($file){

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
            $res=ImagesModel::create(['url'=>$info->getSaveName(),'from'=>1]);
            if(!$res){
                throw new ImageUploadException();
            }

            return $res;

        }else{
            // 上传失败获取错误信息
            throw new ImageUploadException([
                'msg'=>$file->getError()
            ]);
        }
    }

}