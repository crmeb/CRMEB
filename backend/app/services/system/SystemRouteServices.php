<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system;


use app\dao\system\SystemRouteDao;
use crmeb\basic\BaseService;
use crmeb\services\CacheService;
use crmeb\utils\FormBuilder;
use think\App;
use think\exception\ValidateException;
use think\helper\Str;

/**
 * Class SystemRouteServices
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\services\system
 */
class SystemRouteServices extends BaseService
{
    /**
     * @var SystemRouteDao
     */
    protected $dao;

    /**
     * SystemRouteServices constructor.
     * @param App $app
     * @param SystemRouteDao $dao
     */
    public function __construct(App $app, SystemRouteDao $dao)
    {
        parent::__construct($app);
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, 'name,path,method', $page, $limit)->toArray();
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * @param int $id
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/10
     */
    public function getInfo(int $id)
    {
        $routeInfo = $this->dao->get($id);
        if (!$routeInfo) {
            throw new ValidateException('接口信息不存在');
        }

        $routeInfo = $routeInfo->toArray();
        $routeInfo['cate_tree'] = app()->make(SystemRouteCateServices::class)->getAllList($routeInfo['app_name'], 'id,name,pid', 'id asc,sort desc');
        return $routeInfo;
    }

    /**
     * 获取tree数据
     * @param string $appName
     * @param string $name
     * @return mixed
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/05/06
     */
    public function getTreeList(string $appName = 'adminapi', string $name = '')
    {
        return CacheService::remember('ROUTE_LIST' . strtoupper($appName), function () use ($name, $appName) {
            $list = app()->make(SystemRouteCateServices::class)
                ->selectList(['app_name' => $appName], '*', 0, 0, 'id asc,sort desc', [
                    'children' => function ($query) use ($name, $appName) {
                        $query->where('app_name', $appName)
                            ->when('' !== $name, function ($q) use ($name) {
                                $q->where('name|path', 'LIKE', '%' . $name . '%');
                            });
                    }
                ])
                ->toArray();

            foreach ($list as $key => $item) {
                if (!empty($item['children'])) {
                    foreach ($item['children'] as $k => $v) {
                        if (isset($v['cate_id']) && isset($v['method'])) {
                            if ($v['method'] === 'DELETE') {
                                $v['method'] = 'DEL';
                            }
                            $v['pid'] = $v['cate_id'];
                            $list[$key]['children'][$k] = $v;
                        }
                    }
                }
            }
            return get_tree_children($list);
        }, 600);
    }

    /**
     * @param array $importData
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/26
     */
    public function importData(array $importData)
    {
        foreach ($importData as $item) {
            $id = $this->dao->value(['method' => $item['method'], 'path' => $item['path']], 'id');
            if ($id) {
                $this->dao->update($id, [
                    'query' => $item['query'],
                    'header' => $item['header'],
                    'request' => $item['request'],
                    'response' => $item['response'],
                    'request_type' => $item['request_type'],
                    'response_example' => $item['response_example'],
                ]);
            }
        }
        return true;
    }

