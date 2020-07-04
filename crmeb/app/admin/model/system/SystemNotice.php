<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Db;

/**
 * 后台通知model
 * Class SystemNotice
 * @package app\admin\model\system
 */
class SystemNotice extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_notice';

    use ModelTrait;

    protected function setResultAttr($value)
    {
        return json_encode($value);
    }

    protected function setTableTitleAttr($value)
    {
        $list = [];
        if (!empty($value)) {
            $group = explode(',', $value);
            $list = array_map(function ($v) {
                list($title, $key) = explode('-', $v);
                return compact('title', 'key');
            }, $group);
        }
        return json_encode($list);
    }

    protected function getTableTitleAttr($value)
    {
        return json_decode($value, true);
    }

    protected function getResultAttr($value)
    {
        return json_decode($value, true);
    }

    protected function setPushAdminAttr($value)
    {
        $value = is_array($value) ? array_unique(array_filter($value)) : [];
        return implode(',', $value);
    }

    protected function getPushAdminAttr($value)
    {
        return array_filter(explode(',', $value));
    }

    public static function typeByAdminList($type, $field = 'id')
    {
        return self::where('type', $type)->field($field)->find();
    }

    public static function systemNoticeAdminDb()
    {
        return Db::name('SystemNoticeAdmin');
    }

    public static function adminMessage($notice_type, $admin_id, $link_id, array $table_data = [])
    {
        $table_data = json_encode($table_data);
        $add_time = time();
        return self::systemNoticeAdminDb()->insert(compact('notice_type', 'admin_id', 'link_id', 'table_data', 'add_time'));
    }

    public static function noticeMessage($noticeType, $linkId, array $tableData = [])
    {
        $noticeInfo = self::get(['type' => $noticeType]);
        if (!$noticeInfo) return self::setErrorInfo('通知模板消息不存在!');
        $adminIds = array_merge(array_map(function ($v) {
            return $v['id'];
        }, SystemAdmin::getTopAdmin('id')->toArray()) ?: [], self::typeByAdminList($noticeType, 'push_admin')->push_admin ?: []);
        $adminIds = array_unique(array_filter($adminIds));
        if (!count($adminIds)) return self::setErrorInfo('没有有效的通知用户!');
        foreach ($adminIds as $id) {
            self::adminMessage($noticeType, $id, $linkId, $tableData);
        }
        return true;
    }

    public static function getAdminNoticeTotal($adminId)
    {
        $list = self::alias('A')
            ->join('system_notice_admin B', 'B.notice_type = A.type')
            ->where('A.status', 1)
            ->where('B.is_visit', 0)
            ->where('B.is_click', 0)
            ->where('B.admin_id', $adminId)
            ->field('count(B.id) total')
            ->group('A.id')
            ->select()
            ->toArray();
        if (!$list) return 0;
        return array_reduce($list, function ($initial, $res) {
            return $initial + $res['total'];
        }, 0);
    }

    public static function getAdminNotice($adminId)
    {
        $list = self::alias('A')
            ->join('system_notice_admin B', 'B.notice_type = A.type')
            ->where('A.status', 1)
            ->where('B.is_visit', 0)
            ->where('B.is_click', 0)
            ->where('B.admin_id', $adminId)
            ->field('A.id,A.type,A.title,A.icon,count(B.id) total,A.template,max(B.add_time) as last_time')
            ->group('A.id')
            ->having('total > 0')
            ->select()
            ->toArray();
        $noticeTypeList = [];
        array_walk($list, function (&$notice) use (&$noticeTypeList) {
            $notice['message'] = sprintf($notice['template'], $notice['total']);
            $noticeTypeList[] = $notice['type'];
        });
        if (count($noticeTypeList))
            self::systemNoticeAdminDb()->where('notice_type', 'IN', $noticeTypeList)->where('admin_id', $adminId)
                ->update(['is_visit' => 1, 'visit_time' => time()]);
        return $list;
    }

}