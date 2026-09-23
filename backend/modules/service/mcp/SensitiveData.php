<?php
declare(strict_types=1);

namespace modules\service\mcp;

/** MCP 结果中的凭证和手机号保护。 */
final class SensitiveData
{
    /** @param string $name 配置变量或字段名称 @param string $type 输入类型 @return bool */
    public static function isCredential(string $name, string $type = ''): bool
    {
        return strtolower($type) === 'password'
            || (bool)preg_match('/secret|password|passwd|pwd|private_?key|access_?key|api_?key|appkey|write_?key|token|credential|webhook|authorization|sign_?key|appid/i', $name);
    }

    /** @param mixed $value 原始值 @param string $name 字段名 @param string $type 输入类型 @return mixed */
    public static function mask($value, string $name = '', string $type = '')
    {
        if (self::isCredential($name, $type)) {
            return $value === null || $value === '' ? null : '******';
        }
        if (is_array($value)) {
            foreach ($value as $key => &$item) {
                $item = self::mask($item, (string)$key);
            }
            unset($item);
        }
        return $value;
    }

    /** @param string $phone 原始手机号 @return string */
    public static function phone(string $phone): string
    {
        if ($phone === '') {
            return '';
        }
        return strlen($phone) >= 7 ? substr($phone, 0, 3) . '****' . substr($phone, -4) : '******';
    }
}
