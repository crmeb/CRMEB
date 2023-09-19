<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\article;

use app\dao\article\ArticleCategoryDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use crmeb\utils\Arr;
use think\facade\Route as Url;

/**
 * Class ArticleCategoryServices
 * @package app\services\article
 * @method getArticleCategory()
 * @method getArticleTwoCategory()
 */
class ArticleCategoryServices extends BaseServices
{
    /**
     * ArticleCategoryServices constructor.
     * @param ArticleCategoryDao $dao
     */
    public function __construct(ArticleCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取文章分类列表
     * @param array $where
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        $list = $this->dao->getList($where);
        $list = get_tree_children($list);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 生成创建修改表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $id)
    {
        $method = 'POST';
        $url = '/cms/category';
        if ($id) {
            $info = $this->dao->get($id);
            $method = 'PUT';
            $url = $url . '/' . $id;
            $pid = $info['pid'];
        } else {
            $pid = '';
        }
        $f = array();
        $f[] = Form::hidden('id', $info['id'] ?? 0);
        $f[] = Form::select('pid', '上级分类', (int)($info['pid'] ?? ''))->setOptions($this->menus($pid))->filterable(1);
        $f[] = Form::input('title', '分类名称', $info['title'] ?? '')->maxlength(20)->required();
        $f[] = Form::input('intr', '分类简介', $info['intr'] ?? '')->type('textarea')->required();
        $f[] = Form::frameImage('image', '分类图片', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'image')), $info['image'] ?? '')->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false]);
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->precision(0);
        $f[] = Form::radio('status', '状态', $info['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form('添加分类', $f, Url::buildUrl($url), $method);
    }

    /**
     * 保存
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        return $this->dao->save($data);
    }

    /**
     * 修改
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        return $this->dao->update($data['id'], $data);
    }

    /**
     * 删除
     * @param int $id
     * @return mixed
     */
    public function del(int $id)
    {
        /** @var ArticleServices $articleService */
        $articleService = app()->make(ArticleServices::class);
        $pidCount = $this->dao->count(['pid' => $id]);
        if ($pidCount > 0) throw new AdminException(400454);
        $count = $articleService->count(['cid' => $id]);
        if ($count > 0) {
            throw new AdminException(400455);
        } else {
            return $this->dao->delete($id);
        }
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function setStatus(int $id, int $status)
    {
        return $this->dao->update($id, ['status' => $status]);
    }

    /**
     * 获取一级分类组合数据
     * @param string $pid
     * @return array[]
     */
    public function menus($pid = '')
    {
        $list = $this->dao->getMenus(['pid' => 0]);
        $menus = [['value' => 0, 'label' => '顶级分类']];
        if ($pid === 0) return $menus;
        if ($pid != '') $menus = [];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['title']];
        }
        return $menus;
    }

    /**
     * 树形列表
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/9/7
     */
    public function getTreeList()
    {
        return get_tree_children($this->dao->getTreeList(['is_del' => 0, 'status' => 1, 'hidden' => 0], ['id', 'id as value', 'title as label', 'title', 'pid']), 'children', 'id');
//        return sort_list_tier($this->dao->getMenus([]));
    }
}