    /**
     * 获取某个应用下的所有路由权限
     * @param string $app
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/6
     */
    public function getRouteListAll(string $app = 'adminapi')
    {
        //获取所有的路由
        $this->app = app();
        $this->app->route->setTestMode(true);
        $this->app->route->clear();
        $path = $this->app->getRootPath() . 'app' . DS . $app . DS . 'route' . DS;
        $files = is_dir($path) ? scandir($path) : [];
        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                include $path . $file;
            }
        }

        $route = $this->app->route->getRuleList();
        $action_arr = ['index', 'read', 'create', 'save', 'edit', 'update', 'delete'];


        foreach ($route as $key => $item) {
            $real_name = $item['option']['real_name'] ?? '';
            if (is_array($real_name)) {
                foreach ($action_arr as $a) {
                    // 现代路由写法使用 [Controller::class, 'action'] 数组，不能直接按字符串匹配。
                    $routeName = is_string($item['route']) ? $item['route'] : ($item['route'][1] ?? '');
                    if (Str::contains($routeName, $a)) {
                        $real_name = $real_name[$a] ?? '';
                    }
                }
            }
            $item['option']['real_name'] = $real_name;
            $route[$key] = $item;
            $except = $item['option']['except'] ?? [];

            $router = is_string($item['route']) ? explode('/', $item['route']) : [];
            $action = $router[count($router) - 1] ?? null;
            //去除不需要的路由
            if ($except && $action && in_array($action, $except)) {
                unset($route[$key]);
            }
            $only = $item['option']['only'] ?? [];
            if ($only && $action && !in_array($action, $only)) {
                unset($route[$key]);
            }
        }

        return $route;
    }

    /**
     * 获取顶级id
     * @param string $app
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/11
     */
    public function topCateId(string $app, string $cateName, int $pid = 0)
    {
        $oneId = app()->make(SystemRouteCateServices::class)->value(['app_name' => $app, 'name' => $cateName, 'pid' => 0], 'id');
        if (!$oneId) {
            //修复重复同步后反复增加二级文件夹
            $id = app()->make(SystemRouteCateServices::class)->value(['app_name' => $app, 'name' => $cateName, 'pid' => $pid], 'id');
            if ($id) return $id;
            $res = app()->make(SystemRouteCateServices::class)->save([
                'app_name' => $app,
                'name' => $cateName,
                'pid' => $pid,
                'add_time' => time(),
            ]);
            return $res->id;
        }
        return $oneId;
    }

    /**
     * 同步路由
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/6
     */
    public function syncRoute(string $app = 'adminapi')
    {
        $listAll = $this->getRouteListAll($app);

        $list = [];
        foreach ($listAll as $item) {
            if (!isset($item['option']['mark_name']) || strstr($item['rule'], '<MISS>') !== false) {
                continue;
            } else {
                $list[$item['option']['mark_name']][] = $item;
            }
        }
        $newsList = [];;
        foreach ($list as $key => $item) {
            $newItem = [];
            foreach ($item as $value) {
                if (isset($value['option']['cate_name'])) {
                    $newItem[$value['option']['cate_name']][] = $value;
                } else {
                    $newItem[$key][] = $value;
                }
            }
            $newsList[$key] = $newItem;
        }

        $list = [];
        foreach ($newsList as $key => $item) {
            $keys = array_keys($item);
            $pid = $this->topCateId($app, $key, 0);
            if ($keys == 1 && $key == $keys[0]) {
                foreach ($item[$key] as $value) {
                    $list[$pid][] = $value;
                }
            } else {
                foreach ($item as $i => $k) {
                    $cateId = $this->topCateId($app, $i, $pid);
                    foreach ($k as $value) {
                        $list[$cateId][] = $value;
                    }
                }
            }
        }

        //保持新增的权限路由
        $data = $this->dao->selectList(['app_name' => $app], 'path,method')->toArray();
        // 同步过程中同时防止数据库已有记录和本次扫描结果产生重复路由。
        $knownRoutes = [];
        foreach ($data as $routeData) {
            $knownRoutes[strtolower($routeData['method']) . '|' . strtolower($routeData['path'])] = true;
        }
        $save = [];
        foreach ($list as $key => $value) {
            foreach ($value as $item) {
                $routeKey = strtolower($item['method']) . '|' . strtolower($item['rule']);
                if (!isset($knownRoutes[$routeKey]) && strstr($item['rule'], '<MISS>') === false) {
                    //判断路由是否带@符号
                    if (is_array($item['route'])) {
                        // 插件路由通常以 [完整控制器类名, 方法名] 表示。
                        $controller = (string)($item['route'][0] ?? '');
                        $action = (string)($item['route'][1] ?? '');
                        $filePath = ltrim(str_replace('\\', '/', $controller), '/') . '.php';
                    } elseif (strpos($item['route'], '@') !== false) {
                        list($controller, $action) = explode('@', $item['route'], 2);
                        $controller = str_replace('\\', '/', ltrim($controller, '\\'));
                        $filePath = $controller . '.php';
                    } else {
                        $pathAndAction = explode('/', $item['route']);
                        $controller = $pathAndAction[0] ?? '';
                        $action = $pathAndAction[1] ?? '';
                        $filePath = 'app/' . $app . '/controller/' . str_replace('.', '/', $controller) . '.php';
                    }
                    $save[] = [
                        'name' => $item['option']['real_name'] ?? $item['name'],
                        'path' => $item['rule'],
                        'cate_id' => $key,
                        'app_name' => $app,
                        'file_path' => $filePath,
                        'action' => $action,
                        'type' => isset($item['option']['is_common']) && $item['option']['is_common'] ? 1 : 0,
                        'method' => $item['method'],
                        'add_time' => date('Y-m-d H:i:s'),
                    ];
                    $knownRoutes[$routeKey] = true;
                }
            }
        }

        if ($save) {
            $this->dao->saveAll($save);
        }
        //删除不存在的权限路由
        $data = $this->dao->selectList(['app_name' => $app], 'path,method,id')->toArray();
        // 清理历史同步产生的重复记录，同一应用下 method + path 只保留一条。
        $uniqueData = [];
        $duplicateIds = [];
        $existingRoutes = [];
        foreach ($data as $routeData) {
            $routeKey = strtolower($routeData['method']) . '|' . strtolower($routeData['path']);
            if (isset($existingRoutes[$routeKey])) {
                $duplicateIds[] = $routeData['id'];
                continue;
            }
            $existingRoutes[$routeKey] = true;
            $uniqueData[] = $routeData;
        }
        if ($duplicateIds) {
            $this->dao->delete([['id', 'in', $duplicateIds]]);
        }
        $data = $uniqueData;
        $delete = [];
        $deleteData = [];
        foreach ($data as $item) {
            if (!$this->diffRoute($listAll, $item['path'], $item['method'], 'rule') && $item['path'] !== '<MISS>') {
                $delete[] = $item['id'];
                $deleteData[] = [
                    'path' => $item['path'],
                    'method' => $item['method']
                ];
            }
        }
        //删除不存在的路由
        if ($delete) {
            $this->dao->delete([['id', 'in', $delete]]);
        }
        //删除不存在的权限
        if ($deleteData) {
            foreach ($deleteData as $item) {
                app()->make(SystemMenusServices::class)->deleteMenu($item['path'], $item['method']);
            }
        }
        CacheService::clear();
    }

    /**
     * 对比路由
     * @param array $data
     * @param string $path
     * @param string $method
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/6
     */
    protected function diffRoute(array $data, string $path, string $method, string $key = 'path')
    {
        $res = false;
        foreach ($data as $item) {
            if (strtolower($item['method']) == strtolower($method) && strtolower($item[$key]) == strtolower($path)) {
                $res = true;
                break;
            } else if ($method === '*' && strtolower($item[$key]) == strtolower($path)) {
                $res = true;
                break;
            }
        }
        return $res;
    }

    /**
     * 添加和修改路由
     * @param int $id
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function getFrom(int $id = 0, string $appName = 'adminapi')
    {
        $cateList = app()->make(SystemRouteCateServices::class)->getAllList($appName, 'name as label,path as value');

        $url = '/system/route';
        $routeInfo = [];
        if ($id) {
            $routeInfo = $this->dao->get($id);
            $routeInfo = $routeInfo ? $routeInfo->toArray() : [];
            $url .= '/' . $id;
        }

        $rule = [
            FormBuilder::cascader('cate_id', '分类', $routeInfo['cate_id'] ?? 0)->data($cateList),
            FormBuilder::input('name', '路由名称', $routeInfo['name'] ?? '')->required(),
            FormBuilder::input('path', '路由路径', $routeInfo['path'] ?? '')->required(),
            FormBuilder::select('method', '请求方式', $routeInfo['method'] ?? '')->options([
                ['value' => 'POST', 'label' => 'POST'],
                ['value' => 'GET', 'label' => 'GET'],
                ['value' => 'DELETE', 'label' => 'DELETE'],
                ['value' => 'PUT', 'label' => 'PUT'],
                ['value' => '*', 'label' => '*'],
            ])->required(),
            FormBuilder::radio('type', '类型', $routeInfo['type'] ?? 0)->options([
                ['value' => 0, 'lable' => '普通路由'],
                ['value' => 1, 'lable' => '公共路由'],
            ]),
            FormBuilder::hidden('app_name', $appName),
        ];

        return create_form($id ? '修改路由' : '添加路由', $rule, $url, $id ? 'PUT' : 'POST');
    }


    /**
     * 导入数据
     * @param string $filePath
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/26
     */
    public function import(string $filePath)
    {
        $preg = '/\{+[a-zA-Z0-9]+\}/';

        $res = file_get_contents(app()->getRootPath() . $filePath);

        $res = json_decode($res, true);
        $data = $res['apiCollection'][0]['items'];

        $route = [];
        foreach ($data as $item) {
            foreach ($item['items'] as $value) {
                if (isset($value['api'])) {
                    $path = str_replace('//', '/', str_replace('{}', '', $value['api']['path']));
                    $paramePath = $this->getPathValue($value['api']['parameters']['path'] ?? []);
                    if (strstr($path, ':') !== false) {
                        $path = str_replace(':', '', $path);
                    }
                    if (preg_match_all($preg, $path, $matches)) {
                        $paramePathMatche = [];
                        if (isset($matches[0]) && $matches[0]) {
                            foreach ($matches[0] as $v) {
                                $paramePathMatche[] = str_replace(['{', '}'], ['<', '>'], $v);
                            }
                        }
                        if ($paramePathMatche) {
                            $paramePath = implode('/', $paramePathMatche);
                        }
                    }
                    if ($path[0] === '/') {
                        $path = substr($path, 1);
                    }
                    $route[] = [
                        'method' => strtoupper($value['api']['method']),
                        'path' => $path . $paramePath,
                        'request_type' => $value['api']['requestBody']['type'],
                        'query' => $this->getParameters($value['api'], $res['commonParameters'] ?: []),
                        'header' => $this->getParameters($value['api'], $res['commonParameters'] ?: [], 'header'),
                        'request' => $this->getRequest($value['api']['requestBody'] ?? []),
                        'response' => $this->getResponse($value['api']['responses'][0]['jsonSchema']['properties'] ?? []),
                        'response_example' => $this->getResponseExample($value['api']['responseExamples'] ?? []),
                    ];
                }
            }
        }

        return $this->importData($route);
    }

    protected function getParameters(array $api, array $commonParameters = [], string $type = 'query', $parentId = '')
    {
        $parameters = [];
        // 获取参数
        if (!empty($api['parameters'][$type])) {
            foreach ($api['parameters'][$type] as $key => $option) {
                $id = uniqid('', true);
                $parameters[] = [
                    'attribute' => $option['name'],
                    'type' => $option['type'],
                    'trip' => $option['description'] ?? '',
                    'value' => $option['example'] ?? '',
                    'must' => empty($option['enable']) ? "0" : "1",
                    'id' => $id,
                    'parentId' => $parentId,
                ];
            }
        }
        // 获取公共参数
        if (!empty($api['commonParameters'][$type]) && !empty($commonParameters['parameters'][$type])) {
            foreach ($api['commonParameters'][$type] as $key => $option) {
                // 获取common参数
                foreach ($commonParameters['parameters'][$type] as $parameter) {
                    if ($parameter['name'] == $option['name']) {
                        $id = uniqid('', true);
                        $parameters[] = [
                            'attribute' => $parameter['name'],
                            'type' => $parameter['type'],
                            'trip' => $parameter['description'] ?? '',
                            'value' => $parameter['defaultValue'] ?? '',
                            'must' => empty($parameter['enable']) ? '0' : "1",
                            'id' => $id,
                            'parentId' => $parentId,
                        ];
                    }
                }
            }
        }
        return $parameters;
    }

    /**
     * 获取请求返回数据
     * @param array $options
     * @param string $parentId
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/26
     */
    protected function getResponse(array $options, $parentId = '')
    {
        $response = [];
        foreach ($options as $key => $option) {
            $id = uniqid('', true);
            $response[] = [
                'attribute' => $key,
                'type' => $option['type'],
                'trip' => $option['description'] ?? '',
                'id' => $id,
                'parentId' => $parentId,
            ];

            if (isset($option['items']['properties'])) {
                $option['properties'] = $option['items']['properties'];
            }
            if (isset($option['properties'])) {
                $response = array_merge($response, $this->getResponse($option['properties'], $id));
            }
        }

        return $response;
    }

    /**
     * 获取请求数据
     * @param array $options
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/26
     */
    protected function getRequest(array $options)
    {
        $parameters = $options['parameters'] ?? [];
        $jsonSchema = $options['jsonSchema']['properties'] ?? [];

        $request = [];
        foreach ($parameters as $option) {
            $request[] = [
                'attribute' => $option['name'],
                'type' => $option['type'] === 'text' ? 'string' : $option['type'],
                'must' => 0,
                'trip' => $option['description'],
                'id' => $option['id'],
            ];
        }
        foreach ($jsonSchema as $key => $json_option) {
            $request[] = [
                'attribute' => $key,
                'type' => $json_option['type'] === 'text' ? 'string' : $json_option['type'],
                'must' => 0,
                'trip' => $json_option['description'] ?? '',
                'id' => uniqid('', true),
            ];
        }
        return $request;
    }

    /**
     * 处理path路径
     * @param array $options
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/26
     */
    protected function getPathValue(array $options)
    {
        $path = [];
        foreach ($options as $option) {
            if (strstr($option['name'], '?') !== false) {
                $option['name'] = str_replace('?', '', $option['name']) . '?';
            }
            $path[] = '<' . $option['name'] . '>';
        }

        return implode('/', $path);
    }

    protected function getResponseExample($options)
    {
        $example = [];
        foreach ($options as $option) {
            if (empty($example)) {
                $example[] = [
                    'name' => $option['name'],
                    'data' => json_decode($option['data'], true),
                ];
            }
        }
        return $example;
    }

    public function getNameList(string $app = 'adminapi')
    {
        return $this->dao->getColumn(['app_name' => $app], 'name', 'path');
    }
}
