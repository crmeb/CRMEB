<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace app\services\system;


use app\dao\system\SystemCrudDataDao;
use app\services\BaseServices;

/**
 * Class SystemCrudDataService
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/7/28
 * @package app\services\system
 */
class SystemCrudDataService extends BaseServices
{

    /**
     * SystemCrudDataService constructor.
     * @param SystemCrudDataDao $dao
     */
    public function __construct(SystemCrudDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取全部数据
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/1
     */
    public function getlistAll(string $name = '')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList(['name' => $name], '*', $page, $limit, '', [], true)->toArray();
        $count = $this->dao->count(['name' => $name]);
        if ($page && $limit) {
            return compact('list', 'count');
        } else {
            return $list;
        }
    }
}
