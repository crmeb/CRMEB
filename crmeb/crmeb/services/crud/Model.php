<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\crud;


use crmeb\services\crud\enum\FormTypeEnum;
use think\helper\Str;

/**
 * Class Model
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/3/13
 * @package crmeb\command\crud
 */
class Model extends Make
{
    /**
     * 当前命令名称
     * @var string
     */
    protected $name = "model";

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'model' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Model
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/12
     */
    public function handle(string $name, array $options = [])
    {
        $this->options = $options;

        $field = $options['fromField'] ?? [];
        $hasOneFields = $options['hasOneField'] ?? [];

        $this->value['KEY'] = $options['key'] ?? 'id';
        $this->value['MODEL_NAME'] = $options['modelName'] ?? $name;

        $this->setUseDeleteContent()
            ->setAttrFnContent($field)
            ->setHasOneContent($hasOneFields);

        return parent::handle($name, $options);
    }

    /**
     * 设置命令空间
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setUseDeleteContent()
    {
        if (isset($this->options['softDelete']) && $this->options['softDelete']) {
            $this->value['USE_PHP'] = "use think\model\concern\SoftDelete;\n";
            $this->value['CONTENT_PHP'] = $this->tab() . "use SoftDelete;\n";
        }

        return $this;
    }

    /**
     * 设置获取字段方法内容
     * @param array $field
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setAttrFnContent(array $field)
    {
        $attrFnContent = [];
        foreach ($field as $item) {
            if (in_array($item['type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT]) && !empty($item['option'])) {
                $attrFnContent[] = $this->getAttrFnContent($item['field'], $item['name'], $item['option']);
            } else if (FormTypeEnum::CHECKBOX == $item['type']) {
                $attrFnContent[] = $this->getAttrFnCheckboxContent($item['field'], $item['name'], $item['option']);
            } else if (in_array($item['type'], [FormTypeEnum::FRAME_IMAGES, FormTypeEnum::DATE_TIME_RANGE])) {
                $attrFnContent[] = $this->getAttrJoinFnContent($item['field'], $item['name']);
            }
        }
        if ($attrFnContent) {
            $this->value['ATTR_PHP'] = "\n" . implode("\n", $attrFnContent);
        }
        return $this;
    }

    /**
     * 设置hasone方法内容
     * @param array $hasOneFields
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setHasOneContent(array $hasOneFields)
    {
        $hasOneContent = $this->getHasPhpContent($hasOneFields);
        if ($hasOneContent) {
            $this->value['ATTR_PHP'] .= "\n" . $hasOneContent;
        }

        return $this;
    }

    /**
     * 转JSON数据获取器
     * @param string $key
     * @param string $name
     * @return array|false|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/9/5
     */
    public function getAttrJoinFnContent(string $key, string $name)
    {
        $attrFnStub = file_get_contents($this->getStub('attr'));

        $var = [
            '{%FIELD%}',
            '{%DATE%}',
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $tab = $this->tab(2);
        $content = <<<CONTENT
$tab\$value = \$value ? json_decode(\$value, true) : [];
{$tab}return \$value;
CONTENT;

        $value = [
            Str::studly($key . $this->attrPrefix),
            date('Y-m-d'),
            $name,
            $content
        ];

        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * Checkbox代码获取
     * @param string $key
     * @param string $comment
     * @param array $options
     * @return array|false|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/9
     */
    protected function getAttrFnCheckboxContent(string $key, string $comment, array $options)
    {
        $optionsStr = '';
        $tab2 = $this->tab(2);
        $tab3 = $this->tab(3);
        $tab4 = $this->tab(4);
        foreach ($options as $i => $option) {
            if (0 == $i) {
                $n = '';
            } else {
                $n = "\n";
            }
            $optionsStr .= <<<CONTENT
{$n}{$tab3}[
$tab4'value' => '$option[value]',
$tab4'label' => '$option[label]',
{$tab3}],
CONTENT;
        }
        $content = <<<CONTENT
{$tab2}\$options = [
$optionsStr
{$tab2}];

{$tab2}\$var = [];
{$tab2}\$value = \$value ? json_decode(\$value, true) : [];
{$tab2}foreach(\$options as \$item) {
{$tab2}   if (is_array(\$value) && in_array(\$item['value'], \$value)) {
{$tab2}     \$var[] = \$item['label'];
{$tab2}   }   
{$tab2}}

{$tab2}return implode(',', \$var);
CONTENT;


        $var = [
            '{%FIELD%}',
            '{%DATE%}',
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $value = [
            Str::studly($key . $this->attrPrefix),
            date('Y-m-d'),
            $comment,
            $content
        ];

        $attrFnStub = file_get_contents($this->getStub('attr'));
        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * 获取获取器的方法内容
     * @param string $key
     * @param string $comment
     * @param array $options
     * @return array|false|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/11
     */
    protected function getAttrFnContent(string $key, string $comment, array $options)
    {
        $attrFnStub = file_get_contents($this->getStub('attr'));

        $var = [
            '{%FIELD%}',
            '{%DATE%}',
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $value = [
            Str::studly($key . $this->attrPrefix),
            date('Y-m-d'),
            $comment,
            $this->getSwithAndSelectPhpContent($options)
        ];

        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * 获取开关和下拉框获取器内容
     * @param array $options
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/11
     */
    protected function getSwithAndSelectPhpContent(array $options)
    {
        if (!$options) {
            return '';
        }
        $case = [];
        foreach ($options as $option) {
            $case[] = $this->tab(3) . "case " . $option['value'] . ":\n" . $this->tab(4) . "\$attr = '$option[label]';\n" . $this->tab(4) . "break;";
        }
        $caseContent = implode("\n", $case);
        $tab2 = $this->tab(2);
        $content = <<<CONTENT
{$tab2}\$attr = '';
{$tab2}switch ((int)\$value){
{$caseContent}
{$tab2}}
{$tab2}return \$attr;
CONTENT;
        return $content;
    }

    /**
     * 获取关联数据模板
     * @param array $fields
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/4
     */
    protected function getHasPhpContent(array $fields)
    {
        $hasOneStub = file_get_contents($this->getStub('hasOne'));
        $date = date('Y/m/d');

        $content = '';
        foreach ($fields as $item) {
            if (isset($item['hasOne']) && $item['hasOne']) {
                [$modelName, $foreignKey] = is_array($item['hasOne']) ? $item['hasOne'] : [$item['hasOne'], 'id'];
                $modelName = self::getHasOneNamePases($modelName);
                if (!$modelName) {
                    continue;
                }
                $content .= "\n" . str_replace(
                        [
                            '{%NAME%}',
                            '{%DATE%}',
                            '{%FIELD%}',
                            '{%CLASS%}',
                            '{%FOREIGN_KEY%}',
                            '{%LOCAL_KEY%}'
                        ],
                        [
                            $item['name'],
                            $date,
                            Str::camel($item['field']),
                            $modelName,
                            $foreignKey,
                            $item['field']
                        ],
                        $hasOneStub
                    );
            }
        }

        return $content;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/12
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }

    /**
     * 模板文件
     * @param string $type
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/13
     */
    protected function getStub(string $type = 'model')
    {
        $routePath = __DIR__ . DS . 'stubs' . DS . 'model' . DS;

        $stubs = [
            'model' => $routePath . 'crudModel.stub',
            'attr' => $routePath . 'getattr.stub',
            'hasOne' => $routePath . 'hasOne.stub',
            'hasMany' => $routePath . 'hasMany.stub',
        ];

        return $type ? $stubs[$type] : $stubs['model'];
    }

    /**
     * 获取模型命令空间
     * @param string $modelName
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/8
     */
    public static function getHasOneNamePases(string $modelName)
    {
        $dir = root_path('app' . DS . 'model');
        $res = self::searchFiles($dir, '$name = \'' . $modelName . '\'');
        $namepases = '';
        foreach ($res as $item) {
            $namepases = self::getFileNamespace($item);
        }

        return $namepases ? "\\" . $namepases . '\\' . Str::studly($modelName) . "::class" : '';
    }

    /**
     * 搜索文件内容包含某个字符串，返回包含的文件路径
     * @param string $dir
     * @param string $searchString
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/8
     */
    public static function searchFiles(string $dir, string $searchString)
    {
        $foundFiles = [];

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $foundFiles = array_merge($foundFiles, self::searchFiles($path, $searchString));
            } else {
                $content = file_get_contents($path);
                if (strpos($content, $searchString) !== false) {
                    $foundFiles[] = $path;
                }
            }
        }

        return $foundFiles;
    }

    /**
     * 获取文件的命名空间
     * @param string $filePath
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/8
     */
    public static function getFileNamespace(string $filePath)
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);
        $namespace = '';

        foreach ($tokens as $token) {
            if ($token[0] === T_NAMESPACE) {
                $namespace = '';
            } elseif ($namespace !== null && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                $namespace .= $token[1];
            } elseif ($token === ';') {
                break;
            }
        }

        return $namespace;
    }
}
