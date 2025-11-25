<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\services\product\product\StoreProductParamServices;
use think\facade\App;

/**
 * 商品参数
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/12/17
 */
class StoreProductParam extends AuthController
{
    /**
     * @param App $app
     * @param StoreProductParamServices $services
     */
    public function __construct(App $app, StoreProductParamServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取参数列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamList()
    {
        $where = $this->request->getMore([
            ['name', '']
        ]);
        return app('json')->success($this->services->getParamList($where));
    }

    /**
     * 获取参数详情
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamInfo($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $info = $this->services->getParamInfo($id);
        return app('json')->success($info);
    }

    /**
     * 获取参数值
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function getParamValue($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $info = $this->services->getParamValue($id);
        return app('json')->success($info);
    }

    /**
     * 保存参数
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function saveParamData($id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['value', []],
            ['sort', 0],
            ['status', 1]
        ]);
        if (!$data['name']) return app('json')->fail('请输入参数名称');
        if (!count($data['value'])) return app('json')->fail('请输入参数值');
        $this->services->saveParamData($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 修改参数状态
     * @param $id
     * @param $status
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function setParamStatus($id, $status)
    {
        if (!$id) return app('json')->fail('参数错误');
        $this->services->setParamStatus($id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 删除参数
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/12/17
     */
    public function delParamData($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $this->services->delParamData($id);
        return app('json')->success('删除成功');
    }
}
