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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use think\App as ThinkApp;

/**
 * MCP 工具：开发文档检索（查询开发规则 / 技能手册 / 项目文档）
 *
 * 面向 AI 二开者的"运行时知识"入口：在开发期检索仓库内的开发资料，
 * 避免模型凭记忆猜测项目约定。检索范围为硬编码白名单（仓库根的
 * help/docs、.crmeb 知识资产与各级 README），不会触及任何环境配置、
 * 密钥或业务数据文件。
 *
 * 知识资产结构与定位见 .crmeb/AGENTS.md；返回结果为命中文件清单
 * （含标题与命中行摘录），正文以仓库内文件为准。
 */
class DevDocsSearchTool implements McpToolInterface
{
    /**
     * 检索范围 => 相对仓库根的路径清单（目录递归扫描 .md；文件直接检索）
     *
     * 顺序即返回优先级：开发期规则与手册优先于背景文档。
     * 白名单只包含文档类资产，杜绝环境配置 / 密钥文件被检索输出。
     */
    private const SCOPE_ROOTS = [
        'rules'  => ['.crmeb/rules'],
        'skills' => ['.crmeb/skills'],
        'agents' => ['.crmeb/agents'],
        'docs'   => ['help/docs', 'backend/README.md', 'frontend/admin/README.md', 'frontend/uni-app/README.md'],
        'entry'  => ['README.md', 'CONTRIBUTING.md', 'llms.txt', '.crmeb/AGENTS.md', '.crmeb/README.md'],
    ];

    /**
     * 单文件大小上限（字节），超限跳过
     */
    private const MAX_FILE_SIZE = 524288;

    /**
     * 单文件返回的最大命中行数
     */
    private const MAX_MATCHES_PER_FILE = 5;

    /**
     * 单行摘录最大字符数
     */
    private const MAX_LINE_LENGTH = 300;

    /**
     * 应用实例（用于定位仓库根目录）
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
        return 'crmeb_dev_docs_search';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '检索本仓库的开发资料：架构规则（rules）、开发技能手册（skills）、'
            . '专项角色说明（agents）、项目文档（docs，含架构/模块/数据库/API/部署/运维）与入口文档（entry）。'
            . '开发 / 二开本系统前，应先检索「依赖铁律」「插件开发」「事件规范」「数据库规范」等关键词'
            . '了解项目强制约定，再动手写代码；返回命中文件的标题与摘录行，正文以文件为准';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '开发文档检索';
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
                'query' => [
                    'type'        => 'string',
                    'description' => '检索关键词，支持多词（空格分隔，需在同一文件中同时命中），如「依赖铁律」「插件 plugin.json」「订单 售后」',
                ],
                'scope' => [
                    'type'        => 'string',
                    'description' => '检索范围：rules=开发规则 skills=技能手册 agents=专项角色 docs=项目文档 entry=入口文档，不传=全部范围',
                    'enum'        => ['rules', 'skills', 'agents', 'docs', 'entry'],
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => '最多返回的文件数（默认 8，上限 20）',
                ],
            ],
            'required' => ['query'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（query 必填，scope/limit 可选）
     * @return array 命中文件清单（含标题与命中行摘录）
     * @throws McpException 关键词为空或范围内无命中
     */
    public function execute(array $args): array
    {
        $query = trim((string)($args['query'] ?? ''));
        if ($query === '') {
            throw new McpException('query 不能为空：请给出要检索的关键词，如「依赖铁律」「插件开发」');
        }

        $scope = strtolower(trim((string)($args['scope'] ?? '')));
        if ($scope !== '' && !isset(self::SCOPE_ROOTS[$scope])) {
            throw new McpException('scope 仅支持：' . implode(' / ', array_keys(self::SCOPE_ROOTS)));
        }

        $limit = max(1, min(20, (int)($args['limit'] ?? 8)));

        // 多词按空格拆分，要求同一文件内全部命中（文件级 AND）
        $words = array_values(array_filter(array_map('trim', preg_split('/\s+/u', $query) ?: []), 'strlen'));
        // 仓库根定位：backend/ 的父目录；Docker 等隔离部署可用 CRMEB_REPO_ROOT 指向挂载的仓库根
        $env = getenv('CRMEB_REPO_ROOT');
        $repoRoot = is_string($env) && trim($env) !== ''
            ? rtrim(trim($env), '/\\')
            : dirname(rtrim($this->app->getRootPath(), '/\\'));
        if (!is_dir($repoRoot . '/help/docs') && !is_dir($repoRoot . '/.crmeb')) {
            throw new McpException('开发文档目录不可达：当前部署形态下容器内没有仓库根（只有 backend/）。'
                . 'Docker 部署请在 docker-compose.yml 为对应容器追加只读挂载 '
                . '"./help:/repo/help:ro" 与 "./.crmeb:/repo/.crmeb:ro"，并设置环境变量 CRMEB_REPO_ROOT=/repo；'
                . '宿主机手动部署无需任何配置');
        }

        $files = [];
        $totalHits = 0;
        foreach (self::SCOPE_ROOTS as $scopeName => $paths) {
            if ($scope !== '' && $scopeName !== $scope) {
                continue;
            }
            foreach ($paths as $path) {
                $totalHits += $this->collectFiles($repoRoot, $path, $scopeName, $words, $limit, $files);
            }
        }

        if ($files === []) {
            throw new McpException("范围内未检索到「{$query}」相关内容：可拆分关键词、去掉 scope 限定后重试");
        }

        return [
            'query'      => $query,
            'scope'      => $scope === '' ? 'all' : $scope,
            'count'      => count($files),
            'total_hits' => $totalHits,
            'list'       => $files,
        ];
    }

