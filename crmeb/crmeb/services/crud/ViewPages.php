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
use crmeb\services\crud\enum\SearchEnum;
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
        $modelName = $options['modelName'] ?? $name;
        $tableFields = $options['tableFields'] ?? [];
        $searchFields = $options['searchField'] ?? [];

        $this->value['AUTH'] = Str::snake($name);
        $this->value['COMPONENT_NAME'] = $this->value['AUTH'];
        $this->value['KEY'] = $options['key'] ?? 'id';
        $this->value['ROUTE'] = $route;
        $this->value['PATH_API_JS'] = $options['pathApiJs'] ?? '';
        $this->value['NAME_STUDLY'] = Str::studly($name);
        $this->value['NAME_CAMEL'] = Str::camel($name);
        $this->value['MODEL_NAME'] = $modelName;

        $this->setTableVueContent($field)
            ->setSearchVueContent($searchFields)
            ->setDescriptionContent($tableFields);

        return parent::handle($name, $options);
    }

    /**
     * 设置页面table内容
     * @param array $field
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setTableVueContent(array $field)
    {

        $contentVue = [];

        foreach ($field as $item) {
            $fieldValue = $item['field'];
            if (isset($item['type'])) {

                if (in_array($item['type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT, FormTypeEnum::CHECKBOX, FormTypeEnum::FRAME_IMAGES])) {
                    $fieldValue = $fieldValue . $this->attrPrefix;
                }
                //组合表单展示数据
                switch ($item['type']) {
                    case FormTypeEnum::FRAME_IMAGE_ONE:
                        $templateContent = file_get_contents($this->getStub('image'));
                        $contentVue[] = str_replace([
                            '{%FIELD%}',
                            '{%NAME%}'
                        ], [
                            $item['field'],
                            $item['name']
                        ], $templateContent);
                        break;
                    case FormTypeEnum::FRAME_IMAGES:
                        $templateContent = file_get_contents($this->getStub('images'));
                        $contentVue[] = str_replace([
                            '{%FIELD%}',
                            '{%NAME%}'
                        ], [
                            $fieldValue,
                            $item['name']
                        ], $templateContent);
                        break;
                    case FormTypeEnum::DATE_TIME_RANGE:
                        $tab = $this->tab(2);
                        $tab3 = $this->tab(3);
                        $tab4 = $this->tab(4);
                        $contentVue[] = <<<CONTENT
$tab<el-table-column label="$item[name]">
$tab3<template slot-scope="scope">
$tab4<span>{{scope.row.{$fieldValue}[0]}}-- {{scope.row.{$fieldValue}[1]}}</span>
$tab3</template>
$tab</el-table-column>
CONTENT;
                        break;
                    case FormTypeEnum::SWITCH:
                        $tab = $this->tab(2);
                        $tab3 = $this->tab(3);
                        $tab4 = $this->tab(4);
                        $contentVue[] = <<<CONTENT
$tab<el-table-column label="$item[name]">
$tab3<template slot-scope="scope">
$tab4<<el-switch  :active-value="1" :inactive-value="0" v-model="scope.row.{$fieldValue}" :value="scope.row.{$fieldValue}" size="large" @change="updateStatus(scope.row,'{$fieldValue}')" />
$tab3</template>
$tab</el-table-column>
CONTENT;
                        break;
                    default:
                        $tab = $this->tab(2);
                        $contentVue[] = <<<CONTENT
$tab<el-table-column prop="$fieldValue" label="$item[name]">
$tab</el-table-column>
CONTENT;
                }
            }
        }

        $this->value['CONTENT_TABLE_VUE'] = $contentVue ? implode("\n", $contentVue) : '';

        return $this;
    }

    /**
     * 设置搜索页面
     * @param array $searchFields
     * @return ViewPages
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setSearchVueContent(array $searchFields)
    {
        $contentSearchVue = [];
        //页面顶部搜索
        $fieldDatas = [];
        foreach ($searchFields as $item) {
            $fieldValue = $item['field'];
            $tab = $this->tab(2);
            $fieldDatas[] = $tab . $fieldValue . ":''";

            if (isset($item['search']) && $item['search']) {
                if (!$item['type']) {
                    switch ($item['search']) {
                        case SearchEnum::SEARCH_TYPE_BETWEEN:
                            $item['type'] = FormTypeEnum::DATE_TIME;
                            break;
                    }
                }
                switch ($item['type']) {
                    case FormTypeEnum::DATE_TIME:
                    case FormTypeEnum::DATE_TIME_RANGE:
                        $templateContent = file_get_contents($this->getStub('dataPicker'));
                        $contentSearchVue[] = str_replace([
                            '{%FIELD%}',
                            '{%NAME%}'
                        ], [
                            $fieldValue,
                            $item['name']
                        ], $templateContent);
                        break;
                    case FormTypeEnum::CHECKBOX:
                    case FormTypeEnum::RADIO:
                    case FormTypeEnum::SELECT:
                        $optionContentVue = [];
                        if (!empty($item['options'])) {
                            $tab = $this->tab(2);
                            foreach ($item['options'] as $option) {
                                $optionContentVue[] = <<<CONTENT
$tab<el-option value="$option[value]" label="$option[label]"></el-option>
CONTENT;
                            }
                        }
                        if ($optionContentVue) {
                            $templateContent = file_get_contents($this->getStub('select'));
                            $contentSearchVue[] = str_replace(
                                [
                                    '{%FIELD%}',
                                    '{%NAME%}',
                                    '{%CONTENT_SELECT_OPTION_VUE%}'
                                ],
                                [
                                    $fieldValue,
                                    $item['name'],
                                    implode("\n", $optionContentVue)
                                ],
                                $templateContent
                            );
                        }
                        break;
                    default:
                        $templateContent = file_get_contents($this->getStub('input'));
                        $contentSearchVue[] = str_replace([
                            '{%FIELD%}',
                            '{%NAME%}'
                        ], [
                            $fieldValue,
                            $item['name']
                        ], $templateContent);
                        break;
                }
            }
        }

        $this->value['FROM_DATA_CONTENT_VUE'] = $fieldDatas ? "\n" . implode(",\n", $fieldDatas) . ',' : '';

        if ($contentSearchVue) {
            $templateContent = file_get_contents($this->getStub('form'));
            $this->value['CONTENT_SEARCH_VUE'] = str_replace(
                [
                    '{%CONTENT_FORM_VUE%}'
                ],
                [
                    implode("\n", $contentSearchVue)
                ],
                $templateContent);
            $this->value['CLASS_NAME'] = 'mt16';
        }

        return $this;
    }

    /**
     * 获取查看详情字段展示内容
     * @param array $tableFields
     * @return ViewPages
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/12
     */
    protected function setDescriptionContent(array $tableFields)
    {
        $descriptionContent = '';
        foreach ($tableFields as $item) {
            $tab = $this->tab(3);
            $fieldValue = $item['field'];

            if (in_array($item['from_type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT, FormTypeEnum::CHECKBOX, FormTypeEnum::FRAME_IMAGES])) {
                $fieldValue = $fieldValue . $this->attrPrefix;
            }

            if (FormTypeEnum::FRAME_IMAGES === $item['from_type']) {
                $descriptionContent .= <<<CONTENT
$tab<el-descriptions-item label="$item[comment]"><el-image v-for="item in info.$fieldValue" :src="item" :preview-src-list="info.$fieldValue"></el-descriptions-item>\n
CONTENT;
            } else if (FormTypeEnum::FRAME_IMAGE_ONE === $item['from_type']) {
                $descriptionContent .= <<<CONTENT
$tab<el-descriptions-item label="$item[comment]"><el-image :src="info.$fieldValue" :preview-src-list="info.$fieldValue"></el-descriptions-item>\n
CONTENT;
            } else {
                $descriptionContent .= <<<CONTENT
$tab<el-descriptions-item label="$item[comment]">{{info.$fieldValue}}</el-descriptions-item>\n
CONTENT;
            }
        }

        $this->value['CONTENT_DESCRIPTIONS_VUE'] = $descriptionContent;

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
        return $this->getBasePath($path) . $name . DS . 'index.' . $this->fileMime;
    }

    /**
     * 获取模板内容
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
            'input' => $pagesPath . 'input.stub',
            'form' => $pagesPath . 'form.stub',
            'select' => $pagesPath . 'select.stub',
            'dataPicker' => $pagesPath . 'date-picker.stub',
        ];

        return $type ? $stubs[$type] : $stubs['index'];
    }
}
