<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------

namespace crmeb\utils;

/**
 * ZIP 安全解压：拒绝路径穿越、绝对路径、符号链接与解压炸弹，并在解压后校验落盘路径。
 */
class SafeZip
{
    /**
     * 校验本地文件为 ZIP 魔数（不信任扩展名）。
     */
    public static function assertMagic(string $zipPath): void
    {
        if (!is_file($zipPath) || !is_readable($zipPath)) {
            throw new \RuntimeException('ZIP 文件不存在或不可读');
        }
        $fh = fopen($zipPath, 'rb');
        if ($fh === false) {
            throw new \RuntimeException('无法读取 ZIP 文件');
        }
        try {
            $sig = fread($fh, 4);
        } finally {
            fclose($fh);
        }
        // 本地文件头 / 空归档 / spanned
        if (!in_array($sig, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
            throw new \RuntimeException('文件不是合法的 ZIP 包');
        }
    }

    /**
     * 解压前校验所有条目。
     *
     * @param array{max_files?:int,max_bytes?:int,require_plugin_json?:bool} $options
     */
    public static function validateEntries(\ZipArchive $zip, array $options = []): void
    {
        $maxFiles = (int)($options['max_files'] ?? 2000);
        $maxBytes = (int)($options['max_bytes'] ?? 150 * 1024 * 1024);
        $requirePluginJson = (bool)($options['require_plugin_json'] ?? false);

        if ($zip->numFiles < 1 || $zip->numFiles > $maxFiles) {
            throw new \RuntimeException("ZIP 文件数量必须在 1 到 {$maxFiles} 之间");
        }

        $totalSize = 0;
        $manifestCount = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['name'])) {
                throw new \RuntimeException('ZIP 条目信息损坏');
            }
            $name = str_replace('\\', '/', (string)$stat['name']);
            self::assertSafeEntryName($name);

            if (basename($name) === 'plugin.json') {
                $manifestCount++;
                $segments = explode('/', rtrim($name, '/'));
                if (count($segments) > 2) {
                    throw new \RuntimeException('plugin.json 只能位于 ZIP 根目录或一级插件目录');
                }
            }

            $totalSize += (int)($stat['size'] ?? 0);
            if ($totalSize > $maxBytes) {
                throw new \RuntimeException('ZIP 解压后总体积超过限制');
            }

