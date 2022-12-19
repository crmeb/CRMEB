<?php


namespace app\services\wechat;


use app\dao\wechat\WechatQrcodeCateDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class WechatQrcodeCateServices
 * @package app\services\wechat
 * @method getCateList() 分类列表
 */
class WechatQrcodeCateServices extends BaseServices
{
    /**
     * WechatQrcodeCateServices constructor.
     * @param WechatQrcodeCateDao $dao
     */
    public function __construct(WechatQrcodeCateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加编辑分类表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createForm($id = 0)
    {
        $info = $this->dao->get($id);
        $f[] = Form::hidden('id', $id);
        $f[] = Form::input('cate_name', '分类名称', $info['cate_name'] ?? '')->maxlength(30)->required();
        return create_form('添加分类', $f, Url::buildUrl('/app/wechat_qrcode/cate/save'), 'POST');
    }

    /**
     * 保存数据
     * @param $data
     * @return bool
     */
    public function saveData($data)
    {
        $id = $data['id'];
        $data['add_time'] = time();
        if ($id) {
            unset($data['id']);
            $res = $this->dao->update($id, $data);
        } else {
            $res = $this->dao->save($data);
        }
        if (!$res) throw new AdminException(100006);
        return true;
    }

    /**
     * 删除分类
     * @param int $id
     * @return bool
     */
    public function delCate($id = 0)
    {
        if (!$id) throw new AdminException(100100);
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException(100008);
        return true;
    }

}
