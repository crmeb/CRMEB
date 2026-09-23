<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Util;

use DateTime;
use DateTimeZone;
use Exception;
use RuntimeException;
use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

class Util
{
    /**
     * @var string
     */
    public const DATE_FORMAT = 'YmdHis';

    /**
     * @var string
     */
    protected const MIGRATION_FILE_NAME_PATTERN = '/^\d+_([a-z][a-z\d]*(?:_[a-z\d]+)*)\.php$/i';

    /**
     * @var string
     */
    protected const MIGRATION_FILE_NAME_NO_NAME_PATTERN = '/^[0-9]{14}\.php$/';

    /**
     * @var string
     */
    protected const SEED_FILE_NAME_PATTERN = '/^([a-z][a-z\d]*)\.php$/i';

    /**
     * @var string
     */
    protected const CLASS_NAME_PATTERN = '/^(?:[A-Z][a-z\d]*)+$/';

    /**
     * Gets the current timestamp string, in UTC.
     *
     * @return string
     */
    public static function getCurrentTimestamp(): string
    {
        $dt = new DateTime('now', new DateTimeZone('UTC'));

        return $dt->format(static::DATE_FORMAT);
    }

    /**
     * Gets an array of all the existing migration class names.
     *
     * @param string $path Path
     * @return string[]
     */
    public static function getExistingMigrationClassNames(string $path): array
    {
        $classNames = [];

        if (!is_dir($path)) {
            return $classNames;
        }

        // filter the files to only get the ones that match our naming scheme
        $phpFiles = static::getFiles($path);

        foreach ($phpFiles as $filePath) {
            $fileName = basename($filePath);
            if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName)) {
                $classNames[] = static::mapFileNameToClassName($fileName);
            }
        }

        return $classNames;
    }

    /**
     * Get the version from the beginning of a file name.
     *
     * @param string $fileName File Name
     * @return int
     */
    public static function getVersionFromFileName(string $fileName): int
    {
        $matches = [];
        preg_match('/^[0-9]+/', basename($fileName), $matches);
        $value = (int)($matches[0] ?? null);
        if (!$value) {
            throw new RuntimeException(sprintf('Cannot get a valid version from filename `%s`', $fileName));
        }

        return $value;
    }

    /**
     * Turn migration names like 'CreateUserTable' into file names like
     * '12345678901234_create_user_table.php' or 'LimitResourceNamesTo30Chars' into
     * '12345678901234_limit_resource_names_to_30_chars.php'.
     *
     * @param string $className Class Name
     * @return string
     */
    public static function mapClassNameToFileName(string $className): string
    {
        $snake = function ($matches) {
            return '_' . strtolower($matches[0]);
        };
        $fileName = preg_replace_callback('/\d+|[A-Z]/', $snake, $className);
        $fileName = static::getCurrentTimestamp() . "$fileName.php";

        return $fileName;
    }

    /**
     * Turn file names like '12345678901234_create_user_table.php' into class
     * names like 'CreateUserTable'.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function mapFileNameToClassName(string $fileName): string
    {
        $matches = [];
        if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches)) {
            $fileName = $matches[1];
        } elseif (preg_match(static::MIGRATION_FILE_NAME_NO_NAME_PATTERN, $fileName)) {
            return 'V' . substr($fileName, 0, strlen($fileName) - 4);
        }

        $className = str_replace('_', '', ucwords($fileName, '_'));

        return $className;
    }

    /**
     * Check if a migration class name is unique regardless of the
     * timestamp.
     *
     * This method takes a class name and a path to a migrations directory.
     *
     * Migration class names must be in PascalCase format but consecutive
     * capitals are allowed.
     * e.g: AddIndexToPostsTable or CustomHTMLTitle.
     *
     * @param string $className Class Name
     * @param string $path Path
     * @return bool
     */
    public static function isUniqueMigrationClassName(string $className, string $path): bool
    {
        $existingClassNames = static::getExistingMigrationClassNames($path);

        return !in_array($className, $existingClassNames, true);
    }

    /**
     * Check if a migration/seed class name is valid.
     *
     * Migration & Seed class names must be in CamelCase format.
     * e.g: CreateUserTable, AddIndexToPostsTable or UserSeeder.
     *
     * Single words are not allowed on their own.
     *
     * @param string $className Class Name
     * @return bool
     */
    public static function isValidPhinxClassName(string $className): bool
    {
        return (bool)preg_match(static::CLASS_NAME_PATTERN, $className);
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return bool
     */
    public static function isValidMigrationFileName(string $fileName): bool
    {
        return (bool)preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName)
            || (bool)preg_match(static::MIGRATION_FILE_NAME_NO_NAME_PATTERN, $fileName);
    }

    /**
     * Check if a seed file name is valid.
     *
     * @param string $fileName File Name
     * @return bool
     */
    public static function isValidSeedFileName(string $fileName): bool
    {
        return (bool)preg_match(static::SEED_FILE_NAME_PATTERN, $fileName);
    }

    /**
     * Expands a set of paths with curly braces (if supported by the OS).
     *
     * @param string[] $paths Paths
     * @return string[]
     */
    public static function globAll(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            $result = array_merge($result, static::glob($path));
        }

        return $result;
    }

    /**
     * Expands a path with curly braces (if supported by the OS).
     *
     * @param string $path Path
     * @return string[]
     */
    public static function glob(string $path): array
    {
        return glob($path, defined('GLOB_BRACE') ? GLOB_BRACE : 0);
    }

    /**
     * Takes the path to a php file and attempts to include it if readable
     *
     * @param string $filename Filename
     * @param \think\console\Input|null $input Input
     * @param \think\console\Output|null $output Output
     * @param \Phinx\Console\Command\AbstractCommand|mixed|null $context Context
     * @throws \Exception
     * @return string
     */
    public static function loadPhpFile(string $filename, ?InputInterface $input = null, ?OutputInterface $output = null, $context = null): string
    {
        $filePath = realpath($filename);
        if (!file_exists($filePath)) {
            throw new Exception(sprintf("File does not exist: %s \n", $filename));
        }

        /**
         * I lifed this from phpunits FileLoader class
         *
         * @see https://github.com/sebastianbergmann/phpunit/pull/2751
         */
        $isReadable = @fopen($filePath, 'r') !== false;

        if (!$isReadable) {
            throw new Exception(sprintf("Cannot open file %s \n", $filename));
        }

        // prevent this to be propagated to the included file
        unset($isReadable);

        include_once $filePath;

        return $filePath;
    }

    /**
     * Given an array of paths, return all unique PHP files that are in them
     *
     * @param string|string[] $paths Path or array of paths to get .php files.
     * @return string[]
     */
    public static function getFiles($paths): array
    {
        $files = static::globAll(array_map(function ($path) {
            return $path . DIRECTORY_SEPARATOR . '*.php';
        }, (array)$paths));
        // glob() can return the same file multiple times
        // This will cause the migration to fail with a
        // false assumption of duplicate migrations
        // https://php.net/manual/en/function.glob.php#110340
        $files = array_unique($files);

        return $files;
    }

    /**
     * Attempt to remove the current working directory from a path for output.
     *
     * @param string $path Path to remove cwd prefix from
     * @return string
     */
    public static function relativePath(string $path): string
    {
        $realpath = realpath($path);
        if ($realpath !== false) {
            $path = $realpath;
        }

        $cwd = getcwd();
        if ($cwd !== false) {
            $cwd .= DIRECTORY_SEPARATOR;
            $cwdLen = strlen($cwd);

            if (substr($path, 0, $cwdLen) === $cwd) {
                $path = substr($path, $cwdLen);
            }
        }

        return $path;
    }

    /**
     * Parses DSN string into db config array.
     *
     * @param string $dsn DSN string
     * @return array
     */
    public static function parseDsn(string $dsn): array
    {
        $pattern = <<<'REGEXP'
{
    ^
    (?:
        (?P<adapter>[\w\\\\]+)://
    )
    (?:
        (?P<user>.*?)
        (?:
            :(?P<pass>.*?)
        )?
        @
    )?
    (?:
        (?P<host>[^?#/:@]+)
        (?:
            :(?P<port>\d+)
        )?
    )?
    (?:
        /(?P<name>[^?#]*)
    )?
    (?:
        \?(?P<query>[^#]*)
    )?
    $
}x
REGEXP;

        if (!preg_match($pattern, $dsn, $parsed)) {
            return [];
        }

        // filter out everything except the matched groups
        $config = array_intersect_key($parsed, array_flip(['adapter', 'user', 'pass', 'host', 'port', 'name']));
        $config = array_filter($config);

        parse_str($parsed['query'] ?? '', $query);
        $config = array_merge($query, $config);

        return $config;
    }
}
