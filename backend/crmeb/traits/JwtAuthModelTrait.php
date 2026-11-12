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

namespace crmeb\traits;

use crmeb\utils\JwtAuth;
use Firebase\JWT\JWT;

/**
 * Trait JwtAuthModelTrait
 * @package crmeb\traits
 */
trait JwtAuthModelTrait
{
    /**
     * @param string $type
     * @param array $params
     * @return array
     */
    public function getToken(string $type, array $params = []): array
    {
        $id = $this->{$this->getPk()};
        $host = app()->request->host();
        $time = time();

        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => strtotime('+30 days'),
        ];
        $params['jti'] = compact('id', 'type');
        $token = JWT::encode($params, JwtAuth::secretKey());

        return compact('token', 'params');
    }

    /**
     * @param string $jwt
     * @return array
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function parseToken(string $jwt): array
    {
        JWT::$leeway = 60;

        $data = JWT::decode($jwt, JwtAuth::secretKey(), array('HS256'));

        $model = new self();
        return [$model->where($model->getPk(), $data->jti->id)->find(), $data->jti->type];
    }
}
