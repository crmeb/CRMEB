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

namespace app\adminapi\controller\v1\setting;


use app\adminapi\controller\AuthController;
use app\Request;
use app\services\system\log\SystemFileServices;
use app\services\system\SystemCrudDataService;
use app\services\system\SystemCrudListServices;
use app\services\system\SystemCrudServices;
use app\services\system\SystemMenusServices;
use app\services\system\SystemRouteServices;
use crmeb\services\CacheService;
use crmeb\services\crud\enum\FormTypeEnum;
use crmeb\services\crud\Make;
use crmeb\services\crud\Service;
use crmeb\services\FileService;
use think\facade\App;
use think\facade\Db;
use think\facade\Env;
use think\helper\Str;
use think\Response;

/**
 * Class SystemCrud
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\adminapi\controller\v1\setting
 */
class SystemCrud extends AuthController
{

    /**
     * SystemCrud constructor.
     * @param App $app
     * @param SystemCrudServices $services
     */
    public function __construct(App $app, SystemCrudServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function index()
    {
        return app('json')->success($this->services->getList());
    }

    /**
     * 验证路径
     * @param $data
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/2/19
     */
    public function crudVerifyPath($data)
    {
        if (strpos($data['controller'], 'app' . DS . 'adminapi' . DS . 'controller' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['validate'], 'app' . DS . 'adminapi' . DS . 'validate' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['service'], 'app' . DS . 'services' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['dao'], 'app' . DS . 'dao' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['model'], 'app' . DS . 'model' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['route'], 'app' . DS . 'adminapi' . DS . 'route' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['router'], 'router' . DS . 'modules' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['api'], 'api' . DS . 'crud' . DS) !== 0) return false;
        if (strpos($data['pages'], 'pages' . DS . 'crud' . DS) !== 0) return false;
        return true;
    }

    /**
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function save(SystemCrudDataService $service, $id = 0)
    {
        $data = $this->request->postMore([
            ['pid', 0],//上级菜单id
            ['menuName', ''],//菜单名
            ['tableName', ''],//表名
            ['modelName', ''],//模块名称
            ['tableComment', ''],//表备注
            ['tableField', []],//表字段
            ['tableIndex', []],//索引
            ['filePath', []],//生成文件位置
            ['isTable', 0],//是否生成表
            ['deleteField', []],//删除的表字段
        ]);

        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $data['menuName'])) return app('json')->fail('菜单名称只能是中文或者英文');
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $data['modelName'])) return app('json')->fail('模块名称只能是中文或者英文');
        if (!preg_match('/^[a-zA-Z_]+$/u', $data['tableName'])) return app('json')->fail('表名称只能是英文和下划线组成');
        if (!$this->crudVerifyPath($data['filePath'])) return app('json')->fail('生成的文件位置有误，请检查后重新生成');


        $fromField = $searchField = $hasOneField = $columnField = $tableIndex = [];

        $dictionaryids = array_column($data['tableField'], 'dictionary_id');
        $dictionaryList = [];
        foreach ($dictionaryids as $dictionaryid) {
            $dictionaryList[$dictionaryid] = $service->selectList(['cid' => $dictionaryid], 'name as label,value')->toArray();
        }

//        if ($dictionaryids) {
//            $dictionaryList = $service->getColumn([['id', 'in', $dictionaryids]], 'value', 'id');
//            foreach ($dictionaryList as &$value) {
//                $value = is_string($value) ? json_decode($value, true) : $value;
//            }
//        } else {
//            $dictionaryList = [];
//        }

        foreach ($data['tableField'] as $item) {
            //判断字段长度
            if (in_array($item['field_type'], [FormTypeEnum::DATE_TIME, 'timestamp', 'time', 'date', 'year']) && $item['limit'] > 6) {
                return app('json')->fail('字段' . $item['field'] . '长度不能大于6');
            }
            if ($item['field_type'] == 'enum' && !is_array($item['limit'])) {
                return app('json')->fail('数据类型为枚举时,长度为数组类型');
            }
            //收集列表展示数据
            if ($item['is_table'] && !in_array($item['field_type'], ['primaryKey', 'addSoftDelete'])) {
                if (isset($item['primaryKey']) && !$item['primaryKey']) {
                    $columnField[] = [
                        'field' => $item['field'],
                        'name' => $item['table_name'] ?: $item['comment'],
                        'type' => $item['from_type'],
                    ];
                }
            }
            $name = $item['table_name'] ?: $item['comment'];
            $option = $item['options'] ?? (isset($item['dictionary_id']) ? ($dictionaryList[$item['dictionary_id']] ?? []) : []);
            //收集表单展示数据
            if ($item['from_type']) {
                if (!$name) {
                    return app('json')->fail(500048, [], ['field' => $item['field']]);
                }
                if (!$option && in_array($item['from_type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT])) {
                    return app('json')->fail('表单类型为radio或select时,options字段不能为空');
                }
                $fromField[] = [
                    'field' => $item['field'],
                    'type' => $item['from_type'],
                    'name' => $name,
                    'required' => $item['required'],
                    'option' => $option
                ];
            }

            //搜索
            if (!empty($item['search'])) {
                $searchField[] = [
                    'field' => $item['field'],
                    'type' => $item['from_type'],
                    'name' => $name,
                    'search' => $item['search'],
                    'options' => $option
                ];
            }

            //关联
            if (!empty($item['hasOne'])) {
                $hasOneField[] = [
                    'field' => $item['field'],
                    'hasOne' => $item['hasOne'] ?? '',
                    'name' => $name,
                ];
            }

            //索引
            if (!empty($item['index'])) {
                $tableIndex[] = $item['field'];
            }
        }
        if (!$fromField) {
            return app('json')->fail(500046);
        }
        if (!$columnField) {
            return app('json')->fail(500047);
        }
        $data['fromField'] = $fromField;
        $data['tableIndex'] = $tableIndex;
        $data['columnField'] = $columnField;
        $data['searchField'] = $searchField;
        $data['hasOneField'] = $hasOneField;
        if (!$data['tableName']) {
            return app('json')->fail(500042);
        }

        $this->services->createCrud($id, $data);

        return app('json')->success(500043);
    }

    /**
     * 获取创建文件的目录存放位置
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function getFilePath()
    {
        [$tableName] = $this->request->postMore([
            ['tableName', ''],
        ], true);

        if (!$tableName) {
            return app('json')->fail(500042);
        }

        if (in_array($tableName, SystemCrudServices::NOT_CRUD_TABANAME)) {
            return app('json')->fail(500041);
        }

        $routeName = 'crud/' . Str::snake($tableName);

        $key = 'id';
        $tableField = [];

        $field = $this->services->getColumnNamesList($tableName);
        foreach ($field as $item) {
            if ($item['primaryKey']) {
                $key = $item['name'];
            }
            $tableField[] = [
                'field' => $item['name'],
                'field_type' => $item['type'],
                'primaryKey' => (bool)$item['primaryKey'],
                'default' => $item['default'],
                'limit' => $item['limit'],
                'comment' => $item['comment'],
                'required' => false,
                'is_table' => false,
                'table_name' => '',
                'from_type' => '',
            ];
        }

        $make = $this->services->makeFile($tableName, $routeName, false, [
            'menuName' => '',
            'key' => $key,
            'fromField' => [],
            'columnField' => [],
        ]);

        $makePath = [];
        foreach ($make as $k => $item) {
            $makePath[$k] = $item['path'];
        }

        return app('json')->success(compact('makePath', 'tableField'));
    }

    /**
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/12
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail(500035);
        }

        $info = $this->services->get($id);
        if (!$info) {
            return app('json')->fail(100026);
        }

        $routeName = 'crud/' . Str::snake($info->table_name);

        $column = $this->services->getColumnNamesList($info->table_name);
        $key = 'id';
        foreach ($column as $value) {
            if ($value['primaryKey']) {
                $key = $value['name'];
                break;
            }
        }

        $softDelete = false;

        foreach ((array)$info->field['tableField'] as $item) {
            if (isset($item['field_type']) && $item['field_type'] === 'addSoftDelete') {
                $softDelete = true;
                break;
            }
        }

        $make = $this->services->makeFile($info->table_name, $routeName, false, [
            'menuName' => $info->name,
            'modelName' => $info->model_name,
            'tableField' => $info->field['tableField'] ?? [],
            'key' => $key,
            'softDelete' => $softDelete,
            'fromField' => $info->field['fromField'] ?? [],
            'columnField' => $info->field['columnField'] ?? [],
            'searchField' => $info->field['searchField'] ?? [],
            'hasOneField' => $info->field['hasOneField'] ?? [],
        ]);

        $data = [];
        foreach ($make as $key => $item) {
            if (in_array($key, ['pages', 'router', 'api'])) {
                $path = Make::adminTemplatePath() . $item['path'];
            } else {
                $path = app()->getRootPath() . $item['path'];
            }
            $item['name'] = $item['path'];
            try {
                $item['content'] = file_get_contents($path, LOCK_EX);
                $data[$key] = $item;
            } catch (\Throwable $e) {

            }
        }

        //调整排序
        $makeData = [];
        $names = [
            'controller' => '控制器',
            'validate' => '验证器',
            'service' => '逻辑层',
            'dao' => '数据库操作',
            'model' => '模型层',
            'route' => '后端路由',
            'router' => '前端路由',
            'api' => '前端接口',
            'pages' => '前端页面'
        ];
        foreach ($names as $name => $value) {
            if (isset($data[$name])) {
                $data[$name]['file_name'] = $value;
                $makeData[] = $data[$name];
            }
        }
        $data = $makeData;

        $info = $info->toArray();
        //记录没有修改之前的数据
        foreach ((array)$info['field']['tableField'] as $key => $item) {
            $item['default_field'] = $item['field'];
            $item['default_limit'] = $item['limit'];
            $item['default_field_type'] = $item['field_type'];
            $item['default_comment'] = $item['comment'];
            $item['default_default'] = $item['default'];
            $item['default_default_type'] = $item['default_type'] ?? '1';
            $item['default_type'] = $item['default_type'] ?? '1';
            $item['is_table'] = !!$item['is_table'];
            $item['required'] = !!$item['required'];
            $item['index'] = isset($item['index']) && !!$item['index'];
            $item['primaryKey'] = isset($item['primaryKey']) ? (int)$item['primaryKey'] : 0;
            if (!isset($item['dictionary_id'])) {
                $item['dictionary_id'] = 0;
            }
            $info['field']['tableField'][$key] = $item;
        }
        //对比数据库,是否有新增字段
        $newColumn = [];
        $fieldAll = array_column($info['field']['tableField'], 'field');
        foreach ($column as $value) {
            if (!in_array($value['name'], $fieldAll)) {
                $newColumn[] = [
                    'field' => $value['name'],
                    'field_type' => $value['type'],
                    'primaryKey' => $value['primaryKey'] ? 1 : 0,
                    'default' => $value['default'],
                    'limit' => $value['limit'],
                    'comment' => $value['comment'],
                    'required' => '',
                    'is_table' => '',
                    'table_name' => '',
                    'from_type' => '',
                    'default_field' => $value['name'],
                    'default_limit' => $value['limit'],
                    'default_field_type' => $value['type'],
                    'default_comment' => $value['comment'],
                    'default_default' => $value['default'],
                ];
            }
        }

        if ($newColumn) {
            $info['field']['tableField'] = array_merge($newColumn, $info['field']['tableField']);
        }

        $keyInfo = $deleteInfo = $createInfo = $updateInfo = [];
        $tableField = [];
        foreach ($info['field']['tableField'] as $item) {
            if ($item['primaryKey']) {
                $keyInfo = $item;
                continue;
            }
            if ($item['field_type'] == 'timestamp' && $item['field'] === 'delete_time') {
                $deleteInfo = $item;
                continue;
            }
            if ($item['field_type'] == 'timestamp' && $item['field'] === 'create_time') {
                $createInfo = $item;
                continue;
            }
            if ($item['field_type'] == 'timestamp' && $item['field'] === 'update_time') {
                $updateInfo = $item;
                continue;
            }
            $tableField[] = $item;
        }
        if ($keyInfo) {
            array_unshift($tableField, $keyInfo);
        }
        if ($createInfo) {
            array_push($tableField, $createInfo);
        }
        if ($updateInfo) {
            array_push($tableField, $updateInfo);
        }
        if ($deleteInfo) {
            array_push($tableField, $deleteInfo);
        }
        $info['field']['tableField'] = $tableField;
        $info['field']['pid'] = (int)$info['field']['pid'];
        return app('json')->success(['file' => $data, 'crudInfo' => $info]);
    }

    /**
     * @param Request $request
     * @param SystemFileServices $service
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/24
     */
    public function savefile(Request $request, SystemFileServices $service, $id)
    {
        $comment = $request->param('comment');
        $filepath = $request->param('filepath');
        $pwd = $request->param('pwd');

        if ($pwd == '') {
            return app('json')->fail('请输入文件管理密码');
        }
        if (config('filesystem.password') != $pwd) {
            return app('json')->fail('文件管理密码错误');
        }

        if (empty($filepath) || !$id) {
            return app('json')->fail(410087);
        }
        $crudInfo = $this->services->get($id, ['make_path']);
        if (!$crudInfo) {
            return app('json')->fail('修改的CRUD文件不存在');
        }

        $makeFilepath = '';
        foreach ($crudInfo->make_path as $key => $item) {
            $path = $item;
            if (in_array($key, ['pages', 'router', 'api'])) {
                $item = Make::adminTemplatePath() . $item;
            } else {
                $item = app()->getRootPath() . $item;
            }
            if ($filepath == $path) {
                $makeFilepath = $item;
                break;
            }
        }
        if (!$makeFilepath || !in_array($filepath, $crudInfo->make_path)) {
            return app('json')->fail('您没有权限修改此文件');
        }
        $res = $service->savefile($makeFilepath, $comment);
        if ($res) {
            return app('json')->success(100000);
        } else {
            return app('json')->fail(100006);
        }
    }

    /**
     * 获取tree菜单
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function getMenus()
    {
        return app('json')->success(app()->make(SystemMenusServices::class)
            ->getList(['auth_type' => 1, 'is_show' => 1], ['auth_type', 'pid', 'id', 'menu_name as label', 'id as value']));
    }

    /**
     * 获取可以进行关联的表名
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/2
     */
    public function getAssociationTable()
    {
        return app('json')->success($this->services->getTableAll());
    }

    /**
     * 获取表的详细信息
     * @param string $tableName
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/2
     */
    public function getAssociationTableInfo(string $tableName)
    {
        if (!$tableName) {
            return app('json')->fail('缺少表名');
        }
//        if (in_array($tableName, SystemCrudServices::NOT_CRUD_TABANAME)) {
//            return app('json')->fail('不允许查看当前表明细');
//        }
        $tableInfo = $this->services->getColumnNamesList($tableName);

        $data = [];
        foreach ($tableInfo as $key => $item) {
            $data[] = [
                'label' => $item['comment'] ?: $key,
                'value' => $key,
                'leaf' => true
            ];
        }
        return app('json')->success($data);
    }

    /**
     * 获取创建表数据类型
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function columnType()
    {
        return app('json')->success($this->services->getTabelRule());
    }

    /**
     * @param SystemMenusServices $services
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function delete(SystemMenusServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail(500035);
        }

        $info = $this->services->get($id);
        if (!$info) {
            return app('json')->fail(100026);
        }

        $menusServices = app()->make(SystemMenusServices::class);
        if ($info->menu_ids) {
            $menusServices->deleteMenus($info->menu_ids);
        }
        if ($info->menu_id) {
            $menusServices->deleteMenus([$info->menu_id]);
        }

        $routeServices = app()->make(SystemRouteServices::class);
        if ($info->route_ids) {
            $routeServices->deleteRoutes($info->route_ids);
        }

        Db::query("DROP TABLE `" . Env::get('database.prefix', 'eb_') . $info->table_name . "`");

        if ($info->make_path) {
            $errorFile = [];
            foreach ($info->make_path as $key => $item) {
                if (in_array($key, ['pages', 'router', 'api'])) {
                    $item = Make::adminTemplatePath() . $item;
                } else {
                    $item = app()->getRootPath() . $item;
                }
                try {
                    unlink($item);
                } catch (\Throwable $e) {
                    $errorFile[] = $item;
                }
            }
            if ($errorFile) {
                return app('json')->success(500040, [], [
                    'message' => '文件：' . implode("\n", $errorFile) . ';无法被删除!'
                ]);
            }
        }

        $info->delete();


        return app('json')->success(100002);
    }

    /**
     * 下载文件
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/15
     */
    public function download($id)
    {
        if (!$id) {
            return app('json')->fail(500035);
        }

        $info = $this->services->get($id);
        if (!$info) {
            return app('json')->fail(100026);
        }
        $zipPath = app()->getRootPath() . 'backup' . DS . Str::camel($info->table_name);
        $zipName = app()->getRootPath() . 'backup' . DS . Str::camel($info->table_name) . '.zip';
        if (is_file($zipName)) {
            unlink($zipName);
        }
        $makePath = $info->make_path ?? [];

        foreach ($makePath as $key => $item) {
            if (in_array($key, ['pages', 'router', 'api'])) {
                $item = $zipPath . str_replace(dirname(app()->getRootPath()), '', Make::adminTemplatePath()) . $item;
            } else {
                $item = $zipPath . DS . 'crmeb' . DS . $item;
            }
            $makePath[$key] = $item;
        }

        $routeName = 'crud/' . Str::snake($info->table_name);

        $column = $this->services->getColumnNamesList($info->table_name);
        $key = 'id';
        foreach ($column as $value) {
            if ($value['primaryKey']) {
                $key = $value['name'];
                break;
            }
        }

        $softDelete = false;

        foreach ((array)$info->field['tableField'] as $item) {
            if (isset($item['field_type']) && $item['field_type'] === 'addSoftDelete') {
                $softDelete = true;
                break;
            }
        }

        $this->services->makeFile($info->table_name, $routeName, true, [
            'menuName' => $info->name,
            'tableFields' => $info->field['tableField'] ?? [],
            'key' => $key,
            'softDelete' => $softDelete,
            'fromField' => $info->field['fromField'] ?? [],
            'columnField' => $info->field['columnField'] ?? [],
            'searchField' => $info->field['searchField'] ?? [],
            'hasOneField' => $info->field['hasOneField'] ?? [],
        ], $makePath, $zipPath);

        if (!extension_loaded('zip')) {
            return app('json')->fail(500039);
        }

        $fileService = new FileService();
        $fileService->addZip($zipPath, $zipName, app()->getRootPath() . 'backup');

        $key = md5($zipName);
        CacheService::set($key, [
            'path' => $zipName,
            'fileName' => Str::camel($info->table_name) . '.zip',
        ], 300);
        return app('json')->success(['download_url' => sys_config('site_url') . '/adminapi/download/' . $key]);
    }

    /**
     * 获取权限路由
     * @param $tableName
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/20
     */
    public function getRouteList($tableName)
    {
        $info = $this->services->get(['table_name' => $tableName]);
        if (!$info) {
            return app('json')->fail('crud详情查询失败');
        }

        $routeList = app()->make(SystemMenusServices::class)->getColumn([
            ['id', 'in', $info->menu_ids],
            ['auth_type', '=', 2]
        ], 'methods,api_url');

        $newRoute = [];
        foreach ($routeList as $item) {
            if ($item['methods'] == 'GET') {
                if (strstr($item['api_url'], 'create')) {
                    $newRoute['create'] = $item['api_url'];
                } else if (strstr($item['api_url'], 'edit')) {
                    $newRoute['edit'] = $item['api_url'];
                } else if (strstr($item['api_url'], 'status')) {
                    $newRoute['status'] = $item['api_url'];
                } else {
                    if (strstr($item['api_url'], '<id>')) {
                        $newRoute['read'] = $item['api_url'];
                    } else {
                        $newRoute['index'] = $item['api_url'];
                    }
                }
            } else if ($item['methods'] == 'DELETE') {
                $newRoute['delete'] = $item['api_url'];
            } else if ($item['methods'] == 'PUT' && strstr($item['api_url'], 'status')) {
                $newRoute['status'] = $item['api_url'];
            }
        }

        $column = $this->services->getColumnNamesList($info->table_name);
        $key = 'id';
        foreach ($column as $value) {
            if ($value['primaryKey']) {
                $key = $value['name'];
                break;
            }
        }

        $columns = [
            [
                'title' => 'ID',
                'key' => $key,
                'from_type' => '',
            ]
        ];

        $readFields = [
            'name' => $info->field['modelName'] ?: $info->field['menuName'],
            'all' => [],
        ];
        foreach ((array)$info->field['tableField'] as $item) {
            if (isset($item['primaryKey']) && $item['primaryKey']) {
                continue;
            }

            $prefix = app()->make(Service::class)->getAttrPrefix();
            $readFields['all'][] = [
                'field' => in_array($item['from_type'], [FormTypeEnum::FRAME_IMAGES,
                    FormTypeEnum::DATE_TIME_RANGE,
                    FormTypeEnum::RADIO,
                    FormTypeEnum::SELECT,
                    FormTypeEnum::CHECKBOX]) ? $item['field'] . $prefix : $item['field'],
                'comment' => $item['comment'],
                'from_type' => $item['from_type'],
            ];

            if (isset($item['is_table']) && $item['is_table']) {
                $label = '';
                if (in_array($item['from_type'], [FormTypeEnum::SWITCH, FormTypeEnum::DATE_TIME_RANGE, FormTypeEnum::FRAME_IMAGE_ONE, FormTypeEnum::FRAME_IMAGES])) {
                    $keyName = 'slot';
                    if ($item['from_type'] == FormTypeEnum::FRAME_IMAGES) {
                        $label = $prefix;
                    } else if ($item['from_type'] == FormTypeEnum::DATE_TIME_RANGE) {
                        $label = $prefix;
                    }
                } elseif (in_array($item['from_type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT, FormTypeEnum::CHECKBOX])) {
                    $label = $prefix;
                    $keyName = 'key';
                } else {
                    $keyName = 'key';
                }

                $columns[] = [
                    'title' => $item['table_name'] ?: $item['comment'],
                    $keyName => $item['field'] . $label,
                    'from_type' => $item['from_type'],
                ];
            }
        }

        $searchField = $info->field['searchField'] ?? [];

        $search = [];
        foreach ((array)$searchField as $item) {
            if (!$item['type']) {
                $item['type'] = FormTypeEnum::INPUT;
            }
            if ($item['search'] == 'BETWEEN') {
                $item['type'] = 'date-picker';
            } else {
                if (in_array($item['type'], [FormTypeEnum::CHECKBOX, FormTypeEnum::RADIO, FormTypeEnum::SELECT])) {
                    $item['type'] = FormTypeEnum::SELECT;
                } else {
                    $item['type'] = FormTypeEnum::INPUT;
                }
            }

            $search[] = [
                'field' => $item['field'],
                'type' => $item['type'],
                'name' => $item['name'],
                'option' => $item['options'] ?? [],
            ];
        }

        $route = $newRoute;
        return app('json')->success(compact('key', 'route', 'columns', 'readFields', 'search'));
    }

    /**
     * 修改或者保存字典数据
     * @param SystemCrudDataService $service
     * @param int $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/1
     */
    public function saveDataDictionary(SystemCrudDataService $service, $id = 0)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['value', []],
        ]);

        if (!$data['name']) {
            return app('json')->fail('数据字段名不能为空');
        }
        if (!$data['value']) {
            return app('json')->fail('数据字段内容不能为空');
        }
        $data['value'] = json_encode($data['value']);
        if ($id) {
            $service->update($id, $data);
        } else {
            $service->save($data);
        }

        return app('json')->success($id ? '修改成功' : '添加成功');
    }

