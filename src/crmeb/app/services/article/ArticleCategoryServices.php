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

namespace app\services\article;

use app\dao\article\ArticleCategoryDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class ArticleCategoryServices
 * @package app\services\article
 * @method getArticleCategory()
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
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
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
        }
        $f = array();
        $f[] = Form::hidden('id', $info['id'] ?? 0);
        $f[] = Form::input('title', '分类名称', $info['title'] ?? '')->maxlength(20)->required();
        $f[] = Form::input('intr', '分类简介', $info['intr'] ?? '')->type('textarea')->required();
        $f[] = Form::frameImage('image', '分类图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $info['image'] ?? '')->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0));
        $f[] = Form::radio('status', '状态', $info['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form('添加分类', $f, Url::buildUrl($url), $method);
    }

    /**
     * 保存
     * @param array $data
     */
    public function save(array $data)
    {
        $this->dao->save($data);
    }

    /**
     * 修改
     * @param array $data
     */
    public function update(array $data)
    {
        $this->dao->update($data['id'], $data);
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id)
    {
        /** @var ArticleServices $articleService */
        $articleService = app()->make(ArticleServices::class);
        $count = $articleService->count(['cid' => $id]);
        if ($count > 0) {
            throw new AdminException('该分类下有文章，无法删除！');
        } else {
            $this->dao->delete($id);
        }
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     */
    public function setStatus(int $id, int $status)
    {
        $this->dao->update($id, ['status' => $status]);
    }
}
