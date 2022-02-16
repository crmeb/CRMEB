<?php


namespace app\dao\wechat;


use app\dao\BaseDao;
use app\model\wechat\WechatQrcodeCate;

class WechatQrcodeCateDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return WechatQrcodeCate::class;
    }

    /**
     * 渠道码分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCateList()
    {
        return $this->getModel()->where('is_del', 0)->select()->toArray();
    }
}