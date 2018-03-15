<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
Route::get('api/:version/banner/:id','api/:version.Banner/getBanner');

Route::get('api/:version/theme','api/:version.Theme/getSimpleList');
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');

Route::get('api/:version/product/by_category','api/:version.Product/getAllIncategory');
Route::get('api/:version/product/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
Route::get('api/:version/product/recent','api/:version.Product/getRecent');



Route::get('api/:version/category/all','api/:version.Category/getAllcategories');
Route::post('api/:version/category/add','api/:version.Category/addCategory');
Route::patch('api/:version/category/update/:id','api/:version.Category/updateCategory',[],['id'=>'\d+']);
Route::delete('api/:version/category/delete/:id','api/:version.Category/deleteCategory',[],['id'=>'\d+']);



//token
Route::post('api/:version/token/user','api/:version.Token/getToken');
Route::post('api/:version/token/app', 'api/:version.Token/getAppToken');
Route::post('api/:version/token/verify', 'api/:version.Token/verifyToken');


//address
Route::post('api/:version/address','api/:version.Address/createOrUpdateAddress');
Route::get('api/:version/address', 'api/:version.Address/getUserAddress');
Route::get('api/:version/address/default', 'api/:version.Address/getUserAddressDefault');
Route::delete('api/:version/address/delete/:id','api/:version.Address/deleteUserAddress',[],['id'=>'\d+']);



//order
Route::post('api/:version/order','api/:version.Orders/place');
Route::get('api/:version/order/:pid', 'api/:version.Orders/getOrderDetailByPid',[], ['pid'=>'\d+']);
Route::get('api/:version/order/status/:status', 'api/:version.Orders/getParentOrdersByStatus',[], ['status'=>'\d+']);
Route::get('api/:version/order/status_admin/:status', 'api/:version.Orders/getParentOrdersByStatusAdmin',[], ['status'=>'\d+']);
Route::delete('api/:version/order/delete_by_pid/:pid', 'api/:version.Orders/deleteOrderByParentId',[], ['pid'=>'\d+']);
Route::delete('api/:version/order/delete_by_id/:id', 'api/:version.Orders/deleteOrderById',[], ['id'=>'\d+']);
Route::put('api/:version/order/delivery', 'api/:version.Order/delivery');
Route::put('api/:version/order/set_status_pid', 'api/:version.Orders/setOrderAndBookStatusByPid');
Route::put('api/:version/order/set_status_id', 'api/:version.Orders/setOrderAndBookStatusById');
Route::post('api/:version/order/return_order_detail', 'api/:version.Orders/getReturnOrderListDetail');
Route::post('api/:version/order/return', 'api/:version.Orders/returnOrder');
Route::get('api/:version/order/my_return_orders_list', 'api/:version.Orders/getMyReturnOrdersList');
Route::put('api/:version/order/update_field_id', 'api/:version.Orders/updateFieldByListId');
Route::get('api/:version/order/nannongtest', 'api/:version.Orders/nannongtest');


//Route::put('api/:version/order/delivery', 'api/:version.Order/delivery');

//不想把所有查询都写在一起，所以增加by_user，很好的REST与RESTFul的区别
Route::get('api/:version/order/by_user', 'api/:version.Order/getSummaryByUser');
Route::get('api/:version/order/paginate', 'api/:version.Order/getSummary');


Route::post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder');



Route::post('api/:version/user/register','api/:version.User/register');
Route::get('api/:version/token/code','api/:version.Token/getCheckCode');
Route::get('api/:version/Library/all','api/:version.Library/getAllLibrary');
Route::get('api/:version/user/check','api/:version.User/checkUser');
Route::post('api/:version/user/pwd_reset','api/:version.User/resetPassword');
Route::post('api/:version/user/login','api/:version.User/login');
Route::post('api/:version/user/avatar','api/:version.User/uploadAvatar');



Route::post('api/:version/sip2/login','api/:version.Sip2/login');
Route::get('api/:version/sip2/book','api/:version.Sip2/getBookDetail');
Route::get('api/:version/sip2/reader','api/:version.Sip2/getReaderDetail');
Route::post('api/:version/sip2/borrow','api/:version.Sip2/borrowBook');
Route::post('api/:version/sip2/return','api/:version.Sip2/returnBook');


Route::get('api/:version/jalis/libs','api/:version.Jalis/getLibraries');
Route::get('api/:version/jalis/reader_check','api/:version.Jalis/readerCheck');
Route::get('api/:version/jalis/search','api/:version.Jalis/search');
Route::get('api/:version/jalis/lib_holding','api/:version.Jalis/libHolding');
Route::get('api/:version/jalis/lib_items','api/:version.Jalis/libItems');
Route::get('api/:version/jalis/lib_items_avaiable','api/:version.Jalis/libItemsWithSip2Aaviable');
Route::get('api/:version/jalis/marc_detail','api/:version.Jalis/getBookDetailByMarc');



Route::get('api/:version/message/rule','api/:version.Message/getMessageRules');
Route::get('api/:version/message/one','api/:version.Message/getMessageRuleById');
Route::post('api/:version/message/send','api/:version.Message/sendMesageToUser');
Route::get('api/:version/message/messages','api/:version.Message/getUserMessageByUserId');
Route::delete('api/:version/message/delete','api/:version.Message/deleteUserMessage');
Route::post('api/:version/message/read','api/:version.Message/updateMessgeIsRead');




Route::post('api/:version/car/add','api/:version.Car/addBookToCar');
Route::delete('api/:version/car/delete','api/:version.Car/deleteBookFromCar');
Route::delete('api/:version/car/delete_all','api/:version.Car/deleteBookFormCarAll');
Route::get('api/:version/car/all','api/:version.Car/getAllBookByCar');



Route::post('api/:version/collect/add','api/:version.Collect/addToCollect');
Route::delete('api/:version/collect/delete','api/:version.Collect/deleteBookFromCollect');
Route::delete('api/:version/collect/delete_all','api/:version.Collect/deleteBookFormCollectAll');
Route::get('api/:version/collect/all','api/:version.Collect/getAllBookByCollect');


Route::post('api/:version/book/brower','api/:version.Book/recordBookBrower');
Route::get('api/:version/book/classification','api/:version.Book/getBookClassification');
Route::get('api/:version/book/books_byclass','api/:version.Book/getBooksByClassificationNum');
Route::get('api/:version/book/recommend_user','api/:version.Book/getRecommendByUser');
Route::get('api/:version/book/recommend_hot','api/:version.Book/getRecommendHot');
Route::get('api/:version/book/select_school','api/:version.Book/getOrderListWithLibItems');
Route::post('api/:version/book/school_order','api/:version.Book/getOrderListWithSchool');
Route::get('api/:version/book/wait_return_list','api/:version.Book/getWaitReturnBookListByUser');









