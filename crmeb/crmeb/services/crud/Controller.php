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

use crmeb\services\crud\enum\ActionEnum;
use crmeb\services\crud\enum\FormTypeEnum;
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
        $this->options = $options;

        $path = $options['path'] ?? '';
        $field = $options['field'] ?? [];
        $hasOneFields = $options['hasOneField'] ?? [];

        $this->value['MODEL_NAME'] = $options['modelName'] ?? $name;
        $this->value['NAME_CAMEL'] = Str::studly($name);
        $this->value['PATH'] = $this->getfolderPath($path);

        return $this->setUseContent()
            ->setControllerContent($field,
                $options['searchField'] ?? [],
                $name,
                $options['columnField'] ?? [],
                $hasOneFields)
            ->setController($name, $path);
    }

    /**
     * 设置控制器内容
     * @param string $name
     * @param string $path
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setController(string $name, string $path)
    {
        [$className, $contentController] = $this->getStubContent($name, 'controller');

        $this->value['NAME'] = $className;

        $contentStr = str_replace($this->var, $this->value, $contentController);
        $filePath = $this->getFilePathName($path, $this->value['NAME_CAMEL']);
        $this->usePath = $this->value['PATH'];

        $this->setPathname($filePath);
        $this->setContent($contentStr);

        return $this;
    }

    /**
     * 设置use内容
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setUseContent()
    {
        $this->value['USE_PHP'] = "use " . str_replace('/', '\\', $this->options['usePath']) . "Services;\n";

        return $this;
    }

    /**
     * 设置控制器内容
     * @param array $field
     * @param array $searchField
     * @param string $name
     * @param array $columnField
     * @param array $hasOneFields
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setControllerContent(array $field, array $searchField, string $name, array $columnField, array $hasOneFields)
    {
        $var = [
            "{%DATE%}",
            '{%VALIDATE_NAME%}',
            '{%FIELD_PHP%}',
            '{%FIELD%}',
            '{%WITH%}',
            '{%OTHER_PHP%}',
            '{%FIELD_ALL_PHP%}',
            '{%FIELD_SEARCH_PHP%}'
        ];

        $replace = [
            $this->value['DATE'],
            $this->options['validateName'] ?? '',
            $this->getSearchFieldContent($field),
            $this->getSearchListFieldContent($columnField),
            $this->getSearchListWithContent($hasOneFields),
            $this->getSearchListOtherContent($columnField),
            $this->getStatusUpdateContent($columnField),
            $this->getSearchPhpContent($searchField)
        ];

        $this->value['CONTENT_PHP'] = str_replace($var, $replace, $this->getStubControllerContent($name));

        return $this;
    }

    /**
     * 获取搜索字段内容
     * @param array $field
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function getSearchFieldContent(array $field)
    {
        $fieldStr = '';
        foreach ($field as $k) {
            $fieldStr .= $this->tab(3) . "['$k', ''],\n";
        }

        return $fieldStr;
    }

    /**
     * 提取控制器模板内容
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function getStubControllerContent(string $name)
    {
        $contentPhp = '';
        foreach (ActionEnum::ACTION_ALL as $item) {
            [, $stub] = $this->getStubContent($name, $item);
            $contentPhp .= $stub . "\r\n";
        }

        return $contentPhp;
    }

    /**
     * 设置搜索字段展示
     * @param array $columnField
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function getSearchListFieldContent(array $columnField)
    {
        $select = [];
        foreach ($columnField as $item) {
            //处理查询字段
            if (in_array($item['type'], [
                FormTypeEnum::DATE_TIME_RANGE,
                FormTypeEnum::FRAME_IMAGES,
                FormTypeEnum::RADIO,
                FormTypeEnum::SELECT,
                FormTypeEnum::CHECKBOX])) {
                $select[] = '`' . $item['field'] . '` as ' . $item['field'] . $this->attrPrefix;
            }
        }
        unset($item);

        return $select ? '\'*\',\'' . implode('\',\'', $select) . '\'' : '\'*\'';
    }

    /**
     * 设置搜索关联内容
     * @param array $hasOneFields
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function getSearchListWithContent(array $hasOneFields)
    {
        $with = [];
        foreach ($hasOneFields as $item) {
            if (isset($item['hasOne'])) {
                [$modelName,] = is_array($item['hasOne']) ? $item['hasOne'] : [$item['hasOne'], 'id'];
                $modelName = Model::getHasOneNamePases($modelName);
                if (!$modelName) {
                    continue;
                }
                $with[] = "'" . Str::camel($item['field']) . 'HasOne' . "'";
            }
        }
        unset($item);

        return $with ? implode(',', $with) : '[]';
    }

    /**
     * 获取可以修改的字段内容
     * @param array $columnField
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/9/6
     */
    public function getStatusUpdateContent(array $columnField)
    {
        $fieldAll = [];
        foreach ($columnField as $item) {
            if ($item['type'] == FormTypeEnum::SWITCH) {
                $fieldAll[] = $item['field'];
            }
        }
        return $fieldAll ? "['" . implode("','", $fieldAll) . "']" : '[]';
    }

    /**
     * 设置搜索其他内容
     * @param array $columnField
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function getSearchListOtherContent(array $columnField)
    {
        $otherContent = '';

        foreach ($columnField as $item) {
            //处理查询字段
            if (in_array($item['type'], [FormTypeEnum::FRAME_IMAGES, FormTypeEnum::CHECKBOX, FormTypeEnum::DATE_TIME_RANGE])) {
                if (!$otherContent) {
                    $otherContent .= "\n";
                }
                $otherContent .= $this->tab(2) . '$data[\'' . $item['field'] . '\'] = json_encode($data[\'' . $item['field'] . "']);\n";
            }
        }

        return $otherContent;
    }

    /**
     * 获取控制器中搜索内容
     * @param array $fields
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/3
     */
    protected function getSearchPhpContent(array $fields)
    {
        $fieldStr = '';
        foreach ($fields as $i => $item) {
            if (!empty($item['search'])) {
                $fieldStr .= $this->tab(3) . "['$item[field]', '']," . ((count($fields) - 1) == $i ? "" : "\n");
            }
        }

        return $fieldStr;
    }

    /**
     * 返回模板路径
     * @param string $type
     * @return string|string[]
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
            'status' => $controllerPath . 'status.stub',
            'edit' => $controllerPath . 'edit.stub',
            'update' => $controllerPath . 'update.stub',
            'delete' => $controllerPath . 'delete.stub',
            'read' => $controllerPath . 'read.stub',
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
