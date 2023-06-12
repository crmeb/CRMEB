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
 * 创建Controller
 * Class Controller
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/3/13
 * @package crmeb\servives\crud
 */
class Controller extends Make
{

    /**
     * @var string
     */
    protected $name = 'controller';

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'controller' . DS . 'crud';
    }

    /**
     * @return Controller
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/13
     */
    public function handle(string $name, array $options = [])
    {
        $path = $options['path'] ?? '';
        $contentPhp = '';
        $var = ["{%date%}", '{%validateName%}'];
        $fieldPhp = [$this->value['date'], $options['validateName'] ?? ''];

        $action = $options['action'] ?? [];
        $field = $options['field'] ?? [];

        if (!$action) {
            $action = ['index', 'create', 'save', 'edit', 'update', 'delete'];
        }

        if ($field) {
            $fieldStr = '';
            foreach ($field as $k) {
                $fieldStr .= $this->tab(3) . "['$k', ''],\n";
            }
            $fieldPhp[] = $fieldStr;
        }

        $res = false;
        foreach ($action as $item) {
            if (in_array($item, ['save', 'update'])) {
                $res = true;
            }
            [$class, $stub] = $this->getStubContent($name, $item);
            $contentPhp .= $stub . "\r\n";
        }

        if ($res) {
            $var[] = '{%field-php%}';
        }

        if ($var && $fieldPhp) {
            $contentPhp = str_replace($var, $fieldPhp, $contentPhp);
        }

        [$className, $contentController] = $this->getStubContent($name, 'controller');

        $this->value['modelName'] = $options['modelName'] ?? $name;
        $this->value['nameCamel'] = Str::studly($name);
        $this->value['name'] = $className;
        $this->value['path'] = $this->getfolderPath($path);
        $this->value['content-php'] = $contentPhp;
        $this->value['use-php'] = "use " . str_replace('/', '\\', $options['usePath']) . "Services;\n";

        $contentStr = str_replace($this->var, $this->value, $contentController);

        $filePath = $this->getFilePathName($path, $this->value['nameCamel']);
        $this->usePath = $this->value['path'];
        $this->setPathname($filePath);
        $this->setContent($contentStr);
        return $this;
    }

    /**
     * 返回模板路径
     * @param string $type
     * @return mixed|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/13
     */
    protected function getStub(string $type = 'controller')
    {
        $controllerPath = __DIR__ . DS . 'stubs' . DS . 'controller' . DS;

        $stubs = [
            'index' => $controllerPath . 'index.stub',
            'create' => $controllerPath . 'create.stub',
            'save' => $controllerPath . 'save.stub',
            'edit' => $controllerPath . 'edit.stub',
            'update' => $controllerPath . 'update.stub',
            'delete' => $controllerPath . 'delete.stub',
            'controller' => $controllerPath . 'crudController.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/3/13
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = str_replace(['app\\', 'app/'], '', $path);

        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }
}
