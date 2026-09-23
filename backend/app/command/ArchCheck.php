<?php
declare(strict_types=1);

namespace app\command;

use PhpToken;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 架构铁律静态校验命令
 *
 * 机器化校验 .crmeb/rules/架构规范.md 的依赖铁律与事件调用规范（crmeb-arch-checker
 * 子智能体判定逻辑的脚本化入口，供本地 / CI 复用）：
 * 1. app/ 层反向引用 modules/（严重）
 * 2. crmeb/ 层引用 app/ 或 modules/（严重）
 * 3. driver/ 层引用 app/、modules/ 或 crmeb/（严重）
 * 4. modules/ 跨模块互引（严重，引用自身模块前缀允许）
 * 5. 事件触发使用类常量而非字符串 key（一般）
 * 6. 残留无用 use 语句（轻微，静态判定存在局限，需人工确认）
 *
 * 检查基于 token 流：剔除注释与 heredoc 后再匹配，避免注释 / 模板文本误报；
 * use 语句按前缀判定，非 use 形态（new \X\Y、::class、类名字符串）按全文匹配兜底。
 *
 * 用法：php think arch:check [--dir=app,crmeb,modules,driver] [--json]
 * 退出码：0=通过（轻微/一般不拦截）；1=存在严重违规（可接入 CI 拦截）
 */
class ArchCheck extends Command
{
    /**
     * 依赖铁律：各层禁止引用的命名空间首段（完整对照表见 .crmeb/rules/架构规范.md）
     *
     * 注：driver/ 层继承 crmeb\services\*\Base* 基类、实现其接口是官方驱动的标准范式，
     * 属允许引用，故 driver 层仅禁 app 与 modules。
     */
    private const FORBIDDEN_NS = [
        'app'    => ['modules'],
        'crmeb'  => ['app', 'modules'],
        'driver' => ['app', 'modules'],
    ];

    /**
     * 默认扫描层（与铁律四层一致）
     */
    private const LAYERS = ['app', 'crmeb', 'modules', 'driver'];

    /**
     * 扫描时跳过的目录名
     */
    private const SKIP_DIRS = ['vendor', 'runtime', 'node_modules', 'backup', '.git', '.crmeb'];

    /**
     * 违规等级：严重（影响退出码）
     */
    private const LEVEL_ERROR = 'error';

    /**
     * 违规等级：一般（不影响退出码）
     */
    private const LEVEL_WARN = 'warn';

    /**
     * 违规清单
     * @var array<int, array{level:string, check:string, layer:string, file:string, line:int, detail:string}>
     */
    private array $violations = [];

    /**
     * 已记录违规去重键集合（文件|检查类型|目标）
     * @var array<string, bool>
     */
    private array $recorded = [];

