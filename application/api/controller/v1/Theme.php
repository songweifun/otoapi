<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/22
 * Time: 下午4:46
 */

namespace app\api\controller\v1;


use app\api\validate\IdCollection;
use app\api\model\Theme as ThemeModel;
use app\api\validate\IdMustBePositiveInt;
use app\lib\exception\ThemeException;

class Theme
{


    /**
     * @url /theme?ids=1,2,3
     * @param $ids
     */
    public function getSimpleList($ids){
        (new IdCollection())->goCheck($ids);
        $ids=explode(',',$ids);
        $result=ThemeModel::with(['topicImg','headImg'])
            ->select($ids);
        if($result->isEmpty()){
            throw new ThemeException();
        }

        //return json($result);
        //不能直接返回数组
        return $result;

    }

    /**
     * @url /theme/:id
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getComplexOne($id){
        (new IdMustBePositiveInt())->goCheck($id);

        $result=ThemeModel::getThemeWithProducts($id);
        if(!$result){
            throw new ThemeException();
        }
        return $result;


    }

}