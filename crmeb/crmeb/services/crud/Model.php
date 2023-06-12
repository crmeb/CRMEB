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
        $this->value['key'] = $options['key'] ?? 'id';
        if (isset($options['softDelete']) && $options['softDelete']) {
            $this->value['use-php'] = "use think\model\concern\SoftDelete;\n";
            $this->value['content-php'] = $this->tab() . "use SoftDelete;\n";
        }
        $this->value['modelName'] = $options['modelName'] ?? $name;
        $field = $options['fromField'] ?? [];

        $attrFnContent = [];
        foreach ($field as $item) {
            if (in_array($item['type'], ['radio', 'select']) && !empty($item['option'])) {
                $attrFnContent[] = $this->getAttrFnContent($item['field'], $item['name'], $item['option']);
            }
        }
        if ($attrFnContent) {
            $this->value['attr-php'] = "\n" . implode("\n", $attrFnContent);
        }

        return parent::handle($name, $options);
    }

    /**
     *
     * @param string $key
     * @param array $options
     * @return array|false|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/11
     */
    protected function getAttrFnContent(string $key, string $comment, array $options)
    {
        $attrFnStub = file_get_contents($this->getStub('attr'));
        $var = ['{%field%}', '{%date%}', '{%name%}', '{%content-php%}'];
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
        ];

        return $type ? $stubs[$type] : $stubs['model'];
    }
}
