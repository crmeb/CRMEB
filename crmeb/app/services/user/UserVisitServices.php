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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserVisitDao;
use think\facade\Log;

/**
 *
 * Class UserVisitServices
 * @package app\services\user
 * @method count(array $where)
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method sum(array $where, string $field)
 * @method getTrendData($time, $type, $timeType, $str)
 * @method getRegion($time, $channelType)
 * @method int groupCount(array $where, string $group = 'uid') 根据分组获取记录条数
 */
class UserVisitServices extends BaseServices
{

    /**
     * UserVisitServices constructor.
     * @param UserVisitDao $dao
     */
    public function __construct(UserVisitDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 登录后记录访问记录
     * @param array|object $user
     * @return mixed
     */
    public function loginSaveVisit($user)
    {
        try {
            $data = [
                'url' => '/pages/index/index',
                'uid' => $user['uid'] ?? 0,
                'ip' => request()->ip(),
                'add_time' => time(),
                'province' => $user['province'] ?? '',
                'channel_type' => $user['user_type'] ?? 'h5'
            ];
            if (!$data['uid']) {
                return false;
            }
            return $this->dao->save($data);
        } catch (\Throwable $e) {
            Log::error('登录记录访问日志错误，错误原因：' . $e->getMessage());
        }
    }

}
