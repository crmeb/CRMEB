<?php

namespace app\services\product\product;

use app\dao\product\product\StoreProductProtectionDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

class StoreProductProtectionServices extends BaseServices
{
    public function __construct(StoreProductProtectionDao $dao)
    {
        $this->dao = $dao;
    }

    public function protectionList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->protectionList($where, 'id,title,image,num,sort,add_time,status', $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->protectionCount($where);
        return compact('list', 'count');
    }

    public function protectionInfo($id)
    {
        $info = $this->dao->get(['id' => $id]);
        if (!$info) throw new AdminException('数据不存在');
        return $info->toArray();
    }

    public function protectionForm($id)
    {
        $info = $id ? $this->dao->get($id) : [];
        $f[] = Form::input('title', '保障名称', $info['title'] ?? '')->maxlength(8)->required();
        $f[] = Form::textarea('content', '保障内容', $info['content'] ?? '')->required();
        $f[] = Form::frameImage('image', '图标', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'image')),$info['image'] ?? '')->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false]);
        $f[] = Form::number('sort', '排序', (int)($info['sort'] ?? 0))->min(0)->precision(0);
        $f[] = Form::radio('status', '是否显示', (int)($info['status'] ?? 1))->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form($id ? '编辑保障' : '添加保障', $f, Url::buildUrl('/product/protection/save/' . $id), 'POST');
    }

    public function protectionSave($id, $data)
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    public function protectionStatus($id, $status)
    {
        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    public function protectionDel($id)
    {
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }
}
