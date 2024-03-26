<?php

namespace app\services\out;

use app\dao\out\OutInterfaceDao;
use app\Request;
use app\services\BaseServices;
use app\services\system\SystemRouteCateServices;
use app\services\system\SystemRouteServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\AuthException;

class OutInterfaceServices extends BaseServices
{
    public function __construct(OutInterfaceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 验证对外接口权限
     * @param Request $request
     * @return bool
     */
    public function verifyAuth(Request $request)
    {
        $rule = trim(strtolower($request->rule()->getRule()));
        $method = trim(strtolower($request->method()));
        $authList = app()->make(SystemRouteServices::class)->getColumn([['id', 'in', $request->outInfo()['rules']], ['app_name', '=', 'outapi']], 'method,path');
        $rolesAuth = [];
        foreach ($authList as $item) {
            $rolesAuth[trim(strtolower($item['method']))][] = trim(strtolower(str_replace(' ', '', $item['path'])));
        }
        if (in_array($rule, $rolesAuth[$method])) {
            return true;
        }
        $rule = str_replace('<', '{', $rule);
        $rule = str_replace('>', '}', $rule);
        if (in_array($rule, $rolesAuth[$method])) {
            return true;
        } else {
            throw new AuthException(110000);
        }
    }

    /**
     * 对外接口列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function outInterfaceList(): array
    {
        // 获取系统路由分类列表
        $list = app()->make(SystemRouteCateServices::class)->selectList(['app_name' => 'outapi'], 'id,pid,name,name as title')->toArray();
        // 获取系统路由列表
        $data = app()->make(SystemRouteServices::class)->selectList(['app_name' => 'outapi'], 'id,cate_id as pid,name,name as title')->toArray();
        // 遍历分类列表，将分类下的路由添加到对应的子节点中
        foreach ($list as &$item) {
            foreach ($data as $k => $v) {
                if ($item['id'] == $v['pid']) {
                    $item['children'][] = $v;
                }
            }
        }
        // 返回完整的外部接口列表
        return $list;
    }


    /**
     * 新增对外接口文档
     * @param $id
     * @param $data
     * @return bool
     */
    public function saveInterface($id, $data)
    {
        $data['request_params'] = json_encode($data['request_params']);
        $data['return_params'] = json_encode($data['return_params']);
        $data['error_code'] = json_encode($data['error_code']);
        if ($id) {
            $res = $this->dao->update($id, $data);
        } else {
            $res = $this->dao->save($data);
        }
        if (!$res) throw new AdminException(100006);
        return true;
    }

    /**
     * 对外接口文档
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function interfaceInfo($id)
    {
        if (!$id) throw new AdminException(100100);
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException(100026);
        $info = $info->toArray();
        $info['request_params'] = json_decode($info['request_params']);
        $info['return_params'] = json_decode($info['return_params']);
        $info['error_code'] = json_decode($info['error_code']);
        return $info;
    }

    /**
     * 修改接口名称
     * @param $data
     * @return bool
     */
    public function editInterfaceName($data)
    {
        $res = $this->dao->update($data['id'], ['name' => $data['name']]);
        if (!$res) throw new AdminException(100007);
        return true;
    }

    /**
     * 删除接口
     * @param $id
     * @return bool
     */
    public function delInterface($id)
    {
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException(100008);
        return true;
    }
}