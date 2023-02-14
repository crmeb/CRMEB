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

namespace app\services\message;

use app\services\BaseServices;
use crmeb\services\CacheService;

/**
 * 站内信services类
 * Class MessageSystemServices
 */
class NoticeService extends BaseServices
{
    protected $noticeInfo;
    protected $event;

    /**
     * 设置
     * @param string $event
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setEvent(string $event)
    {
        if ($this->event != $event) {
            $this->noticeInfo = CacheService::get('NOTICE_' . $event);
            if (!$this->noticeInfo) {
                /** @var SystemNotificationServices $services */
                $services = app()->make(SystemNotificationServices::class);
                $noticeInfo = $services->getOneNotce(['mark' => $event]);
                $this->noticeInfo = $noticeInfo ? $noticeInfo->toArray() : [];
                CacheService::set('NOTICE_' . $event, $this->noticeInfo);
            }
            $this->event = $event;
        }
        return $this;
    }
}
