<?php

namespace app\api\controller\store;

use app\models\store\StoreCategory;
use app\Request;

class CategoryController
{
    public function category(Request $request)
    {
        $cateogry = StoreCategory::with('children')->where('is_show', 1)->order('sort desc,id desc')->where('pid', 0)->select();
        return app('json')->success($cateogry->hidden(['add_time', 'is_show', 'sort', 'children.sort', 'children.add_time', 'children.pid', 'children.is_show'])->toArray());
    }
}