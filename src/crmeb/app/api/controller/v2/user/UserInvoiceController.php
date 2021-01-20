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

namespace app\api\controller\v2\user;


use app\services\user\UserInvoiceServices;
use think\Request;

/**
 * Class UserInvoiceController
 * @package app\api\controller\v2\user
 */
class UserInvoiceController
{
    /**
     * @var UserInvoiceServices
     */
    protected $services;

    /**
     * UserInvoiceController constructor.
     * @param UserInvoiceServices $services
     */
    public function __construct(UserInvoiceServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取单个发票信息
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function invoice($id)
    {
        if (!$id) {
            app('json')->fail('缺少参数');
        }
        return app('json')->successful($this->services->getInvoice((int)$id));
    }

    /**
     * 发票列表
     * @param Request $request
     * @return mixed
     */
    public function invoiceList(Request $request)
    {
        $data = $request->postMore([
            ['header_type', ''],
            ['type', '']
        ]);
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->getUserList($uid, $data));
    }

    /**
     * 设置默认发票
     * @param Request $request
     * @return mixed
     */
    public function setDefaultInvoice(Request $request)
    {
        list($id) = $request->getMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $uid = (int)$request->uid();
        $this->services->setDefaultInvoice($uid, (int)$id);
        return app('json')->successful('设置成功');
    }

    /**
     * 获取默认发票
     * @param Request $request
     * @return mixed
     */
    public function getDefaultInvoice(Request $request)
    {
        [$type] = $request->postMore(['type', 1], true);
        $uid = (int)$request->uid();
        $defaultInvoice = $this->services->getUserDefaultInvoice($uid, (int)$type);
        if ($defaultInvoice) {
            $defaultInvoice = $defaultInvoice->toArray();
            return app('json')->successful('ok', $defaultInvoice);
        }
        return app('json')->successful('empty', []);
    }

    /**
     * 修改 添加地址
     * @param Request $request
     * @return mixed
     */
    public function saveInvoice(Request $request)
    {
        $data = $request->postMore([
            [['id', 'd'], 0],
            [['header_type', 'd'], 1],
            [['type', 'd'], 1],
            ['drawer_phone', ''],
            ['email', ''],
            ['name', ''],
            ['duty_number', ''],
            ['tell', ''],
            ['address', ''],
            ['bank', ''],
            ['card_number', ''],
            ['is_default', 0]
        ]);
        if (!$data['drawer_phone']) return app('json')->fail('请填写开票手机号');
        if (!check_phone($data['drawer_phone'])) return app('json')->fail('手机号码格式不正确');
        if (!$data['name']) return app('json')->fail('请填写发票抬头（开具发票企业名称）');
        if ($data['header_type'] == 1 && !preg_match('/^[\x80-\xff]{2,60}$/', $data['name'])) {
            return app('json')->fail('请填写正确的发票抬头（开具发票企业名称）');
        }
        if ($data['header_type'] == 2 && !preg_match('/^[0-9a-zA-Z&\(\)\（\）\x80-\xff]{2,150}$/', $data['name'])) {
            return app('json')->fail('请填写正确的发票抬头（开具发票企业名称）');
        }
        if ($data['header_type'] == 2 && !$data['duty_number']) {
            return app('json')->fail('请填写发票税号');
        }
        if ($data['header_type'] == 2 && !preg_match('/^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/', $data['duty_number'])) {
            return app('json')->fail('请填写正确的发票税号');
        }
        if ($data['card_number'] && !preg_match('/^[1-9]\d{11,19}$/', $data['card_number'])) {
            return app('json')->fail('请填写正确的银行卡号');
        }
        $uid = (int)$request->uid();
        $re = $this->services->saveInvoice($uid, $data);
        if ($re) {
            return app('json')->success($re['type'] == 'edit' ? $re['msg'] : $re['data']);
        } else {
            return app('json')->fail('处理失败');
        }

    }

    /**
     * 删除发票
     * @param Request $request
     * @return mixed
     */
    public function delInvoice(Request $request)
    {
        [$id] = $request->postMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $uid = (int)$request->uid();
        $re = $this->services->delInvoice($uid, (int)$id);
        if ($re)
            return app('json')->successful();
        else
            return app('json')->fail('删除地址失败!');
    }
}
