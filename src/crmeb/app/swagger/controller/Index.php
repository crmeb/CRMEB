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
}