    /**
     * 查看数据字典
     * @param SystemCrudDataService $service
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/7
     */
    public function getDataDictionaryOne(SystemCrudDataService $service, $id)
    {
        if (!$id) {
            return app('json')->fail('缺少参数');
        }
        $info = $service->get($id);
        if (!$info) {
            return app('json')->fail('没有查询到数据');
        }
        return app('json')->success($info->toArray());
    }

    /**
     * 获取数据字典列表
     * @param SystemCrudDataService $service
     * @return Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/1
     */
    public function getDataDictionary(SystemCrudDataService $service)
    {
        $name = $this->request->get('name', '');
        $data = $service->getlistAll($name);
        return app('json')->success($data);
    }

    /**
     * 删除数据字典
     * @param SystemCrudDataService $service
     * @param $id
     * @return Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/8/4
     */
    public function deleteDataDictionary(SystemCrudDataService $service, $id)
    {
        if (!$id) {
            return app('json')->fail('缺少参数');
        }
        if ($service->delete($id)) {
            return app('json')->success('删除成功');
        } else {
            return app('json')->fail('删除失败');
        }
    }




    /** 数据字典新 */

    /**
     * 获取数据字典列表
     * @param SystemCrudListServices $service
     * @return Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryList(SystemCrudListServices $service)
    {
        $where = $this->request->getMore([
            ['status', ''],
        ]);
        return app('json')->success($service->dataDictionaryList($where));
    }

    /**
     * 获取数据字典添加修改表单
     * @param SystemCrudListServices $service
     * @param $id
     * @return Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListCreate(SystemCrudListServices $service, $id)
    {
        return app('json')->success($service->dataDictionaryListCreate($id));
    }

    /**
     * 保存数据字典
     * @param SystemCrudListServices $service
     * @param $id
     * @return Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListSave(SystemCrudListServices $service, $id)
    {
        $data = $this->request->getMore([
            ['name', ''],
            ['mark', ''],
            ['level', ''],
            ['status', ''],
        ]);
        $service->dataDictionaryListSave($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除数据字典
     * @param SystemCrudListServices $service
     * @param $id
     * @return Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryListDel(SystemCrudListServices $service, $id)
    {
        $service->dataDictionaryListDel($id);
        return app('json')->success('删除成功');
    }

    /**
     * 数据字典内容列表
     * @param SystemCrudDataService $service
     * @param $cid
     * @return Response
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoList(SystemCrudDataService $service, $cid)
    {
        return app('json')->success($service->dataDictionaryInfoList($cid));
    }

    /**
     * 数据字典内容添加修改表单
     * @param SystemCrudDataService $service
     * @param $cid
     * @param $id
     * @param $pid
     * @return Response
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoCreate(SystemCrudDataService $service, $cid, $id, $pid)
    {
        return app('json')->success($service->dataDictionaryInfoCreate($cid, (int)$id, (int)$pid));
    }

    /**
     * 保存数据字典内容
     * @param SystemCrudDataService $service
     * @param $cid
     * @param $id
     * @return Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoSave(SystemCrudDataService $service, $cid, $id)
    {
        $data = $this->request->getMore([
            ['name', ''],
            ['pid', 0],
            ['value', ''],
            ['sort', 0],
        ]);
        $service->dataDictionaryInfoSave($cid, $id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除数据字典内容
     * @param SystemCrudDataService $service
     * @param $id
     * @return Response
     * @throws \ReflectionException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/20
     */
    public function dataDictionaryInfoDel(SystemCrudDataService $service, $id)
    {
        $service->dataDictionaryInfoDel($id);
        return app('json')->success('删除成功');
    }
}
