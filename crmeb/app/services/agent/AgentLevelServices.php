<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\agent;

use app\dao\agent\AgentLevelDao;
use app\services\BaseServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 * Class AgentLevelServices
 * @package app\services\agent
 */
class AgentLevelServices extends BaseServices
{
    /**
     * AgentLevelServices constructor.
     * @param AgentLevelDao $dao
     */
    public function __construct(AgentLevelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某一个等级信息
     * @param int $id
     * @param string $field
     * @param array $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelInfo(int $id, string $field = '*', array $with = [])
    {
        return $this->dao->getOne(['id' => $id, 'is_del' => 0], $field, $with);
    }

    /**
     * 获取等级列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelList(array $where)
    {
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', ['task' => function ($query) {
            $query->field('count(*) as sum');
        }], $page, $limit);
        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item['one_brokerage_ratio'] = $item['one_brokerage_percent'];
            $item['two_brokerage_ratio'] = $item['two_brokerage_percent'];
            if (strpos($item['image'], '/statics/system_images/') !== false) {
                $item['image'] = set_file_url($item['image']);
            }
        }
        return compact('count', 'list');
    }

    /**
     * 商城获取分销员等级列表
     * @param int $uid
     * @return array
     */
    public function getUserlevelList(int $uid)
    {
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return [];
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410032);
        }
        //检测升级
        $this->checkUserLevelFinish($uid);

