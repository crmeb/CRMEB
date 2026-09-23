<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------

namespace modules\channel\wechat_mini\listener;

use modules\channel\wechat_mini\sdk\MiniProgramService;

/**
 * 微信小程序 API 代理调用监听器
 * 响应业务层触发的 MiniProgramApiCall 事件，代理调用 MiniProgramService 的静态方法
 */
class MiniProgramApiCallListener
{
    /**
     * 允许通过事件代理调用的 MiniProgramService 静态方法白名单
     * 仅包含直播、拼团等业务需要的只读/管理类接口，避免任意方法暴露
     * @var array<string, true>
     */
    protected static array $allowedMethods = [
        'appCodeUnlimitService'    => true,
        'materialTemporaryService' => true,
        'addGoods'                 => true,
        'auditGoods'               => true,
        'resetauditGoods'          => true,
        'deleteGoods'              => true,
        'getGooodsInfo'            => true,
        'getRoleList'              => true,
        'getLivePlayback'          => true,
        'createLiveRoom'           => true,
        'roomAddGoods'             => true,
        'getLiveInfo'              => true,
    ];

    /**
     * 处理小程序 API 调用事件
     * @param array $params [
     *     'method' => string 调用的静态方法名,
     *     'args'   => array 静态方法参数列表,
     *     'chain'  => array 可选，方法链（针对 materialTemporaryService 返回实例后继续调用）,
     *                格式：[['method' => 'uploadImage', 'args' => [$path], 'return_field' => 'media_id'], ...]
     * ]
     * @return mixed 方法返回结果
     * @throws \RuntimeException 方法不在白名单或调用失败时抛出
     */
    public function handle(array $params): mixed
    {
        $method = (string)($params['method'] ?? '');
        $args = (array)($params['args'] ?? []);
        $chain = (array)($params['chain'] ?? []);

        if ($method === '' || !isset(self::$allowedMethods[$method])) {
            throw new \RuntimeException('MiniProgramApiCall 方法不在白名单: ' . $method);
        }

        if (!method_exists(MiniProgramService::class, $method)) {
            throw new \RuntimeException('MiniProgramService 方法不存在: ' . $method);
        }

        $result = MiniProgramService::$method(...$args);

        // 执行方法链，支持在返回的实例对象上继续调用方法
        foreach ($chain as $step) {
            if (!is_array($step) || empty($step['method'])) {
                throw new \RuntimeException('MiniProgramApiCall chain 步骤格式错误');
            }
            $chainArgs = (array)($step['args'] ?? []);
            $chainMethod = (string)$step['method'];
            if (!is_object($result) || !method_exists($result, $chainMethod)) {
                throw new \RuntimeException('MiniProgramApiCall chain 方法不可调用: ' . $chainMethod);
            }
            $result = $result->$chainMethod(...$chainArgs);
        }

        // 若最后一步指定了返回字段，则提取(兼容对象属性与数组键两种返回形态)
        if ($chain !== []) {
            $lastStep = end($chain);
            if (!empty($lastStep['return_field'])) {
                $field = (string)$lastStep['return_field'];
                if (is_array($result)) {
                    return $result[$field] ?? null;
                }
                if (is_object($result)) {
                    return $result->$field ?? null;
                }
                return null;
            }
        }

        return $result;
    }
}
