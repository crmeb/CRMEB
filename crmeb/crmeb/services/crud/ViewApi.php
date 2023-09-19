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

use crmeb\exceptions\CrudException;
use crmeb\services\crud\enum\ActionEnum;
use think\App;
use think\helper\Str;

/**
 * Class ViewApi
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/1
 * @package crmeb\services\crud
 */
class ViewApi extends Make
{

    /**
     * @var string
     */
    protected $name = 'api';

    /**
     * @var string
     */
    protected $fileMime = 'js';

    /**
     * ViewApi constructor.
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
        return 'api' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return ViewApi
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    public function handle(string $name, array $options = [])
    {
        $path = $options['path'] ?? '';
        $route = $options['route'] ?? '';
        if (!$route) {
            throw new CrudException(500045);
        }

        return $this->setJsContent($name, $route)
            ->setApi($name, $path);
    }

    /**
     * 设置页面JS内容
     * @param string $name
     * @param string $route
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setJsContent(string $name, string $route)
    {
        $contentJs = '';

        foreach (ActionEnum::ACTION_ALL as $item) {
            $contentJs .= file_get_contents($this->getStub($item)) . "\n";
        }

        $var = [
            '{%ROUTE%}',
            '{%NAME_CAMEL%}',
            '{%NAME_STUDLY%}',
        ];

        $value = [
            $route,
            Str::camel($name),
            Str::studly($name),
        ];

        $contentJs = str_replace($var, $value, $contentJs);


        $this->value['CONTENT_JS'] = $contentJs;

        return $this;
    }

    /**
     * 设置页面api内容
     * @param string $name
     * @param string $path
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setApi(string $name, string $path)
    {
        //生成api
        [, $content] = $this->getStubContent($name, $this->name);

        $contentStr = str_replace($this->var, $this->value, $content);
        $filePath = $this->getFilePathName($path, Str::camel($name));

        $this->setPathname($filePath);
        $this->setContent($contentStr);

        return $this;
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

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }

    /**
     * 模板文件配置
     * @param string $type
     * @return mixed
     */
    protected function getStub(string $type = 'api')
    {
        $servicePath = __DIR__ . DS . 'stubs' . DS . 'view' . DS . 'api' . DS;

        $stubs = [
            'index' => $servicePath . 'getCrudListApi.stub',
            'create' => $servicePath . 'getCrudCreateApi.stub',
            'save' => $servicePath . 'crudSaveApi.stub',
            'status' => $servicePath . 'crudStatusApi.stub',
            'edit' => $servicePath . 'getCrudEditApi.stub',
            'read' => $servicePath . 'getCrudReadApi.stub',
            'delete' => $servicePath . 'crudDeleteApi.stub',
            'update' => $servicePath . 'crudUpdateApi.stub',
            'api' => $servicePath . 'crud.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }

}