    /**
     * 检索单个路径（目录递归 / 单文件），把命中的文件写入结果集
     * @param string $repoRoot 仓库根目录
     * @param string $path 相对路径（目录或文件）
     * @param string $scopeName 范围标识
     * @param array<int, string> $words 关键词（文件级 AND）
     * @param int $limit 结果集上限
     * @param array<int, array> $files 结果集（引用写入）
     * @return int 本路径贡献的命中文件数
     */
    private function collectFiles(string $repoRoot, string $path, string $scopeName, array $words, int $limit, array &$files): int
    {
        $fullPath = $repoRoot . '/' . $path;
        if (!file_exists($fullPath)) {
            return 0;
        }

        $count = 0;
        $targets = is_dir($fullPath) ? $this->markdownFiles($fullPath) : [$fullPath];
        foreach ($targets as $file) {
            if (count($files) >= $limit) {
                break;
            }
            $hit = $this->searchFile($repoRoot, $file, $scopeName, $words);
            if ($hit !== null) {
                $files[] = $hit;
                $count++;
            }
        }
        return $count;
    }

    /**
     * 递归收集目录下的 Markdown 文件（按文件名排序，跳过隐藏目录与超大文件）
     * @param string $dir 目录绝对路径
     * @return array<int, string> 文件绝对路径
     */
    private function markdownFiles(string $dir): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = [];
        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if (!$item->isFile() || strtolower($item->getExtension()) !== 'md') {
                continue;
            }
            // 跳过隐藏目录（如 .crmeb/skills 下的本地工具目录）与超大文件
            $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($dir) + 1));
            foreach (explode('/', $relative) as $segment) {
                if (str_starts_with($segment, '.')) {
                    continue 2;
                }
            }
            if ($item->getSize() > self::MAX_FILE_SIZE) {
                continue;
            }
            $files[] = $item->getPathname();
        }
        sort($files);
        return $files;
    }

    /**
     * 检索单个文件：全部关键词在文件内命中才算命中
     * @param string $repoRoot 仓库根目录
     * @param string $file 文件绝对路径
     * @param string $scopeName 范围标识
     * @param array<int, string> $words 关键词
     * @return array|null 命中结果（含相对路径 / 标题 / 命中行），未命中返回 null
     */
    private function searchFile(string $repoRoot, string $file, string $scopeName, array $words): ?array
    {
        $content = @file_get_contents($file);
        if ($content === false || $content === '') {
            return null;
        }

        // 大小写不敏感：统一转小写后定位，命中行仍取原文
        $lower = mb_strtolower($content);
        $lines = explode("\n", $content);
        $lowerLines = explode("\n", $lower);

        // 文件级 AND：每个关键词至少在一行出现
        foreach ($words as $word) {
            if (mb_strpos($lower, mb_strtolower($word)) === false) {
                return null;
            }
        }

        // 收集命中行（首个词所在的行），附上一行上下文
        $matches = [];
        $firstWord = mb_strtolower($words[0]);
        foreach ($lowerLines as $index => $lowerLine) {
            if (count($matches) >= self::MAX_MATCHES_PER_FILE) {
                break;
            }
            if (!str_contains($lowerLine, $firstWord)) {
                continue;
            }
            // 其余关键词要求在同窗口（命中行 ±1 行）内出现，保证摘录相关
            $windowFrom = max(0, $index - 1);
            $window = implode("\n", array_slice($lowerLines, $windowFrom, min(3, count($lowerLines) - $windowFrom)));
            $relevant = true;
            for ($i = 1; $i < count($words); $i++) {
                if (!str_contains($window, mb_strtolower($words[$i]))) {
                    $relevant = false;
                    break;
                }
            }
            if (!$relevant) {
                continue;
            }
            $matches[] = [
                'line' => $index + 1,
                'text' => $this->clipLine($lines[$index] ?? ''),
            ];
        }
        if ($matches === []) {
            return null;
        }

        return [
            'file'    => str_replace('\\', '/', substr($file, strlen($repoRoot) + 1)),
            'scope'   => $scopeName,
            'title'   => $this->pickTitle($lines),
            'matches' => $matches,
        ];
    }

    /**
     * 摘录单行并截断到上限
     * @param string $line 原始行
     * @return string 摘录文本
     */
    private function clipLine(string $line): string
    {
        $line = trim($line);
        if (mb_strlen($line) > self::MAX_LINE_LENGTH) {
            return mb_substr($line, 0, self::MAX_LINE_LENGTH) . '…';
        }
        return $line;
    }

    /**
     * 取文件标题（首个一级 / 二级标题行，无标题则取文件名）
     * @param array<int, string> $lines 文件行
     * @return string 标题
     */
    private function pickTitle(array $lines): string
    {
        foreach (array_slice($lines, 0, 30) as $line) {
            $line = trim($line);
            if (preg_match('/^#{1,2}\s+(.+)$/u', $line, $m)) {
                return trim($m[1]);
            }
        }
        return '';
    }
}
