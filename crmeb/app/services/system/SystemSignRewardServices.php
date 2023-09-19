<?php

namespace app\services\system;

use app\dao\system\SystemSignRewardDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignRewardServices extends BaseServices
{
    /**
     * @param SystemSignRewardDao $dao
     */
    public function __construct(SystemSignRewardDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 签到奖励列表
     * @param int $type
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function getList($type = 0)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList(['type' => $type], '*', $page, $limit, 'days');
        $count = $this->dao->count(['type' => $type]);
        return compact('list', 'count');
    }

    /**
     * 新增修改签到奖励表单
     * @param int $id
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/31
     */
    public function rewardsForm($id = 0, $type = 0)
    {
        $info = $this->dao->get($id);
        if ($info) $type = $info['type'];
        $form[] = Form::hidden('type', $type);
        $form[] = Form::number('days', $type == 1 ? '累积签到天数' : '连续签到天数', (int)($info['days'] ?? 0))->max(sys_config('sign_mode') == 1 ? 7 : 30);
        $form[] = Form::number('point', '赠送积分', (int)($info['point'] ?? 0))->controls(false)->max(999)->min(0);
        $form[] = Form::number('exp', '赠送经验', (int)($info['exp'] ?? 0))->controls(false)->max(999)->min(0);
        return create_form($type == 1 ? '累积签到奖励' : '连续签到奖励', $form, Url::buildUrl('/marketing/sign/save_rewards/' . $id), 'POST');
    }

    /**
     * 保存签到奖励
     * @param $id
     * @param $data
     * @return bool
     * @throws \ReflectionException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/10
     */
    public function saveRewards($id, $data)
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            if ($this->dao->count(['type' => $data['type'], 'days' => $data['days']])) {
                throw new AdminException('签到奖励已存在');
            } else {
                $this->dao->save($data);
            }
        }
        return true;
    }

    /**
     * 获取累积或者连续签到奖励数据
     * @param $type
     * @param $days
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/1
     */
    public function getSignRewards($type, $days)
    {
        $info = $this->dao->get(['type' => $type, 'days' => $days]);
        if ($info) return [true, $info['point'], $info['exp']];
        return [false, 0, 0];
    }
}