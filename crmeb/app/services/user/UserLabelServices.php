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
use app\dao\user\UserLabelDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class UserLabelServices
 * @package app\services\user
 *  * @method getColumn(array $where, string $field, string $key = '') 获取某个字段数组
 */
class UserLabelServices extends BaseServices
{

    /**
     * UserLabelServices constructor.
     * @param UserLabelDao $dao
     */
    public function __construct(UserLabelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某一本标签
     * @param $id
     * @return array|\think\Model|null
     */
    public function getLable($id)
    {
        return $this->dao->get($id);
    }

    /**
     * 获取所有用户标签
     * @param array $where
     * @param array|string[] $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLabelList(array $where = [], array $field = ['*'])
    {
        return $this->dao->getList(0, 0, $where, $field);
    }

    /**
     * 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($page, $limit, $where);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 添加修改标签表单
     * @param int $id
     * @param int $cateId
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function add(int $id, int $cateId)
    {
        $label = $this->getLable($id);
        $field = array();
        /** @var UserLabelCateServices $service */
        $service = app()->make(UserLabelCateServices::class);
        $options = [];
        foreach ($service->getLabelCateAll() as $item) {
            $options[] = ['value' => $item['id'], 'label' => $item['name']];
        }
        if (!$label) {
            $title = '添加标签';
            $field[] = Form::select('label_cate', '标签分类', $cateId)->setOptions($options);
            $field[] = Form::input('label_name', '标签名称', '')->required();
        } else {
            $title = '修改标签';
            $field[] = Form::select('label_cate', '分类', (int)$label->getData('label_cate'))->setOptions($options);
            $field[] = Form::hidden('id', $label->getData('id'));
            $field[] = Form::input('label_name', '标签名称', $label->getData('label_name'))->required('请填写标签名称');
        }
        return create_form($title, $field, Url::buildUrl('/user/user_label/save'), 'POST');
    }

    /**
     * 保存标签表单数据
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function save(int $id, array $data)
    {
        if (!$data['label_cate']) {
            throw new AdminException(400669);
        }
        $levelName = $this->dao->getOne(['label_name' => $data['label_name'], 'label_cate' => $data['label_cate']]);
        if ($id) {
            if (!$this->getLable($id)) {
                throw new AdminException(100026);
            }
            if ($levelName && $id != $levelName['id']) {
                throw new AdminException(400670);
            }
            if ($this->dao->update($id, $data)) {
                return true;
            } else {
                throw new AdminException(100007);
            }
        } else {
            unset($data['id']);
            if ($levelName) {
                throw new AdminException(400670);
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
     * @param $id
     * @throws \Exception
     */
    public function delLabel(int $id)
    {
        if ($this->getLable($id)) {
            if (!$this->dao->delete($id)) {
                throw new AdminException(100008);
            }
        }
        return true;
    }

    /**
     * tree处理 分类、标签数据
     * @param array $cate
     * @param array $label
     * @return array
     */
    public function get_tree_children(array $cate, array $label)
    {
        if ($cate) {
            foreach ($cate as $key => $value) {
                if ($label) {
                    foreach ($label as $k => $item) {
                        if ($value['id'] == $item['label_cate']) {
                            $cate[$key]['children'][] = $item;
                            unset($label[$k]);
                        }
                    }
                } else {
                    $cate[$key]['children'] = [];
                }
            }
        }
        return $cate;
    }
}
