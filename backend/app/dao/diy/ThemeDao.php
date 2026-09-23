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
namespace app\dao\diy;

use crmeb\basic\BaseDao;
use app\model\diy\Theme;

/**
 * 自定义主题
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2025/12/18
 */
class ThemeDao extends BaseDao
{
    /**
     * 获取模型类名
     * @return string
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    protected function setModel(): string
    {
        return Theme::class;
    }

    /**
     * 条件查询模型对象
     * @param $where
     * @return \crmeb\basic\BaseModel
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function getConditionModel($where)
    {
        return $this->getModel()->where('is_del', 0)
            ->when(isset($where['title']) && $where['title'] !== '', function ($query) use ($where) {
                $query->where('title|info', 'like', '%' . $where['title'] . '%');
            })->when(isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] !== null, function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })->when(isset($where['is_use']) && $where['is_use'] !== '' && $where['is_use'] !== null, function ($query) use ($where) {
                $query->where('is_use', $where['is_use']);
            })->when(isset($where['type']) && $where['type'] !== '' && $where['type'] !== null, function ($query) use ($where) {
                $query->where('type', $where['type']);
            })->when(isset($where['page_type']) && $where['page_type'] !== '', function ($query) use ($where) {
                $query->where('page_type', $where['page_type']);
            });
    }

    /**
     * 获取自定义主题列表
     * @param $where
     * @param $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function themeList($where, $field, $page = 0, $limit = 0, $order = 'id desc')
    {
        return $this->getConditionModel($where)
            ->field($field)
            ->order($order)
            ->when($page != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }

    /**
     * 获取自定义主题数量
     * @param $where
     * @return int
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function themeCount($where)
    {
        return $this->getConditionModel($where)->count();
    }
}
