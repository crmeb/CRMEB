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
declare (strict_types = 1);

namespace app\services\system;

use app\dao\system\MessageSystemDao;
use app\services\BaseServices;
use think\exception\ValidateException;

/**
 *
 * Class MessageSystemServices
 * @package app\services\system
 * @method save(array $data) 保存数据
 * @method mixed saveAll(array $data) 批量保存数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 *
 */
class MessageSystemServices extends BaseServices
{

    /**
     * SystemNotificationServices constructor.
     * @param MessageSystemDao $dao
     */
    public function __construct(MessageSystemDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 站内信列表
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMessageSystemList($uid)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $where['uid'] = $uid;
        $list = $this->dao->getMessageList($where, '*', $page, $limit);
        $count = $this->dao->getCount($where);
        if (!$list) return ['list' => [], 'count' => 0];
        foreach ($list as &$item) {
            $item['add_time'] = time_tran($item['add_time']);
        }
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 站内信详情
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo($where)
    {
        $info = $this->dao->getOne($where);
        if (!$info || $info['is_del'] == 1) {
            throw new ValidateException('数据不存在');
        }
        $info = $info->toArray();
        if ($info['look'] == 0) {
            $this->update($info['id'], ['look' => 1]);
        }
        $info['add_time'] = time_tran($info['add_time']);
        return $info;
    }
}
