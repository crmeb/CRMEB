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
namespace modules\marketing\signin\service;

use modules\marketing\signin\dao\SystemSignRewardDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * 签到奖励规则管理服务
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignRewardServices extends BaseService
{
    /**
     * 奖励表有符号 INT 字段的上限
     * @var int
     */
    private const MAX_INT = 2147483647;

    /**
     * @var SystemSignRewardDao
     */
    protected $dao;

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
        $list = $this->dao->selectList(['type' => $type], 'id,type,days,point,exp', $page, $limit, 'days');
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
        $form[] = Form::number('days', $type == 1 ? '累积签到天数' : '连续签到天数', (int)($info['days'] ?? 0))->max($this->getMaxDays())->min(1)->precision(0);
        $form[] = Form::number('point', '赠送积分', (int)($info['point'] ?? 0))->controls(false)->max(self::MAX_INT)->min(0)->precision(0);
        $form[] = Form::number('exp', '赠送经验', (int)($info['exp'] ?? 0))->controls(false)->max(self::MAX_INT)->min(0)->precision(0);
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
        $id = $this->integerValue($id, '奖励编号', 0);
        if (!is_array($data)) {
            throw new AdminException('签到奖励参数错误');
        }
        $data = [
            'type' => $this->integerValue($data['type'] ?? 0, '奖励类型', 0, 1),
            'days' => $this->integerValue($data['days'] ?? 0, '签到天数', 1),
            'point' => $this->integerValue($data['point'] ?? 0, '赠送积分', 0),
            'exp' => $this->integerValue($data['exp'] ?? 0, '赠送经验', 0),
        ];

        return $this->transaction(function () use ($id, $data) {
            // 配置行始终存在于已安装的签到插件中，空奖励表也能获得稳定的互斥锁。
            $config = $this->dao->lockSignMode();
            if (!$config) {
                throw new AdminException('签到模式配置不存在，请检查签到插件安装状态');
            }
            $mode = json_decode((string)$config['value'], true);
            if (!in_array($mode, [-1, 0, 1, '-1', '0', '1'], true)) {
                throw new AdminException('签到模式配置错误');
            }
            if ($data['days'] > $this->getMaxDays((int)$mode)) {
                throw new AdminException((int)$mode === 1 ? '周循环签到奖励天数不能超过7天' : '月循环签到奖励天数不能超过31天');
            }
            if ($id && !$this->dao->getDb()->where('id', $id)->field('id')->lock(true)->find()) {
                throw new AdminException('签到奖励不存在');
            }
            if ($this->dao->hasDuplicate($data['type'], $data['days'], $id)) {
                throw new AdminException('签到奖励已存在');
            }
            if ($id) {
                $this->dao->update($id, $data);
            } elseif (!$this->dao->save($data)) {
                throw new AdminException('保存签到奖励失败');
            }
            return true;
        });
    }

    /**
     * 获取当前签到周期允许的奖励天数上限。
     * @param int|null $mode 已锁定的签到模式，为空时读取展示配置
     * @return int
     */
    public function getMaxDays(?int $mode = null): int
    {
        $mode = $mode ?? (int)sys_config('sign_mode', -1);
        return $mode === 1 ? 7 : ($mode === 0 ? 31 : self::MAX_INT);
    }

    /**
     * 严格读取整数，禁止截断小数、文本或溢出值。
     * @param mixed $value 输入值
     * @param string $label 字段名称
     * @param int $minimum 最小值
     * @param int $maximum 最大值
     * @return int
     */
    protected function integerValue($value, string $label, int $minimum, int $maximum = self::MAX_INT): int
    {
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new AdminException($label . '必须为整数');
        }
        $digits = ltrim((string)$value, '0');
        $digits = $digits === '' ? '0' : $digits;
        $maximumText = (string)$maximum;
        if (strlen($digits) > strlen($maximumText)
            || (strlen($digits) === strlen($maximumText) && strcmp($digits, $maximumText) > 0)
            || (int)$value < $minimum) {
            throw new AdminException($label . '超出允许范围');
        }
        return (int)$value;
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
        $info = $this->dao->getDb()->where('type', $type)->where('days', $days)
            ->field('id,point,exp')->order('id asc')->find();
        if ($info) return [true, $info['point'], $info['exp']];
        return [false, 0, 0];
    }
}
