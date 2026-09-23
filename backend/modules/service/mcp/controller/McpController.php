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
declare(strict_types=1);

namespace modules\service\mcp\controller;

use crmeb\basic\BaseController;
use modules\service\mcp\McpException;
use modules\service\mcp\McpToolRegistry;
use modules\service\mcp\service\McpAccountServices;
use think\Request;
use think\response\Json;

/**
 * MCP (Model Context Protocol) 服务控制器
 *
 * 以 JSON-RPC 2.0 over HTTP 单端点方式提供 MCP 协议服务（与 Streamable HTTP
 * 传输的请求-响应模式兼容），AI 客户端（WorkBuddy/豆包办公/TraeWork/千问办公 等）可直连调用。
 *
 * 认证方式：账号体系是唯一认证权威（eb_service_mcp_account 表）。通过请求头
 * X-Mcp-Key 或 Authorization: Bearer 携带账号密钥；服务可用性由总开关
 * mcp_enabled（安装默认开启，见「系统配置 → MCP服务」）与插件启用状态共同控制。
 * 所有账号（含系统账号）一律按各自工具白名单访问（tools/list 过滤、
 * tools/call 拦截未勾选工具）；系统账号（is_system=1）默认保留一条供 AI Agent
 * 模块内置工具源调用（安装时初始授予全部工具，其密钥镜像到系统配置
 * mcp_appkey，AI Agent 零改动读取），同样可在「MCP配置」页编辑权限。
 *
 * 工具扩展机制：内置工具由注册中心按目录约定自动发现（tools/ 目录下
 * 以 Tool.php 结尾且实现 McpToolInterface 的类），新增工具无需修改本文件；
 * 内置工具注册后触发 McpToolsRegister 事件（字符串 key），其他插件可在
 * 自身 event.php 中监听该事件，向注册中心追加/覆盖同名工具。
 */
class McpController extends BaseController
{
    /**
     * 支持的 MCP 协议版本（取客户端请求版本与服务端最新支持版本的交集）
     */
    private const PROTOCOL_VERSIONS = ['2024-11-05', '2025-03-26', '2025-06-18'];

    /**
     * 初始化（MCP 接口不走后台/用户 Token 体系，认证在 index 内按请求处理）
     */
    protected function initialize()
    {
    }

    /**
     * 获取工具注册中心（内置工具注册 + 扩展事件派发，每请求缓存）
     *
     * @return McpToolRegistry
     */
    private function registry(): McpToolRegistry
    {
        return McpToolRegistry::booted();
    }

    /**
     * 认证校验：总开关 + 账号密钥比对（账号体系是唯一认证权威）
     *
     * 密钥支持两种请求头携带方式：
     * - X-Mcp-Key: {appkey}
     * - Authorization: Bearer {appkey}
     *
     * 命中账号即完成认证：停用账号（status=0）密钥立即失效并明确提示；
     * 返回该账号的工具白名单——系统账号与普通账号一致，均按白名单授权
     * （系统账号安装时初始授予全部工具，可在「MCP配置」页编辑收紧）。
     *
     * @return array|string 成功返回 {tools: array}，失败返回错误消息
     */
    private function authenticate(): array|string
    {
        // 总开关未开启（安装默认开启，可在「系统配置 → MCP服务」中切换）
        if (!intval(sys_config('mcp_enabled', 1))) {
            return 'MCP 服务未开启';
        }

        // 依次尝试 X-Mcp-Key 与 Authorization: Bearer
        $input = (string)$this->request->header('x-mcp-key', '');
        if ($input === '') {
            $authorization = (string)$this->request->header('authorization', '');
            if (stripos($authorization, 'bearer ') === 0) {
                $input = trim(substr($authorization, 7));
            }
        }

        if ($input === '') {
            return '缺少访问密钥';
        }

        // 账号密钥：命中即认证（软删除账号密钥立即失效）
        $account = app()->make(McpAccountServices::class)->getByKey($input);
        if ($account === null) {
            return '访问密钥错误';
        }
        // 停用账号密钥立即失效（每请求查库，切换即时生效，无缓存）
        if ((int)($account['status'] ?? 1) === 0) {
            return '该账号已停用，请联系管理员启用';
        }
        return ['tools' => array_values((array)($account['tools'] ?? []))];
    }

