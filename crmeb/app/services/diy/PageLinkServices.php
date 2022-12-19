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
use app\dao\diy\PageLinkDao;
use crmeb\exceptions\AdminException;


/**
 *
 * Class DiyServices
 * @package app\services\diy
 */
class PageLinkServices extends BaseServices
{

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
        return compact('list', 'count');
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id){
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException(100008);
    }
}