    /**
     * 命令定义
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('arch:check')
            ->addOption('dir', null, Option::VALUE_REQUIRED, '限定检查的层目录（逗号分隔，默认 app,crmeb,modules,driver）')
            ->addOption('json', null, Option::VALUE_NONE, '以 JSON 格式输出（供 CI 消费）')
            ->setDescription('架构铁律静态校验（依赖方向 / 跨模块互引 / 事件 key / 残留 use）');
    }

    /**
     * 执行校验
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=通过；1=存在严重违规
     */
    protected function execute(Input $input, Output $output): int
    {
        $root = rtrim($this->app->getRootPath(), '/\\');
        $dirsOpt = (string)($input->getOption('dir') ?? '');
        // dir 支持「层」或「层/子路径」两种粒度（如 app、modules/selftest），需还原出所属层用于铁律判定
        $scopes = $dirsOpt !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $dirsOpt))))
            : self::LAYERS;

        $fileCount = 0;
        foreach ($scopes as $scope) {
            $fileCount += $this->scanScope($root, $scope);
        }

        if ($input->getOption('json')) {
            $output->writeln(json_encode([
                'files'   => $fileCount,
                'summary' => $this->summarize(),
                'items'   => $this->violations,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $this->summarize()['error'] > 0 ? 1 : 0;
        }

        $this->render($output, $fileCount);
        return $this->summarize()['error'] > 0 ? 1 : 0;
    }

    /**
     * 扫描一个范围（层或层下子路径）的全部 PHP 文件
     * @param string $root 项目根目录
     * @param string $scope 范围（app、modules、modules/selftest 等）
     * @return int 扫描的文件数
     */
    private function scanScope(string $root, string $scope): int
    {
        $segments = explode('/', trim($scope, '/'));
        // 首段为架构层名，用于查铁律表；剩余段为可选子路径
        $layer = array_shift($segments);
        $base = $root . DIRECTORY_SEPARATOR . $layer . ($segments ? '/' . implode('/', $segments) : '');
        if (!is_dir($base)) {
            return 0;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            // 跳过运行时产物与依赖目录
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            foreach (self::SKIP_DIRS as $skip) {
                if (in_array($skip, explode('/', $relative), true)) {
                    continue 2;
                }
            }

            $count++;
            // modules 层的自身模块名取第一级目录（modules/{name}/...），用于跨模块互引判定
            $module = null;
            if ($layer === 'modules') {
                $module = explode('/', $relative)[1] ?? null;
            }

            $this->checkFile($root, $relative, $layer, $module);
        }
        return $count;
    }

    /**
     * 解析并检查单个文件
     * @param string $root 项目根目录（用于绝对路径展示）
     * @param string $relative 相对路径（如 app/adminapi/controller/Xxx.php）
     * @param string $layer 所属层
     * @param string|null $module modules 层的自身模块名，其余层为 null
     * @return void
     */
    private function checkFile(string $root, string $relative, string $layer, ?string $module): void
    {
        $code = @file_get_contents($root . '/' . $relative);
        if ($code === false || $code === '') {
            return;
        }

        // token 化并剔除注释 / heredoc，收集顶层 use 语句与有效代码文本
        [$codeText, $uses] = $this->tokenize($code);

        // 一、use 语句形态的依赖违规
        foreach ($uses as $use) {
            if ($use['is_group'] || $use['kind'] !== '') {
                continue;
            }
            $first = $use['segments'][0] ?? '';
            if (in_array($first, self::FORBIDDEN_NS[$layer] ?? [], true)) {
                $this->record(self::LEVEL_ERROR, 'use-forbidden', 'F:' . $first, $relative, $use['line'],
                    "use {$use['raw']}; —— {$layer}/ 层禁止引用 {$first}/");
                continue;
            }
            // modules 层跨模块互引：引用 modules\ 其他一级模块
            if ($layer === 'modules' && $first === 'modules'
                && ($use['segments'][1] ?? '') !== ($module ?? '')) {
                $target = $use['segments'][1] ?? '?';
                $this->record(self::LEVEL_ERROR, 'cross-module-use', 'C:' . $target, $relative, $use['line'],
                    "use {$use['raw']}; —— 跨模块引用 {$target}/，跨模块通信走核心契约或字符串事件");
            }
        }

        // 二、全文兜底：非 use 形态引用（new \X\Y、::class、类名字符串等）
        if (preg_match_all('/(?<![A-Za-z0-9_\\\\])(?:\\\\)?(app|modules|crmeb|driver)\\\\([A-Za-z0-9_]+)/',
            $codeText, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            $lineOffsets = $this->buildLineMap($codeText);
            foreach ($matches as $m) {
                $target = $m[1][0];
                $targetChild = $m[2][0];
                $line = $this->lineAt($lineOffsets, (int)$m[0][1]);
                $snippet = $m[0][0];
                if (in_array($target, self::FORBIDDEN_NS[$layer] ?? [], true)) {
                    $this->record(self::LEVEL_ERROR, 'inline-forbidden', 'F:' . $target, $relative, $line,
                        "{$snippet} —— {$layer}/ 层禁止引用 {$target}/");
                } elseif ($layer === 'modules' && $target === 'modules' && $targetChild !== ($module ?? '')) {
                    $this->record(self::LEVEL_ERROR, 'inline-cross-module', 'C:' . $targetChild, $relative, $line,
                        "{$snippet} —— 跨模块引用 {$targetChild}/");
                }
            }
        }

        // 三、事件触发使用类常量（规范要求字符串 key）
        if (preg_match_all('/\b(?:event|Event::trigger|Event::until)\s*\(\s*([A-Za-z_][A-Za-z0-9_\\\\]*)::class/',
            $codeText, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            $lineOffsets = $this->buildLineMap($codeText);
            foreach ($matches as $m) {
                $this->record(self::LEVEL_WARN, 'event-class-key', 'E:' . $m[0][0], $relative,
                    $this->lineAt($lineOffsets, (int)$m[0][1]),
                    "{$m[0][0]} —— 事件 key 必须用字符串（event('XxxEvent', ...)），禁用类常量");
            }
        }

        // 四、残留无用 use（轻微提示，需人工确认）
        foreach ($uses as $use) {
            if ($use['is_group'] || $use['kind'] !== '' || $this->isShortNameUsed($code, $use)) {
                continue;
            }
            $this->record(self::LEVEL_WARN, 'unused-use', 'U:' . $use['raw'], $relative, $use['line'],
                "use {$use['raw']}; —— 全文未发现对短名 {$use['short']} 的引用，疑似残留（请人工确认）");
        }
    }

    /**
     * token 化：剔除注释与 heredoc，收集顶层 use 语句
     * @param string $code 源码
     * @return array{0:string, 1:array<int, array{raw:string, segments:array<int,string>, short:string, line:int, is_group:bool, kind:string, start:int, end:int}>}
     */
    private function tokenize(string $code): array
    {
        $tokens = PhpToken::tokenize($code, TOKEN_PARSE);
        $parts = [];
        $uses = [];
        $depth = 0;
        $n = count($tokens);

        for ($i = 0; $i < $n; $i++) {
            $token = $tokens[$i];
            // 注释不参与任何判定
            if ($token->is(T_COMMENT) || $token->is(T_DOC_COMMENT)) {
                continue;
            }
            // heredoc / nowdoc 区段整段跳过：模板文本中的命名空间字样不算真实引用
            if ($token->is(T_START_HEREDOC)) {
                while ($i < $n && !$tokens[$i]->is(T_END_HEREDOC)) {
                    $i++;
                }
                continue;
            }

            // 收集顶层 use 语句（类体内 trait use / 闭包 use 按嵌套深度排除）
            if ($token->is(T_USE) && $depth === 0) {
                $line = $token->line;
                $stmt = '';
                $end = $i;
                for ($j = $i + 1; $j < $n; $j++) {
                    $next = $tokens[$j];
                    if ($next->is(T_COMMENT) || $next->is(T_DOC_COMMENT)) {
                        continue;
                    }
                    if ($next->text === ';') {
                        $end = $j;
                        break;
                    }
                    $stmt .= $next->text;
                    $end = $j;
                }
                $stmt = trim($stmt);
                $isGroup = str_contains($stmt, '{');
                // use function / use const 不做短名残留判定
                $kind = '';
                if (preg_match('/^(function|const)\b/', $stmt, $km)) {
                    $kind = $km[1];
                    $stmt = trim(substr($stmt, strlen($kind)));
                }
                // 处理别名：use A\B\C as D 时短名取 D
                $alias = '';
                if (preg_match('/\s+as\s+(\w+)$/i', $stmt, $am)) {
                    $alias = $am[1];
                    $stmt = trim(substr($stmt, 0, strlen($stmt) - strlen($am[0])));
                }
                $segments = $isGroup ? [] : array_values(array_filter(array_map('trim', explode('\\', $stmt)), 'strlen'));
                $short = $alias !== '' ? $alias : (string)end($segments);
                $uses[] = [
                    'raw'      => $stmt,
                    'segments' => array_values(array_map('trim', $segments)),
                    'short'    => $short,
                    'line'     => $line,
                    'is_group' => $isGroup,
                    'kind'     => $kind,
                    'start'    => $i,
                    'end'      => $end,
                ];
            }

            if ($token->text === '{') {
                $depth++;
            } elseif ($token->text === '}') {
                $depth--;
            }
            // 字符串字面量不参与全文匹配：模板文本 / 配置串里的命名空间字样不是真实依赖
            // （真实引用由 use 语句、::class 与纯代码片段覆盖）
            if ($token->is(T_CONSTANT_ENCAPSED_STRING) || $token->is(T_ENCAPSED_AND_WHITESPACE)
                || $token->is(T_INLINE_HTML)) {
                continue;
            }
            $parts[] = $token->text;
        }

        return [implode('', $parts), $uses];
    }

    /**
     * 判断 use 的短名在全文是否被实际引用
     * @param string $code 原始源码（基于 token 重查，排除 use 语句自身）
     * @param array{raw:string, segments:array, short:string, line:int, is_group:bool, kind:string, start:int, end:int} $use use 语句信息
     * @return bool true=已被引用
     */
    private function isShortNameUsed(string $code, array $use): bool
    {
        if ($use['short'] === '') {
            return true;
        }
        $tokens = PhpToken::tokenize($code, TOKEN_PARSE);
        for ($i = 0, $n = count($tokens); $i < $n; $i++) {
            $token = $tokens[$i];
            // use 语句区间内的短名不算引用
            if ($i >= $use['start'] && $i <= $use['end']) {
                continue;
            }
            if (!$token->is(T_STRING) || $token->text !== $use['short']) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * 构建文本偏移量 -> 行号映射
     * @param string $text 文本
     * @return array<int, int> 偏移量 => 行号（升序）
     */
    private function buildLineMap(string $text): array
    {
        $map = [0 => 1];
        $line = 1;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            if ($text[$i] === "\n") {
                $line++;
                $map[$i + 1] = $line;
            }
        }
        return $map;
    }

    /**
     * 根据偏移量取行号
     * @param array<int, int> $map 行映射
     * @param int $offset 偏移量
     * @return int 行号
     */
    private function lineAt(array $map, int $offset): int
    {
        $line = 1;
        foreach ($map as $pos => $val) {
            if ($pos > $offset) {
                break;
            }
            $line = $val;
        }
        return $line;
    }

    /**
     * 记录违规（按去重键归并，同一目标只保留首处）
     * @param string $level error / warn
     * @param string $check 检查项标识（用于报告分组展示）
     * @param string $dedupe 去重键（如 F:modules、C:trade、E:事件名、U:use 语句）
     * @param string $relative 文件相对路径
     * @param int $line 行号
     * @param string $detail 违规说明
     * @return void
     */
    private function record(string $level, string $check, string $dedupe, string $relative, int $line, string $detail): void
    {
        // 去重键不含 check 大类：同一目标的 use 形态先记录后，inline 兜底不再重复展示
        $key = $relative . '|' . $dedupe;
        if (isset($this->recorded[$key])) {
            return;
        }
        $this->recorded[$key] = true;
        $this->violations[] = compact('level', 'check', 'relative', 'line', 'detail');
    }

    /**
     * 汇总各等级数量
     * @return array{error:int, warn:int, total:int}
     */
    private function summarize(): array
    {
        $summary = ['error' => 0, 'warn' => 0, 'total' => count($this->violations)];
        foreach ($this->violations as $item) {
            $summary[$item['level']]++;
        }
        return $summary;
    }

    /**
     * 渲染 Markdown 风格报告
     * @param Output $output 输出对象
     * @param int $fileCount 扫描文件数
     * @return void
     */
    private function render(Output $output, int $fileCount): void
    {
        $output->writeln('架构铁律静态校验（判定标准：.crmeb/rules/架构规范.md）');
        $output->writeln('扫描层：' . implode(', ', self::LAYERS) . "，共 {$fileCount} 个 PHP 文件");
        $output->writeln('');

        $titles = [
            'use-forbidden'      => 'use 引用禁止层（依赖铁律）',
            'inline-forbidden'   => '代码引用禁止层（依赖铁律）',
            'cross-module-use'   => '跨模块互引（use 形态）',
            'inline-cross-module'=> '跨模块互引（引用形态）',
            'event-class-key'    => '事件使用类常量',
            'unused-use'         => '残留无用 use（需人工确认）',
        ];
        $levels = [
            'use-forbidden'      => self::LEVEL_ERROR,
            'inline-forbidden'   => self::LEVEL_ERROR,
            'cross-module-use'   => self::LEVEL_ERROR,
            'inline-cross-module'=> self::LEVEL_ERROR,
            'event-class-key'    => self::LEVEL_WARN,
            'unused-use'         => self::LEVEL_WARN,
        ];

        foreach ($titles as $check => $title) {
            $items = array_values(array_filter($this->violations, fn ($v) => $v['check'] === $check));
            if ($items === []) {
                continue;
            }
            $tag = $levels[$check] === self::LEVEL_ERROR ? '[严重]' : '[一般]';
            $output->writeln("## {$tag} {$title}");
            foreach ($items as $item) {
                $output->writeln("  {$item['relative']}:{$item['line']}  {$item['detail']}");
            }
            $output->writeln('');
        }

        $summary = $this->summarize();
        if ($summary['total'] === 0) {
            $output->writeln('全部通过，未发现架构铁律违规。');
            return;
        }
        $output->writeln("汇总：严重 {$summary['error']} 处 / 一般 {$summary['warn']} 处");
        $output->writeln('严重违规 > 0 时退出码为 1（可接入 CI）；修复标准见 .crmeb/rules/架构规范.md');
    }
}
