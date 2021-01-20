<?php
namespace app\swagger\route;
use think\facade\Route;

Route::group(function () {
    Route::get('create_api', function() { //生成api文件
        $openapi = \OpenApi\scan('../app/api')->toJson();
        $swagger_json_path = '../app/swagger/view/json/api_swagger.json';
        @file_put_contents($swagger_json_path, $openapi);
    });
    Route::get('api', function() { //获取api json数据
        header('Content-Type: application/json');
        $swagger_json_path = '../app/swagger/view/json/api_swagger.json';
        if(file_exists($swagger_json_path)){
            $jsonStr = file_get_contents($swagger_json_path);
            echo $jsonStr;
        }
    });
    Route::get('create_adminapi', function() { //生成adminapi文件
        $openapi = \OpenApi\scan('../app/adminapi')->toJson();
        $swagger_json_path = '../app/swagger/view/json/adminapi_swagger.json';
        @file_put_contents($swagger_json_path, $openapi);
    });
    
    Route::get('adminapi', function() { //获取adminapi json 数据
        header('Content-Type: application/json');
        $swagger_json_path = '../app/swagger/view/json/adminapi_swagger.json';
        if(file_exists($swagger_json_path)){
            $jsonStr = file_get_contents($swagger_json_path);
            echo $jsonStr;
        }
    });
    Route::get('index', 'Index/index');
});
