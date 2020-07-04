<?php

namespace app\admin\controller;


use app\admin\model\system\SystemCity;
use crmeb\services\ExpressService;
use crmeb\services\HttpService;
use crmeb\services\JsonService;
use crmeb\utils\Queue;
use think\facade\Db;
use think\facade\Queue as QueueJob;

class Test
{
    public function index($page = 1, $limit = 50, $level = 0)
    {


//        var_dump(is_file('uploads/981_1_user.jpg'));


//        $appCode = '4a5b910fc344434cacb2edd36e0e56aa';
//        $list = ExpressService::query('75341495702624');
//        dump($list);
//        $data = [];
//        foreach ($list['data'] as $item) {
//            $data[] = [
//                'level' => $item['level'],
//                'parent_id' => $item['parent_id'],
//                'area_code' => $item['area_code'],
//                'name' => $item['name'],
//                'merger_name' => $item['merger_name'],
//                'lng' => $item['lng'],
//                'lat' => $item['lat'],
//                'city_id' => $item['id'],
//            ];
//        }
//        $res = SystemCity::insertAll($data);
//        return JsonService::successful(['count' => $res]);
        $sql = Db::name('system_menus')->whereIn('paid', function ($query) {
            $query->name('system_menus')->where('menu_name', '评论管理')->find();
        })->fetchSql(true)->delete();
        var_dump($sql);
    }


}