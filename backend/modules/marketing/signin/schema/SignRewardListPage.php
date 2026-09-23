<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\marketing\signin\schema;

use crmeb\services\schema\BaseSchemaPageProvider;
use crmeb\utils\FormBuilder as Form;
use modules\marketing\signin\dao\SystemSignRewardDao;
use modules\marketing\signin\service\SystemSignRewardServices;

/**
 * 签到奖励列表页（访问 /admin/plugin/signin/rewards 自动渲染）
 *
 * 替代原 UMD 前端页面 pages/plugin/signin/pages/sign_rewards：
 * 连续/累积签到奖励两个分类（tabs）+ 表格 + 新增/编辑抽屉 + 删除确认。
 */
class SignRewardListPage extends BaseSchemaPageProvider
{
    /**
     * 签到奖励数据访问对象
     * @var SystemSignRewardDao
     */
    protected SystemSignRewardDao $dao;

    /**
     * 签到奖励业务服务（复用保存唯一性校验逻辑）
     * @var SystemSignRewardServices
     */
    protected SystemSignRewardServices $services;

    /**
     * 构造注入（注册中心经容器实例化，自动解析依赖）
     * @param SystemSignRewardDao $dao 签到奖励 DAO
     * @param SystemSignRewardServices $services 签到奖励服务
     */
    public function __construct(SystemSignRewardDao $dao, SystemSignRewardServices $services)
    {
        $this->dao = $dao;
        $this->services = $services;
    }

    /**
     * 页面标识（静态方法，同插件内唯一）
     * @return string
     */
    public static function pageKey(): string
    {
        return 'rewards';
    }

    /**
     * 页面结构配置：分类头（连续/累积）+ 列定义 + 行操作
     * @return array
     */
    public function config(): array
    {
        return [
            'type'  => 'list',
            'title' => '签到奖励',
            'tabs'  => [
                'field'      => 'type',
                'options'    => [
                    ['label' => '连续签到奖励', 'value' => 0],
                    ['label' => '累积签到奖励', 'value' => 1],
                ],
                'with_count' => false,
            ],
            'actions' => [
                // follow_tab：文案随分类头拼接（"添加" + 连续/累积签到奖励），与原页面行为一致
                ['action' => 'create', 'label' => '添加', 'theme' => 'primary', 'follow_tab' => true, 'auth' => ['marketing-sign-rewards-save']],
            ],
            'columns' => [
                ['prop' => 'type_text', 'label' => '类型', 'minWidth' => 140],
                ['prop' => 'days', 'label' => '天数(天)', 'minWidth' => 90],
                ['prop' => 'point', 'label' => '奖励积分', 'minWidth' => 90],
                ['prop' => 'exp', 'label' => '奖励经验', 'minWidth' => 90],
            ],
            'row_actions' => [
                ['action' => 'edit', 'label' => '编辑', 'mode' => 'drawer_form', 'auth' => ['marketing-sign-rewards-save']],
                ['action' => 'delete', 'label' => '删除', 'mode' => 'confirm_action', 'confirm' => '删除后不可恢复，确认删除？', 'auth' => ['marketing-sign-rewards-delete']],
            ],
        ];
    }

    /**
     * 列表数据（按分类头 type 过滤，天数升序）
     *
     * type_text 为展示组装字段：连续签到X天奖励 / 累积签到X天奖励
     *
     * @param array $where 搜索条件（含分类头提交的 type）
     * @param int $page 页码
     * @param int $limit 每页条数
     * @param array $order 排序
     * @return array
     */
    public function list(array $where, int $page, int $limit, array $order = []): array
    {
        $type = (int)($where['type'] ?? 0);
        $list = $this->dao->selectList(['type' => $type], 'id,type,days,point,exp', $page, $limit, 'days');
        foreach ($list as &$item) {
            $item['type_text'] = $item['type'] == 1
                ? '累积签到' . $item['days'] . '天奖励'
                : '连续签到' . $item['days'] . '天奖励';
        }
        $count = $this->dao->count(['type' => $type]);
        return compact('list', 'count');
    }

    /**
     * 表单规则（新增/编辑抽屉）
     *
     * 新增时奖励类型默认跟随分类头（前端 form 端点附带 tab 参数，见
     * ListTemplate openFormDrawer），编辑时以数据本身类型为准
     *
     * @param int $id 数据主键，0 表示新增
     * @return array
     */
    public function form(int $id = 0): array
    {
        $info = $id ? $this->dao->get($id) : null;
        $type = $info ? (int)$info['type'] : (int)app('request')->param('tab', 0);
        $field[] = Form::radio('type', '奖励类型', $type)->options([
            ['label' => '连续签到奖励', 'value' => 0],
            ['label' => '累积签到奖励', 'value' => 1],
        ]);
        $field[] = Form::number('days', $type == 1 ? '累积签到天数' : '连续签到天数', (int)($info['days'] ?? 0))
            ->max($this->services->getMaxDays())->min(1)->precision(0);
        $field[] = Form::number('point', '赠送积分', (int)($info['point'] ?? 0))->controls(false)->max(2147483647)->min(0)->precision(0);
        $field[] = Form::number('exp', '赠送经验', (int)($info['exp'] ?? 0))->controls(false)->max(2147483647)->min(0)->precision(0);
        return create_form($id ? '编辑签到奖励' : '新增签到奖励', $field, '', $id ? 'PUT' : 'POST');
    }

    /**
     * 保存（新增/编辑统一入口，复用服务层唯一性校验）
     *
     * @param int $id 数据主键，0 表示新增
     * @param array $data 表单数据
     * @return void
     */
    public function save(int $id, array $data): void
    {
        $this->services->saveRewards($id, [
            'type'  => $data['type'] ?? 0,
            'days'  => $data['days'] ?? 0,
            'point' => $data['point'] ?? 0,
            'exp'   => $data['exp'] ?? 0,
        ]);
    }

    /**
     * 通用动作（删除）
     *
     * @param string $action 动作标识
     * @param array $payload 动作参数（含 id）
     * @return array
     */
    public function action(string $action, array $payload): array
    {
        if ($action === 'delete') {
            $this->dao->delete((int)($payload['id'] ?? 0));
            return ['msg' => '删除成功'];
        }
        return parent::action($action, $payload);
    }
}
