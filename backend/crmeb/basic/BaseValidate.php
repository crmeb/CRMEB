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
declare(strict_types=1);

namespace crmeb\basic;

use think\Validate;

/**
 * 验证器基类
 * 所有验证器继承此类
 * Class BaseValidate
 * @package crmeb\basic
 */
abstract class BaseValidate extends Validate
{
    /**
     * 场景
     * @var array
     */
    protected $scene = [];

    /**
     * 验证数据
     * @param array $data
     * @param string|null $scene
     * @return array|bool
     * @throws \think\exception\ValidateException
     */
    public function goCheck(array $data, ?string $scene = null)
    {
        if ($scene) {
            $this->scene($scene);
        }
        $result = $this->check($data);
        if (!$result) {
            throw new \think\exception\ValidateException($this->getError());
        }
        return $data;
    }
}
