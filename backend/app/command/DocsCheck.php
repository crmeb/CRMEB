<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 文档一致性校验命令
 *
 * 纯文件系统静态校验，不依赖数据库 / Redis，可直接接入 CI。扫描范围（文档白名单）：
 * .crmeb/、help/docs/、tests/ 目录及根 AGENTS.md、frontend/{admin,uni-app}/AGENTS.md。
 * backend、frontend 业务目录内部 README 不在扫描范围（避免误扫与噪声，按需扩展）。
 *
 * 检查项（对应 .crmeb/rules/目录与文档规范.md 与 tests/plans 方案批次 6）：
 * 1. error   Markdown 本地相对链接目标存在（剥离代码块与行内代码后解析，跳过 http/#/file://）
 * 2. error   frontmatter 约束：rules / skills SKILL.md / agents 的 name 与目录名一致、description 非空；
 *            references/ 子文档仅在带 frontmatter 时校验 description 非空；整体缺失 frontmatter 降为 warning（灰度）
 * 3. warning 索引登记一致性：.crmeb/AGENTS.md 与 help/docs/文档索引.md 是否漏登实际存在的规则/技能/角色/文档
 * 4. warning 行内代码中的仓库内路径真实存在（仅查以 backend/、frontend/、help/、tests/、.crmeb/ 开头且含 / 的路径）
 *
 * 用法：php think docs:check [--root 仓库根目录]
 * 退出码：0=无 error（warning 不阻断）；1=存在 error，可接入 CI / 发布流程拦截（与 event:check 同模式）
 */
class DocsCheck extends Command
{
    /** 文档扫描根（相对仓库根的目录） */
    protected array $scanDirs = ['.crmeb', 'help/docs', 'tests'];

    /** 额外扫描的单文件（相对仓库根） */
    protected array $scanFiles = ['AGENTS.md', 'frontend/admin/AGENTS.md', 'frontend/uni-app/AGENTS.md'];

    /** 行内代码路径白名单前缀：仅这些目录开头的路径才做存在性检查（防误报，灰度策略） */
    protected array $pathPrefixes = ['backend/', 'frontend/', 'help/', 'docker/', 'tests/', '.crmeb/'];

    /** 索引检查豁免：存在于目录但无需登记的文件 */
    protected array $indexExempts = ['文档索引.md'];

    /** 行内路径豁免：随 .gitignore 不入库、由脚本或容器创建的运行时目录（clone 后不存在属正常） */
    protected array $runtimeExempts = [
        'backend/runtime', 'backend/public/uploads', 'backend/public/install.lock',
        'docker/mysql/data', 'docker/mysql/log', 'docker/nginx/log',
        'frontend/admin/dist', 'frontend/uni-app/dist',
    ];

    /** 行内路径豁免的目录前缀：过程文档与可分发模板描述的是计划/待办路径，存在性随实施变化 */
    protected array $inlinePathExemptDirs = ['tests/plans/', 'tests/reports/', 'tests/skills/'];

    /** @var string 仓库根目录（运行时定位） */
    protected string $repoRoot = '';

    /** @var array<int, array{level: string, file: string, line: int, msg: string}> 问题清单 */
    protected array $issues = [];

    protected function configure()
    {
        $this->setName('docs:check')
            ->addOption('root', null, Option::VALUE_REQUIRED, '仓库根目录（默认自动从 backend 向上探测含 AGENTS.md 与 .crmeb 的目录）')
            ->setDescription('文档一致性校验（本地链接 / frontmatter / 索引登记 / 引用路径）');
    }

