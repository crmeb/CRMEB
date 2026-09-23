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
declare (strict_types=1);

namespace app\dao\system\attachment;

use crmeb\basic\BaseDao;
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
        return $this->search($where)->where('module_type', 1)
            ->field('att_id,name,real_name,att_dir,satt_dir,att_size,att_type,image_type,module_type,time,pid,type')
            ->page($page, $limit)->order('att_id DESC')->select()->toArray();
    }

    /**
     * 移动图片
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function move(array $data)
    {
        return $this->getModel()->where('module_type', 1)->whereIn('att_id', $data['images'])->update(['pid' => $data['pid']]);
    }

    /**
     * 按逻辑名称前缀获取用户生成海报，非分页最多1000条
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
        $query = $this->getModel()->where('module_type', 2)->where('real_name', 'LIKE', $where['like_real_name'] . '%')
            ->field('att_id,name,real_name,att_dir,satt_dir,image_type,time,pid,type');
        if ($page > 0 && $limit > 0) {
            $query->page($page, min($limit, 1000));
        } else {
            $query->limit(1000);
        }
        return $query->order('att_id desc')->select()->toArray();
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

    /**
     * 按扫码能力摘要获取最新图片，非分页最多1000条
     * @param string $scan_token SHA256摘要，非令牌原文
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/06/13
     */
    public function scanUploadImage($scan_token)
    {
        return $this->getModel()->where('scan_token', $scan_token)->where('module_type', 1)->where('type', 0)
            ->field('att_dir,att_id')->order('att_id DESC')->limit(1000)->select()->toArray();
    }
}
