<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 9:33
 */

namespace app\core\behavior;

use think\Cache;
use think\Config;
use think\Request;
use think\Validate;
use app\core\implement\BehaviorIntterface;

/*
 * 请求数据验证
 * class RequestFilter
 *
 * */
class RequestFilterBehavior implements BehaviorIntterface
{
    /*
     * 请求数据验证默认行为
     * @retun
     * */
    public function run()
    {
        $request = Request::instance();
        $method = strtoupper($request->method());
        switch ($method) {
            case 'GET':
                $data = $request->get();
                break;
            case 'POST':
                $data = $request->post();
                break;
            case 'DELETE':
                $data = $request->delete();
                break;
            case 'PUT':
                $data = $request->put();
                break;
            default :
                $data = [];
                break;
        }
        //开启路由
        $hash = $request->routeInfo();
        if(Config::get('url_route_on') && isset($hash['rule'][1])){

        }else{
            $module=$request->module();
            $controller=$request->controller();
            $action=$request->action();

        }
    }


}