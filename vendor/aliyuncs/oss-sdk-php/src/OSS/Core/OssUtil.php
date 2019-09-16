<?php

namespace OSS\Core;

/**
 * Class OssUtil
 *
 * Oss工具类，主要供OssClient使用，用户也可以使用本类进行返回结果的格式化
 *
 * @package OSS
 */
class OssUtil
{
    const OSS_CONTENT = 'content';
    const OSS_LENGTH = 'length';
    const OSS_HEADERS = 'headers';
    const OSS_MAX_OBJECT_GROUP_VALUE = 1000;
    const OSS_MAX_PART_SIZE = 5368709120; // 5GB
    const OSS_MID_PART_SIZE = 10485760; // 10MB
    const OSS_MIN_PART_SIZE = 102400; // 100KB

    /**
     * 生成query params
     *
     * @param array $options 关联数组
     * @return string 返回诸如 key1=value1&key2=value2
     */
    public static function toQueryString($options = array())
    {
        $temp = array();
        uksort($options, 'strnatcasecmp');
        foreach ($options as $key => $value) {
            if (is_string($key) && !is_array($value)) {
                $temp[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        return implode('&', $temp);
    }

    /**
     * 转义字符替换
     *
     * @param string $subject
     * @return string
     */
    public static function sReplace($subject)
    {
        $search = array('<', '>', '&', '\'', '"');
        $replace = array('&lt;', '&gt;', '&amp;', '&apos;', '&quot;');
        return str_replace($search, $replace, $subject);
    }

    /**
     * 检查是否是中文编码
     *
     * @param $str
     * @return int
     */
    public static function chkChinese($str)
    {
        return preg_match('/[\x80-\xff]./', $str);
    }

    /**
     * 检测是否GB2312编码
     *
     * @param string $str
     * @return boolean false UTF-8编码  TRUE GB2312编码
     */
    public static function isGb2312($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) return true;  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191))
                        return false;
                    else
                        return true;
                }
            }
        }
        return false;
    }

    /**
     * 检测是否GBK编码
     *
     * @param string $str
     * @param boolean $gbk
     * @return boolean
     */
    public static function checkChar($str, $gbk = true)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) return $gbk ? true : FALSE;  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if ($gbk) {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? FALSE : TRUE;//GBK
                    } else {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? TRUE : FALSE;
                    }
                }
            }
        }
        return $gbk ? TRUE : FALSE;
    }

    /**
     * 检验bucket名称是否合法
     * bucket的命名规范：
     * 1. 只能包括小写字母，数字
     * 2. 必须以小写字母或者数字开头
     * 3. 长度必须在3-63字节之间
     *
     * @param string $bucket Bucket名称
     * @return boolean
     */
    public static function validateBucket($bucket)
    {
        $pattern = '/^[a-z0-9][a-z0-9-]{2,62}$/';
        if (!preg_match($pattern, $bucket)) {
            return false;
        }
        return true;
    }

    /**
     * 检验object名称是否合法
     * object命名规范:
     * 1. 规则长度必须在1-1023字节之间
     * 2. 使用UTF-8编码
     * 3. 不能以 "/" "\\"开头
     *
     * @param string $object Object名称
     * @return boolean
     */
    public static function validateObject($object)
    {
        $pattern = '/^.{1,1023}$/';
        if (empty($object) || !preg_match($pattern, $object) ||
            self::startsWith($object, '/') || self::startsWith($object, '\\')
        ) {
            return false;
        }
        return true;
    }


    /**
     * 判断字符串$str是不是以$findMe开始
     *
     * @param string $str
     * @param string $findMe
     * @return bool
     */
    public static function startsWith($str, $findMe)
    {
        if (strpos($str, $findMe) === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 生成createBucketXmlBody接口的xml消息
     *
     * @param string $storageClass
     * @return string
     */
    public static function createBucketXmlBody($storageClass)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CreateBucketConfiguration></CreateBucketConfiguration>');
        $xml->addChild('StorageClass',  $storageClass);
        return $xml->asXML();
    }

    /**
     * 检验$options
     *
     * @param array $options
     * @throws OssException
     * @return boolean
     */
    public static function validateOptions($options)
    {
        //$options
        if ($options != NULL && !is_array($options)) {
            throw new OssException ($options . ':' . 'option must be array');
        }
    }

    /**
     * 检查上传文件的内容是否合法
     *
     * @param $content string
     * @throws OssException
     */
    public static function validateContent($content)
    {
        if (empty($content)) {
            throw new OssException("http body content is invalid");
        }
    }

    /**
     * 校验BUCKET/OBJECT/OBJECT GROUP是否为空
     *
     * @param  string $name
     * @param  string $errMsg
     * @throws OssException
     * @return void
     */
    public static function throwOssExceptionWithMessageIfEmpty($name, $errMsg)
    {
        if (empty($name)) {
            throw new OssException($errMsg);
        }
    }

    /**
     * 仅供测试使用的接口,请勿使用
     *
     * @param $filename
     * @param $size
     */
    public static function generateFile($filename, $size)
    {
        if (file_exists($filename) && $size == filesize($filename)) {
            echo $filename . " already exists, no need to create again. ";
            return;
        }
        $part_size = 1 * 1024 * 1024;
        $fp = fopen($filename, "w");
        $characters = <<<BBB
0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
BBB;

        $charactersLength = strlen($characters);
        if ($fp) {
            while ($size > 0) {
                if ($size < $part_size) {
                    $write_size = $size;
                } else {
                    $write_size = $part_size;
                }
                $size -= $write_size;
                $a = $characters[rand(0, $charactersLength - 1)];
                $content = str_repeat($a, $write_size);
                $flag = fwrite($fp, $content);
                if (!$flag) {
                    echo "write to " . $filename . " failed. <br>";
                    break;
                }
            }
        } else {
            echo "open " . $filename . " failed. <br>";
        }
        fclose($fp);
    }

    /**
     * 得到文件的md5编码
     *
     * @param $filename
     * @param $from_pos
     * @param $to_pos
     * @return string
     */
    public static function getMd5SumForFile($filename, $from_pos, $to_pos)
    {
        $content_md5 = "";
        if (($to_pos - $from_pos) > self::OSS_MAX_PART_SIZE) {
            return $content_md5;
        }
        $filesize = filesize($filename);
        if ($from_pos >= $filesize || $to_pos >= $filesize || $from_pos < 0 || $to_pos < 0) {
            return $content_md5;
        }

        $total_length = $to_pos - $from_pos + 1;
        $buffer = 8192;
        $left_length = $total_length;
        if (!file_exists($filename)) {
            return $content_md5;
        }

        if (false === $fh = fopen($filename, 'rb')) {
            return $content_md5;
        }

        fseek($fh, $from_pos);
        $data = '';
        while (!feof($fh)) {
            if ($left_length >= $buffer) {
                $read_length = $buffer;
            } else {
                $read_length = $left_length;
            }
            if ($read_length <= 0) {
                break;
            } else {
                $data .= fread($fh, $read_length);
                $left_length = $left_length - $read_length;
            }
        }
        fclose($fh);
        $content_md5 = base64_encode(md5($data, true));
        return $content_md5;
    }

    /**
     * 检测是否windows系统，因为windows系统默认编码为GBK
     *
     * @return bool
     */
    public static function isWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) == "WIN";
    }

    /**
     * 主要是由于windows系统编码是gbk，遇到中文时候，如果不进行转换处理会出现找不到文件的问题
     *
     * @param $file_path
     * @return string
     */
    public static function encodePath($file_path)
    {
        if (self::chkChinese($file_path) && self::isWin()) {
            $file_path = iconv('utf-8', 'gbk', $file_path);
        }
        return $file_path;
    }

    /**
     * 判断用户输入的endpoint是否是 xxx.xxx.xxx.xxx:port 或者 xxx.xxx.xxx.xxx的ip格式
     *
     * @param string $endpoint 需要做判断的endpoint
     * @return boolean
     */
    public static function isIPFormat($endpoint)
    {
        $ip_array = explode(":", $endpoint);
        $hostname = $ip_array[0];
        $ret = filter_var($hostname, FILTER_VALIDATE_IP);
        if (!$ret) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 生成DeleteMultiObjects接口的xml消息
     *
     * @param string[] $objects
     * @param bool $quiet
     * @return string
     */
    public static function createDeleteObjectsXmlBody($objects, $quiet)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Delete></Delete>');
        $xml->addChild('Quiet', $quiet);
        foreach ($objects as $object) {
            $sub_object = $xml->addChild('Object');
            $object = OssUtil::sReplace($object);
            $sub_object->addChild('Key', $object);
        }
        return $xml->asXML();
    }

    /**
     * 生成CompleteMultipartUpload接口的xml消息
     *
     * @param array[] $listParts
     * @return string
     */
    public static function createCompleteMultipartUploadXmlBody($listParts)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CompleteMultipartUpload></CompleteMultipartUpload>');
        foreach ($listParts as $node) {
            $part = $xml->addChild('Part');
            $part->addChild('PartNumber', $node['PartNumber']);
            $part->addChild('ETag', $node['ETag']);
        }
        return $xml->asXML();
    }

    /**
     * 读取目录
     *
     * @param string $dir
     * @param string $exclude
     * @param bool $recursive
     * @return string[]
     */
    public static function readDir($dir, $exclude = ".|..|.svn|.git", $recursive = false)
    {
        $file_list_array = array();
        $base_path = $dir;
        $exclude_array = explode("|", $exclude);
        $exclude_array = array_unique(array_merge($exclude_array, array('.', '..')));

        if ($recursive) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $new_file) {
                if ($new_file->isDir()) continue;
                $object = str_replace($base_path, '', $new_file);
                if (!in_array(strtolower($object), $exclude_array)) {
                    $object = ltrim($object, '/');
                    if (is_file($new_file)) {
                        $key = md5($new_file . $object, false);
                        $file_list_array[$key] = array('path' => $new_file, 'file' => $object,);
                    }
                }
            }
        } else if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array(strtolower($file), $exclude_array)) {
                    $new_file = $dir . '/' . $file;
                    $object = $file;
                    $object = ltrim($object, '/');
                    if (is_file($new_file)) {
                        $key = md5($new_file . $object, false);
                        $file_list_array[$key] = array('path' => $new_file, 'file' => $object,);
                    }
                }
            }
            closedir($handle);
        }
        return $file_list_array;
    }

    /**
     * Decode key based on the encoding type
     *
     * @param string $key
     * @param string $encoding
     * @return string
     */
    public static function decodeKey($key, $encoding)
    {
        if ($encoding == "") {
            return $key;
        }

        if ($encoding == "url") {
            return rawurldecode($key);
        } else {
            throw new OssException("Unrecognized encoding type: " . $encoding);
        }
    }
}
