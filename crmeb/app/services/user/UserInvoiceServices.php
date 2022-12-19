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
declare (strict_types=1);

namespace app\services\user;


use app\dao\user\UserInvoiceDao;
use app\services\BaseServices;
use crmeb\exceptions\ApiException;


/**
 * Class UserInvoiceServices
 * @package app\services\user
 */
class UserInvoiceServices extends BaseServices
{
    /**
     * LiveAnchorServices constructor.
     * @param UserInvoiceDao $dao
     */
    public function __construct(UserInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 检测系统设置发票功能
     * @param bool $is_speclial
     * @return bool
     */
    public function invoiceFuncStatus(bool $is_speclial = true)
    {
        $invoice = (bool)sys_config('invoice_func_status', 0);
        if ($is_speclial) {
            $specialInvoice = sys_config('special_invoice_status', 0);
            return ['invoice_func' => $invoice, 'special_invoice' => $invoice && $specialInvoice];
        }
        return $invoice;
    }

    /**
     * 获取单个发票信息
     * @param int $id
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInvoice(int $id, int $uid = 0)
    {
        $invoice = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        if (!$invoice || ($uid && $invoice['uid'] != $uid)) {
            return [];
        }
        return $invoice->toArray();
    }

    /**
     * 检测该发票是否可用
     * @param int $id
     * @param int $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkInvoice(int $id, int $uid)
    {
        $invoice = $this->getInvoice($id, $uid);
        if (!$invoice) {
            throw new ApiException(100026);
        }
        $invoice_func = $this->invoiceFuncStatus();
        if (!$invoice_func['invoice_func']) {
            throw new ApiException(410280);
        }
        //专用发票
        if ($invoice['type'] == 2) {
            if (!$invoice_func['special_invoice']) {
                throw new ApiException(410281);
            }
        }
        return $invoice;
    }


    /**
     * 获取某个用户发票列表
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserList(int $uid, $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $where['uid'] = $uid;
        return $this->dao->getList($where, '*', $page, $limit);
    }

    /**
     * 获取某个用户默认发票
     * @param int $uid
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserDefaultInvoice(int $uid, int $type, string $field = '*')
    {
        return $this->dao->getOne(['uid' => $uid, 'is_default' => 1, 'is_del' => 0, 'type' => $type], $field);
    }

    /**
     * 添加|修改
     * @param int $uid
     * @param array $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveInvoice(int $uid, array $data)
    {
        $id = (int)$data['id'];
        $data['uid'] = $uid;
        unset($data['id']);
        $invoice = $this->dao->get(['uid' => $uid, 'name' => $data['name'], 'drawer_phone' => $data['drawer_phone'], 'is_del' => 0]);
        if ($id) {
            if ($invoice && $id != $invoice['id']) {
                throw new ApiException(410282);
            }
            if ($this->dao->update($id, $data, 'id')) {
                if ($data['is_default']) {
                    $this->setDefaultInvoice($uid, $id);
                }
                return ['type' => 'edit', 'msg' => '修改发票成功', 'data' => []];
            } else {
                throw new ApiException(100007);
            }
        } else {
            if ($invoice) {
                throw new ApiException(410282);
            }
            if ($add_invoice = $this->dao->save($data)) {
                $id = (int)$add_invoice['id'];
                if ($data['is_default']) {
                    $this->setDefaultInvoice($uid, $id);
                }
                return ['type' => 'add', 'msg' => '添加发票成功', 'data' => ['id' => $id]];
            } else {
                throw new ApiException(100022);
            }
        }
    }

    /**
     * 设置默认发票
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setDefaultInvoice(int $uid, int $id)
    {
        if (!$invoice = $this->getInvoice($id)) {
            throw new ApiException(100026);
        }
        if ($invoice['uid'] != $uid) {
            throw new ApiException(100101);
        }
        if (!$this->dao->setDefault($uid, $id, $invoice['header_type'], $invoice['type'])) {
            throw new ApiException(410283);
        }
        return true;
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delInvoice(int $uid, int $id)
    {
        if ($invoice = $this->getInvoice($id)) {
            if ($invoice['uid'] != $uid) {
                throw new ApiException(100101);
            }
            if (!$this->dao->update($id, ['is_del' => 1])) {
                throw new ApiException(100008);
            }
        }
        return true;
    }

}
