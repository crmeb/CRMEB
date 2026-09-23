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

namespace modules\service\mcp\tools;

use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;
use think\App as ThinkApp;

/**
 * MCP 工具：路由定义检索（查询接口路由与控制器入口）
 *
 * 面向 AI 二开者的"运行时知识"入口：静态检索仓库内全部路由定义文件，
 * 按关键词返回路由定义行（含文件 / 应用 / 行号），用于定位「某接口的
 * 路由与控制器在哪」「某模块对外暴露哪些接口」——避免凭记忆猜接口。
 *
 * 路由文件来源：app/{应用}/route/ 与 modules/ 各模块 route/（模块路由
 * 自动加载机制见 .crmeb/rules/架构规范详解.md）。工具只做只读检索，
 * 不解析请求，路由的实际注册以运行时为准。
 */
class DevRouteSearchTool implements McpToolInterface
{
    /**
     * 多应用路由目录（相对 backend 根，通配应用名）
     */
    private const APP_ROUTE_GLOB = 'app/*/route';

    /**
     * 模块路由目录（相对 backend 根，覆盖 modules/{模块}/route 与 modules/{父}/{子}/route）
     */
    private const MODULE_ROUTE_GLOBS = ['modules/*/route', 'modules/*/*/route'];

    /**
     * 后台全局路由目录
     */
    private const GLOBAL_ROUTE_DIR = 'route';

    /**
     * 单行摘录最大字符数
     */
    private const MAX_LINE_LENGTH = 260;

    /**
     * 应用实例（用于定位 backend 根目录）
     * @var ThinkApp
     */
    private ThinkApp $app;

    /**
     * 构造函数
     * @param ThinkApp $app 应用实例
     */
    public function __construct(ThinkApp $app)
    {
        $this->app = $app;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_dev_route_search';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '检索接口路由定义：按关键词（路径片段 / 控制器名，如 order、StoreProduct、refund）'
            . '返回路由定义行及其所在文件、应用（adminapi/api/outapi/kefuapi）、模块与行号，'
            . '用于定位「某接口的路由与控制器在哪」「某模块暴露哪些接口」。'
            . 'keyword 传空则返回各路由文件清单概览；路由的最终注册以运行时为准';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '路由定义检索';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => [
                    'type'        => 'string',
                    'description' => '检索关键词（路由路径片段或控制器名，如 order、StoreProduct、afterSale），不传返回路由文件清单',
                ],
                'app' => [
                    'type'        => 'string',
                    'description' => '应用筛选：adminapi=管理端 api=用户端 outapi=对外接口 kefuapi=客服，不传=全部',
                    'enum'        => ['adminapi', 'api', 'outapi', 'kefuapi'],
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => '最多返回的命中条数（默认 30，上限 100）',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword/app/limit 均可选）
     * @return array 路由文件清单或命中行列表
     * @throws McpException 范围内无路由文件或无命中
     */
    public function execute(array $args): array
    {
        $keyword = trim((string)($args['keyword'] ?? ''));
        $app = strtolower(trim((string)($args['app'] ?? '')));
        $limit = max(1, min(100, (int)($args['limit'] ?? 30)));

        $backendRoot = rtrim($this->app->getRootPath(), '/\\');
        $files = $this->collectRouteFiles($backendRoot);
        if ($files === []) {
            throw new McpException('未发现任何路由文件（app/*/route、modules/**/route、route/）');
        }

        // keyword 为空：返回路由文件清单概览
        if ($keyword === '') {
            $list = [];
            foreach ($files as $file) {
                if ($app !== '' && $file['app'] !== $app) {
                    continue;
                }
                $list[] = $file + ['route_count' => $this->countRouteLines($file['path'])];
                if (count($list) >= $limit) {
                    break;
                }
            }
            return ['mode' => 'files', 'count' => count($list), 'list' => $list];
        }

        // keyword 检索：匹配定义行与注释行
        $lowerKeyword = mb_strtolower($keyword);
        $hits = [];
        foreach ($files as $file) {
            if ($app !== '' && $file['app'] !== $app) {
                continue;
            }
            $content = @file_get_contents($file['path']);
            if ($content === false) {
                continue;
            }
            foreach (explode("\n", $content) as $index => $line) {
                if (count($hits) >= $limit) {
                    break 2;
                }
                if (!str_contains(mb_strtolower($line), $lowerKeyword)) {
                    continue;
                }
                $code = trim($line);
                // 只保留定义行与紧邻注释（避免把无关字符串行都带出来）
                if (!preg_match('/Route::[A-Za-z_]+\s*\(/', $code) && !str_starts_with($code, '//') && !str_starts_with($code, '#')) {
                    continue;
                }
                $hits[] = [
                    'file' => str_replace('\\', '/', substr($file['path'], strlen($backendRoot) + 1)),
                    'app'  => $file['app'],
                    'line' => $index + 1,
                    'code' => $this->clip($code),
                ];
            }
        }

        if ($hits === []) {
            throw new McpException("路由文件中未检索到「{$keyword}」：可尝试控制器名（如 StoreProduct）、路径片段（如 refund）或去掉 app 筛选");
        }

        return [
            'mode'  => 'hits',
            'query' => $keyword,
            'count' => count($hits),
            'list'  => $hits,
        ];
    }

    /**
     * 收集全部路由文件（含归属应用 / 模块标注）
     * @param string $backendRoot backend 根目录
     * @return array<int, array{path:string, app:string, label:string}>
     */
    private function collectRouteFiles(string $backendRoot): array
    {
        $files = [];

        // 多应用路由：app/{应用}/route/*.php
        foreach (glob($backendRoot . '/' . self::APP_ROUTE_GLOB, GLOB_ONLYDIR) ?: [] as $dir) {
            $app = basename(dirname($dir));
            foreach (glob($dir . '/*.php') ?: [] as $file) {
                $files[] = [
                    'path'  => $file,
                    'app'   => $app,
                    'label' => 'app/' . $app . '/route/' . basename($file),
                ];
            }
        }

        // 模块路由：modules/{模块}/route 与 modules/{父}/{子}/route
        foreach (self::MODULE_ROUTE_GLOBS as $pattern) {
            foreach (glob($backendRoot . '/' . $pattern, GLOB_ONLYDIR) ?: [] as $dir) {
                $relative = str_replace('\\', '/', substr($dir, strlen($backendRoot) + 1));
                foreach (glob($dir . '/*.php') ?: [] as $file) {
                    $files[] = [
                        'path'  => $file,
                        'app'   => 'modules',
                        'label' => $relative . '/' . basename($file),
                    ];
                }
            }
        }

        // 后台全局路由
        foreach (glob($backendRoot . '/' . self::GLOBAL_ROUTE_DIR . '/*.php') ?: [] as $file) {
            $files[] = [
                'path'  => $file,
                'app'   => 'global',
                'label' => self::GLOBAL_ROUTE_DIR . '/' . basename($file),
            ];
        }

        return $files;
    }

    /**
     * 统计文件中的路由定义行数
     * @param string $file 文件绝对路径
     * @return int 定义行数
     */
    private function countRouteLines(string $file): int
    {
        $content = @file_get_contents($file);
        if ($content === false) {
            return 0;
        }
        return (int)preg_match_all('/Route::[A-Za-z_]+\s*\(/', $content);
    }

    /**
     * 摘录单行并截断到上限
     * @param string $line 原始行
     * @return string 摘录文本
     */
    private function clip(string $line): string
    {
        if (mb_strlen($line) > self::MAX_LINE_LENGTH) {
            return mb_substr($line, 0, self::MAX_LINE_LENGTH) . '…';
        }
        return $line;
    }
}
