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

namespace app\services\wechat;


use app\dao\wechat\WechatMenuDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\WechatService;
use function GuzzleHttp\Psr7\str;

/**
 * 微信菜单
 * Class WechatMenuServices
 * @package app\services\wechat
 */
class WechatMenuServices extends BaseServices
{
    /**
     * 构造方法
     * WechatMenuServices constructor.
     * @param WechatMenuDao $dao
     */
    public function __construct(WechatMenuDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取微信菜单
     * @return array|mixed
     */
    public function getWechatMenu()
    {
        $menus = $this->dao->value(['key' => 'wechat_menus'], 'result');
        return $menus ? json_decode($menus, true) : [];
    }

    /**
     * 保存微信菜单
     * @param array $buttons
     * @return bool
     */
    public function saveMenu(array $buttons)
    {
        try {
            WechatService::menuService()->add($buttons);
            if ($this->dao->count(['key' => 'wechat_menus', 'result' => json_encode($buttons)])) {
                $this->dao->update('wechat_menus', ['result' => json_encode($buttons), 'add_time' => time()], 'key');
            } else {
                $this->dao->save(['key' => 'wechat_menus', 'result' => json_encode($buttons), 'add_time' => time()]);
            }
            return true;
        } catch (\Exception $e) {
            if (strstr($e->getMessage(), 'Request AccessToken fail. response')) {
                $msgData = str_replace('Request AccessToken fail. response: ', '', $e->getMessage());
                $msgData = json_decode($msgData, true);
                $errcode = $msgData['errcode'] ?? 0;
                if ($errcode == 40164) {
                    throw new AdminException('您得ip不再白名单中,请前往腾讯微信公众平台添加ip白名单');
                }
            }
            if (strstr($e->getMessage(), 'invalid weapp appid')) {
                throw new AdminException('您填写得appid无效,请检查');
            }
            throw new AdminException($e->getMessage());
        }
    }
}
