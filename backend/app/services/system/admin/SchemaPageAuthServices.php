<?php
declare(strict_types=1);

namespace app\services\system\admin;

use app\dao\system\SystemMenusDao;
use app\Request;
use crmeb\basic\BaseService;
use crmeb\exceptions\AuthException;

/**
 * 通用页面授权：复用现有页面菜单及角色，不把共享网关当作页面权限。
 */
class SchemaPageAuthServices extends BaseService
{
    /** @var SystemMenusDao 菜单数据访问 */
    protected $dao;

    /** @var SystemRoleServices 角色权限服务 */
    protected SystemRoleServices $roleServices;

    /**
     * @param SystemMenusDao $dao 菜单数据访问
     * @param SystemRoleServices $roleServices 角色权限服务
     */
    public function __construct(SystemMenusDao $dao, SystemRoleServices $roleServices)
    {
        $this->dao = $dao;
        $this->roleServices = $roleServices;
    }

    /**
     * 校验页面菜单和操作声明中的权限；未声明细分权限的旧页面沿用页面授权。
     *
     * @param Request $request 认证请求
     * @param string $pluginKey 插件目录名
     * @param string $pageKey 页面标识
     * @param array $config 页面配置
     * @param string $endpoint 网关端点
     * @param string $action 动作标识
     * @param int $id 表单记录编号
     * @return void
     */
    public function verify(Request $request, string $pluginKey, string $pageKey, array $config, string $endpoint, string $action = '', int $id = 0): void
    {
        $admin = $request->adminInfo();
        if (!$request->adminId() || !$admin) {
            throw new AuthException('参数错误');
        }
        if ((int)($admin['level'] ?? -1) === 0) {
            return;
        }

        $roleIds = array_values(array_filter(array_map('intval', $this->roleServices->getRoleIds((array)($admin['roles'] ?? []))), fn ($value) => $value > 0));
        if (!$roleIds) {
            throw new AuthException('暂无权限');
        }

        $path = '/plugin/' . $pluginKey . '/' . $pageKey;
        // 忽略菜单中的 query/hash 参数，兼容同一页面的已有菜单变体。
        $menus = $this->dao->getDb()->whereIn('id', $roleIds)
            ->where(['auth_type' => 1, 'is_del' => 0, 'is_show' => 1, 'access' => 1])
            ->whereLike('menu_path', $path . '%')->column('menu_path');
        $allowed = false;
        foreach ($menus as $menuPath) {
            if (rtrim((string)parse_url($menuPath, PHP_URL_PATH), '/') === $path) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            throw new AuthException('暂无权限');
        }

        $this->verifyDeclaredAuth($roleIds, $config['auth'] ?? []);
        if (in_array($endpoint, ['form', 'save'], true) && ($config['type'] ?? '') === 'list') {
            $action = $id > 0 ? 'edit' : 'create';
        }
        $action = $action === '' ? $endpoint : $action;
        // 动作权限由后端配置确定，绝不使用客户端传入的 auth。
        foreach (['actions', 'row_actions', 'batch_actions', 'columns'] as $group) {
            foreach ((array)($config[$group] ?? []) as $declaration) {
                if (!is_array($declaration)) {
                    continue;
                }
                $declaredAction = $declaration['action'] ?? (($declaration['type'] ?? '') === 'switch' ? 'status' : '');
                $formAction = ($config['type'] ?? '') === 'list' && $id > 0
                    && in_array($endpoint, ['form', 'save'], true) && $group === 'row_actions'
                    && ($declaration['mode'] ?? '') === 'drawer_form';
                $detailAction = ($config['type'] ?? '') === 'list' && $endpoint === 'detail'
                    && $group === 'row_actions' && ($declaration['mode'] ?? '') === 'drawer_detail';
                if ($declaredAction === $action || $formAction || $detailAction) {
                    $this->verifyDeclaredAuth($roleIds, $declaration['auth'] ?? []);
                }
            }
        }
    }

    /**
     * 权限串列表按任一匹配，与管理端现有 v-auth 语义一致。
     *
     * @param array $roleIds 当前角色菜单编号
     * @param mixed $auth 配置声明的权限串
     * @return void
     */
    private function verifyDeclaredAuth(array $roleIds, $auth): void
    {
        $auth = is_string($auth) ? [$auth] : (is_array($auth) ? $auth : []);
        $auth = array_values(array_filter($auth, fn ($value) => is_string($value) && $value !== ''));
        if (!$auth) {
            return;
        }
        if (!$this->dao->getDb()->whereIn('id', $roleIds)->whereIn('unique_auth', $auth)
            ->where(['is_del' => 0, 'is_show' => 1, 'access' => 1])->count()) {
            throw new AuthException('暂无权限');
        }
    }
}
