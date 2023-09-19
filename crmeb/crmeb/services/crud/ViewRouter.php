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
use think\App;
use think\helper\Str;

/**
 * Class ViewRouter
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/1
 * @package crmeb\services\crud
 */
class ViewRouter extends Make
{
    /**
     * @var string
     */
    protected $name = 'router';

    /**
     * @var string
     */
    protected $fileMime = 'js';

    /**
     * ViewRouter constructor.
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
        return 'router' . DS . 'modules' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $options
     * @return ViewRouter
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    public function handle(string $name, array $options = [])
    {
        $path = $options['path'] ?? '';
        [$nameData, $content] = $this->getStubContent($name);

        $menus = $options['menuName'] ?? $name;
        $route = $options['route'] ?? Str::snake($name);
        $pagePath = $options['pagePath'] ?? Str::camel($name);
        if (!$route) {
            throw new CrudException(500045);
        }

        $this->value['MENUS'] = $menus;
        $this->value['NAME'] = $nameData;
        $this->value['ROUTE'] = $route;
        $this->value['PAGE_PATH'] = $pagePath;
        if (isset($this->value['PATH'])) {
            $this->value['PATH'] = $this->getfolderPath($path);
        }

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, Str::camel($name));

        $this->setContent($contentStr);
        $this->setPathname($filePath);
        return $this;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/3
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }

    /**
     * @param string $type
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/1
     */
    protected function getStub(string $type = '')
    {
        return __DIR__ . DS . 'stubs' . DS . 'view' . DS . 'router' . DS . 'modules' . DS . 'crud.stub';
    }
}
