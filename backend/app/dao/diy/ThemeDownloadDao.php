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
use app\model\diy\ThemeDownload;

/**
 * 主题下载记录 Dao
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2026/3/10
 */
class ThemeDownloadDao extends BaseDao
{
    /**
     * 获取模型类名
     * @return string
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    protected function setModel(): string
    {
        return ThemeDownload::class;
    }

    /**
     * 条件查询模型对象
     * @param array $where
     * @return \crmeb\basic\BaseModel
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function getConditionModel(array $where)
    {
        return $this->getModel()
            ->when(isset($where['tid']) && $where['tid'] !== '', function ($query) use ($where) {
                $query->where('tid', $where['tid']);
            })
            ->when(isset($where['title']) && $where['title'] !== '', function ($query) use ($where) {
                $query->where('title', 'like', '%' . $where['title'] . '%');
            });
    }

    /**
     * 获取下载记录列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function themeDownloadList(array $where = [], string $field = '*', int $page = 0, int $limit = 0, string $order = 'id desc'): array
    {
        return $this->getConditionModel($where)
            ->field($field)
            ->order($order)
            ->when($page != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }

    /**
     * 获取下载记录数量
     * @param array $where
     * @return int
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function themeDownloadCount(array $where): int
    {
        return $this->getConditionModel($where)->count();
    }
}
