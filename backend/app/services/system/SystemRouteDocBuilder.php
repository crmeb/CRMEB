<?php

namespace app\services\system;

use app\dao\system\SystemRouteDao;
use crmeb\basic\BaseService;
use crmeb\services\CacheService;

/**
 * 根据控制器源码补齐 system_route 接口文档字段
 */
class SystemRouteDocBuilder extends BaseService
{
    /** @var SystemRouteDao */
    protected $dao;

    /** @var array<string,string> */
    protected $fieldTips = [
        'id' => '主键ID',
        'ids' => 'ID集合，多个用逗号分隔',
        'uid' => '用户UID',
        'page' => '页码，从1开始',
        'limit' => '每页条数',
        'status' => '状态',
        'keyword' => '搜索关键词',
        'name' => '名称',
        'title' => '标题',
        'type' => '类型',
        'account' => '账号',
        'pwd' => '密码',
        'password' => '密码',
        'phone' => '手机号',
        'code' => '验证码/编码',
        'image' => '图片地址',
        'images' => '图片列表',
        'content' => '内容',
        'mark' => '备注',
        'sort' => '排序值，越大越靠前',
        'cate_id' => '分类ID',
        'pid' => '上级ID',
        'product_id' => '商品ID',
        'order_id' => '订单号/订单ID',
        'order_no' => '订单号',
        'prompt' => '提示词',
        'prompts' => '系统提示词数组',
        'model_id' => '模型ID，0表示默认模型',
        'token' => '令牌',
        'key' => '唯一键/验证标识',
        'captchaVerification' => '行为验证码二次校验串',
        'captchaType' => '验证码类型',
        'data' => '提交数据',
        'value' => '配置值',
        'field' => '字段名',
        'start_time' => '开始时间',
        'end_time' => '结束时间',
        'date' => '日期',
        'is_show' => '是否显示：1显示 0隐藏',
        'is_del' => '是否删除：1是 0否',
        'real_name' => '真实姓名',
        'nickname' => '昵称',
        'avatar' => '头像',
        'price' => '价格',
        'stock' => '库存',
        'num' => '数量',
        'remark' => '备注',
        'describe' => '描述',
        'description' => '说明',
        'url' => '链接地址',
        'path' => '路径',
        'method' => '请求方法',
        'appid' => '应用AppId',
        'appsecret' => '应用密钥',
        'access_token' => '访问令牌',
        'refresh_token' => '刷新令牌',
        'Authori-zation' => '鉴权头，格式 Bearer {token}',
        'authorization' => '鉴权头，格式 Bearer {token}',
        'token' => '登录令牌',
        'expires_time' => '令牌过期时间戳',
        'menus' => '菜单树',
        'unique_auth' => '权限标识列表',
        'user_info' => '当前用户信息',
        'logo' => '站点Logo',
        'logo_square' => '方形Logo',
        'version' => '系统版本',
        'site_name' => '站点名称',
        'list' => '列表数据',
        'count' => '总条数',
        'info' => '详情对象',
        'model' => '模型标识',
        'content' => '内容',
        'ok' => '是否成功',
        'success' => '是否成功',
    ];

    /** @var array<string, string> 控制器源码缓存 */
    protected $fileCache = [];

    public function __construct(SystemRouteDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 补齐指定应用文档
     * @param string $app
     * @return array{total:int,updated:int,skipped:int}
     */
    public function build(string $app = 'adminapi'): array
    {
        $root = app()->getRootPath();
        $list = $this->dao->selectList(['app_name' => $app], '*')->toArray();
        $updated = 0;
        $skipped = 0;
        $sourceCache = [];

        foreach ($list as $route) {
            $file = $root . ltrim(str_replace('\\', '/', (string)$route['file_path']), '/');
            if (!is_file($file)) {
                $skipped++;
                continue;
            }
            if (!isset($sourceCache[$file])) {
                $sourceCache[$file] = (string)file_get_contents($file);
            }
            $source = $sourceCache[$file];
            $action = (string)($route['action'] ?? '');
            $methodSource = $this->extractMethodSource($source, $action);
            $doc = $this->buildDoc($route, $methodSource, $source, $app);
            $this->dao->update((int)$route['id'], $doc);
            $updated++;
        }

        CacheService::clear();
        return ['total' => count($list), 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * @param array $route
     * @param string $methodSource
     * @param string $fileSource
     * @param string $app
     * @return array
     */
    protected function buildDoc(array $route, string $methodSource, string $fileSource, string $app): array
    {
        $describe = $this->extractDescribe($methodSource, $fileSource, $action = (string)$route['action'], (string)$route['name']);
        $moreParams = $this->extractMoreParams($methodSource);
        $pathParams = $this->extractPathParams((string)$route['path']);
        $method = strtoupper((string)$route['method']);
        $isWrite = in_array($method, ['POST', 'PUT', 'DELETE'], true);

        $query = [];
        $request = [];
        foreach ($pathParams as $param) {
            $query[] = $param;
        }
        foreach ($moreParams as $param) {
            if ($isWrite && $method !== 'DELETE') {
                // DELETE 多数走 query；POST/PUT 默认 body
                $request[] = $param;
            } else {
                $query[] = $param;
            }
        }

        // 若方法里同时出现 getMore + postMore，按调用分别归类
        $split = $this->extractMoreParamsByKind($methodSource);
        if ($split['get'] || $split['post']) {
            $query = array_merge($pathParams, $split['get']);
            $request = $split['post'];
            if ($method === 'GET' || $method === 'DELETE') {
                $query = array_merge($query, $split['post']);
                $request = [];
            }
        }

        $header = [];
        if (!$this->isPublicRoute($route)) {
            $header[] = $this->param('Authori-zation', 'string', 1, '鉴权头，格式 Bearer {token}');
        }

        [$response, $dataExample] = $this->buildResponseDoc($route, $methodSource, $query, $request);

        $requestExample = [];
        foreach ($request as $item) {
            $requestExample[$item['attribute']] = $item['value'] ?? $this->exampleValue($item['type']);
        }
        if (!$requestExample) {
            foreach ($query as $item) {
                if (($item['from'] ?? '') === 'path') {
                    continue;
                }
                $requestExample[$item['attribute']] = $item['value'] ?? $this->exampleValue($item['type']);
            }
        }

        $responseExample = [[
            'name' => '成功',
            'data' => [
                'status' => 200,
                'msg' => 'ok',
                'data' => $dataExample,
            ],
        ]];

        $errorCode = [
            $this->param('400', 'string', 0, '参数错误或业务校验失败'),
            $this->param('401', 'string', 0, '未登录或 Token 失效'),
            $this->param('410000', 'string', 0, '无权限访问'),
        ];

        $requestType = $isWrite ? 'application/json' : 'application/x-www-form-urlencoded';

        return [
            'describe' => $describe,
            'header' => $header,
            'query' => $this->uniqueParams($query),
            'request' => $this->uniqueParams($request),
            'request_type' => $requestType,
            'response' => $response,
            'request_example' => $requestExample,
            'response_example' => $responseExample,
            'error_code' => $errorCode,
        ];
    }

    /**
     * @param string $source
     * @param string $action
     * @return string
     */
    protected function extractMethodSource(string $source, string $action): string
    {
        if ($action === '') {
            return '';
        }
        $pattern = '/function\s+' . preg_quote($action, '/') . '\s*\([^)]*\)\s*(?::\s*[^{\s]+)?\s*\{/';
        if (!preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            return '';
        }
        $start = $m[0][1];
        $bracePos = strpos($source, '{', $start);
        if ($bracePos === false) {
            return '';
        }
        $i = $bracePos;
        $depth = 0;
        $len = strlen($source);
        for (; $i < $len; $i++) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    // 向前取方法注释
                    $docStart = $this->findDocblockStart($source, $start);
                    return substr($source, $docStart, $i - $docStart + 1);
                }
            }
        }
        return '';
    }

