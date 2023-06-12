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
use think\helper\Str;

class Route extends Make
{
    /**
     * @var string
     */
    protected $name = 'route';

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'route' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Route
     */
    public function handle(string $name, array $options = [])
    {
        $path = $options['path'] ?? '';
        $action = $options['action'] ?? [];
        $route = $options['route'] ?? '';
        $controller = $options['controller'] ?? $name;
        $routePath = $options['routePath'] ?? '';
        $menus = $options['menus'] ?? '';
        if (!$route) {
            throw new CrudException(500045);
        }
        if (!$action) {
            $action = ['index', 'create', 'save', 'edit', 'update', 'delete'];
        }

        $var = [
            '{%route%}',
            '{%controller%}',
            '{%routePath%}',
            '{%menus%}',
        ];

        $value = [
            $route,
            $routePath,
            $controller ? ($routePath ? '.' : '') . Str::studly($controller) : '',
            $menus
        ];

        $routeContent = "";
        foreach ($action as $item) {
            $routeContent .= file_get_contents($this->getStub($item)) . "\r\n";
        }

        if ($var && $value) {
            $routeContent = str_replace($var, $value, $routeContent);
        }

        $content = file_get_contents($this->getStub());

        $this->value['content-php'] = $routeContent;

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, strtolower($name));

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
     * @date 2023/4/11
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }

    /**
     * 设置模板
     * @param string $type
     * @return mixed|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/14
     */
    protected function getStub(string $type = 'route')
    {
        $routePath = __DIR__ . DS . 'stubs' . DS . 'route' . DS;

        $stubs = [
            'index' => $routePath . 'index.stub',
            'create' => $routePath . 'create.stub',
            'save' => $routePath . 'save.stub',
            'edit' => $routePath . 'edit.stub',
            'update' => $routePath . 'update.stub',
            'delete' => $routePath . 'delete.stub',
            'route' => $routePath . 'route.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }
}
