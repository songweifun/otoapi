<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/7/23
 * Time: 上午9:39
 */

namespace app\api\controller\v1;
use app\api\model\Category as CategoryModel;
use app\api\validate\IdMustBePositiveInt;
use app\lib\exception\CategoryException;


class Category
{
    public function getAllcategories(){

        $categories=CategoryModel::all([],'img');
        if($categories->isEmpty()){
            throw new CategoryException();
        }
        return json($categories);



    }

    public function addCategory(){
        $postData=request()->post() ;
        //print_r($_FILES);die;
        $res=CategoryModel::create([
            'name'=>$postData['name'],
            'description'=>$postData['description'],
            'coverImg'=>$postData['coverImg']

        ]);

        return json($res->toArray());
    }


    public function updateCategory($id){
        (new IdMustBePositiveInt())->goCheck();
        $toUpdateData=request()->patch();
        //return json($toUpdateData);
        $category=new CategoryModel();
        $res=$category->where('id','=',$id)->update($toUpdateData);
        if($res){
            return json($category->get($id));
        }else{

        }

    }


    public function deleteCategory($id){

        (new IdMustBePositiveInt())->goCheck();

        $category=new CategoryModel();
        $return =$category->get($id);
        $res=CategoryModel::destroy($id);
        if($res){
            return json($return);
        }else{

        }

    }

}