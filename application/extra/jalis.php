<?php
/**
 * Created by PhpStorm.
 * User: daivd
 * Date: 2017/12/4
 * Time: 下午1:56
 */

return [

    'jalis_appid'=>'abf37fb2998a4b29945f9bfc913cdec0',
    'jalis_appkey'=>'7ff3c2c4b53645b194a5d9b47b33fb7a',
    'jalis_base_url'=>"https://dc.jalis.nju.edu.cn",

    'jalis_libs_url'=>"/dc/api/v1/libs",


    'jalis_reader_check_url'=>"/dc/api/v1/reader/check",
    'jalis_search_url'=>"/dc/api/v1/marc/search",
    'jalis_lib_holding_url'=>"/dc/api/v1/marc/lib_holding",//查询特定书目在每个图书馆的副本数
    'jalis_lib_items_url'=>"/dc/api/v1/marc/items", //查询特定书目在特定馆的副本信息
    'jalis_marcbook_detail_url'=>"/dc/api/v1/marc", //根据marcid获得数目的详细信息

];