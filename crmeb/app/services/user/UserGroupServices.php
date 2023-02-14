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

use app\services\BaseServices;
use app\dao\user\UserGroupDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class UserGroupServices
 * @package app\services\user
 */
class UserGroupServices extends BaseServices
{

    /**
     * UserGroupServices constructor.
     * @param UserGroupDao $dao
     */
    public function __construct(UserGroupDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某一个分组
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getGroup(int $id)
    {
        return $this->dao->get($id);
    }

    /**
     * 获取分组列表
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGroupList(string $field = 'id,group_name', bool $is_page = false): array
    {
        $page = $limit = 0;
        if ($is_page) {
            [$page, $limit] = $this->getPageValue();
            $count = $this->dao->count([]);
        }
        $list = $this->dao->getList([], $field, $page, $limit);

        return $is_page ? compact('list', 'count') : $list;
    }

    /**
     * 获取一些用户的分组名称
     * @param array $ids
     * @return array
     */
    public function getUsersGroupName(array $ids)
    {
        return $this->dao->getColumn([['id', 'IN', $ids]], 'group_name', 'id');
    }

    /**
     * 添加/修改分组页面
     * @param int $id
     * @return string
     */
    public function add(int $id)
    {
        $group = $this->getGroup($id);
        $field = array();
        if (!$group) {
            $title = '添加分组';
            $field[] = Form::input('group_name', '分组名称', '')->required();
        } else {
            $title = '修改分组';
            $field[] = Form::hidden('id', $id);
            $field[] = Form::input('group_name', '分组名称', $group->getData('group_name'))->required();
        }
        return create_form($title, $field, Url::buildUrl('/user/user_group/save'), 'POST');
    }

    /**
     * 添加|修改
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function save(int $id, array $data)
    {
        $groupName = $this->dao->getOne(['group_name' => $data['group_name']]);
        if ($id) {
            if (!$this->getGroup($id)) {
                throw new AdminException(100026);
            }
            if ($groupName && $id != $groupName['id']) {
                throw new AdminException(400666);
            }
            if ($this->dao->update($id, $data)) {
                return true;
            } else {
                throw new AdminException(100007);
            }
        } else {
            unset($data['id']);
            if ($groupName) {
                throw new AdminException(400666);
            }
            if ($this->dao->save($data)) {
                return true;
            } else {
                throw new AdminException(100022);
            }
        }
    }

    /**
     * 删除
     * @param int $id
     * @return string
     */
    public function delGroup(int $id)
    {
        if ($this->getGroup($id)) {
            if (!$this->dao->delete($id)) {
                throw new AdminException(100008);
            }
        }
        return '删除成功!';
    }
}
