<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/21
 * Time: 下午5:34
 */

namespace app\api\model;

use app\api\model\SchoolInfo as SchoolInfoModel;


class UserDetail extends BaseModel
{
    public function getLibraryIdAttr($value)
    {

        $schoolInfo=SchoolInfoModel::find($value);

        return $schoolInfo['name'];
    }

}