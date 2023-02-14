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

namespace app\services\diy;

use app\services\BaseServices;
use app\dao\diy\PageCategoryDao;
use crmeb\services\CacheService;


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
        return CacheService::remember($this->tree_page_category_key, function () {
            return $this->getSonCategoryList();
        }, 86400);
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
                $arr [] = $item;
            }
        }
        return $arr;
    }

}
