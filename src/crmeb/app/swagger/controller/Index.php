<?php
declare (strict_types=1);

namespace app\swagger\controller;


class Index
{
    /**
     * @OA\Info(title="Swagger API",version="2.0")
     */
    public function index()
    {
        return view();
    }

    /**
     * 获取应用下的jsonApi
     * @return false|string
     */
    public function getApiContent(string $api_name = 'api')
    {
        if (!in_array($api_name, ['api', 'kefuapi', 'adminapi'])) {
            return [];
        }
        $swaggerJsonPath = app()->getAppPath() . 'view/json/' . $api_name . '_swagger.json';
        if (file_exists($swaggerJsonPath)) {
            return json_decode(file_get_contents($swaggerJsonPath), true);
        } else {
            $openapi = \OpenApi\scan(root_path('app/' . $api_name))->toJson();
            @file_put_contents($swaggerJsonPath, $openapi);
            return json_decode($openapi, true);
        }
    }
}
