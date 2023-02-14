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

namespace app\services\user;


use app\dao\other\CategoryDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder;
use think\Model;

/**
 * Class UserLabelCateServices
 * @package app\services\user
 * @method delete($id, ?string $key = null) 删除
 * @method update($id, array $data, ?string $key = null) 更新数据
 * @method save(array $data) 保存数据
 * @method array|Model|null get($id, ?array $field = [], ?array $with = []) 获取一条数据
 * @method getAll(array $with = []) 获取全部标签分类
 */
class UserLabelCateServices extends BaseServices
{
    /**
     * 标签分类缓存
     * @var string
     */
    protected $cacheName = 'label_list_all';

    /**
     * UserLabelCateServices constructor.
     * @param CategoryDao $dao
     */
    public function __construct(CategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取标签分类
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLabelList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getCateList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除分类缓存
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteCateCache()
    {
        return CacheService::delete($this->cacheName);
    }

    /**
     * 获取标签全部分类
     * @return bool|mixed|null
     */
    public function getLabelCateAll()
    {
        return CacheService::remember($this->cacheName, function () {
            return $this->dao->getCateList(['type' => 0]);
        });
    }

    /**
     * 标签分类表单
     * @param array $cataData
     * @return mixed
     */
    public function labelCateForm(array $cataData = [])
    {
        $f[] = FormBuilder::input('name', '分类名称', $cataData['name'] ?? '')->required();
        $f[] = FormBuilder::number('sort', '排序', (int)($cataData['sort'] ?? 0));
        return $f;
    }

    /**
     * 创建表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        return create_form('添加标签分类', $this->labelCateForm(), $this->url('/user/user_label_cate'), 'POST');
    }

    /**
     * 修改分类标签表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateForm(int $id)
    {
        $labelCate = $this->dao->get($id);
        if (!$labelCate) {
            throw new AdminException(100026);
        }
        return create_form('编辑标签分类', $this->labelCateForm($labelCate->toArray()), $this->url('user/user_label_cate/' . $id), 'PUT');
    }

    /**
     * 用户标签列表
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLabel(int $uid)
    {
        $list = $this->dao->getAll(['type' => 0], ['label']);
        /** @var UserLabelRelationServices $services */
        $services = app()->make(UserLabelRelationServices::class);
        $labelIds = $services->getUserLabels($uid) ?? [];
        foreach ($list as $key => &$item) {
            if (is_array($item['label'])) {
                if (!$item['label']) {
                    unset($list[$key]);
                    continue;
                }
                foreach ($item['label'] as &$value) {
                    if (in_array($value['id'], $labelIds)) {
                        $value['disabled'] = true;
                    } else {
                        $value['disabled'] = false;
                    }
                }
            }

        }
        return array_merge($list);
    }
}
