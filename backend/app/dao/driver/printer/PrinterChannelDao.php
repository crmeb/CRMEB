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

namespace app\dao\driver\printer;

use app\model\driver\printer\PrinterChannel;
use crmeb\basic\BaseDao;

/**
 * 打印机渠道数据访问层
 * Class PrinterChannelDao
 * @package app\dao\driver\printer
 */
class PrinterChannelDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return PrinterChannel::class;
    }

    /**
     * 获取所有已启用的打印机
     * @return \think\Collection
     */
    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->where('is_del', 0)->order('sort', 'desc')->select();
    }
}
