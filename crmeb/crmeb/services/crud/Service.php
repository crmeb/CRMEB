<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\services\crud;

use think\helper\Str;

/**
 * Class Business
 * @package crmeb\services
 */
class Service extends Make
{
    /**
     * @var string
     */
    protected $name = "services";

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'services' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Service
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/23
     */
    public function handle(string $name, array $options = [])
    {
        $path = $options['path'] ?? '';

        $this->value['use-php'] = $this->getDaoClassName($name, $path);
        $this->value['modelName'] = $options['modelName'] ?? $name;


        $field = $options['field'] ?? [];
        $action = ['index', 'form', 'save', 'update'];

        $contentAction = '';
        foreach ($action as $item) {
            [$class, $stub] = $this->getStubContent($name, $item);
            $contentAction .= $stub . "\n";
        }

        if ($field) {
            //生成form表单
            $var = ['{%key%}', '{%date%}', '{%route%}', '{%form-php%}', '{%modelName%}', '{%field%}'];
            $value = [$options['key'] ?? 'id', $this->value['date'], Str::snake($options['route'] ?? $name)];
            $from = [];
            $select = [];
            foreach ($field as $item) {
                //处理查询字段
                if (in_array($item['type'], ['radio', 'select', 'checkbox'])) {
                    $select[] = $item['field'] . ' as ' . $item['field'] . $this->attrPrefix;
                } else {
                    $select[] = $item['field'];
                }
                //处理表单信息
                switch ($item['type']) {
                    case 'frameImageOne':
                        $from[] = $this->tab(2) . $this->getframeImageOnePhpContent($item['field'], $item['name']) . ';';
                        break;
                    case 'frameImages':
                        $from[] = $this->tab(2) . $this->getframeImagesPhpContent($item['field'], $item['name']) . ';';
                        break;
                    default:
                        $valueContent = "''";
                        $input = '$info["' . $item['field'] . '"] ?? ';
                        if (in_array($item['type'], ['checkbox'])) {
                            $valueContent = '[]';
                            $input = '(array)(' . $input . $valueContent . ')';
                        } else if ($item['type'] == 'radio') {
                            $valueContent = '-1';
                            $input = '(int)(' . $input . $valueContent . ')';
                        } else {
                            $input = $input . $valueContent;
                        }
                        $from[] = $this->tab(2) . '$rule[] = FormBuilder::' . $item['type'] . '("' . $item['field'] . '", "' . $item['name'] . '",  ' . $input . ')' . $this->getOptionContent(in_array($item['type'], ['radio', 'select', 'checkbox']), $item['option'] ?? []) . ';';
                        break;
                }
            }
            if ($from) {
                $this->value['use-php'] .= "\n" . 'use crmeb\services\FormBuilder;';
                $value[] = implode("\n", $from);
            } else {
                $value[] = '';
            }
            $value[] = $options['modelName'] ?? $options['menus'] ?? $name;
            if (!empty($options['key'])) {
                array_push($select, $options['key']);
            }
            $value[] = implode(',', $select);
            if ($value && $var) {
                $contentAction = str_replace($var, $value, $contentAction);
            }
        }

        //生成service
        [$className, $content] = $this->getStubContent($name, $this->name);

        $this->value['nameCamel'] = Str::studly($name);
        $this->value['name'] = $className;
        $this->value['path'] = $this->getfolderPath($path);
        $this->value['content-php'] = $contentAction;

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, $this->value['nameCamel']);
        $this->usePath = $this->baseDir . '\\' . $this->value['nameCamel'];

        $this->setContent($contentStr);
        $this->setPathname($filePath);
        return $this;
    }

    /**
     * @param array $option
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/23
     */
    protected function getOptionContent(bool $isOption, array $option = [])
    {
        if (!$isOption) {
            return '';
        }
        if (!$option) {
            $option = [
                ['value' => 1, 'label' => '开启'],
                ['value' => 0, 'label' => '关闭']
            ];
        }
        $php = '';
        if ($option) {
            $attOption = [];
            foreach ($option as $item) {
                $value = (int)$item['value'];
                $attOption[] = $this->tab(3) . "['value'=>{$value}, 'label'=>'{$item['label']}'],";
            }

            $strOption = implode("\n", $attOption);
            $php = "->options([\n" . $strOption . "\n" . $this->tab(2) . "])";
        }

        return $php;
    }

    /**
     * 单图获取formphp内容
     * @param string $field
     * @param string $name
     * @param string $icon
     * @param string $width
     * @param string $height
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/14
     */
    protected function getframeImageOnePhpContent(string $field, string $name, bool $required = false, string $icon = 'ios-add', string $width = '950px', string $height = '505px')
    {
        $name = addslashes($name);
        $requiredText = $required ? '->required()' : '';
        $content = <<<CONTENT
\$rule[] = FormBuilder::frameImage('$field', '$name', url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => '$field']), \$info['$field'] ?? '')->icon('$icon')->width('$width')->height('$height')->modal(['footer-hide' => true])$requiredText
CONTENT;
        return $content;
    }

    /**
     * 多图获取formphp内容
     * @param string $field
     * @param string $name
     * @param string $icon
     * @param int $maxLength
     * @param string $width
     * @param string $height
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/14
     */
    protected function getframeImagesPhpContent(string $field, string $name, bool $required = false, string $icon = 'ios-images', int $maxLength = 10, string $width = '950px', string $height = '505px')
    {
        $name = addslashes($name);
        $requiredText = $required ? '->required()' : '';
        $content = <<<CONTENT
\$rule[] = FormBuilder::frameImages('$field', '$name', url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => '$field', 'type' => 'many', 'maxLength' => $maxLength]), \$info['$field'] ?? [])->maxLength($maxLength)->icon('$icon')->width('$width')->height('$height')->modal(['footer-hide' => true])$requiredText
CONTENT;
        return $content;
    }

    /**
     * @param string $name
     * @param string $path
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/23
     */
    protected function getDaoClassName(string $name, string $path)
    {
        $path = str_replace(['app\\services', 'app/services'], '', $path);
        $path = ltrim(str_replace('\\', '/', $path), '/');
        return 'use app\dao\crud\\' . ($path ? $path . '\\' : '') . Str::studly($name) . 'Dao;';
    }


    /**
     * @param string $type
     * @return mixed|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/13
     */
    protected function getStub(string $type = 'services')
    {
        $servicePath = __DIR__ . DS . 'stubs' . DS . 'service' . DS;

        $stubs = [
            'index' => $servicePath . 'crudListIndex.stub',
            'form' => $servicePath . 'getCrudForm.stub',
            'save' => $servicePath . 'crudSave.stub',
            'update' => $servicePath . 'crudUpdate.stub',
            'services' => $servicePath . 'crudService.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }
}
