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

namespace app\model\message;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class NoticeLog extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'notice_log';

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    public function searchMarkAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('mark', $value);
        }
    }

    public function searchUidAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('uid', $value);
        }
    }

    public function searchChannelKeyAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('channel_key', $value);
        }
    }

    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }

    public function searchSendTimeAttr($query, $value)
    {
        if (!empty($value)) {
            $query->whereBetweenTime('send_time', $value[0], $value[1]);
        }
    }
}
