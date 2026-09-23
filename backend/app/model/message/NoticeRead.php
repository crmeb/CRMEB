<?php
declare(strict_types=1);

namespace app\model\message;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 站内信已读记录模型
 * 对应表：eb_notice_read
 */
class NoticeRead extends BaseModel
{
    use ModelTrait;

    protected $name = 'notice_read';
    protected $pk = 'id';

    public function notice()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\app\model\user\User::class, 'uid', 'uid');
    }

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }
        $this->is_read = 1;
        $this->read_at = time();
        return (bool) $this->save();
    }
}