            $attributes = 0;
            $opsys = 0;
            if ($zip->getExternalAttributesIndex($i, $opsys, $attributes)) {
                // Unix 外部属性：高 16 位为 mode；0120000 = symlink
                if ($opsys === \ZipArchive::OPSYS_UNIX) {
                    $fileType = ($attributes >> 16) & 0170000;
                    if ($fileType === 0120000) {
                        throw new \RuntimeException("ZIP 不允许包含符号链接: {$name}");
                    }
                }
            }
        }

        if ($requirePluginJson && $manifestCount !== 1) {
            throw new \RuntimeException('ZIP 包必须且只能包含一个 plugin.json');
        }
    }

    /**
     * 安全解压到目标目录（逐条写出，禁止 extractTo 直落）。
     *
     * @param array{max_files?:int,max_bytes?:int,require_plugin_json?:bool} $options
     */
    public static function extract(string $zipPath, string $destDir, array $options = []): void
    {
        self::assertMagic($zipPath);

        $destDir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destDir), DIRECTORY_SEPARATOR);
        if ($destDir === '') {
            throw new \RuntimeException('解压目标目录无效');
        }
        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException('无法创建解压目录');
        }
        $destReal = realpath($destDir);
        if ($destReal === false) {
            throw new \RuntimeException('无法解析解压目录');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('无法打开 ZIP 文件，可能已损坏');
        }

        try {
            self::validateEntries($zip, $options);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', (string)$stat['name']);
                self::assertSafeEntryName($name);

                $relative = str_replace('/', DIRECTORY_SEPARATOR, $name);
                $target = $destReal . DIRECTORY_SEPARATOR . $relative;
                $parent = dirname($target);
                self::assertPathContained($destReal, $parent);

                if (str_ends_with($name, '/')) {
                    if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                        throw new \RuntimeException("无法创建目录: {$name}");
                    }
                    continue;
                }

                if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) {
                    throw new \RuntimeException("无法创建目录: {$name}");
                }

                $stream = $zip->getStream($name);
                if ($stream === false) {
                    // 部分目录条目无内容流，跳过即可
                    if (($stat['size'] ?? 0) === 0) {
                        continue;
                    }
                    throw new \RuntimeException("无法读取 ZIP 条目: {$name}");
                }
                $out = fopen($target, 'wb');
                if ($out === false) {
                    fclose($stream);
                    throw new \RuntimeException("无法写入文件: {$name}");
                }
                try {
                    stream_copy_to_stream($stream, $out);
                } finally {
                    fclose($out);
                    fclose($stream);
                }
                self::assertPathContained($destReal, $target);
            }
            self::assertExtractedTree($destReal);
        } finally {
            $zip->close();
        }
    }

    /**
     * 将相对路径解析为 base 目录下的真实文件路径（防穿越）。
     */
    public static function resolveUnder(string $baseDir, string $relative): string
    {
        $baseReal = realpath($baseDir);
        if ($baseReal === false || !is_dir($baseReal)) {
            throw new \RuntimeException('基准目录不存在');
        }

        $relative = str_replace('\\', '/', $relative);
        $relative = ltrim($relative, '/');
        if ($relative === '' || str_contains($relative, "\0") || str_contains($relative, '..')
            || preg_match('#^[A-Za-z]:/#', $relative)) {
            throw new \RuntimeException('非法文件路径');
        }

        $candidate = $baseReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $real = realpath($candidate);
        if ($real === false || !is_file($real)) {
            throw new \RuntimeException('文件不存在');
        }
        self::assertPathContained($baseReal, $real);
        return $real;
    }

    /**
     * 解压后遍历：拒绝软链，且每个文件必须落在目标目录内。
     */
    public static function assertExtractedTree(string $destDir): void
    {
        $root = realpath($destDir);
        if ($root === false) {
            throw new \RuntimeException('解压目录无效');
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            if (is_link($pathname)) {
                throw new \RuntimeException('解压结果不允许包含符号链接: ' . $file->getFilename());
            }
            $real = realpath($pathname);
            if ($real === false) {
                throw new \RuntimeException('解压结果路径无效');
            }
            self::assertPathContained($root, $real);
        }
    }

    protected static function assertSafeEntryName(string $name): void
    {
        $normalized = rtrim(str_replace('\\', '/', $name), '/');
        $segments = $normalized === '' ? [] : explode('/', $normalized);
        if ($name === '' || str_contains($name, "\0") || str_starts_with($name, '/')
            || preg_match('/^[A-Za-z]:\//', $name) || str_contains($name, '//')
            || in_array('..', $segments, true) || in_array('.', $segments, true)) {
            throw new \RuntimeException("ZIP 包含非法路径: {$name}");
        }
    }

    protected static function assertPathContained(string $root, string $path): void
    {
        $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
        $pathNorm = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        // 目录尚未创建时用字符串前缀；已存在则用 realpath
        if (file_exists($pathNorm)) {
            $real = realpath($pathNorm);
            if ($real === false) {
                throw new \RuntimeException('路径解析失败');
            }
            $pathNorm = $real;
        } else {
            // 规范化 .. 已在条目名阶段拒绝；此处再挡一层
            if (str_contains($pathNorm, '..' . DIRECTORY_SEPARATOR) || str_ends_with($pathNorm, '..')) {
                throw new \RuntimeException('路径越界');
            }
        }
        if ($pathNorm !== $root && !str_starts_with($pathNorm, $root . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('路径越界，拒绝写出到目标目录之外');
        }
    }
}