    /**
     * 执行校验
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=无 error 级问题，1=存在 error 级问题
     */
    protected function execute(Input $input, Output $output)
    {
        $root = $input->getOption('root') ?: dirname($this->app->getRootPath());
        $this->repoRoot = rtrim((string)$root, '/\\');

        if (!is_file($this->repoRoot . '/AGENTS.md') || !is_dir($this->repoRoot . '/.crmeb')) {
            $output->error("无法定位仓库根：{$this->repoRoot} 缺少 AGENTS.md 或 .crmeb/，请用 --root 显式指定");
            return 1;
        }

        $files = $this->collectMarkdownFiles();

        // 预处理：每篇文档 → ['rel' => 相对路径, 'lines' => 逐行文本, 'codeTokens' => 行内代码片段(带行号)]
        $docs = [];
        foreach ($files as $abs) {
            $content = file_get_contents($abs);
            if ($content === false || $content === '') {
                continue;
            }
            $rel = ltrim(str_replace('\\', '/', substr($abs, strlen($this->repoRoot))), '/');
            $docs[$rel] = $this->preprocess($content);
        }

        $this->checkLinks($docs);
        $this->checkFrontmatter();
        $this->checkIndexes($docs);
        $this->checkInlinePaths($docs);

        // 按级别分组输出：error 在前
        $errors = array_values(array_filter($this->issues, fn($i) => $i['level'] === 'error'));
        $warnings = array_values(array_filter($this->issues, fn($i) => $i['level'] === 'warning'));

        $output->writeln('文档一致性校验');
        $output->writeln('扫描：' . count($docs) . ' 篇 Markdown / 仓库根：' . $this->repoRoot);
        $output->writeln(str_repeat('-', 72));

        foreach ([['❌', 'error', $errors], ['⚠️ ', 'warning', $warnings]] as [$icon, $level, $list]) {
            $label = $level === 'error' ? 'error 级' : 'warning 级';
            if (!$list) {
                $output->writeln("✅ {$label}：0");
                continue;
            }
            $output->writeln($icon . " {$label}：" . count($list) . ' 个');
            foreach ($list as $issue) {
                $output->writeln('  ' . $issue['file'] . ':' . $issue['line'] . '  ' . $issue['msg']);
            }
        }

        $output->writeln(str_repeat('-', 72));
        if ($errors) {
            $output->error('校验未通过：存在 ' . count($errors) . ' 个 error 级问题，请修复后重试');
            return 1;
        }
        $output->writeln('✅ 校验通过' . ($warnings ? '（存在 warning，灰度观察项，不阻断）' : ''));
        return 0;
    }

    /**
     * 收集扫描范围内的 Markdown 文件（绝对路径列表）
     * @return array<int, string>
     */
    protected function collectMarkdownFiles(): array
    {
        $files = [];
        foreach ($this->scanDirs as $dir) {
            $base = $this->repoRoot . '/' . $dir;
            if (!is_dir($base)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $f) {
                if ($f->getExtension() === 'md') {
                    $files[] = $f->getPathname();
                }
            }
        }
        foreach ($this->scanFiles as $rel) {
            if (is_file($this->repoRoot . '/' . $rel)) {
                $files[] = $this->repoRoot . '/' . $rel;
            }
        }
        return array_values(array_unique($files));
    }

