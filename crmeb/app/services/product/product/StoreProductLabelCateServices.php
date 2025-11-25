<?php

namespace app\services\product\product;

use app\dao\product\product\StoreProductLabelCateDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

class StoreProductLabelCateServices extends BaseServices
{
    public function __construct(StoreProductLabelCateDao $dao)
    {
        $this->dao = $dao;
    }

    public function getLabelCateList(array $where = [])
    {
        $list = $this->dao->getLabelCateList($where, 'id,name,sort,add_time');
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        return $list;
    }

    public function labelCateForm($id = 0)
    {
        $info = $id ? $this->dao->get($id) : [];
        $f[] = Form::input('name', '分类名称', $info['name'] ?? '')->maxlength(8)->required();
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0)->precision(0);
        return create_form($id ? '编辑分类' : '添加分类', $f, Url::buildUrl('/product/label_cate/save/' . $id), 'POST');
    }

    public function labelCateSave($id, $data)
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    public function labelCateDel($id)
    {
        $count = app()->make(StoreProductLabelServices::class)->getCount(['cate_id' => $id, 'is_del' => 0]);
        if($count) throw new AdminException('该分类下存在标签，无法删除');
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }
}
