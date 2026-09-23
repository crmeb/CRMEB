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
declare(strict_types=1);

namespace app\dao\ai;

use app\model\ai\AiModel;
use crmeb\basic\BaseDao;

/**
 * AI 模型登记数据访问层
 */
class AiModelDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return AiModel::class;
    }

    /**
     * 获取全部启用模型（按排序取优先级）
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->getModel()->where('status', 1)->order('sort desc,id asc')->select()->toArray();
    }

    /**
     * 获取系统默认模型（未标记默认时退化为排序最高的启用模型）
     * @return array
     */
    public function getDefaultModel(): array
    {
        $model = $this->getModel()->where('status', 1)->where('is_default', 1)->order('sort desc,id asc')->find();
        if (!$model) {
            $model = $this->getModel()->where('status', 1)->order('sort desc,id asc')->find();
        }
        return $model ? $model->toArray() : [];
    }

    /**
     * 按 ID 获取模型记录
     * @param int $id 模型ID
     * @return array
     */
    public function findById(int $id): array
    {
        $model = $this->getModel()->where('id', $id)->find();
        return $model ? $model->toArray() : [];
    }

    /**
     * 清除其他模型的默认标记
     * @param int $id 保留为默认的模型ID
     * @return int
     */
    public function clearDefaultExcept(int $id): int
    {
        return $this->getModel()->where('id', '<>', $id)->where('is_default', 1)->update(['is_default' => 0]);
    }

    /**
     * 统计同名模型数量
     * @param string $name 模型显示名
     * @param int $exceptId 排除的模型ID
     * @return int
     */
    public function countByName(string $name, int $exceptId = 0): int
    {
        $query = $this->getModel()->where('name', $name);
        if ($exceptId > 0) {
            $query->where('id', '<>', $exceptId);
        }
        return $query->count();
    }
}
