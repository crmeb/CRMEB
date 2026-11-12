<?php
declare(strict_types=1);

namespace app\services\system;

/**
 * 读取当前安装包 SQL（crmeb_table.sql + data/），供安装向导以外的恢复出厂复用。
 * 始终读磁盘文件，不内嵌副本。
 */
class InstallSqlHelper
{
    public function installDir(): string
    {
        return rtrim(public_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return list<string>
     */
    public function getTableStatements(): array
    {
        $file = $this->installDir() . 'crmeb_table.sql';
        if (!is_file($file)) {
            return [];
        }
        $statements = $this->splitSql((string)file_get_contents($file));
        return array_values(array_filter($statements, static function (string $statement): bool {
            return (bool)preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`eb_/i', trim($statement));
        }));
    }

    /**
     * @return list<array{table: string, statements: list<string>}>
     */
    public function getDataGroups(): array
    {
        $files = glob($this->installDir() . 'data' . DIRECTORY_SEPARATOR . '*_data*.sql', GLOB_NOSORT);
        if ($files === false || $files === []) {
            return [];
        }
        $files = array_values(array_filter($files, static function (string $file): bool {
            return (bool)preg_match('/^[a-z0-9_]+_data(?:_\d+)?\.sql$/i', basename($file));
        }));
        sort($files, SORT_STRING);

        $groups = [];
        foreach ($files as $file) {
            $statements = [];
            foreach ($this->splitSql((string)file_get_contents($file)) as $statement) {
                $statement = trim($statement);
                if ($statement !== '' && preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $statement)) {
                    $statements[] = $statement;
                }
            }
            if ($statements === []) {
                continue;
            }
            preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $statements[0], $matches);
            $groups[] = [
                'table' => $matches[1] ?? pathinfo($file, PATHINFO_FILENAME),
                'statements' => $statements,
            ];
        }
        return $groups;
    }

    public function prepareSql(string $sql, string $dbPrefix, string $requestScheme, string $requestHost): string
    {
        $sql = str_replace('`eb_', '`' . $dbPrefix, $sql);
        $sql = str_replace('http://demo.crmeb.com', $requestScheme . '://' . $requestHost, $sql);
        return str_replace(
            'http:\\\\/\\\\/demo.crmeb.com',
            $requestScheme . ':\\\\/\\\\/' . $requestHost,
            $sql
        );
    }

    /**
     * @return list<string>
     */
    public function splitSql(string $sql): array
    {
        $sql = preg_replace(
            "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/",
            "ENGINE=\\1 DEFAULT CHARSET=utf8",
            $sql
        ) ?? $sql;
        $sql = str_replace("\r", "\n", $sql);
        $ret = [];
        $num = 0;
        foreach (explode(";\n", trim($sql)) as $query) {
            $ret[$num] = '';
            $queries = array_filter(explode("\n", trim($query)));
            foreach ($queries as $line) {
                $str1 = substr($line, 0, 1);
                if ($str1 !== '#' && $str1 !== '-') {
                    $ret[$num] .= $line;
                }
            }
            $num++;
        }
        return $ret;
    }
}
