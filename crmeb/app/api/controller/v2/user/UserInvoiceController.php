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
            app('json')->fail(100100);
        }
        return app('json')->success($this->services->getInvoice((int)$id));
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
        return app('json')->success($this->services->getUserList($uid, $data));
    }

    /**
     * 设置默认发票
     * @param Request $request
     * @return mixed
     */
    public function setDefaultInvoice(Request $request)
    {
        list($id) = $request->getMore([['id', 0]], true);
        if (!$id || !is_numeric($id)) return app('json')->fail(100100);
        $uid = (int)$request->uid();
        $this->services->setDefaultInvoice($uid, (int)$id);
        return app('json')->success(100014);
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
            return app('json')->success($defaultInvoice);
        }
        return app('json')->success([]);
    }

    /**
     * 修改 添加发票
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
        if (!$data['drawer_phone']) return app('json')->fail(410144);
        if (!check_phone($data['drawer_phone'])) return app('json')->fail(410018);
        if (!$data['name']) return app('json')->fail(410145);
        if (!in_array($data['header_type'], [1, 2])) {
            $data['header_type'] = empty($data['duty_number']) ? 1 : 2;
        }
        if ($data['header_type'] == 1 && !preg_match('/^[\x80-\xff]{2,60}$/', $data['name'])) {
            return app('json')->fail(410146);
        }
        if ($data['header_type'] == 2 && !preg_match('/^[0-9a-zA-Z&\(\)\（\）\x80-\xff]{2,150}$/', $data['name'])) {
            return app('json')->fail(410146);
        }
        if ($data['header_type'] == 2 && !$data['duty_number']) {
            return app('json')->fail(410147);
        }
        if ($data['header_type'] == 2 && !preg_match('/^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/', $data['duty_number'])) {
            return app('json')->fail(410148);
        }
        if ($data['card_number'] && !preg_match('/^[1-9]\d{11,19}$/', $data['card_number'])) {
            return app('json')->fail(410149);
        }
        $uid = (int)$request->uid();
        $re = $this->services->saveInvoice($uid, $data);
        if ($re) {
            return app('json')->success($re['type'] == 'edit' ? 100001 : $re['data']);
        } else {
            return app('json')->fail(100005);
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
        if (!$id || !is_numeric($id)) return app('json')->fail(100100);
        $uid = (int)$request->uid();
        $re = $this->services->delInvoice($uid, (int)$id);
        if ($re)
            return app('json')->success(100002);
        else
            return app('json')->fail(100008);
    }
}
