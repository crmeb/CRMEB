<?php
declare(strict_types=1);

namespace modules\aiagent\service;

use crmeb\exceptions\AdminException;

/**
 * AI Agent 模块 Skill 文件（.crmeb/skills/{key}/SKILL.md）的读写管理。
 *
 * 存储即文件系统：一个 Skill 对应 .crmeb/skills 下一个目录的 SKILL.md，
 * frontmatter 携带 name/description/status，正文为注入模型的规则内容。
 * 目录内的其余 .md 文件作为附属资料存在：不随 SKILL.md 注入对话，
 * 由模型经 crmeb_skill_load 虚拟工具的 file 参数按需加载（见 attachments/loadAttachment）。
 */
class SkillRepository
{
    /** Skill 标识直接作为目录名，仅允许小写字母开头的字母数字下划线中划线 */
    private const KEY_PATTERN = '/^[a-z][a-z0-9_-]{0,63}$/';

    /** Skill 文件内容字节上限（200KB）：服务端兜底，防止超长内容拖垮文件系统与模型上下文 */
    private const MAX_FILE_BYTES = 204800;

    /** @var string Skill 存储根目录（.crmeb/skills） */
    private string $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.crmeb' . DIRECTORY_SEPARATOR . 'skills';
    }

    /** @return array<int, array{key:string,name:string,description:string,status:int,update_time:int}> */
    public function all(): array
    {
        $skills = [];
        foreach (glob($this->root . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'SKILL.md') ?: [] as $file) {
            $key = basename(dirname($file));
            $content = (string)file_get_contents($file);
            $skills[] = [
                'key' => $key,
                'name' => $this->metadata($content, 'name') ?: $this->heading($content) ?: $key,
                'description' => $this->metadata($content, 'description'),
                'status' => $this->metadata($content, 'status') === '0' ? 0 : 1,
                'update_time' => (int)filemtime($file),
            ];
        }
        return $skills;
    }

    /**
     * 列出 Skill 目录下的附属资料文件（SKILL.md/EXAMPLES.md 之外的 .md 文件）
     * @param string $key Skill 标识
     * @return array<int, array{file:string,title:string,size:int,update_time:int}> file=文件名（供 crmeb_skill_load 的 file 参数），title=文件首个一级标题
     */
    public function attachments(string $key): array
    {
        $dir = $this->root . DIRECTORY_SEPARATOR . $key;
        if (!preg_match(self::KEY_PATTERN, $key) || !is_dir($dir)) {
            return [];
        }
        $list = [];
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.md') ?: [] as $file) {
            $name = basename($file);
            if (!$this->isAttachmentFile($name)) {
                continue;
            }
            $content = (string)file_get_contents($file);
            $list[] = [
                'file'  => $name,
                'title' => preg_match('/^#\s+(.+)$/m', $content, $match) ? trim($match[1]) : '',
                'size'  => (int)filesize($file),
                'update_time' => (int)filemtime($file),
            ];
        }
        return $list;
    }

    /**
     * 是否为可按需加载的附属资料文件：SKILL.md 为规则本体经主流程注入，
     * EXAMPLES.md 为维护者回归用例（约定不进入模型上下文），均排除
     * @param string $name 文件名（basename）
     * @return bool
     */
    private function isAttachmentFile(string $name): bool
    {
        return $name !== 'SKILL.md' && $name !== 'EXAMPLES.md';
    }

    /**
     * 加载 Skill 附属资料文件全文（Skill 停用/不存在或路径非法时返回 null）
     * @param string $key Skill 标识（须为启用中的 Skill）
     * @param string $file 附属文件名（仅允许目录内的 .md 文件，禁止任何路径成分）
     * @return array{file:string,content:string}|null
     */
    public function loadAttachment(string $key, string $file): ?array
    {
        $file = trim($file);
        if ($file === ''
            || str_contains($file, '/')
            || str_contains($file, '\\')
            || str_contains($file, '..')
            || !str_ends_with($file, '.md')
            || !$this->isAttachmentFile($file)) {
            return null;
        }
        $allowed = array_column($this->all(), null, 'key');
        if (!isset($allowed[$key]) || $allowed[$key]['status'] !== 1) {
            return null;
        }
        $dir = $this->root . DIRECTORY_SEPARATOR . $key;
        $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
        $dirReal = realpath($dir);
        // realpath 前缀校验兜底防目录穿越（符号链接等边缘场景）
        if ($path === false || $dirReal === false
            || !str_starts_with($path, $dirReal . DIRECTORY_SEPARATOR)
            || !is_file($path)) {
            return null;
        }
        $content = (string)file_get_contents($path);
        if (strlen($content) > 32768) {
            $content = substr($content, 0, 32768);
        }
        return ['file' => $file, 'content' => $this->body($content)];
    }

    /**
     * 附属资料详情（含全文，供后台编辑表单回显）
     * @param string $key Skill 标识
     * @param string $file 附属资料文件名
     * @return array{key:string,file:string,title:string,content:string,update_time:int}
     */
    public function attachmentDetail(string $key, string $file): array
    {
        $this->existingFile($key);
        $file = $this->assertAttachmentName($file);
        $path = $this->attachmentPath($key, $file);
        if (!is_file($path)) {
            throw new AdminException('附属资料不存在');
        }
        $content = (string)file_get_contents($path);
        return [
            'key'         => $key,
            'file'        => $file,
            'title'       => preg_match('/^#\s+(.+)$/m', $content, $match) ? trim($match[1]) : '',
            'content'     => $content,
            'update_time' => (int)filemtime($path),
        ];
    }

    /**
     * 保存（新建或覆盖）Skill 附属资料文件
     * @param string $key Skill 标识
     * @param string $file 附属资料文件名
     * @param string $content Markdown 全文
     * @return void
     */
    public function saveAttachment(string $key, string $file, string $content): void
    {
        $this->existingFile($key);
        $file = $this->assertAttachmentName($file);
        if (trim($content) === '') {
            throw new AdminException('请输入附属资料内容');
        }
        if (strlen($content) > self::MAX_FILE_BYTES) {
            throw new AdminException('附属资料内容不能超过 200KB');
        }
        $path = $this->attachmentPath($key, $file);
        $this->atomicWrite($path, rtrim($content) . "\n");
    }

    /**
     * 删除 Skill 附属资料文件
     * @param string $key Skill 标识
     * @param string $file 附属资料文件名
     * @return void
     */
    public function deleteAttachment(string $key, string $file): void
    {
        $this->existingFile($key);
        $file = $this->assertAttachmentName($file);
        $path = $this->attachmentPath($key, $file);
        if (!is_file($path) || !unlink($path)) {
            throw new AdminException('附属资料删除失败，请检查文件权限');
        }
    }

    /**
     * 校验附属资料文件名（后台管理入口用，非法直接抛错）：
     * 仅允许 .md 结尾、禁止任何路径成分、SKILL.md 与 EXAMPLES.md 为保留名
     * @param string $file 文件名
     * @return string 校验后的文件名
     */
    private function assertAttachmentName(string $file): string
    {
        $file = trim($file);
        if ($file === ''
            || str_contains($file, '/')
            || str_contains($file, '\\')
            || str_contains($file, '..')
            || !str_ends_with($file, '.md')
            || !$this->isAttachmentFile($file)) {
            throw new AdminException('附属资料文件名不合法：仅允许 .md 文件（SKILL.md 与 EXAMPLES.md 为保留名）');
        }
        return $file;
    }

    /** 附属资料文件路径（key 与 file 均已校验，无路径穿越风险） */
    private function attachmentPath(string $key, string $file): string
    {
        return $this->root . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * 按标识加载启用中的 Skill 全文（停用与不存在的静默跳过）
     * @return array<string, string> key => content
     */
    public function load(array $keys): array
    {
        $result = [];
        $allowed = array_column($this->all(), null, 'key');
        foreach (array_values(array_unique(array_map('strval', $keys))) as $key) {
            if (!isset($allowed[$key]) || $allowed[$key]['status'] !== 1) {
                continue;
            }
            $content = (string)file_get_contents($this->filePath($key));
            if (strlen($content) > 32768) {
                $content = substr($content, 0, 32768);
            }
            $result[$key] = $content;
        }
        return $result;
    }

    /**
     * Skill 详情（含去掉 frontmatter 的正文与附属资料清单，供编辑表单回显）
     * @param string $key Skill 标识
     * @return array
     */
    public function detail(string $key): array
    {
        $file = $this->existingFile($key);
        $skill = $this->find($key);
        $skill['content'] = $this->body((string)file_get_contents($file));
        $skill['attachments'] = $this->attachments($key);
        return $skill;
    }

    /**
     * 新增 Skill
     * @param array $data 表单数据（key/name/description/content/status）
     * @return string Skill 标识
     */
    public function create(array $data): string
    {
        $key = trim((string)($data['key'] ?? ''));
        $this->assertKey($key);
        if (is_file($this->filePath($key))) {
            throw new AdminException('Skill 标识已存在，请更换');
        }
        $this->putFile($key, $this->buildFileContent($data));
        return $key;
    }

    /**
     * 修改 Skill（表单需携带完整字段整体重写）
     * @param string $key Skill 标识
     * @param array $data 表单数据（name/description/content/status）
     * @return void
     */
    public function update(string $key, array $data): void
    {
        $this->existingFile($key);
        $data['key'] = $key;
        $this->putFile($key, $this->buildFileContent($data));
    }

    /**
     * 启停 Skill（停用后不会注入任何 Agent 对话）
     * @param string $key Skill 标识
     * @param int $status 状态（0=停用 1=启用）
     * @return void
     */
    public function setStatus(string $key, int $status): void
    {
        $skill = $this->detail($key);
        $this->putFile($key, $this->buildFileContent([
            'name'        => $skill['name'],
            'description' => $skill['description'],
            'content'     => $skill['content'],
            'status'      => $status,
        ]));
    }

    /**
     * 删除 Skill（移除 SKILL.md 与全部附属资料，目录一并清理；
     * 只删 SKILL.md 会让附属资料残留，同名 Skill 重建时旧资料会"复活"）
     * @param string $key Skill 标识
     * @return void
     */
    public function delete(string $key): void
    {
        $file = $this->existingFile($key);
        if (!unlink($file)) {
            throw new AdminException('Skill 删除失败，请检查文件权限');
        }
        $dir = dirname($file);
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $item) {
            if (is_file($item)) {
                @unlink($item);
            }
        }
        @rmdir($dir);
    }

    /**
     * 表单数据 → SKILL.md 文件内容
     * @param array $data 表单数据
     * @return string
     */
    private function buildFileContent(array $data): string
    {
        $name = $this->singleLine(trim((string)($data['name'] ?? '')));
        if ($name === '') {
            throw new AdminException('请输入 Skill 名称');
        }
        $body = rtrim((string)($data['content'] ?? ''));
        if ($body === '') {
            throw new AdminException('请输入 Skill 正文内容');
        }
        $description = $this->singleLine(trim((string)($data['description'] ?? '')));
        $status = (int)($data['status'] ?? 1) === 0 ? 0 : 1;

        return "---\nname: {$name}\ndescription: {$description}\nstatus: {$status}\n---\n\n{$body}\n";
    }

    /**
     * 从文件内容提取 frontmatter 之后的正文
     * @param string $content 文件全文
     * @return string
     */
    private function body(string $content): string
    {
        if (preg_match('/^---\s*\R.*?\R---\s*/s', $content, $match)) {
            return ltrim(substr($content, strlen($match[0])), "\r\n");
        }
        return $content;
    }

    /**
     * frontmatter 标量值压成单行并去除干扰字符（配合 metadata 的解析规则）
     * @param string $value 原始值
     * @return string
     */
    private function singleLine(string $value): string
    {
        $value = str_replace(['"', "'", '\\'], '', $value);
        return trim((string)preg_replace('/\s+/u', ' ', $value));
    }

    /** 校验标识合法性（同时防目录穿越） */
    private function assertKey(string $key): void
    {
        if (!preg_match(self::KEY_PATTERN, $key) || basename($key) !== $key) {
            throw new AdminException('Skill 标识只能由小写字母、数字、下划线、中划线组成，且以字母开头');
        }
    }

    /** 标识对应文件路径（不校验存在性） */
    private function filePath(string $key): string
    {
        return $this->root . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . 'SKILL.md';
    }

    /** 校验标识合法且文件存在，返回路径 */
    private function existingFile(string $key): string
    {
        $this->assertKey($key);
        $file = $this->filePath($key);
        if (!is_file($file)) {
            throw new AdminException('Skill 不存在');
        }
        return $file;
    }

    /** 写入 Skill 文件（目录缺失时自动创建；先写临时文件再 rename 原子替换，避免并发读半截内容） */
    private function putFile(string $key, string $content): void
    {
        if (strlen($content) > self::MAX_FILE_BYTES) {
            throw new AdminException('Skill 内容不能超过 200KB');
        }
        $file = $this->filePath($key);
        $this->atomicWrite($file, $content);
    }

    /**
     * 原子写入：写入同目录临时文件后 rename 替换目标（rename 同分区为原子操作），
     * 避免直接覆盖时进程中断留下半截文件，或并发读取到不完整内容
     * @param string $path 目标文件路径
     * @param string $content 全文
     */
    private function atomicWrite(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new AdminException('Skill 目录创建失败，请检查目录权限');
        }
        $tmp = $dir . DIRECTORY_SEPARATOR . '.tmp_' . bin2hex(random_bytes(6));
        if (file_put_contents($tmp, $content) === false || !rename($tmp, $path)) {
            @unlink($tmp);
            throw new AdminException('Skill 文件写入失败，请检查目录权限');
        }
    }

    /** 按标识查找元信息 */
    private function find(string $key): array
    {
        foreach ($this->all() as $skill) {
            if ($skill['key'] === $key) {
                return $skill;
            }
        }
        throw new AdminException('Skill 不存在');
    }

    /**
     * 从 SKILL.md frontmatter 中读取指定字段的值
     * @param string $content SKILL.md 全文
     * @param string $key 字段名（name/description/status 等）
     * @return string 字段值，缺失时返回空串
     */
    private function metadata(string $content, string $key): string
    {
        // 冒号后的空白只允许同行空格/制表符（禁止 \s 跨行），否则空值 description 会把下一行 status 的值吞进来
        if (preg_match('/^---\s*\R(.*?)\R---/s', $content, $frontmatter)
            && preg_match('/^' . preg_quote($key, '/') . ':[ \t]*["\']?(.*?)["\']?[ \t]*$/mi', $frontmatter[1], $match)) {
            return trim($match[1]);
        }
        return '';
    }

    /**
     * 提取 SKILL.md 正文的第一个一级标题（frontmatter 无 name 字段时作为名称兜底）
     * @param string $content SKILL.md 全文
     * @return string 标题文本，缺失时返回空串
     */
    private function heading(string $content): string
    {
        return preg_match('/^#\s+(.+)$/m', $content, $match) ? trim($match[1]) : '';
    }
}
