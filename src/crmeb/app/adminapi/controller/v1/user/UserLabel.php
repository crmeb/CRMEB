<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\services\user\UserLabelCateServices;
use app\services\user\UserLabelRelationServices;
use app\services\user\UserLabelServices;
use think\facade\App;

/**
 * 用户标签控制器
 * Class UserLabel
 * @package app\adminapi\controller\v1\user
 */
class UserLabel extends AuthController
{

    /**
     * UserLabel constructor.
     * @param App $app
     * @param UserLabelServices $service
     */
    public function __construct(App $app, UserLabelServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 标签列表
     * @return mixed
     */
    public function index($label_cate = 0)
    {
        return app('json')->success($this->service->getList(['label_cate' => $label_cate]));
    }

    /**
     * 添加修改标签表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function add()
    {
        [$id] = $this->request->getMore([
            ['id', 0],
        ], true);
        return app('json')->success($this->service->add((int)$id));
    }

    /**
     * 保存标签表单数据
     * @param int $id
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['label_cate', 0],
            ['label_name', ''],
        ]);
        if (!$data['label_name'] = trim($data['label_name'])) return app('json')->fail('会员标签不能为空！');
        $this->service->save((int)$data['id'], $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete()
    {
        list($id) = $this->request->getMore([
            ['id', 0],
        ], true);
        if (!$id) return app('json')->fail('数据不存在');
        $this->service->delLabel((int)$id);
        return app('json')->success('刪除成功！');
    }

    /**
     * 标签分类
     * @param UserLabelCateServices $services
     * @return mixed
     */
    public function getUserLabel(UserLabelCateServices $services, $uid)
    {
        return app('json')->success($services->getUserLabel((int)$uid));
    }

    /**
     * 设置用户标签
     * @param UserLabelRelationServices $services
     * @param $uid
     * @return mixed
     */
    public function setUserLabel(UserLabelRelationServices $services, $uid)
    {
        [$labels, $unLabelIds] = $this->request->postMore([
            ['label_ids', []],
            ['un_label_ids', []]
        ], true);
        if (!count($labels) && !count($unLabelIds)) {
            return app('json')->fail('缺少标签id');
        }
        if ($services->setUserLable($uid, $labels) && $services->unUserLabel($uid, $unLabelIds)) {
            return app('json')->success('设置成功');
        } else {
            return app('json')->fail('设置失败');
        }
    }
}
