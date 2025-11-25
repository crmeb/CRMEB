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
declare (strict_types=1);

namespace app\services\diy;

use app\services\BaseServices;
use app\dao\diy\PageCategoryDao;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 * Class PageCategoryServices
 * @package app\services\diy
 */
class PageCategoryServices extends BaseServices
{

    protected $tree_page_category_key = 'tree_page_categroy';

    /**
     * PageCategoryServices constructor.
     * @param PageCategoryDao $dao
     */
    public function __construct(PageCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取分类列表
     * @return bool|mixed|null
     */
    public function getCategroyList()
    {
//        return CacheService::remember($this->tree_page_category_key, function () {
        return $this->getSonCategoryList();
//        }, 86400);
    }

    /**
     * tree分类列表
     * @param int $pid
     * @param string $parent_name
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSonCategoryList($pid = 0)
    {
        $list = $this->dao->getList(['pid' => $pid], 'id,pid,type,name');
        $arr = [];
        if ($list) {
            foreach ($list as $item) {
                $item['title'] = $item['name'];
                $item['expand'] = true;
                $item['children'] = $this->getSonCategoryList($item['id']);
                $arr[] = $item;
            }
        }
        return $arr;
    }

    public function getLinkCategoryForm($cate_id = 0, $pid = 1)
    {
        $info = $this->dao->get($cate_id);
        $list = $this->dao->getList(['pid' => 1]);
        $data = [['value' => 1, 'label' => '顶级分类']];
        foreach ($list as $menu) {
            $data[] = ['value' => $menu['id'], 'label' => $menu['name']];
        }
        $pid = isset($info['pid']) ? $info['pid'] : $pid;
        $f[] = Form::hidden('id', $cate_id);
        $f[] = Form::select('pid', '上级分类', (int)$pid)->setOptions($data)->filterable(true);
        $f[] = Form::input('name', '分类名称', $info['name'] ?? '')->required();
        $f[] = Form::input('type', '分类类型', $info['type'] ?? '')->required();
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0)->precision(0);
        $f[] = Form::radio('status', '状态', $info['status'] ?? 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]]);
        return create_form($cate_id ? '修改分类' : '添加分类', $f, Url::buildUrl('/diy/link/category/save/' . $cate_id), 'POST');
    }

    public function getLinkCategorySave($cate_id, $data)
    {
        if ($cate_id) {
            $res = $this->dao->update($cate_id, $data);
        } else {
            $data['add_time'] = time();
            $res = $this->dao->save($data);
        }
        if (!$res) {
            throw new AdminException('保存失败');
        } else {
            return true;
        }
    }

    public function getLinkCategoryDel($cate_id)
    {
        $res = $this->dao->delete($cate_id);
        if (!$res) {
            throw new AdminException('删除失败');
        } else {
            return true;
        }
    }
}
