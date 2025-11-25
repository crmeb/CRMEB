<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\services\product\product\StoreProductLabelCateServices;
use app\services\product\product\StoreProductLabelServices;
use think\facade\App;

class StoreProductLabel extends AuthController
{
    protected $labelCateServices;
    protected $labelServices;

    public function __construct(App $app, StoreProductLabelCateServices $labelCateServices, StoreProductLabelServices $labelServices)
    {
        parent::__construct($app);
        $this->labelCateServices = $labelCateServices;
        $this->labelServices = $labelServices;
    }

    public function labelCateList()
    {
        $where = $this->request->getMore([
            ['name', ''],
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->labelCateServices->getLabelCateList($where));
    }

    public function labelCateForm($id)
    {
        return app('json')->success($this->labelCateServices->labelCateForm($id));
    }

    public function labelCateSave($id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['sort', 0],
        ]);
        $this->labelCateServices->labelCateSave($id, $data);
        return app('json')->success('保存成功');
    }

    public function labelCateDel($id)
    {
        $this->labelCateServices->labelCateDel($id);
        return app('json')->success('删除成功');
    }


    public function labelList()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['cate_id', ''],
            ['status', ''],
            ['is_show', ''],
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->labelServices->LabelList($where));
    }

    public function labelInfo($id)
    {
        return app('json')->success($this->labelServices->labelInfo($id));

    }

    public function labelSave()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['cate_id', 0],
            ['type', 0],
            ['font_color', ''],
            ['bg_color', ''],
            ['border_color', ''],
            ['image', ''],
            ['sort', 0],
            ['status', 1],
            ['is_show', 1],
        ]);
        $this->labelServices->labelSave($data);
        return app('json')->success('保存成功');
    }

    public function labelDel($id)
    {
        $this->labelServices->labelDel($id);
        return app('json')->success('删除成功');
    }

    public function labelIsShow($id, $is_show)
    {
        $this->labelServices->labelIsShow($id, $is_show);
        return app('json')->success('修改成功');
    }

    public function labelStatus($id, $status)
    {
        $this->labelServices->labelStatus($id, $status);
        return app('json')->success('修改成功');
    }

    public function labelUseList()
    {
        return app('json')->success($this->labelServices->labelUseList());
    }
}
