<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route;

use Psr\Http\Message\ResponseInterface;
use think\App;
use think\Container;
use think\Request;
use think\Response;
use think\Validate;

/**
 * 路由调度基础类
 */
abstract class Dispatch
{
    /**
     * 应用对象
     * @var \think\App
     */
    protected $app;

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 路由规则
     * @var Rule
     */
    protected $rule;

    /**
     * 调度信息
     * @var mixed
     */
    protected $dispatch;

    /**
     * 路由变量
     * @var array
     */
    protected $param;

    public function __construct(Request $request, Rule $rule, $dispatch, array $param = [])
    {
        $this->request  = $request;
        $this->rule     = $rule;
        $this->dispatch = $dispatch;
        $this->param    = $param;
    }

    public function init(App $app)
    {
        $this->app = $app;

        // 执行路由后置操作
        $this->doRouteAfter();
    }

    /**
     * 执行路由调度
     * @access public
     * @return mixed
     */
    public function run(): Response
    {
        if ($this->rule instanceof RuleItem && $this->request->method() == 'OPTIONS' && $this->rule->isAutoOptions()) {
            $rules = $this->rule->getRouter()->getRule($this->rule->getRule());
            $allow = [];
            foreach ($rules as $item) {
                $allow[] = strtoupper($item->getMethod());
            }

            return Response::create('', 'html', 204)->header(['Allow' => implode(', ', $allow)]);
        }

        $data = $this->exec();
        return $this->autoResponse($data);
    }

    protected function autoResponse($data): Response
    {
        if ($data instanceof Response) {
            $response = $data;
        } elseif ($data instanceof ResponseInterface) {
            $response = Response::create((string) $data->getBody(), 'html', $data->getStatusCode());

            foreach ($data->getHeaders() as $header => $values) {
                $response->header([$header => implode(", ", $values)]);
            }
        } elseif (!is_null($data)) {
            // 默认自动识别响应输出类型
            $type     = $this->request->isJson() ? 'json' : 'html';
            $response = Response::create($data, $type);
        } else {
            $data = ob_get_clean();

            $content  = false === $data ? '' : $data;
            $status   = '' === $content && $this->request->isJson() ? 204 : 200;
            $response = Response::create($content, 'html', $status);
        }

        return $response;
    }

    /**
     * 检查路由后置操作
     * @access protected
     * @return void
     */
    protected function doRouteAfter(): void
    {
        $option = $this->rule->getOption();

        // 添加中间件
        if (!empty($option['middleware'])) {
            $this->app->middleware->import($option['middleware'], 'route');
        }

        if (!empty($option['append'])) {
            $this->param = array_merge($this->param, $option['append']);
        }

        // 绑定模型数据
        if (!empty($option['model'])) {
            $this->createBindModel($option['model'], $this->param);
        }

        // 记录当前请求的路由规则
        $this->request->setRule($this->rule);

        // 记录路由变量
        $this->request->setRoute($this->param);

        // 数据自动验证
        if (isset($option['validate'])) {
            $this->autoValidate($option['validate']);
        }
    }

    /**
     * 路由绑定模型实例
     * @access protected
     * @param array $bindModel 绑定模型
     * @param array $matches   路由变量
     * @return void
     */
    protected function createBindModel(array $bindModel, array $matches): void
    {
        foreach ($bindModel as $key => $val) {
            if ($val instanceof \Closure) {
                $result = $this->app->invokeFunction($val, $matches);
            } else {
                $fields = explode('&', $key);

                if (is_array($val)) {
                    [$model, $exception] = $val;
                } else {
                    $model     = $val;
                    $exception = true;
                }

                $where = [];
                $match = true;

                foreach ($fields as $field) {
                    if (!isset($matches[$field])) {
                        $match = false;
                        break;
                    } else {
                        $where[] = [$field, '=', $matches[$field]];
                    }
                }

                if ($match) {
                    $result = $model::where($where)->failException($exception)->find();
                }
            }

            if (!empty($result)) {
                // 注入容器
                $this->app->instance(get_class($result), $result);
            }
        }
    }

    /**
     * 验证数据
     * @access protected
     * @param array $option
     * @return void
     * @throws \think\exception\ValidateException
     */
    protected function autoValidate(array $option): void
    {
        [$validate, $scene, $message, $batch] = $option;

        if (is_array($validate)) {
            // 指定验证规则
            $v = new Validate();
            $v->rule($validate);
        } else {
            // 调用验证器
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);

            $v = new $class();

            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        /** @var Validate $v */
        $v->message($message)
            ->batch($batch)
            ->failException(true)
            ->check($this->request->param());
    }

    public function getDispatch()
    {
        return $this->dispatch;
    }

    public function getParam(): array
    {
        return $this->param;
    }

    abstract public function exec();

    public function __sleep()
    {
        return ['rule', 'dispatch', 'param', 'controller', 'actionName'];
    }

    public function __wakeup()
    {
        $this->app     = Container::pull('app');
        $this->request = $this->app->request;
    }

    public function __debugInfo()
    {
        return [
            'dispatch' => $this->dispatch,
            'param'    => $this->param,
            'rule'     => $this->rule,
        ];
    }
}