    /**
     * MCP 服务入口：处理 JSON-RPC 2.0 协议请求
     *
     * 支持方法：
     * - initialize: 协议握手，返回服务端信息与能力
     * - tools/list: 返回可用工具定义列表
     * - tools/call: 调用指定工具执行业务操作
     *
     * @param Request $request HTTP 请求对象
     * @return Json JSON-RPC 2.0 格式响应（通知请求返回 204 空响应）
     */
    public function index(Request $request): Json
    {
        $data = json_decode((string)file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!is_array($data)) {
            return json([
                'jsonrpc' => '2.0',
                'error'   => ['code' => -32700, 'message' => 'Parse error'],
                'id'      => null,
            ]);
        }

        // 协议规定 notification（无 id）无需响应，返回 204 空响应
        if ($id === null) {
            return json(['jsonrpc' => '2.0', 'id' => null])->code(204);
        }

        // 认证检查（命中账号后按其工具白名单授权）
        $auth = $this->authenticate();
        if (is_string($auth)) {
            return json([
                'jsonrpc' => '2.0',
                'id'      => $id,
                'error'   => ['code' => -32600, 'message' => $auth],
            ]);
        }

        $method = (string)($data['method'] ?? '');
        $params = (array)($data['params'] ?? []);

        try {
            return match ($method) {
                'initialize' => $this->responseInitialize($id, $params),
                'tools/list' => json([
                    'jsonrpc' => '2.0',
                    'id'      => $id,
                    'result'  => ['tools' => $this->scopedDefinitions($auth['tools'])],
                ]),
                'tools/call' => $this->responseToolCall($id, $params, $auth['tools']),
                default      => json([
                    'jsonrpc' => '2.0',
                    'id'      => $id,
                    'error'   => ['code' => -32601, 'message' => "Method not found: {$method}"],
                ]),
            };
        } catch (McpException $e) {
            // 业务异常：消息透传给客户端
            return json([
                'jsonrpc' => '2.0',
                'id'      => $id,
                'error'   => ['code' => -32603, 'message' => $e->getMessage()],
            ]);
        } catch (\Throwable $e) {
            // 系统异常：记录日志便于排障，不暴露内部细节
            \think\facade\Log::error('MCP 请求异常: ' . $e->getMessage(), [
                'method'  => $method,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return json([
                'jsonrpc' => '2.0',
                'id'      => $id,
                'error'   => ['code' => -32603, 'message' => '服务器内部错误'],
            ]);
        }
    }

    /**
     * initialize 握手响应：按版本协商规则返回协议版本与服务端能力
     *
     * @param int|string|null $id     请求 ID
     * @param array           $params 请求参数（含客户端 protocolVersion）
     * @return Json
     */
    private function responseInitialize(int|string|null $id, array $params): Json
    {
        // 客户端请求版本在支持列表内则回显，否则返回服务端最新支持版本
        $clientVersion = (string)($params['protocolVersion'] ?? '');
        $protocolVersion = in_array($clientVersion, self::PROTOCOL_VERSIONS, true)
            ? $clientVersion
            : self::PROTOCOL_VERSIONS[0];

        return json([
            'jsonrpc' => '2.0',
            'id'      => $id,
            'result'  => [
                'protocolVersion' => $protocolVersion,
                'capabilities'    => ['tools' => new \stdClass()],
                'serverInfo'      => [
                    'name'    => 'crmeb-mcp-server',
                    'version' => '1.0.0',
                ],
            ],
        ]);
    }

    /**
     * 按授权范围过滤工具定义（tools/list 响应数据）
     *
     * @param array $allowed 授权工具名列表（账号白名单）
     * @return array 工具定义数组
     */
    private function scopedDefinitions(array $allowed): array
    {
        $allowedMap = array_flip($allowed);
        return array_values(array_filter($this->registry()->allDefinitions(), function (array $definition) use ($allowedMap): bool {
            return isset($allowedMap[(string)($definition['name'] ?? '')]);
        }));
    }

    /**
     * tools/call 工具调用响应
     *
     * @param int|string|null $id     请求 ID
     * @param array           $params 请求参数（name + arguments）
     * @param array           $allowed 授权工具名列表（账号白名单）
     * @return Json
     */
    private function responseToolCall(int|string|null $id, array $params, array $allowed): Json
    {
        $toolName = (string)($params['name'] ?? '');
        $toolArgs = (array)($params['arguments'] ?? []);
        $registry = $this->registry();
        // 白名单拦截：未勾选的工具一律拒绝（系统账号同样受白名单约束）
        if (!in_array($toolName, $allowed, true)) {
            throw new McpException("当前账号无权访问工具: {$toolName}");
        }
        $result = $registry->call($toolName, $toolArgs);

        return json([
            'jsonrpc' => '2.0',
            'id'      => $id,
            'result'  => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    ],
                ],
            ],
        ]);
    }
}
