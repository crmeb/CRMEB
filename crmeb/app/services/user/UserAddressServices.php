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

use app\api\validate\user\AddressValidate;
use app\services\BaseServices;
use app\dao\user\UserAddressDao;
use app\services\shipping\SystemCityServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 *
 * Class UserAddressServices
 * @package app\services\user
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取一条数据
 * @method be($map, string $field = '') 验证数据是否存在
 */
class UserAddressServices extends BaseServices
{

    /**
     * UserAddressServices constructor.
     * @param UserAddressDao $dao
     */
    public function __construct(UserAddressDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单个地址
     * @param $id
     * @param $field
     * @return array
     */
    public function getAddress($id, $field = [])
    {
        return $this->dao->get($id, $field);
    }

    /**
     * 获取所有地址
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getAddressList(array $where, string $field = '*'): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $field, $page, $limit);
        $count = $this->getAddresCount($where);
        return compact('list', 'count');
    }

    /**
     * 获取某个用户的所有地址
     * @param int $uid
     * @param string $field
     * @return array
     */
    public function getUserAddressList(int $uid, string $field = '*'): array
    {
        [$page, $limit] = $this->getPageValue();
        $where = ['uid' => $uid];
        $where['is_del'] = 0;
        return $this->dao->getList($where, $field, $page, $limit);
    }

    /**
     * 获取用户默认地址
     * @param int $uid
     * @param string $field
     * @return array
     */
    public function getUserDefaultAddress(int $uid, string $field = '*')
    {
        return $this->dao->getOne(['uid' => $uid, 'is_default' => 1, 'is_del' => 0], $field);
    }

    /**
     * 获取条数
     * @param array $where
     * @return int
     */
    public function getAddresCount(array $where): int
    {
        return $this->dao->count($where);
    }

    /**
     * 添加地址
     * @param array $data
     * @return bool
     */
    public function create(array $data)
    {
        if (!$this->dao->save($data))
            throw new AdminException(100022);
        return true;
    }

    /**
     * 修改地址
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateAddress(int $id, array $data)
    {
        if (!$this->dao->update($id, $data))
            throw new AdminException(100007);
        return true;
    }

    /**
     * 设置默认定制
     * @param int $uid
     * @param int $id
     * @return bool
     */
    public function setDefault(int $uid, int $id)
    {
        if (!$this->getAddress($id)) {
            throw new ApiException(400648);
        }
        if (!$this->dao->update($uid, ['is_default' => 0], 'uid'))
            throw new ApiException(400649);
        if (!$this->dao->update($id, ['is_default' => 1]))
            throw new ApiException(400650);
        return true;
    }

    /**
     * 获取单个地址
     * @param int $id
     * @return mixed
     */
    public function address(int $id)
    {
        $addressInfo = $this->getAddress($id);
        if (!$addressInfo || $addressInfo['is_del'] == 1) {
            throw new ApiException(100026);
        }
        return $addressInfo->toArray();
    }

    /**
     * 添加|修改地址
     * @param int $uid
     * @param array $addressInfo
     * @return mixed
     */
    public function editAddress(int $uid, array $addressInfo)
    {
        if ($addressInfo['id'] == 0) {
            $where = [
                ['uid', '=', $uid],
                ['real_name', '=', $addressInfo['real_name']],
                ['phone', '=', $addressInfo['phone']],
                ['detail', '=', $addressInfo['detail']],
                ['is_del', '=', 0]
            ];
            if (isset($addressInfo['address']['city_id'])) {
                $where += ['city_id', '=', $addressInfo['address']['city_id']];
            }
            $res = $this->dao->getCount($where);
            if ($res) throw new ApiException(400651);
        }

        if ($addressInfo['type'] == 1 && !$addressInfo['id']) {
            $city = $addressInfo['address']['city'];
            /** @var SystemCityServices $systemCity */
            $systemCity = app()->make(SystemCityServices::class);
            $cityInfo = $systemCity->getOne([['name', '=', $city], ['parent_id', '<>', 0]]);
            if ($cityInfo && $cityInfo['city_id']) {
                $addressInfo['address']['city_id'] = $cityInfo['city_id'];
            } else {
                $cityInfo = $systemCity->getOne([['name', 'like', "%$city%"], ['parent_id', '<>', 0]]);
                if (!$cityInfo) {
                    throw new ApiException(400652);
                }
                $addressInfo['address']['city_id'] = $cityInfo['city_id'];
            }
        }
        if (!isset($addressInfo['address']['city_id']) || $addressInfo['address']['city_id'] == 0) throw new ApiException(100022);
        $addressInfo['province'] = $addressInfo['address']['province'];
        $addressInfo['city'] = $addressInfo['address']['city'];
        $addressInfo['city_id'] = $addressInfo['address']['city_id'] ?? 0;
        $addressInfo['district'] = $addressInfo['address']['district'];
        $addressInfo['is_default'] = (int)$addressInfo['is_default'] == true ? 1 : 0;
        $addressInfo['uid'] = $uid;
        unset($addressInfo['address'], $addressInfo['type']);
        //数据验证
        validate(AddressValidate::class)->check($addressInfo);
        $address_check = [];
        if ($addressInfo['id']) {
            $address_check = $this->getAddress((int)$addressInfo['id']);
        }
        if ($address_check && $address_check['is_del'] == 0 && $address_check['uid'] = $uid) {
            $id = (int)$addressInfo['id'];
            unset($addressInfo['id']);
            if (!$this->dao->update($id, $addressInfo, 'id')) {
                throw new ApiException(100007);
            }
            if ($addressInfo['is_default']) {
                $this->setDefault($uid, $id);
            }
            return ['type' => 'edit', 'msg' => '编辑地址成功', 'data' => []];
        } else {
            $addressInfo['add_time'] = time();

            //首次添加地址，自动设置为默认地址
            $addrCount = $this->getAddresCount(['uid' => $uid]);
            if (!$addrCount) $addressInfo['is_default'] = 1;

            if (!$address = $this->dao->save($addressInfo)) {
                throw new ApiException(100022);
            }
            if ($addressInfo['is_default']) {
                $this->setDefault($uid, (int)$address->id);
            }
            return ['type' => 'add', 'msg' => '添加地址成功', 'data' => ['id' => $address->id]];
        }
    }

    /**
     * 删除地址
     * @param int $uid
     * @param int $id
     * @return bool
     */
    public function delAddress(int $uid, int $id)
    {
        $addressInfo = $this->getAddress($id);
        if (!$addressInfo || $addressInfo['is_del'] == 1 || $addressInfo['uid'] != $uid) {
            throw new ApiException(100026);
        }
        if ($this->dao->update($id, ['is_del' => '1'], 'id'))
            return true;
        else
            throw new ApiException(100008);
    }

    /**
     * 设置默认用户地址
     * @param $id
     * @param $uid
     * @return bool
     */
    public function setDefaultAddress(int $id, int $uid)
    {
        $res1 = $this->dao->update($uid, ['is_default' => 0], 'uid');
        $res2 = $this->dao->update(['id' => $id, 'uid' => $uid], ['is_default' => 1]);
        $res = $res1 !== false && $res2 !== false;
        return $res;
    }
}
