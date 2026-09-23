<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route;

use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use think\App;
use think\Container;
use think\exception\HttpException;
use think\Request;
use think\Response;
use think\Validate;

/**
 * 路由调度基础类
 */
abstract class Dispatch
{
    /**
     * 控制器名
     * @var string
     */
    protected $controller;

    /**
     * 操作名
     * @var string
     */
    protected $actionName;

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    public function __construct(protected Request $request, protected Rule $rule, protected $dispatch, protected array $param = [], protected array $option = [], protected ?RuleItem $miss = null)
    {
    }

    /**
     * 执行路由调度
     * @access public
     * @return Response
     */
    public function run(): Response
    {
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
        $option = $this->option;

        // 添加中间件
        if (!empty($option['middleware'])) {
            if (isset($option['without_middleware'])) {
                $middleware = [];
                foreach ($option['middleware'] as $item) {
                    $middlewareName = is_array($item) ? $item[0] : $item;

                    if (!in_array($middlewareName, $option['without_middleware'], true)) {
                        $middleware[] = $item;
                    }
                }
            } else {
                $middleware = $option['middleware'];
            }
            $this->app->middleware->import($middleware, 'route');
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
     * 获取操作的绑定参数
     * @access protected
     * @return array
     */
    protected function getActionBindVars(): array
    {
        $bind = $this->rule->config('action_bind_param');
        return match ($bind) {
            'route' => $this->param,
            'param' => $this->request->param(),
            default => array_merge($this->request->get(), $this->param),
        };
    }

    /**
     * 执行中间件调度
     * @access public
     * @param object $instance 控制器实例
     * @param string $action
     * @return void
     */
    protected function responseWithMiddlewarePipeline($instance, $action)
    {
        // 注册控制器中间件
        $this->registerControllerMiddleware($instance);
        return $this->app->middleware->pipeline('controller')
            ->send($this->request)
            ->then(function () use ($instance, $action) {
                // 获取当前操作名
                $suffix = $this->rule->config('action_suffix');
                $action = $action . $suffix;

                if (is_callable([$instance, $action])) {
                    $vars = $this->getActionBindVars();
                    try {
                        $reflect = new ReflectionMethod($instance, $action);
                        // 严格获取当前操作方法名
                        $actionName = $reflect->getName();
                        if ($suffix) {
                            $actionName = substr($actionName, 0, -strlen($suffix));
                        }

                        $this->request->setAction($actionName);
                    } catch (ReflectionException $e) {
                        $reflect = new ReflectionMethod($instance, '__call');
                        $vars    = [$action, $vars];
                        $this->request->setAction($action);
                    }
                } else {
                    // 操作不存在
                    throw new HttpException(404, 'method not exists:' . $instance::class . '->' . $action . '()');
                }

                $data = $this->app->invokeReflectMethod($instance, $reflect, $vars);

                return $this->autoResponse($data);
            });
    }

    /**
     * 使用反射机制注册控制器中间件
     * @access public
     * @param object $controller 控制器实例
     * @return void
     */
    protected function registerControllerMiddleware($controller): void
    {
        $class = new ReflectionClass($controller);

        if ($class->hasProperty('middleware')) {
            $reflectionProperty = $class->getProperty('middleware');

            if (PHP_VERSION_ID < 80100) {
                $reflectionProperty->setAccessible(true);
            }

            $middlewares = $reflectionProperty->getValue($controller);
            $action      = $this->request->action(true);

            foreach ($middlewares as $key => $val) {
                if (!is_int($key)) {
                    $middleware = $key;
                    $options    = $val;
                } elseif (isset($val['middleware'])) {
                    $middleware = $val['middleware'];
                    $options    = $val['options'] ?? [];
                } else {
                    $middleware = $val;
                    $options    = [];
                }

                if (isset($options['only']) && !in_array($action, $this->parseActions($options['only']))) {
                    continue;
                } elseif (isset($options['except']) && in_array($action, $this->parseActions($options['except']))) {
                    continue;
                }

                if (is_string($middleware) && str_contains($middleware, ':')) {
                    $middleware = explode(':', $middleware);
                    if (count($middleware) > 1) {
                        $middleware = [$middleware[0], array_slice($middleware, 1)];
                    }
                }

                $this->app->middleware->controller($middleware);
            }
        }
    }

    protected function parseActions($actions)
    {
        return array_map(function ($item) {
            return strtolower($item);
        }, is_string($actions) ? explode(',', $actions) : $actions);
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
                $this->app->instance($result::class, $result);
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
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);

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

    public function __serialize(): array
    {
        return [
            'rule'       => $this->rule,
            'dispatch'   => $this->dispatch,
            'param'      => $this->param,
            'controller' => $this->controller,
            'actionName' => $this->actionName,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->rule       = $data['rule'];
        $this->dispatch   = $data['dispatch'];
        $this->param      = $data['param'];
        $this->controller = $data['controller'];
        $this->actionName = $data['actionName'];
        
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
