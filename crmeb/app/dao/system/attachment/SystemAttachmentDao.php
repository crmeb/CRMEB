<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\system\attachment;

use app\dao\BaseDao;
use app\model\system\attachment\SystemAttachment;

/**
 *
 * Class SystemAttachmentDao
 * @package app\dao\attachment
 */
class SystemAttachmentDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemAttachment::class;
    }

    /**
     * 获取图片列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page, int $limit)
    {
        return $this->search($where)->where('module_type', 1)->page($page, $limit)->order('att_id DESC')->select()->toArray();
    }

    /**
     * 移动图片
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function move(array $data)
    {
        return $this->getModel()->whereIn('att_id', $data['images'])->update(['pid' => $data['pid']]);
    }

    /**
     * 获取名称
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLikeNameList(array $where, int $page, int $limit)
    {
        return $this->search($where)->page($page, $limit)->order('att_id desc')->select()->toArray();
    }

    /**
     * 获取昨日系统生成
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getYesterday()
    {
        return $this->getModel()->whereTime('time', 'yesterday')->where('module_type', 2)->field(['name', 'att_dir', 'att_id', 'image_type'])->select();
    }

    /**
     * 删除昨日生成海报
     * @throws \Exception
     */
    public function delYesterday()
    {
        $this->getModel()->whereTime('time', 'yesterday')->where('module_type', 2)->delete();
    }
}
