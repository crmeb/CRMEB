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
use app\services\system\SystemRouteServices;
use crmeb\services\CacheService;
use think\facade\App;

/**
 * Class SystemRoute
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/6
 * @package app\adminapi\controller\v1\setting
 */
class SystemRoute extends AuthController
{

    /**
     * SystemRoute constructor.
     * @param App $app
     * @param SystemRouteServices $services
     */
    public function __construct(App $app, SystemRouteServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 同步路由权限
     * @param string $appName
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/6
     */
    public function syncRoute(string $appName = 'adminapi')
    {
        $this->services->syncRoute($appName);

        return app('json')->success(100038);
    }

    /**
     * 列表数据
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name_like', ''],
            ['app_name', 'adminapi']
        ]);

        return app('json')->success($this->services->getList($where));
    }

    /**
     * tree数据
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function tree()
    {
        [$name, $appName] = $this->request->getMore([
            ['name_like', ''],
            ['app_name', 'adminapi']
        ], true);

        return app('json')->success($this->services->getTreeList($appName, $name));
    }


    /**
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function save($id = 0)
    {
        $data = $this->request->postMore([
            ['cate_id', 0],
            ['name', ''],
            ['path', ''],
            ['method', ''],
            ['type', 0],
            ['app_name', ''],
            ['query', []],
            ['header', []],
            ['request', []],
            ['response', []],
            ['request_example', []],
            ['response_example', []],
            ['describe', ''],
            ['error_code', []],
        ]);

//        if (!$data['name']) {
//            return app('json')->fail(500031);
//        }
//        if (!$data['path']) {
//            return app('json')->fail(500032);
//        }
//        if (!$data['method']) {
//            return app('json')->fail(500033);
//        }
//        if (!$data['app_name']) {
//            return app('json')->fail(500034);
//        }
        if ($id) {
            $this->services->update($id, $data);
        } else {
            $data['add_time'] = date('Y-m-d H:i:s');
            $this->services->save($data);
        }
        CacheService::clear();

        return app('json')->success($id ? 100001 : 100021);
    }

    /**
     * @param $id
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function read($id)
    {
        return app('json')->success($this->services->getInfo((int)$id));
    }

    /**
     * @param $id
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/7
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail(500035);
        }

        $this->services->destroy($id);

        return app('json')->success(100002);
    }
}
