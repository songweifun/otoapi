<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/11/22
 * Time: 下午1:56
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Library as LibraryModel;
use app\lib\exception\LibraryException;

class Library extends BaseController
{

    public function getAllLibrary(){
        $libraries=LibraryModel::order('sort asc')->select();

        if(!$libraries){

            throw new LibraryException();

        }else{
            return json($libraries);
        }

    }

}