    protected function findDocblockStart(string $source, int $methodPos): int
    {
        $before = substr($source, 0, $methodPos);
        // 取方法正前方最近的一个 docblock，避免命中类注释
        if (preg_match_all('/\/\*\*[\s\S]*?\*\//', $before, $matches, PREG_OFFSET_CAPTURE)) {
            $last = end($matches[0]);
            $docStart = (int)$last[1];
            $between = trim(substr($before, $docStart + strlen($last[0])));
            // docblock 与 function 之间只允许可见性修饰符
            if ($between === '' || preg_match('/^(public|protected|private)\s*$/', $between)) {
                return $docStart;
            }
        }
        return $methodPos;
    }

    protected function extractDescribe(string $methodSource, string $fileSource, string $action, string $fallback): string
    {
        // 仅取方法源码开头的第一个 docblock（已裁剪为当前方法）
        if (preg_match('/^\s*\/\*\*([\s\S]*?)\*\//', $methodSource, $m)) {
            $lines = preg_split('/\R/', $m[1]) ?: [];
            $parts = [];
            foreach ($lines as $line) {
                $line = trim($line);
                $line = preg_replace('/^\*\s?/', '', $line) ?? '';
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '@') || str_starts_with($line, 'Class ')) {
                    continue;
                }
                $parts[] = $line;
            }
            if ($parts) {
                return implode("\n", $parts);
            }
        }
        return $fallback !== '' ? $fallback : ($action !== '' ? $action : '接口说明');
    }

    /**
     * @return array<int, array>
     */
    protected function extractMoreParams(string $methodSource): array
    {
        $all = [];
        foreach ($this->extractMoreParamsByKind($methodSource) as $items) {
            foreach ($items as $item) {
                $all[] = $item;
            }
        }
        return $this->uniqueParams($all);
    }

    /**
     * @return array{get:array,post:array}
     */
    protected function extractMoreParamsByKind(string $methodSource): array
    {
        $result = ['get' => [], 'post' => []];
        if ($methodSource === '') {
            return $result;
        }
        if (preg_match_all('/->\s*(getMore|postMore)\s*\(/', $methodSource, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $idx => $full) {
                $kind = strtolower($matches[1][$idx][0]) === 'getmore' ? 'get' : 'post';
                $openParen = $full[1] + strlen($full[0]) - 1;
                $argSource = $this->sliceBalanced($methodSource, $openParen, '(', ')');
                // 取第一个数组字面量
                $arrayPos = strpos($argSource, '[');
                if ($arrayPos === false) {
                    continue;
                }
                $arrayBody = $this->sliceBalanced($argSource, $arrayPos, '[', ']');
                // 去掉最外层 []
                $inner = substr($arrayBody, 1, -1);
                $result[$kind] = array_merge($result[$kind], $this->parseMoreArrayBody($inner));
            }
        }
        return [
            'get' => $this->uniqueParams($result['get']),
            'post' => $this->uniqueParams($result['post']),
        ];
    }

    /**
     * 截取成对括号/中括号内容（含起止符）
     */
    protected function sliceBalanced(string $source, int $start, string $open, string $close): string
    {
        $depth = 0;
        $len = strlen($source);
        $inStr = false;
        $quote = '';
        for ($i = $start; $i < $len; $i++) {
            $ch = $source[$i];
            $prev = $i > 0 ? $source[$i - 1] : '';
            if ($inStr) {
                if ($ch === $quote && $prev !== '\\') {
                    $inStr = false;
                }
                continue;
            }
            if ($ch === "'" || $ch === '"') {
                $inStr = true;
                $quote = $ch;
                continue;
            }
            if ($ch === $open) {
                $depth++;
            } elseif ($ch === $close) {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }
        return '';
    }

    /**
     * @return array<int, array>
     */
    protected function parseMoreArrayBody(string $body): array
    {
        $params = [];
        // ['name', 'default'] 或 ['name', 0] 或 ['name', []]
        if (preg_match_all("/\[\s*['\"]([^'\"]+)['\"]\s*,\s*([^\[\]]+|\[[^\]]*\])\s*\]/", $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $m[1];
                $defaultRaw = trim($m[2]);
                [$type, $value] = $this->inferTypeAndValue($defaultRaw);
                $must = $this->isLikelyRequired($name, $value) ? 1 : 0;
                $params[] = $this->param($name, $type, $must, $this->tip($name), $value);
            }
        }
        return $params;
    }

    /**
     * @return array<int, array>
     */
    protected function extractPathParams(string $path): array
    {
        $params = [];
        if (preg_match_all('/<([^>]+)>/', $path, $matches)) {
            foreach ($matches[1] as $raw) {
                $optional = str_ends_with($raw, '?');
                $name = rtrim($raw, '?');
                $params[] = $this->param($name, 'string', $optional ? 0 : 1, $this->tip($name) . ($optional ? '（可选路径参数）' : '（路径参数）'), '', 'path');
            }
        }
        return $params;
    }

    /**
     * @return array{0:string,1:mixed}
     */
    protected function inferTypeAndValue(string $defaultRaw): array
    {
        $raw = rtrim($defaultRaw, ',');
        if ($raw === '[]' || str_starts_with($raw, '[')) {
            return ['array', []];
        }
        if (preg_match('/^-?\d+$/', $raw)) {
            return ['number', (int)$raw];
        }
        if (preg_match('/^-?\d+\.\d+$/', $raw)) {
            return ['number', (float)$raw];
        }
        if ($raw === 'true' || $raw === 'false') {
            return ['boolean', $raw === 'true'];
        }
        if ($raw === 'null') {
            return ['string', null];
        }
        if (preg_match("/^['\"](.*)['\"]$/s", $raw, $m)) {
            return ['string', $m[1]];
        }
        return ['string', ''];
    }

    protected function tip(string $name): string
    {
        if (isset($this->fieldTips[$name])) {
            return $this->fieldTips[$name];
        }
        // user_name -> 用户名风格弱提示
        $text = str_replace('_', ' ', $name);
        return $text;
    }

    /**
     * 构建返回参数：status/msg/data，data 下至少再展开两层（共三层）
     * @param array $route
     * @param string $methodSource
     * @param array $query
     * @param array $request
     * @return array{0:array,1:array|\stdClass}
     */
    protected function buildResponseDoc(array $route, string $methodSource, array $query, array $request): array
    {
        $dataId = uniqid('data_', true);
        $status = $this->param('status', 'number', 0, '业务状态码，200成功');
        $msg = $this->param('msg', 'string', 0, '提示信息');
        $data = $this->param('data', 'object', 0, '业务数据');
        $data['id'] = $dataId;

        $fields = $this->extractResponseDataFields($route, $methodSource, $query, $request);
        $fields = $this->ensureThreeLevelFields($fields, $request, $route);

        [$flat, $example] = $this->flattenFieldTree($fields, $dataId);
        if (!$flat) {
            $resultId = uniqid('p_', true);
            $itemId = uniqid('p_', true);
            $flat = [
                array_merge($this->param('result', 'object', 0, '业务结果对象'), ['id' => $resultId, 'parentId' => $dataId]),
                array_merge($this->param('item', 'object', 0, '结果明细'), ['id' => $itemId, 'parentId' => $resultId]),
                array_merge($this->param('id', 'number', 0, '主键ID'), ['parentId' => $itemId]),
                array_merge($this->param('name', 'string', 0, '名称'), ['parentId' => $itemId]),
            ];
            $example = ['result' => ['item' => ['id' => 0, 'name' => '']]];
        }

        $response = array_merge([$status, $msg, $data], $flat);
        return [$response, $example];
    }

    /**
     * 将嵌套字段树压平为 parentId 列表，并生成示例
     * @param array $fields
     * @param string $parentId
     * @return array{0:array,1:array}
     */
    protected function flattenFieldTree(array $fields, string $parentId): array
    {
        $flat = [];
        $example = [];
        foreach ($fields as $field) {
            $name = (string)($field['attribute'] ?? '');
            if ($name === '') {
                continue;
            }
            $type = (string)($field['type'] ?? 'string');
            $row = $this->param($name, $type, 0, (string)($field['trip'] ?? $this->tip($name)));
            $row['parentId'] = $parentId;
            $id = $row['id'];
            $flat[] = $row;

            $children = $field['children'] ?? [];
            if ($children) {
                [$childFlat, $childExample] = $this->flattenFieldTree($children, $id);
                $flat = array_merge($flat, $childFlat);
                if ($type === 'array') {
                    $example[$name] = [$childExample];
                } else {
                    $example[$name] = $childExample;
                }
            } else {
                $example[$name] = $field['value'] ?? $this->exampleValue($type);
            }
        }
        return [$flat, $example];
    }

    /**
     * 保证 data 下字段树至少两层（整体三层：data -> 字段 -> 子字段）
     * @param array $fields
     * @param array $request
     * @param array $route
     * @return array
     */
    protected function ensureThreeLevelFields(array $fields, array $request, array $route): array
    {
        if (!$fields) {
            $fields = [
                [
                    'attribute' => 'result',
                    'type' => 'object',
                    'trip' => '业务结果对象',
                    'children' => [],
                ],
            ];
        }

        $itemSeed = $this->buildItemSeedFields($request, $route);
        foreach ($fields as &$field) {
            $type = (string)($field['type'] ?? 'string');
            $children = $field['children'] ?? [];
            if (in_array($type, ['object', 'array'], true)) {
                if (!$children) {
                    $children = $this->defaultChildrenForField((string)$field['attribute'], $type, $itemSeed);
                } else {
                    // 已有一层子节点时，若子节点仍是 object/array 且无孙节点，继续补齐
                    foreach ($children as &$child) {
                        $childType = (string)($child['type'] ?? 'string');
                        if (in_array($childType, ['object', 'array'], true) && empty($child['children'])) {
                            $child['children'] = $this->defaultChildrenForField((string)$child['attribute'], $childType, $itemSeed);
                        }
                    }
                    unset($child);
                }
                // array 统一包一层 item，便于形成三层
                if ($type === 'array') {
                    $hasItem = false;
                    foreach ($children as $child) {
                        if (($child['attribute'] ?? '') === 'item') {
                            $hasItem = true;
                            break;
                        }
                    }
                    if (!$hasItem) {
                        // 若 children 已是标量字段，视为 item 下字段
                        $looksLikeItemFields = true;
                        foreach ($children as $child) {
                            if (!empty($child['children'])) {
                                $looksLikeItemFields = false;
                                break;
                            }
                        }
                        if ($looksLikeItemFields) {
                            $children = [[
                                'attribute' => 'item',
                                'type' => 'object',
                                'trip' => '列表单项',
                                'children' => $children ?: $itemSeed,
                            ]];
                        }
                    }
                }
                $field['children'] = $children;
            }
        }
        unset($field);

        // 若全部都是标量（只有两层），包进 result.item
        $allScalar = true;
        foreach ($fields as $field) {
            if (!empty($field['children']) || in_array(($field['type'] ?? ''), ['object', 'array'], true)) {
                $allScalar = false;
                break;
            }
        }
        if ($allScalar) {
            $fields = [[
                'attribute' => 'result',
                'type' => 'object',
                'trip' => '业务结果',
                'children' => [[
                    'attribute' => 'item',
                    'type' => 'object',
                    'trip' => '结果明细',
                    'children' => $fields,
                ]],
            ]];
        }

        return $fields;
    }

    /**
     * @param array $request
     * @param array $route
     * @return array<int, array>
     */
    protected function buildItemSeedFields(array $request, array $route): array
    {
        $seed = [
            ['attribute' => 'id', 'type' => 'number', 'trip' => '主键ID', 'value' => 0],
        ];
        $seen = ['id' => true];
        foreach ($request as $item) {
            $name = (string)($item['attribute'] ?? '');
            if ($name === '' || isset($seen[$name]) || in_array($name, ['page', 'limit', 'keyword', 'Authori-zation', 'pwd', 'password', 'key', 'captchaVerification', 'captchaType', 'code'], true)) {
                continue;
            }
            $seen[$name] = true;
            $seed[] = [
                'attribute' => $name,
                'type' => (string)($item['type'] ?? 'string'),
                'trip' => (string)($item['trip'] ?? $this->tip($name)),
            ];
            if (count($seed) >= 8) {
                break;
            }
        }
        if (count($seed) < 3) {
            foreach (['name' => '名称', 'status' => '状态', 'create_time' => '创建时间'] as $k => $tip) {
                if (isset($seen[$k])) {
                    continue;
                }
                $seed[] = [
                    'attribute' => $k,
                    'type' => $this->guessFieldType($k),
                    'trip' => $tip,
                ];
            }
        }
        return $seed;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $itemSeed
     * @return array
     */
    protected function defaultChildrenForField(string $name, string $type, array $itemSeed): array
    {
        $n = strtolower($name);

        // 字符串数组：元素为标量（data -> field -> item 即三层）
        if (in_array($n, ['unique_auth', 'site_func', 'roles', 'permissions', 'ids', 'tags', 'labels'], true)
            || ($type === 'array' && str_ends_with($n, '_ids'))) {
            return [[
                'attribute' => 'item',
                'type' => 'string',
                'trip' => match ($n) {
                    'unique_auth' => '权限标识',
                    'site_func' => '站点功能标识',
                    'roles' => '角色标识',
                    'permissions' => '权限标识',
                    default => '数组元素',
                },
                'value' => '',
            ]];
        }

        if ($type === 'array' || in_array($n, ['list', 'menus', 'items', 'rows', 'children', 'tabs'], true)) {
            return [[
                'attribute' => 'item',
                'type' => 'object',
                'trip' => '数组单项',
                'children' => $this->defaultObjectChildren($n, $itemSeed),
            ]];
        }
        return $this->defaultObjectChildren($n, $itemSeed);
    }

    /**
     * @param string $name
     * @param array $itemSeed
     * @return array
     */
    protected function defaultObjectChildren(string $name, array $itemSeed): array
    {
        if (in_array($name, ['user_info', 'user', 'admin_info'], true)) {
            return [
                ['attribute' => 'id', 'type' => 'number', 'trip' => '用户ID', 'value' => 0],
                ['attribute' => 'account', 'type' => 'string', 'trip' => '账号'],
                ['attribute' => 'real_name', 'type' => 'string', 'trip' => '真实姓名'],
                ['attribute' => 'head_pic', 'type' => 'string', 'trip' => '头像'],
                ['attribute' => 'level', 'type' => 'number', 'trip' => '等级/身份'],
            ];
        }
        if (in_array($name, ['menus', 'menu'], true)) {
            return [
                ['attribute' => 'id', 'type' => 'number', 'trip' => '菜单ID', 'value' => 0],
                ['attribute' => 'menu_name', 'type' => 'string', 'trip' => '菜单名称'],
                ['attribute' => 'menu_path', 'type' => 'string', 'trip' => '路由路径'],
                ['attribute' => 'children', 'type' => 'array', 'trip' => '子菜单', 'children' => [
                    ['attribute' => 'item', 'type' => 'object', 'trip' => '子菜单项', 'children' => [
                        ['attribute' => 'id', 'type' => 'number', 'trip' => '菜单ID', 'value' => 0],
                        ['attribute' => 'menu_name', 'type' => 'string', 'trip' => '菜单名称'],
                    ]],
                ]],
            ];
        }
        if (in_array($name, ['list', 'items', 'rows', 'tabs'], true)) {
            return [
                ['attribute' => 'id', 'type' => 'number', 'trip' => '主键ID', 'value' => 0],
                ['attribute' => 'name', 'type' => 'string', 'trip' => '名称'],
                ['attribute' => 'status', 'type' => 'number', 'trip' => '状态'],
                ['attribute' => 'create_time', 'type' => 'string', 'trip' => '创建时间'],
            ];
        }
        if ($name === 'item' || $name === 'result' || $name === 'info' || $name === 'detail') {
            // 避免把请求参数（账号密码验证码）误当成返回明细
            $clean = [];
            foreach ($itemSeed as $row) {
                $attr = (string)($row['attribute'] ?? '');
                if (in_array($attr, ['pwd', 'password', 'key', 'captchaVerification', 'captchaType', 'code'], true)) {
                    continue;
                }
                $clean[] = $row;
            }
            if (count($clean) < 2) {
                $clean = [
                    ['attribute' => 'id', 'type' => 'number', 'trip' => '主键ID', 'value' => 0],
                    ['attribute' => 'name', 'type' => 'string', 'trip' => '名称'],
                    ['attribute' => 'status', 'type' => 'number', 'trip' => '状态'],
                ];
            }
            return $clean;
        }
        return $itemSeed;
    }

    /**
     * 从 success()/compact()/Service 返回值/列表约定中推断 data 字段
     * @return array<int, array{attribute:string,type?:string,trip?:string,value?:mixed}>
     */
    protected function extractResponseDataFields(array $route, string $methodSource, array $query, array $request): array
    {
        $fields = [];
        $action = strtolower((string)($route['action'] ?? ''));
        $path = strtolower((string)($route['path'] ?? ''));
        $fileSource = $this->readFileSource(app()->getRootPath() . ltrim(str_replace('\\', '/', (string)($route['file_path'] ?? '')), '/'));

        // 1) success([ ... ]) / success('msg', [ ... ])
        foreach ($this->extractSuccessArrayFields($methodSource) as $field) {
            $fields[] = $field;
        }

        // 2) success(compact('a', 'b'))
        if (!$fields && preg_match_all('/compact\s*\(([^)]*)\)/', $methodSource, $compacts)) {
            foreach ($compacts[1] as $args) {
                if (preg_match_all('/[\'\"]([a-zA-Z_][\w]*)[\'\"]/', $args, $names)) {
                    foreach ($names[1] as $name) {
                        $fields[] = [
                            'attribute' => $name,
                            'type' => $this->guessFieldType($name),
                            'trip' => $this->tip($name),
                        ];
                    }
                }
            }
        }

        // 3) success($var) / success($this->services->xxx())：追变量赋值或 Service 方法返回
        if (!$fields) {
            foreach ($this->extractSuccessExpressions($methodSource) as $expr) {
                $resolved = $this->resolveExpressionFields($expr, $methodSource, $fileSource);
                if ($resolved) {
                    $fields = $resolved;
                    break;
                }
            }
        }

        // 4) 列表类：getList / index / list
        $isList = (bool)preg_match('/getList|get_list|->list\(/', $methodSource)
            || in_array($action, ['index', 'list', 'getlist', 'lst'], true)
            || str_ends_with($path, '/list')
            || str_contains($path, '/lst');
        if (!$fields && $isList) {
            $itemSeed = $this->buildItemSeedFields($request, $route);
            $fields = [
                [
                    'attribute' => 'list',
                    'type' => 'array',
                    'trip' => '列表数据',
                    'children' => [[
                        'attribute' => 'item',
                        'type' => 'object',
                        'trip' => '列表单项',
                        'children' => $itemSeed,
                    ]],
                ],
                ['attribute' => 'count', 'type' => 'number', 'trip' => '总条数', 'value' => 0],
            ];
        }

        // 5) SSE / 流式
        if (!$fields && (str_contains($methodSource, 'text/event-stream') || str_contains($methodSource, 'event-stream'))) {
            $fields = [
                [
                    'attribute' => 'event',
                    'type' => 'object',
                    'trip' => 'SSE 事件载荷',
                    'children' => [
                        ['attribute' => 'type', 'type' => 'string', 'trip' => '事件类型：start/delta/reasoning/done/error'],
                        ['attribute' => 'content', 'type' => 'string', 'trip' => '增量文本内容'],
                        ['attribute' => 'msg', 'type' => 'string', 'trip' => '错误信息（error 时）'],
                    ],
                ],
            ];
        }

        // 6) 仅返回文案：success('xxx') —— data 通常为空数组
        if (!$fields && preg_match('/->\s*success\s*\(\s*[\'\"][^\'\"]+[\'\"]\s*\)/', $methodSource)) {
            $fields = [
                [
                    'attribute' => 'result',
                    'type' => 'object',
                    'trip' => '操作结果（该接口 data 常为空，以 msg 为准）',
                    'children' => [
                        ['attribute' => 'ok', 'type' => 'boolean', 'trip' => '是否成功', 'value' => true],
                        ['attribute' => 'message', 'type' => 'string', 'trip' => '结果说明'],
                    ],
                ],
            ];
        }

        // 7) 详情类兜底：用请求体字段包一层 info
        if (!$fields && (in_array($action, ['read', 'info', 'detail', 'edit', 'create'], true) || str_contains($path, '/<id>'))) {
            $itemSeed = $this->buildItemSeedFields($request, $route);
            $fields = [[
                'attribute' => 'info',
                'type' => 'object',
                'trip' => '详情对象',
                'children' => $itemSeed,
            ]];
        }

        return $this->uniqueResponseFields($fields);
    }

    /**
     * @return array<int, string>
     */
    protected function extractSuccessExpressions(string $methodSource): array
    {
        $exprs = [];
        if (!preg_match_all('/->\s*success\s*\(/', $methodSource, $matches, PREG_OFFSET_CAPTURE)) {
            return $exprs;
        }
        foreach ($matches[0] as $match) {
            $open = $match[1] + strlen($match[0]) - 1;
            $args = $this->sliceBalanced($methodSource, $open, '(', ')');
            if ($args === '') {
                continue;
            }
            $inner = trim(substr($args, 1, -1));
            if ($inner === '') {
                continue;
            }
            // 跳过纯字符串消息
            if ($inner[0] === "'" || $inner[0] === '"') {
                $comma = $this->findTopLevelComma($inner);
                if ($comma === null) {
                    continue;
                }
                $inner = trim(substr($inner, $comma + 1));
            }
            if ($inner !== '' && $inner[0] !== '[') {
                $exprs[] = rtrim($inner, ", \t\n\r");
            }
        }
        return $exprs;
    }

    /**
     * 解析表达式对应返回字段
     * @return array<int, array>
     */
    protected function resolveExpressionFields(string $expr, string $methodSource, string $controllerSource): array
    {
        $expr = trim($expr);
        // $this->services->getList(...) / $this->service->read($id)
        if (preg_match('/\$this->(\w+)\s*->\s*(\w+)\s*\(/', $expr, $m)) {
            return $this->resolveServiceMethodFields($m[1], $m[2], $controllerSource);
        }
        // $var
        if (preg_match('/^\$([a-zA-Z_][\w]*)$/', $expr, $m)) {
            $var = $m[1];
            // 找方法内最后一次赋值
            if (preg_match_all('/\$' . preg_quote($var, '/') . '\s*=\s*([^;]+);/', $methodSource, $assigns)) {
                $rhs = trim(end($assigns[1]));
                if (str_starts_with($rhs, '[')) {
                    return $this->fieldsFromArrayLiteral($rhs);
                }
                if (preg_match('/compact\s*\(([^)]*)\)/', $rhs, $c)) {
                    $fields = [];
                    if (preg_match_all('/[\'\"]([a-zA-Z_][\w]*)[\'\"]/', $c[1], $names)) {
                        foreach ($names[1] as $name) {
                            $fields[] = [
                                'attribute' => $name,
                                'type' => $this->guessFieldType($name),
                                'trip' => $this->tip($name),
                            ];
                        }
                    }
                    return $fields;
                }
                if (preg_match('/\$this->(\w+)\s*->\s*(\w+)\s*\(/', $rhs, $sm)) {
                    return $this->resolveServiceMethodFields($sm[1], $sm[2], $controllerSource);
                }
            }
        }
        return [];
    }

    /**
     * @return array<int, array>
     */
    protected function resolveServiceMethodFields(string $prop, string $method, string $controllerSource): array
    {
        // 列表约定
        if (in_array(strtolower($method), ['getlist', 'list', 'getlistforadmin', 'getpagelist'], true)) {
            return [
                [
                    'attribute' => 'list',
                    'type' => 'array',
                    'trip' => '列表数据',
                    'children' => [[
                        'attribute' => 'item',
                        'type' => 'object',
                        'trip' => '列表单项',
                        'children' => [
                            ['attribute' => 'id', 'type' => 'number', 'trip' => '主键ID', 'value' => 0],
                            ['attribute' => 'name', 'type' => 'string', 'trip' => '名称'],
                            ['attribute' => 'status', 'type' => 'number', 'trip' => '状态'],
                        ],
                    ]],
                ],
                ['attribute' => 'count', 'type' => 'number', 'trip' => '总条数', 'value' => 0],
            ];
        }

        $class = $this->resolveServiceClass($prop, $controllerSource);
        if ($class === '') {
            return [];
        }
        $path = $this->classToPath($class);
        if ($path === '' || !is_file($path)) {
            return [];
        }
        $serviceSource = $this->readFileSource($path);
        $methodSource = $this->extractMethodSource($serviceSource, $method);
        if ($methodSource === '') {
            return [];
        }
        // return [ ... ]（用括号平衡截取，避免嵌套数组截断）
        if (preg_match_all('/return\s*\[/', $methodSource, $returns, PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($returns[0]) as $hit) {
                $pos = strpos($methodSource, '[', $hit[1]);
                if ($pos === false) {
                    continue;
                }
                $literal = $this->sliceBalanced($methodSource, $pos, '[', ']');
                $fields = $this->fieldsFromArrayLiteral($literal);
                if ($fields) {
                    return $fields;
                }
            }
        }
        // return compact(...)
        if (preg_match('/return\s+compact\s*\(([^)]*)\)/', $methodSource, $c)) {
            $fields = [];
            if (preg_match_all('/[\'\"]([a-zA-Z_][\w]*)[\'\"]/', $c[1], $names)) {
                foreach ($names[1] as $name) {
                    $fields[] = [
                        'attribute' => $name,
                        'type' => $this->guessFieldType($name),
                        'trip' => $this->tip($name),
                    ];
                }
            }
            return $fields;
        }
        // getDetail/read/info 常见回详情对象
        if (in_array(strtolower($method), ['getdetail', 'read', 'info', 'getinfo', 'detail'], true)) {
            return [[
                'attribute' => 'info',
                'type' => 'object',
                'trip' => '详情对象',
                'children' => [
                    ['attribute' => 'id', 'type' => 'number', 'trip' => '主键ID', 'value' => 0],
                    ['attribute' => 'name', 'type' => 'string', 'trip' => '名称'],
                    ['attribute' => 'status', 'type' => 'number', 'trip' => '状态'],
                    ['attribute' => 'create_time', 'type' => 'string', 'trip' => '创建时间'],
                ],
            ]];
        }
        return [];
    }

    protected function resolveServiceClass(string $prop, string $controllerSource): string
    {
        $short = '';
        // @var Foo $services
        if (preg_match('/@var\s+([\\\\\w]+)\s+\$' . preg_quote($prop, '/') . '\b/', $controllerSource, $m)) {
            $short = ltrim($m[1], '\\');
        }
        // function __construct(... Foo $services)
        if ($short === '' && preg_match('/function\s+__construct\s*\(([^)]*)\)/', $controllerSource, $m)) {
            if (preg_match('/([\\\\\w]+)\s+\$' . preg_quote($prop, '/') . '\b/', $m[1], $mm)) {
                $short = ltrim($mm[1], '\\');
            }
        }
        // $this->services = app()->make(Foo::class)
        if ($short === '' && preg_match('/\$this->' . preg_quote($prop, '/') . '\s*=\s*app\(\)->make\(\s*([\\\\\w]+)::class/', $controllerSource, $m)) {
            $short = ltrim($m[1], '\\');
        }
        if ($short === '') {
            return '';
        }
        return $this->expandClassName($short, $controllerSource);
    }

    /**
     * 结合 use / 当前 namespace 展开短类名
     */
    protected function expandClassName(string $class, string $source): string
    {
        $class = ltrim($class, '\\');
        if (str_contains($class, '\\')) {
            return $class;
        }
        // use Foo\Bar\SystemAdminServices;
        // use Foo\Bar\SystemAdminServices as AdminServices;
        if (preg_match('/use\s+([\\\\\w]+)\\\\' . preg_quote($class, '/') . '\s*;/', $source, $m)) {
            return ltrim($m[1] . '\\' . $class, '\\');
        }
        if (preg_match('/use\s+([\\\\\w]+)\s+as\s+' . preg_quote($class, '/') . '\s*;/', $source, $m)) {
            return ltrim($m[1], '\\');
        }
        if (preg_match('/namespace\s+([\\\\\w]+)\s*;/', $source, $m)) {
            return ltrim($m[1] . '\\' . $class, '\\');
        }
        return $class;
    }

    protected function classToPath(string $class): string
    {
        $rel = ltrim(str_replace('\\', '/', $class), '/');
        $root = app()->getRootPath();
        $candidates = [
            $root . $rel . '.php',
        ];
        foreach ($candidates as $file) {
            if (is_file($file)) {
                return $file;
            }
        }
        return $root . $rel . '.php';
    }

    protected function readFileSource(string $file): string
    {
        if ($file === '' || !is_file($file)) {
            return '';
        }
        if (!isset($this->fileCache[$file])) {
            $this->fileCache[$file] = (string)file_get_contents($file);
        }
        return $this->fileCache[$file];
    }

    /**
     * 递归提取数组字面量字段树（含嵌套 object）
     * @return array<int, array>
     */
    protected function fieldsFromArrayLiteral(string $literal): array
    {
        $fields = [];
        $literal = trim($literal);
        if ($literal === '' || $literal[0] !== '[') {
            return $fields;
        }
        $wrapped = $this->sliceBalanced($literal, 0, '[', ']');
        if ($wrapped === '') {
            return $fields;
        }
        $body = substr($wrapped, 1, -1);
        $depth = 0;
        $inStr = false;
        $quote = '';
        $len = strlen($body);
        for ($i = 0; $i < $len; $i++) {
            $ch = $body[$i];
            $prev = $i > 0 ? $body[$i - 1] : '';
            if ($inStr) {
                if ($ch === $quote && $prev !== '\\') {
                    $inStr = false;
                }
                continue;
            }
            if ($depth === 0 && preg_match('/\G[\'\"]([a-zA-Z_][\w]*)[\'\"]\s*=>/', $body, $m, 0, $i)) {
                $name = $m[1];
                $i += strlen($m[0]);
                while ($i < $len && ctype_space($body[$i])) {
                    $i++;
                }
                $valueStart = $i;
                $field = [
                    'attribute' => $name,
                    'type' => $this->guessFieldType($name),
                    'trip' => $this->tip($name),
                ];
                if ($i < $len && $body[$i] === '[') {
                    $nestedLit = $this->sliceBalanced($body, $i, '[', ']');
                    $nestedFields = $this->fieldsFromArrayLiteral($nestedLit);
                    if ($nestedFields) {
                        $field['type'] = 'object';
                        $field['children'] = $nestedFields;
                    } else {
                        $field['type'] = 'array';
                    }
                    $i += max(0, strlen($nestedLit) - 1);
                } else {
                    // 跳过非数组值，直到顶层逗号
                    $valueDepth = 0;
                    $valueInStr = false;
                    $valueQuote = '';
                    for (; $i < $len; $i++) {
                        $vc = $body[$i];
                        $vp = $i > 0 ? $body[$i - 1] : '';
                        if ($valueInStr) {
                            if ($vc === $valueQuote && $vp !== '\\') {
                                $valueInStr = false;
                            }
                            continue;
                        }
                        if ($vc === "'" || $vc === '"') {
                            $valueInStr = true;
                            $valueQuote = $vc;
                            continue;
                        }
                        if ($vc === '(' || $vc === '[' || $vc === '{') {
                            $valueDepth++;
                            continue;
                        }
                        if ($vc === ')' || $vc === ']' || $vc === '}') {
                            $valueDepth = max(0, $valueDepth - 1);
                            continue;
                        }
                        if ($vc === ',' && $valueDepth === 0) {
                            break;
                        }
                    }
                    // 根据值片段微调类型
                    $valueSnippet = trim(substr($body, $valueStart, max(0, $i - $valueStart)));
                    if (preg_match('/^\d+(\.\d+)?$/', $valueSnippet)) {
                        $field['type'] = 'number';
                    } elseif (in_array($valueSnippet, ['true', 'false'], true)) {
                        $field['type'] = 'boolean';
                    } elseif (str_starts_with($valueSnippet, '[')) {
                        $field['type'] = 'array';
                    }
                }
                $fields[] = $field;
                continue;
            }
            if ($ch === "'" || $ch === '"') {
                $inStr = true;
                $quote = $ch;
                continue;
            }
            if ($ch === '[' || $ch === '(' || $ch === '{') {
                $depth++;
                continue;
            }
            if ($ch === ']' || $ch === ')' || $ch === '}') {
                $depth = max(0, $depth - 1);
            }
        }
        return $fields;
    }

    /**
     * 解析 success([ ... ]) 中的一级键
     * @return array<int, array>
     */
    protected function extractSuccessArrayFields(string $methodSource): array
    {
        $fields = [];
        if (!preg_match_all('/->\s*success\s*\(/', $methodSource, $matches, PREG_OFFSET_CAPTURE)) {
            return $fields;
        }
        foreach ($matches[0] as $match) {
            $open = $match[1] + strlen($match[0]) - 1;
            $args = $this->sliceBalanced($methodSource, $open, '(', ')');
            if ($args === '') {
                continue;
            }
            $inner = substr($args, 1, -1);
            $innerTrim = ltrim($inner);
            // success('msg', [ ... ]) 取第二个数组
            if ($innerTrim !== '' && ($innerTrim[0] === "'" || $innerTrim[0] === '"')) {
                $commaPos = $this->findTopLevelComma($inner);
                if ($commaPos !== null) {
                    $inner = substr($inner, $commaPos + 1);
                    $innerTrim = ltrim($inner);
                }
            }
            if ($innerTrim === '' || $innerTrim[0] !== '[') {
                continue;
            }
            $arrayPos = strpos($inner, '[');
            $arrayLit = $this->sliceBalanced($inner, (int)$arrayPos, '[', ']');
            if ($arrayLit === '') {
                continue;
            }
            $fields = $this->fieldsFromArrayLiteral($arrayLit);
            if ($fields) {
                break;
            }
        }
        return $fields;
    }

    protected function findTopLevelComma(string $source): ?int
    {
        $depthParen = 0;
        $depthBracket = 0;
        $inStr = false;
        $quote = '';
        $len = strlen($source);
        for ($i = 0; $i < $len; $i++) {
            $ch = $source[$i];
            $prev = $i > 0 ? $source[$i - 1] : '';
            if ($inStr) {
                if ($ch === $quote && $prev !== '\\') {
                    $inStr = false;
                }
                continue;
            }
            if ($ch === "'" || $ch === '"') {
                $inStr = true;
                $quote = $ch;
                continue;
            }
            if ($ch === '(') {
                $depthParen++;
            } elseif ($ch === ')') {
                $depthParen--;
            } elseif ($ch === '[') {
                $depthBracket++;
            } elseif ($ch === ']') {
                $depthBracket--;
            } elseif ($ch === ',' && $depthParen === 0 && $depthBracket === 0) {
                return $i;
            }
        }
        return null;
    }

    protected function guessFieldType(string $name): string
    {
        $n = strtolower($name);
        if (in_array($n, ['list', 'items', 'rows', 'images', 'ids', 'label_ids', 'children', 'menus', 'unique_auth', 'site_func', 'tabs'], true)
            || str_ends_with($n, '_list') || str_ends_with($n, '_ids')) {
            return 'array';
        }
        if (in_array($n, ['count', 'total', 'page', 'limit', 'status', 'type', 'id', 'uid', 'pid', 'num', 'stock', 'sort', 'expires_time'], true)
            || str_ends_with($n, '_id') || str_ends_with($n, '_num') || str_ends_with($n, '_count') || str_ends_with($n, '_time')) {
            return 'number';
        }
        if (str_starts_with($n, 'is_') || str_starts_with($n, 'has_') || in_array($n, ['ok', 'success', 'queue', 'timer'], true)) {
            return in_array($n, ['ok', 'success', 'queue', 'timer'], true) ? 'boolean' : 'number';
        }
        if (in_array($n, ['info', 'detail', 'config', 'data', 'user', 'order', 'product', 'user_info', 'result'], true)
            || str_ends_with($n, '_info')) {
            return 'object';
        }
        return 'string';
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function uniqueResponseFields(array $fields): array
    {
        $seen = [];
        $out = [];
        foreach ($fields as $item) {
            $key = strtolower((string)($item['attribute'] ?? ''));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            if (!empty($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->uniqueResponseFields($item['children']);
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * 公共免鉴权接口（登录/验证码/升级入口等）
     */
    protected function isPublicRoute(array $route): bool
    {
        if ((int)($route['type'] ?? 0) === 1) {
            return true;
        }
        $path = (string)($route['path'] ?? '');
        $exact = [
            'login', 'login/info', 'login/mobile', 'captcha_pro', 'ajcaptcha', 'ajcheck',
            'upgrade', 'upgrade/run', 'get_workerman_url', 'custom_admin_js', 'image/scan_upload',
            'site_config', 'index', 'theme_version',
        ];
        if (in_array($path, $exact, true)) {
            return true;
        }
        return (bool)preg_match('#^(login/|register|verify_code|sms_captcha|pc/login|wechat/auth|v2/.*/auth_login)#', $path);
    }

    /**
     * 常见必填字段
     */
    protected function isLikelyRequired(string $name, $defaultValue): bool
    {
        $required = [
            'account', 'pwd', 'password', 'prompt', 'phone', 'appid', 'appsecret',
            'id', 'uid', 'order_id', 'order_no', 'product_id', 'code',
        ];
        if (!in_array($name, $required, true)) {
            return false;
        }
        return $defaultValue === '' || $defaultValue === null;
    }

    protected function exampleValue(string $type)
    {
        $t = strtolower($type);
        if (str_contains($t, 'array') || str_contains($t, 'object')) {
            return [];
        }
        if (str_contains($t, 'number') || str_contains($t, 'int')) {
            return 0;
        }
        if (str_contains($t, 'bool')) {
            return false;
        }
        return '';
    }

    protected function param(string $attribute, string $type, $must, string $trip, $value = '', string $from = ''): array
    {
        $row = [
            'attribute' => $attribute,
            'type' => $type,
            'must' => (string)(int)$must,
            'trip' => $trip,
            'id' => uniqid('p_', true),
            'parentId' => '',
        ];
        if ($value !== '' || $value === 0 || $value === false || $value === []) {
            $row['value'] = $value;
        }
        if ($from !== '') {
            $row['from'] = $from;
        }
        return $row;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function uniqueParams(array $params): array
    {
        $seen = [];
        $out = [];
        foreach ($params as $item) {
            $key = strtolower((string)($item['attribute'] ?? '')) . '|' . (string)($item['parentId'] ?? '');
            if (($item['attribute'] ?? '') === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }
        return $out;
    }
}
