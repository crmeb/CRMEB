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

namespace app\services\wechat;

use app\services\BaseServices;
use app\dao\wechat\WechatReplyKeyDao;

/**
 *
 * Class UserWechatuserServices
 * @package app\services\user
 */
class WechatReplyKeyServices extends BaseServices
{

    /**
     * 构造方法
     * WechatReplyKeyServices constructor.
     * @param WechatReplyKeyDao $dao
     */
    public function __construct(WechatReplyKeyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return mixed
     */
    public function getReplyKeyAll(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getReplyKeyList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }
}
