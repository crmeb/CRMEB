<?php

namespace app\admin\controller\widget;

use app\admin\controller\AuthController;
use think\facade\Route as Url;
use app\admin\model\system\{
    SystemAttachment as SystemAttachmentModel, SystemAttachmentCategory as Category
};
use crmeb\services\{JsonService as Json, JsonService, UtilService as Util, FormBuilder as Form};
use crmeb\services\upload\Upload;

/**
 * TODO 附件控制器
 * Class Images
 * @package app\admin\controller\widget
 */
class Images extends AuthController
{
    /**
     * 附件列表
     * @return \think\response\Json
     */
    public function index()
    {
        $pid = request()->param('pid');
        if ($pid === NULL) {
            $pid = session('pid') ? session('pid') : 0;
        }
        session('pid', $pid);
        $this->assign('pid', $pid);
////       //TODO 分类标题
//       $typearray = Category::getAll();
//       $this->assign(compact('typearray'));
//       $this->assign(SystemAttachmentModel::getAll($pid));
        return $this->fetch('widget/images');
    }

    /**获取图片列表
     *
     */
    public function get_image_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 18],
            ['pid', 0]
        ]);
        return Json::successful(SystemAttachmentModel::getImageList($where));
    }

    /**获取分类
     * @param string $name
     */
    public function get_image_cate($name = '')
    {
        return Json::successful(Category::getAll($name));
    }

    /**
     * 图片管理上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $pid = $this->request->param('pid', session('pid'));
        $upload_type = $this->request->get('upload_type', sys_config('upload_type', 1));
        try {
            $path = make_path('attach', 2, true);
            $upload = new Upload((int)$upload_type, [
                'accessKey' => sys_config('accessKey'),
                'secretKey' => sys_config('secretKey'),
                'uploadUrl' => sys_config('uploadUrl'),
                'storageName' => sys_config('storage_name'),
                'storageRegion' => sys_config('storage_region'),
            ]);
            $res = $upload->to($path)->validate()->move();
            if ($res === false) {
                return JsonService::fail('上传失败：' . $upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                if ($fileInfo) {
                    SystemAttachmentModel::attachmentAdd($fileInfo['name'], $fileInfo['size'], $fileInfo['type'], $fileInfo['dir'], $fileInfo['thumb_path'], $pid, $upload_type, $fileInfo['time']);
                }
                return JsonService::successful('上传成功', ['src' => $res->filePath]);
            }
        } catch (\Exception $e) {
            return JsonService::fail('上传失败：' . $e->getMessage());
        }
    }

    /**
     * ajax 提交删除
     */
    public function delete()
    {
        $request = app('request');
        $post = $request->post();
        if (empty($post['imageid']))
            Json::fail('还没选择要删除的图片呢？');
        foreach ($post['imageid'] as $v) {
            if ($v) self::deleteimganddata($v);
        }
        Json::successful('删除成功');
    }

    /**删除图片和数据记录
     * @param $att_id
     */
    public function deleteimganddata($att_id)
    {

        $attinfo = SystemAttachmentModel::get($att_id);
        if ($attinfo) {
            try {
                $upload = new Upload((int)$attinfo['image_type'], [
                    'accessKey' => sys_config('accessKey'),
                    'secretKey' => sys_config('secretKey'),
                    'uploadUrl' => sys_config('uploadUrl'),
                    'storageName' => sys_config('storage_name'),
                    'storageRegion' => sys_config('storage_region'),
                ]);
                if ($attinfo['image_type'] == 1) {
                    if (strpos($attinfo['att_dir'], '/') == 0) {
                        $attinfo['att_dir'] = substr($attinfo['att_dir'], 1);
                    }
                    $upload->delete($attinfo['att_dir']);
                } else {
                    $upload->delete($attinfo['name']);
                }
            } catch (\Throwable $e) {
            }
            SystemAttachmentModel::where('att_id', $att_id)->delete();
        }
    }

    /**
     * 移动图片分类显示
     */
    public function moveimg($imgaes)
    {

        $formbuider = [];
        $formbuider[] = Form::hidden('imgaes', $imgaes);
        $formbuider[] = Form::select('pid', '选择分类')->setOptions(function () {
            $list = Category::getCateList();
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $form = Form::make_post_form('编辑分类', $formbuider, Url::buildUrl('moveImgCecate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 移动图片分类操作
     */
    public function moveImgCecate()
    {
        $data = Util::postMore([
            'pid',
            'imgaes'
        ]);
        if ($data['imgaes'] == '') return Json::fail('请选择图片');
        if (!$data['pid']) return Json::fail('请选择分类');
        $res = SystemAttachmentModel::where('att_id', 'in', $data['imgaes'])->update(['pid' => $data['pid']]);
        if ($res)
            Json::successful('移动成功');
        else
            Json::fail('移动失败！');
    }

    /**
     * ajax 添加分类
     */
    public function addcate($id = 0)
    {
        $formbuider = [];
        $formbuider[] = Form::select('pid', '上级分类', (string)$id)->setOptions(function () {
            $list = Category::getCateList(0,1);
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name', '分类名称');
        $jsContent = <<<SCRIPT
parent.SuccessCateg();
parent.layer.close(parent.layer.getFrameIndex(window.name));
SCRIPT;
        $form = Form::make_post_form('添加分类', $formbuider, Url::buildUrl('saveCate'), $jsContent);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加分类
     */
    public function saveCate()
    {
        $request = app('request');
        $post = $request->post();
        $data['pid'] = $post['pid'];
        $data['name'] = $post['name'];
        if (empty($post['name']))
            Json::fail('分类名称不能为空！');
        $res = Category::create($data);
        if ($res)
            Json::successful('添加成功');
        else
            Json::fail('添加失败！');

    }

    /**
     * 编辑分类
     */
    public function editcate($id)
    {
        $Category = Category::get($id);
        if (!$Category) return Json::fail('数据不存在!');
        $formbuider = [];
        $formbuider[] = Form::hidden('id', $id);
        $formbuider[] = Form::select('pid', '上级分类', (string)$Category->getData('pid'))->setOptions(function () use ($id) {
            $list = Category::getCateList();
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name', '分类名称', $Category->getData('name'));
        $jsContent = <<<SCRIPT
parent.SuccessCateg();
parent.layer.close(parent.layer.getFrameIndex(window.name));
SCRIPT;
        $form = Form::make_post_form('编辑分类', $formbuider, Url::buildUrl('updateCate'), $jsContent);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 更新分类
     * @param $id
     */
    public function updateCate($id)
    {
        $data = Util::postMore([
            'pid',
            'name'
        ]);
        if ($data['pid'] == '') return Json::fail('请选择父类');
        if (!$data['name']) return Json::fail('请输入分类名称');
        Category::edit($data, $id);
        return Json::successful('分类编辑成功!');
    }

    /**
     * 删除分类
     */
    public function deletecate($id)
    {
        $chdcount = Category::where('pid', $id)->count();
        if ($chdcount) return Json::fail('有子栏目不能删除');
        $chdcount = SystemAttachmentModel::where('pid', $id)->count();
        if ($chdcount) return Json::fail('栏目内有图片不能删除');
        if (Category::del($id))
            return Json::successful('删除成功!');
        else
            return Json::fail('删除失败');


    }
}
