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

use think\App;
use think\helper\Str;

/**
 * Class ViewPages
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/1
 * @package crmeb\services\crud
 */
class ViewPages extends Make
{

    /**
     * @var string
     */
    protected $name = 'pages';

    /**
     * @var string
     */
    protected $fileMime = 'vue';

    /**
     * ViewPages constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->basePath = $this->adminTemplatePath;
    }

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'pages' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $options
     * @return ViewPages
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/3
     */
    public function handle(string $name, array $options = [])
    {
        $field = $options['field'] ?? [];
        $route = $options['route'] ?? '';

        $columnStr = [];
        $contentVue = [];
        foreach ($field as $key => $item) {
            $keyName = 'key';
            $fieldValue = $item['field'];
            if (isset($item['type'])) {
                switch ($item['type']) {
                    case 'frameImageOne':
                        $keyName = 'slot';
                        $templateContent = file_get_contents($this->getStub('image'));
                        $contentVue[] = str_replace(['{%field%}'], [$item['field']], $templateContent);
                        break;
                    case 'frameImages':
                        $keyName = 'slot';
                        $templateContent = file_get_contents($this->getStub('images'));
                        $contentVue[] = str_replace(['{%field%}'], [$item['field']], $templateContent);
                        break;
                }
                if (in_array($item['type'], ['radio', 'select', 'checkbox'])) {
                    $fieldValue = $fieldValue . $this->attrPrefix;
                }
            }
            $columnStr[] = ($key == 0 ? $this->tab(2) : '') . "{\n" . $this->tab(3) . "title:\"{$item['name']}\"," . $this->tab(2) . "\n" . $this->tab(3) . "$keyName:\"{$fieldValue}\"\n" . $this->tab(2) . "}\n";
        }
        $this->value['auth'] = Str::snake($name);
        $this->value['componentName'] = $this->value['auth'];
        $this->value['key'] = $options['key'] ?? 'id';
        $this->value['route'] = $route;
        $this->value['content-vue'] = $columnStr ? "\n" . implode($this->tab(2) . ',', $columnStr) . $this->tab(2) . ',' : '';
        $this->value['pathApiJs'] = $options['pathApiJs'] ?? '';
        $this->value['nameStudly'] = Str::studly($name);
        $this->value['nameCamel'] = Str::camel($name);
        $this->value['content-table-vue'] = $contentVue ? implode("\n", $contentVue) : '';

        return parent::handle($name, $options);
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        return $this->getBasePath($path) . $name . DS . 'index.' . $this->fileMime;
    }

    /**
     * @param string $type
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/1
     */
    protected function getStub(string $type = 'index')
    {
        $pagesPath = __DIR__ . DS . 'stubs' . DS . 'view' . DS . 'pages' . DS . 'crud' . DS;

        $stubs = [
            'index' => $pagesPath . 'index.stub',
            'image' => $pagesPath . 'image.stub',
            'images' => $pagesPath . 'images.stub',
        ];

        return $type ? $stubs[$type] : $stubs['index'];
    }
}
