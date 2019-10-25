<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use crmeb\services\JsonService;
use crmeb\services\SystemConfigService;
use app\admin\model\system\SystemStore as SystemStoreModel;
use crmeb\services\UtilService;

/**
 * 门店管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemStore extends AuthController
{

    /*
     * 门店设置
     * */
    public function index()
    {
        $store = SystemStoreModel::getStoreDispose();
        $this->assign(compact('store'));
        return $this->fetch();
    }

    /*
     * 位置选择
     * */
    public function select_address()
    {
        $key = SystemConfigService::get('tengxun_map_key');
        if(!$key) return $this->failed('请前往设置->系统设置->物流配置 配置腾讯地图KEY','#');
        $this->assign(compact('key'));
        return $this->fetch();
    }

    /*
     * 保存修改门店信息
     * param int $id
     * */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['name',''],
            ['introduction',''],
            ['image',''],
            ['phone',''],
            ['address',''],
            ['detailed_address',''],
            ['latlng',''],
            ['valid_time',[]],
            ['day_time',[]],
        ]);
        SystemStoreModel::beginTrans();
        try{
            $data['address'] = implode(',',$data['address']);
            $data['latlng'] = explode(',',$data['latlng']);
            if(!isset($data['latlng'][0]) || !isset($data['latlng'][1])) return JsonService::fail('请选择门店位置');
            $data['latitude'] = $data['latlng'][0];
            $data['longitude'] = $data['latlng'][1];
            $data['valid_time'] = implode(' - ',$data['valid_time']);
            $data['day_time'] = implode(' - ',$data['day_time']);
            unset($data['latlng']);
            if($data['image'] && strstr($data['image'],'http') === false){
                $site_url = SystemConfig::getConfigValue('site_url');
                $data['image'] = $site_url.$data['image'];
            }
            if($id){
                if(SystemStoreModel::where('id',$id)->update($data)){
                    SystemStoreModel::commitTrans();
                    return JsonService::success('修改成功');
                }else{
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('修改失败或者您没有修改什么！');
                }
            }else{
                $data['add_time'] = time();
                $data['is_show'] = 1;
                if($res=SystemStoreModel::create($data)){
                    SystemStoreModel::commitTrans();
                    return JsonService::success('保存成功',['id'=>$res->id]);
                }else{
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('保存失败！');
                }
            }
        }catch (\Exception $e){
            SystemStoreModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }
}