<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\diy;

use crmeb\basic\BaseService;
use app\dao\diy\PageLinkDao;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class DiyServices
 * @package app\services\diy
 */
class PageLinkServices extends BaseService
{
    /**
     * @var PageLinkDao
     */
    protected $dao;

    /**
     * PageLinkServices constructor.
     * @param PageLinkDao $dao
     */
    public function __construct(PageLinkDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取页面链接
     * @param array $where
     * @return array
     */
    public function getLinkList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item['h5_url'] = sys_config('site_url') . $item['url'];
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        return compact('list', 'count');
    }

    /**
     * 获取链接数量
     *
     * @param array $where
     * @return int
     */
    public function count(array $where): int
    {
        return $this->dao->count($where);
    }

    /**
     * 按分类获取全部系统链接，用于页面链接分类树。
     *
     * @return array<int, array>
     */
    public function getLinksByCategory(): array
    {
        $links = $this->dao->getList([], '*');
        $groupedLinks = [];
        foreach ($links as $link) {
            $groupedLinks[(int)$link['cate_id']][] = $link;
        }
        return $groupedLinks;
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id)
    {
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException('删除失败');
    }

    public function getLinkSave($id, $data)
    {
        unset($data['id']);
        if ($id) {
            $res = $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $data['status'] = 1;
            $res = $this->dao->save($data);
        }
        if (!$res) {
            throw new AdminException('保存失败');
        } else {
            return true;
        }
    }

    /**
     * 获取链接表单
     * @param int $id
     * @param int $cate_id
     * @param PageCategoryServices $pageCategoryServices
     * @return array
     */
    public function getLinkForm(int $id, int $cate_id, PageCategoryServices $pageCategoryServices)
    {
        $info = $id ? $this->dao->get($id) : null;
        // 获取分类树
        $tree = $pageCategoryServices->getSonCategoryList(1);
        $options = $this->buildCategoryOptions($tree);
        $cate_id = $info ? (int)$info['cate_id'] : $cate_id;
        $f[] = Form::hidden('id', $id);
        $f[] = Form::select('cate_id', '分类', $cate_id)->setOptions($options)->filterable(true)->requiredNum();
        $f[] = Form::input('name', '名称', $info['name'] ?? '')->required();
        $f[] = Form::input('url', '跳转链接', $info['url'] ?? '')->required();
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0)->precision(0);
        $f[] = Form::radio('status', '是否开启', $info['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form($id ? '编辑链接' : '添加链接', $f, Url::buildUrl('/diy/link/save/' . $id), 'POST');
    }

    /**
     * 构建分类选项（扁平化，带缩进）
     * @param array $tree
     * @param int $level
     * @return array
     */
    private function buildCategoryOptions(array $tree, int $level = 0): array
    {
        $options = [];
        foreach ($tree as $node) {
            $prefix = $level > 0 ? str_repeat('  ', $level) . '|- ' : '';
            $options[] = ['value' => $node['id'], 'label' => $prefix . $node['title']];
            if (!empty($node['children'])) {
                $options = array_merge($options, $this->buildCategoryOptions($node['children'], $level + 1));
            }
        }
        return $options;
    }
}
