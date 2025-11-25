<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app;

use Spatie\Macroable\Macroable;

/**
 * Class Request
 * @package app
 * @method tokenData() 获取token信息
 * @method user(string $key = null) 获取用户信息
 * @method uid() 获取用户uid
 * @method isAdminLogin() 后台登陆状态
 * @method adminId() 后台管理员id
 * @method adminInfo() 后台管理信息
 * @method kefuId() 客服id
 * @method kefuInfo() 客服信息
 */
class Request extends \think\Request
{
    use Macroable;

    /**
     * 不过滤变量名
     * @var array
     */
    protected $except = ['menu_path', 'api_url', 'unique_auth',
        'description', 'custom_form', 'params_list', 'content', 'tableField', 'url', 'customCode', 'value', 'refund_reason_wap_img'];

    /**
     * 获取请求的数据
     * @param array $params
     * @param bool $suffix
     * @param bool $filter
     * @return array
     */
    public function more(array $params, bool $suffix = false, bool $filter = true): array
    {
        $p = [];
        $i = 0;
        foreach ($params as $param) {
            if (!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $this->param($param);
            } else {
                if (!isset($param[1])) $param[1] = null;
                if (!isset($param[2])) $param[2] = '';
                if (is_array($param[0])) {
                    $name = is_array($param[1]) ? $param[0][0] . '/a' : $param[0][0] . '/' . $param[0][1];
                    $keyName = $param[0][0];
                } else {
                    $name = is_array($param[1]) ? $param[0] . '/a' : $param[0];
                    $keyName = $param[0];
                }

                $p[$suffix == true ? $i++ : ($param[3] ?? $keyName)] = $this->param($name, $param[1], $param[2]);
            }
        }

        if ($filter && $p) {
            $p = $this->filterArrayValues($p);
        }

        return $p;
    }

    /**
     * 过滤接数组中的字符串
     * @param $str
     * @param bool $filter
     * @return array|mixed|string|string[]
     */
    public function filterArrayValues($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // 如果值是数组，并且不在不过滤变量名里面，递归调用 filterArrayValues，否则直接赋值
                $result[$key] = in_array($key, $this->except) ? $value : $this->filterArrayValues($value);
            } else {
                if (in_array($key, $this->except) || is_int($value) || is_null($value)) {
                    $result[$key] = $value;
                } else {
                    // 如果值是字符串，过滤特殊字符
                    $result[$key] = filter_str($value);
                }

            }
        }
        return $result;
    }

    /**
     * 获取get参数
     * @param array $params
     * @param bool $suffix
     * @param bool $filter
     * @return array
     */
    public function getMore(array $params, bool $suffix = false, bool $filter = true): array
    {
        return $this->more($params, $suffix, $filter);
    }

    /**
     * 获取post参数
     * @param array $params
     * @param bool $suffix
     * @param bool $filter
     * @return array
     */
    public function postMore(array $params, bool $suffix = false, bool $filter = true): array
    {
        return $this->more($params, $suffix, $filter);
    }

    /**
     * 获取用户访问端
     * @return array|string|null
     */
    public function getFromType()
    {
        return $this->header('Form-type', '');
    }

    /**
     * 当前访问端
     * @param string $terminal
     * @return bool
     */
    public function isTerminal(string $terminal)
    {
        return strtolower($this->getFromType()) === $terminal;
    }

    /**
     * 是否是H5端
     * @return bool
     */
    public function isH5()
    {
        return $this->isTerminal('h5');
    }

    /**
     * 是否是微信端
     * @return bool
     */
    public function isWechat()
    {
        return $this->isTerminal('wechat');
    }

    /**
     * 是否是小程序端
     * @return bool
     */
    public function isRoutine()
    {
        return $this->isTerminal('routine');
    }

    /**
     * 是否是app端
     * @return bool
     */
    public function isApp()
    {
        return $this->isTerminal('app');
    }

    /**
     * 是否是pc端
     * @return bool
     */
    public function isPc()
    {
        return $this->isTerminal('pc');
    }
}
