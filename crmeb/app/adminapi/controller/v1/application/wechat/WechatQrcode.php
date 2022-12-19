<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\application\wechat;

use app\adminapi\controller\AuthController;
use app\services\wechat\WechatQrcodeCateServices;
use app\services\wechat\WechatQrcodeRecordServices;
use app\services\wechat\WechatQrcodeServices;
use think\facade\App;

class WechatQrcode extends AuthController
{
    protected $qrcodeCateServices;
    protected $wechatQrcodeServices;
    protected $qrcodeRecordServices;

    /**
     * WechatQrcode constructor.
     * @param App $app
     * @param WechatQrcodeCateServices $services
     */
    public function __construct(App $app, WechatQrcodeCateServices $qrcodeCateServices, WechatQrcodeServices $wechatQrcodeServices, WechatQrcodeRecordServices $qrcodeRecordServices)
    {
        parent::__construct($app);
        $this->qrcodeCateServices = $qrcodeCateServices;
        $this->wechatQrcodeServices = $wechatQrcodeServices;
        $this->qrcodeRecordServices = $qrcodeRecordServices;
    }

    /**
     * 分类列表
     * @return mixed
     */
    public function getCateList()
    {
        $data = $this->qrcodeCateServices->getCateList();
        $count = $this->qrcodeCateServices->count(['is_del' => 0]);
        return app('json')->success(compact('data', 'count'));
    }

    /**
     * 添加编辑表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createForm($id)
    {
        return app('json')->success($this->qrcodeCateServices->createForm($id));
    }

    /**
     * 保存数据
     * @return mixed
     */
    public function saveCate()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['cate_name', '']
        ]);
        $this->qrcodeCateServices->saveData($data);
        return app('json')->success(100000);
    }

    /**
     * 删除分类
     * @param $id
     * @return mixed
     */
    public function delCate($id)
    {
        $this->qrcodeCateServices->delCate($id);
        return app('json')->success(100000);
    }

    /**
     * 保存渠道码
     * @param $id
     * @return mixed
     */
    public function saveQrcode($id = 0)
    {
        $data = $this->request->postMore([
            ['uid', 0],
            ['name', ''],
            ['image', ''],
            ['cate_id', 0],
            ['label_id', []],
            ['type', 0],
            ['content', ''],
            ['time', 0],
        ]);
        $this->wechatQrcodeServices->saveQrcode($id, $data);
        return app('json')->success(100000);
    }

    /**
     * 获取渠道码列表
     * @return mixed
     */
    public function qrcodeList()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['cate_id', 0]
        ]);
        $where['is_del'] = 0;
        $data = $this->wechatQrcodeServices->qrcodeList($where);
        return app('json')->success($data);
    }

    /**
     * 获取详情
     * @param int $id
     * @return mixed
     */
    public function qrcodeInfo($id = 0)
    {
        if (!$id) return app('json')->fail(100100);
        $info = $this->wechatQrcodeServices->qrcodeInfo($id);
        return app('json')->success($info);
    }

    /**
     * 删除渠道码
     * @param int $id
     * @return mixed
     */
    public function delQrcode($id = 0)
    {
        if (!$id) return app('json')->fail(100100);
        $this->wechatQrcodeServices->update($id, ['is_del' => 1]);
        return app('json')->success(100002);
    }

    /**
     * 切换状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        if (!$id) return app('json')->fail(100100);
        $this->wechatQrcodeServices->update($id, ['status' => $status]);
        return app('json')->success(100014);
    }

    /**
     * 用户列表
     * @param $qid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userList($qid)
    {
        return app('json')->success($this->qrcodeRecordServices->userList($qid));
    }


    /**
     * 渠道码统计
     * @param $qid
     * @return mixed
     */
    public function qrcodeStatistic($qid)
    {
        [$time] = $this->request->getMore([
            ['time', ''],
        ], true);
        $where['qid'] = $qid;
        return app('json')->success($this->qrcodeRecordServices->qrcodeStatistic($where, $time));
    }
}
