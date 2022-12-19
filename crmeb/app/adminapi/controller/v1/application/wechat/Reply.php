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

use app\services\other\QrcodeServices;
use EasyWeChat\Core\Exceptions\HttpException;
use app\adminapi\controller\AuthController;
use app\services\wechat\WechatReplyServices;
use app\services\wechat\WechatKeyServices;
use think\facade\App;

/**
 * 关键字管理  控制器
 * Class Reply
 * @package app\admin\controller\wechat
 */
class Reply extends AuthController
{
    /**
     * 构造方法
     * Menus constructor.
     * @param App $app
     * @param WechatReplyServices $services
     */
    public function __construct(App $app, WechatReplyServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 关注回复
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function reply()
    {
        $where = $this->request->getMore([
            ['key', ''],
        ]);
        if ($where['key'] == '') return app('json')->fail(100100);
        $info = $this->services->getDataByKey($where['key']);
        return app('json')->success(compact('info'));
    }

    /**
     * 关键字回复列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['key', ''],
            ['type', ''],
        ]);
        $list = $this->services->getKeyAll($where);
        return app('json')->success($list);
    }

    /**
     * 关键字详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read($id)
    {
        $info = $this->services->getKeyInfo($id);
        return app('json')->success(compact('info'));
    }

    /**
     * 保存关键字
     * @param int $id
     * @return mixed
     */
    public function save($id = 0)
    {
        $data = $this->request->postMore([
            'key',
            'type',
            ['status', 0],
            ['data', []],
        ]);
        try {
            if (!isset($data['key']) && empty($data['key']))
                return app('json')->fail(400239);
            if (!isset($data['type']) && empty($data['type']))
                return app('json')->fail(400240);
            if (!in_array($data['type'], $this->services->replyType()))
                return app('json')->fail(400241);

            if (!isset($data['data']) || !is_array($data['data']))
                return app('json')->fail(400242);
            $res = $this->services->redact($data['data'], $id, $data['key'], $data['type'], $data['status']);
            if (!$res)
                return app('json')->fail(100006);
            else
                return app('json')->success(100000, $data);
        } catch (HttpException $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 删除关键字
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$this->services->delete($id)) {
            return app('json')->fail(100008);
        } else {
            /** @var WechatKeyServices $keyServices */
            $keyServices = app()->make(WechatKeyServices::class);
            $res = $keyServices->delete($id, 'reply_id');
            if (!$res) {
                return app('json')->fail(100008);
            }
        }
        return app('json')->success(100002);
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return app('json')->fail(100100);
        $this->services->update($id, ['status' => $status], 'id');
        return app('json')->success(100014);
    }

    /**
     * 生成关注回复二维码
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function code_reply($id)
    {
        if (!$id) {
            return app('json')->fail('100100');
        }
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        $code = $qrcode->getForeverQrcode('reply', $id);
        if (!$code['ticket']) {
            return app('json')->fail(400237);
        }
        return app('json')->success($code->toArray());
    }

}