        $list = $this->dao->getList(['is_del' => 0, 'status' => 1]);
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image']);
        }
        $agent_level = $user['agent_level'] ?? 0;
        //没等级默认最低等级
        if (!$agent_level) {
            $levelInfo = $list[0] ?? [];
            $levelInfo['grade'] = -1;
        } else {
            $levelInfo = $this->getLevelInfo($agent_level) ?: [];
        }
        $sum_task = $finish_task = 0;
        if ($levelInfo) {
            /** @var AgentLevelTaskServices $levelTaskServices */
            $levelTaskServices = app()->make(AgentLevelTaskServices::class);
            $sum_task = $levelTaskServices->count(['level_id' => $levelInfo['id'], 'is_del' => 0, 'status' => 1]);
            /** @var AgentLevelTaskRecordServices $levelTaskRecordServices */
            $levelTaskRecordServices = app()->make(AgentLevelTaskRecordServices::class);
            $finish_task = $levelTaskRecordServices->count(['level_id' => $levelInfo['id'], 'uid' => $uid]);
        }
        $levelInfo['sum_task'] = $sum_task;
        $levelInfo['finish_task'] = $finish_task;
        return ['user' => $user, 'level_list' => $list, 'level_info' => $levelInfo];
    }

    /**
     * 获取下一等级
     * @param int $level_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNextLevelInfo(int $level_id = 0)
    {
        $grade = 0;
        if ($level_id) {
            $grade = $this->dao->value(['id' => $level_id, 'is_del' => 0, 'status' => 1], 'grade') ?: 0;
        }
        return $this->dao->getOne([['grade', '>', $grade], ['is_del', '=', 0], ['status', '=', 1]]);
    }

    /**
     * 检测用户是否能升级
     * @param int $uid
     * @param array $uids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkUserLevelFinish(int $uid, array $uids = [])
    {
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return false;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            return false;
        }
        $list = $this->dao->getList(['is_del' => 0, 'status' => 1]);
        if (!$list) {
            return false;
        }
        if (!$uids) {
            //获取上级uid ｜｜ 开启自购返回自己uid
            $spread_uid = $userServices->getSpreadUid($uid, $userInfo);
            $two_spread_uid = 0;
            if ($spread_uid > 0 && $one_user_info = $userServices->getUserInfo($spread_uid)) {
                $two_spread_uid = $userServices->getSpreadUid($spread_uid, $one_user_info, false);
            }
            $uids = array_unique([$uid, $spread_uid, $two_spread_uid]);
        }
        foreach ($uids as $uid) {
            if ($uid <= 0) continue;
            if ($uid != $userInfo['uid']) {
                $userInfo = $userServices->getUserInfo($uid);
            }
            $now_grade = 0;
            if ($userInfo['agent_level']) {
                $now_grade = $this->dao->value(['id' => $userInfo['agent_level']], 'grade') ?: 0;
            }
            foreach ($list as $levelInfo) {
                if (!$levelInfo || $levelInfo['grade'] <= $now_grade) {
                    continue;
                }
                /** @var AgentLevelTaskServices $levelTaskServices */
                $levelTaskServices = app()->make(AgentLevelTaskServices::class);
                $task_list = $levelTaskServices->getTaskList(['level_id' => $levelInfo['id'], 'is_del' => 0, 'status' => 1]);
                if (!$task_list) {
                    continue;
                }
                foreach ($task_list as $task) {
                    $levelTaskServices->checkLevelTaskFinish($uid, (int)$task['id'], $task);
                }
                /** @var AgentLevelTaskRecordServices $levelTaskRecordServices */
                $levelTaskRecordServices = app()->make(AgentLevelTaskRecordServices::class);
                $ids = array_column($task_list, 'id');
                $finish_task = $levelTaskRecordServices->count(['level_id' => $levelInfo['id'], 'uid' => $uid, 'task_id' => $ids]);
                //任务完成升这一等级
                if ($finish_task >= $levelInfo['task_num']) {
                    $userServices->update($uid, ['agent_level' => $levelInfo['id']]);
                } else {
                    break;
                }
            }
        }

        return true;
    }

    /**
     * 分销等级上浮
     * @param $storeBrokerageRatio
     * @param $storeBrokerageTwo
     * @param $spread_one_uid
     * @param $spread_two_uid
     * @return array|int[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgentLevelBrokerage($storeBrokerageRatio, $storeBrokerageTwo, $spread_one_uid, $spread_two_uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $one_agent_level = $userServices->value(['uid' => $spread_one_uid], 'agent_level') ?? 0;
        $two_agent_level = $userServices->value(['uid' => $spread_two_uid], 'agent_level') ?? 0;

        if ($one_agent_level) {
            $oneLevelInfo = $this->getLevelInfo($one_agent_level);
            if ($oneLevelInfo && $oneLevelInfo['status'] == 1) {
                $storeBrokerageRatio = $oneLevelInfo['one_brokerage_percent'];
            }
        }

        if ($two_agent_level) {
            $twoLevelInfo = $this->getLevelInfo($two_agent_level);
            if ($twoLevelInfo && $twoLevelInfo['status'] == 1) {
                $storeBrokerageTwo = $twoLevelInfo['two_brokerage_percent'];
            }
        }

        return [$storeBrokerageRatio, $storeBrokerageTwo];
    }

    /**
     * 添加等级表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        $field[] = Form::input('name', '等级名称')->maxlength(8)->col(24);
        $field[] = Form::number('grade', '等级', 0)->min(0)->precision(0);
        $field[] = Form::frameImage('image', '背景图', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'image')))->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false]);
        $field[] = Form::number('one_brokerage_percent', '一级佣金比例', 0)->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'domProps' => ['innerHTML' => '到达该等级之后，一级分佣按照此比例计算佣金']
        ])->max(100)->precision(2);
        $field[] = Form::number('two_brokerage_percent', '二级佣金比例', 0)->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'domProps' => ['innerHTML' => '到达该等级之后，二级分佣按照此比例计算佣金']
        ])->min(0)->max(100)->precision(2);
        $field[] = Form::radio('status', '是否显示', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form('添加分销员等级', $field, Url::buildUrl('/agent/level'), 'POST');
    }

    /**
     * 获取修改等级表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editForm(int $id)
    {
        $levelInfo = $this->getLevelInfo($id);
        if (!$levelInfo)
            throw new AdminException(100026);
        $field = [];
        $field[] = Form::hidden('id', $id);
        $field[] = Form::input('name', '等级名称', $levelInfo['name'])->maxlength(8)->col(24);
        $field[] = Form::number('grade', '等级', $levelInfo['grade'])->min(0)->precision(0);
        $field[] = Form::frameImage('image', '背景图', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'image')), $levelInfo['image'])->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false]);
        $field[] = Form::number('one_brokerage_percent', '一级佣金比例', $levelInfo['one_brokerage_percent'])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'domProps' => ['innerHTML' => '到达该等级之后，一级分佣按照此比例计算佣金']
        ])->max(100)->precision(2);
        $field[] = Form::number('two_brokerage_percent', '二级佣金比例', $levelInfo['two_brokerage_percent'])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'domProps' => ['innerHTML' => '到达该等级之后，二级分佣按照此比例计算佣金']
        ])->min(0)->max(100)->precision(2);
        $field[] = Form::radio('status', '是否显示', $levelInfo['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);

        return create_form('编辑分销员等级', $field, Url::buildUrl('/agent/level/' . $id), 'PUT');
    }

    /**
     * 赠送分销等级表单
     * @param int $uid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function levelForm(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException(400214);
        }
        $levelList = $this->dao->getList(['is_del' => 0, 'status' => 1], '*', [], 0, 0, $userInfo['agent_level']);
        $setOptionLabel = function () use ($levelList) {
            $menus = [];
            foreach ($levelList as $level) {
                $menus[] = ['value' => $level['id'], 'label' => $level['name']];
            }
            return $menus;
        };
        $field[] = Form::hidden('uid', $uid);
        $field[] = Form::select('id', '分销等级', $userInfo['agent_level'] != 0 ? $userInfo['agent_level'] : '')->setOptions(Form::setOptions($setOptionLabel))->filterable(true);
        return create_form('修改分销等级', $field, Url::buildUrl('/agent/give_level'), 'post');
    }

    /**
     * 赠送分销等级
     * @param int $uid
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function givelevel(int $uid, int $id)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid, 'uid');
        if (!$userInfo) {
            throw new AdminException(400214);
        }
        $levelInfo = $this->getLevelInfo($id, 'id');
        if (!$levelInfo) {
            throw new AdminException(400442);
        }
        if ($userServices->update($uid, ['agent_level' => $id]) === false) {
            throw new AdminException(400219);
        }
        return true;
    }

    /**
     * 获取指定分销等级的任务数量表单
     * @param int $id 分销等级ID
     * @return array|string
     */
    public function getTaskNumForm($id)
    {
        // 获取指定分销等级的信息
        $levelInfo = $this->getLevelInfo($id);
        // 构建任务数量输入框
        $field[] = Form::input('task_num', '完成任务数量', $levelInfo['task_num'])->maxlength(8)->col(24)->info('默认全部完成升级，可设置升级任务数量');
        // 创建表单并返回HTML字符串
        return create_form('设置完成任务数量', $field, Url::buildUrl('/agent/set_task_num/' . $id), 'post');
    }

    /**
     * 设置指定分销等级的任务数量
     * @param int $id 分销等级ID
     * @param array $data 包含任务数量的数组
     * @return bool 返回true表示设置成功
     * @throws AdminException 如果分销等级不存在或任务数量为空或任务数量大于已有任务数量，则抛出异常
     */
    public function setTaskNum($id, $data)
    {
        // 判断分销等级是否存在
        if (!$id) throw new AdminException('分销等级不存在');
        // 判断任务数量是否为空
        if (!$data['task_num']) throw new AdminException('请输入任务数量');
        // 获取当前分销等级已有的任务数量
        $count = app()->make(AgentLevelTaskServices::class)->count(['level_id' => $id, 'is_del' => 0, 'status' => 1]);
        // 判断任务数量是否大于已有任务数量
        if ($data['task_num'] > $count) throw new AdminException('任务数量不能大于已有任务数量');
        // 更新分销等级的任务数量
        $this->dao->update($id, ['task_num' => $data['task_num']]);
        // 返回true表示设置成功
        return true;
    }

    /**
     * 获取分销等级数组
     * @return array
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/6/16
     */
    public function getAgentLevelArr()
    {
        return $this->dao->getColumn(['status'=>1,'is_del'=>0], 'name', 'grade');
    }
}