    /**
     * 预处理文档：剥离 fenced 代码块（置空但保留行号），收集行内代码片段
     * @param string $content 原文
     * @return array{raw: string, lines: array<int, string>, code: array<int, array{line: int, token: string}>}
     */
    protected function preprocess(string $content): array
    {
        $lines = explode("\n", $content);
        $inFence = false;
        $fence = '';
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*(`{3,}|~{3,})/', $line, $m)) {
                if (!$inFence) {
                    $inFence = true;
                    $fence = $m[1][0]; // 以起始围栏字符为准，关闭围栏只认同一字符
                    $lines[$i] = '';
                    continue;
                }
                if (str_starts_with(trim($line), $fence)) {
                    $inFence = false;
                }
                $lines[$i] = '';
                continue;
            }
            if ($inFence) {
                $lines[$i] = '';
            }
        }

        // 第二遍：行内代码收集（fenced 已置空），并从正文剥离，供链接检查使用
        $code = [];
        foreach ($lines as $i => $line) {
            if ($line === '') {
                continue;
            }
            if (preg_match_all('/`([^`\n]+)`/', $line, $m)) {
                foreach ($m[1] as $token) {
                    $code[] = ['line' => $i + 1, 'token' => $token];
                }
                $lines[$i] = preg_replace('/`[^`\n]*`/', '``', $line);
            }
        }
        return ['raw' => $content, 'lines' => $lines, 'code' => $code];
    }

    /**
     * 检查 1：本地相对链接目标存在（error 级）
     * @param array<string, array<string, mixed>> $docs 文档预处理结果（相对路径 => raw/lines/code）
     */
    protected function checkLinks(array $docs): void
    {
        foreach ($docs as $rel => $doc) {
            $baseDir = dirname($this->repoRoot . '/' . $rel);
            foreach ($doc['lines'] as $idx => $line) {
                // 匹配 [text](target) 与 ![alt](target)，target 取第一个空白前的 token（兼容 [a](b "title")）
                if (!preg_match_all('/\]\(\s*([^)\s]+)/', $line, $m)) {
                    continue;
                }
                foreach ($m[1] as $target) {
                    $lineNo = $idx + 1;
                    // 跳过协议与锚点链接
                    if ($target[0] === '#' || preg_match('~^(https?:|mailto:|file:|tel:)~i', $target)) {
                        continue;
                    }
                    // 剥离锚点与查询串，解码 URL 编码（如 %20）
                    $path = rawurldecode(preg_replace('/[?#].*$/', '', $target));
                    if ($path === '' || str_contains($path, '\\') || str_contains($path, '<')) {
                        continue;
                    }
                    $path = rtrim($path, '/');
                    $full = $this->normalizePath($baseDir . '/' . $path);
                    if ($full === null) {
                        continue; // 含 .. 逃逸仓库等异常，交由人工判断，不在本命令职责内
                    }
                    if (!file_exists($full)) {
                        $this->issues[] = [
                            'level' => 'error', 'file' => $rel, 'line' => $lineNo,
                            'msg' => "本地链接目标不存在：{$target}",
                        ];
                    }
                }
            }
        }
    }

    /**
     * 检查 2：frontmatter 约束（name/description）
     * rules 与 agents：整目录；skills：仅 SKILL.md 与 references/（references 仅在带 frontmatter 时查 description）
     */
    protected function checkFrontmatter(): void
    {
        // 规则文件：name 无处比对（文件名即真源），校验 description 非空
        foreach (glob($this->repoRoot . '/.crmeb/rules/*.md') ?: [] as $abs) {
            $rel = $this->toRel($abs);
            $fm = $this->parseFrontmatter((string)file_get_contents($abs));
            if ($fm === null) {
                $this->issues[] = ['level' => 'warning', 'file' => $rel, 'line' => 1, 'msg' => '缺少 frontmatter（约定应含 description）'];
                continue;
            }
            if (trim((string)($fm['description'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => 'frontmatter description 为空'];
            }
        }

        // 技能：SKILL.md 的 name 必须与目录名一致，description 非空；references/ 子文档按约定"若带则非空"
        foreach (glob($this->repoRoot . '/.crmeb/skills/*/SKILL.md') ?: [] as $abs) {
            $dirName = basename(dirname($abs));
            $rel = $this->toRel($abs);
            $fm = $this->parseFrontmatter((string)file_get_contents($abs));
            if ($fm === null) {
                $this->issues[] = ['level' => 'warning', 'file' => $rel, 'line' => 1, 'msg' => '缺少 frontmatter（约定应含 name 与目录名一致的 name、description）'];
                continue;
            }
            $name = trim((string)($fm['name'] ?? ''));
            if ($name === '') {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => "frontmatter name 缺失（应为 {$dirName}）"];
            } elseif ($name !== $dirName) {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => "frontmatter name（{$name}）与目录名（{$dirName}）不一致"];
            }
            if (trim((string)($fm['description'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => 'frontmatter description 为空'];
            }
        }
        foreach (glob($this->repoRoot . '/.crmeb/skills/*/references/**/*.md') ?: [] as $abs) {
            $fm = $this->parseFrontmatter((string)file_get_contents($abs));
            if ($fm !== null && trim((string)($fm['description'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'file' => $this->toRel($abs), 'line' => 1, 'msg' => 'frontmatter description 为空（references/ 子文档带 frontmatter 时必须非空）'];
            }
        }

        // 子智能体：单文件，name 与文件名一致，description 非空
        foreach (glob($this->repoRoot . '/.crmeb/agents/*.md') ?: [] as $abs) {
            $stem = basename($abs, '.md');
            $rel = $this->toRel($abs);
            $fm = $this->parseFrontmatter((string)file_get_contents($abs));
            if ($fm === null) {
                $this->issues[] = ['level' => 'warning', 'file' => $rel, 'line' => 1, 'msg' => '缺少 frontmatter（约定应含 name 与文件名一致的 name、description）'];
                continue;
            }
            $name = trim((string)($fm['name'] ?? ''));
            if ($name === '') {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => "frontmatter name 缺失（应为 {$stem}）"];
            } elseif ($name !== $stem) {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => "frontmatter name（{$name}）与文件名（{$stem}）不一致"];
            }
            if (trim((string)($fm['description'] ?? '')) === '') {
                $this->issues[] = ['level' => 'error', 'file' => $rel, 'line' => 1, 'msg' => 'frontmatter description 为空'];
            }
        }
    }

    /**
     * 检查 3：索引登记一致性（warning 级）
     * 反向检查：目录实际存在的规则 / 技能 / 角色 / 文档是否被对应索引文件提及（正向链接由检查 1 覆盖）
     * @param array<string, array<string, mixed>> $docs 文档预处理结果（相对路径 => raw/lines/code）
     */
    protected function checkIndexes(array $docs): void
    {
        $agentsMd = $docs['.crmeb/AGENTS.md']['raw'] ?? null;
        if ($agentsMd !== null) {
            // 用原始全文匹配：索引表中技能/角色名多在反引号内，预处理后会被剥离造成误报
            $joined = $agentsMd;
            foreach (glob($this->repoRoot . '/.crmeb/rules/*.md') ?: [] as $abs) {
                $name = basename($abs);
                if (!str_contains($joined, $name)) {
                    $this->issues[] = ['level' => 'warning', 'file' => '.crmeb/AGENTS.md', 'line' => 0, 'msg' => "规则未登记索引：rules/{$name}"];
                }
            }
            foreach (glob($this->repoRoot . '/.crmeb/skills/*', GLOB_ONLYDIR) ?: [] as $dir) {
                $name = basename($dir);
                if (!str_contains($joined, $name)) {
                    $this->issues[] = ['level' => 'warning', 'file' => '.crmeb/AGENTS.md', 'line' => 0, 'msg' => "技能未登记索引：{$name}"];
                }
            }
            foreach (glob($this->repoRoot . '/.crmeb/agents/*.md') ?: [] as $abs) {
                $name = basename($abs, '.md');
                if (!str_contains($joined, $name)) {
                    $this->issues[] = ['level' => 'warning', 'file' => '.crmeb/AGENTS.md', 'line' => 0, 'msg' => "子智能体未登记索引：{$name}"];
                }
            }
        }

        $docIndex = 'help/docs/文档索引.md';
        if (isset($docs[$docIndex])) {
            $joined = $docs[$docIndex]['raw'];
            foreach (glob($this->repoRoot . '/help/docs/**/*.md') ?: [] as $abs) {
                $name = basename($abs);
                if (in_array($name, $this->indexExempts, true)) {
                    continue;
                }
                if (!$this->indexMentions($joined, $name)) {
                    $this->issues[] = ['level' => 'warning', 'file' => $docIndex, 'line' => 0, 'msg' => "文档未登记索引：help/docs/ 下 {$name}"];
                }
            }
        }
    }

    /**
     * 索引是否登记了某文档：以原文或 URL 编码形态出现（覆盖链接文本、链接目标、纯文本引用）
     * @param string $joined 索引全文
     * @param string $name 文件名（含 .md）
     */
    protected function indexMentions(string $joined, string $name): bool
    {
        return str_contains($joined, $name) || str_contains($joined, rawurlencode($name));
    }

    /**
     * 检查 4：行内代码中的仓库内路径真实存在（warning 级）
     * 误报控制：仅检查白名单前缀开头、含 /、不含通配符与占位符的 token
     * @param array<string, array<string, mixed>> $docs 文档预处理结果（相对路径 => raw/lines/code）
     */
    protected function checkInlinePaths(array $docs): void
    {
        foreach ($docs as $rel => $doc) {
            // dev/ 过程文档豁免：引用的是计划与待办路径（如"待创建的命令类"），存在性随实施变化
            foreach ($this->inlinePathExemptDirs as $exempt) {
                if (str_starts_with($rel, $exempt)) {
                    continue 2;
                }
            }
            foreach ($doc['code'] as $hit) {
                // 归一化：剥离行号 / ::method() 调用形态与全角等不可见空白
                $token = preg_replace('/^[\s\x{00A0}\x{3000}]+|[\s\x{00A0}\x{3000}]+$/u', '', $hit['token']);
                if ($token === '') {
                    continue;
                }
                // 占位符 / 通配符 / 命令形态直接跳过
                if (preg_match('/[<>*{}?]|\.\.\./', $token) || preg_match('/\s/u', $token)) {
                    continue;
                }
                if (!str_contains($token, '/')) {
                    continue;
                }
                // 剥离 ::method() 调用后缀与行号后缀（:53、:882-910、:36,48、#L123）
                $path = preg_replace('/::[A-Za-z_]\w*(?:\(\))?$/', '', $token);
                $path = preg_replace('/(?:#L\d+)?(?::\d+(?:[-,]\d+)*)?$/', '', $path);
                $path = rtrim($path, '/.,;');
                $matched = false;
                foreach ($this->pathPrefixes as $prefix) {
                    if (str_starts_with($path, $prefix)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    continue;
                }
                if (!file_exists($this->repoRoot . '/' . $path)) {
                    // 运行时目录（clone 后由脚本/容器创建）属正常缺失
                    foreach ($this->runtimeExempts as $exempt) {
                        if (str_starts_with($path, $exempt)) {
                            continue 2;
                        }
                    }
                    $this->issues[] = [
                        'level' => 'warning', 'file' => $rel, 'line' => $hit['line'],
                        'msg' => "引用的仓库路径不存在：{$token}",
                    ];
                }
            }
        }
    }

    /**
     * 解析 YAML frontmatter（仅支持顶层 key: value 形态，满足 name/description 用途）
     * @param string $content 文档全文
     * @return array<string, string>|null null=无 frontmatter
     */
    protected function parseFrontmatter(string $content): ?array
    {
        if (!preg_match('/\A---\r?\n(.*?)\r?\n---/s', $content, $m)) {
            return null;
        }
        $fm = [];
        foreach (explode("\n", $m[1]) as $line) {
            if (preg_match('/^([A-Za-z_][A-Za-z0-9_-]*)\s*:\s*(.*)$/', trim($line), $kv)) {
                $fm[$kv[1]] = trim($kv[2], " \t\"'");
            }
        }
        return $fm;
    }

    /**
     * 规整路径并防止越出仓库根（含 .. 的路径规整后校验前缀）
     * @param string $path 待规整路径
     * @return string|null 规整后的绝对路径；越界返回 null
     */
    protected function normalizePath(string $path): ?string
    {
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $seg;
        }
        $full = '/' . implode('/', $parts);
        $root = rtrim($this->repoRoot, '/');
        return ($full === $root || str_starts_with($full, $root . '/')) ? $full : null;
    }

    /**
     * 绝对路径转相对仓库根的显示路径
     * @param string $abs 绝对路径
     */
    protected function toRel(string $abs): string
    {
        return ltrim(str_replace('\\', '/', substr($abs, strlen($this->repoRoot))), '/');
    }
}
