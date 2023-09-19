<?php

namespace app\services\system\lang;

use app\dao\system\lang\LangTypeDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use FormBuilder\Exception\FormBuilderException;
use think\facade\Route as Url;

class LangTypeServices extends BaseServices
{
    /**
     * @param LangTypeDao $dao
     */
    public function __construct(LangTypeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取语言类型列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langTypeList(array $where = []): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 添加语言类型表单
     * @param int $id
     * @return array
     * @throws FormBuilderException
     */
    public function langTypeForm(int $id = 0): array
    {
        if ($id) $info = $this->dao->get($id);
        $field = [];
        $field[] = Form::input('language_name', '语言名称', $info['language_name'] ?? '')->required('请填写语言名称');
        $langCountryList = app()->make(LangCountryServices::class)->selectList([])->toArray();
        $options = [];
        foreach ($langCountryList as $item) {
            $options[] = ['value' => $item['code'], 'label' => $item['name'] . ' [ ' . $item['code'] . ' ]'];
        }
        $field[] = Form::select('file_name', '语言标识', $info['file_name'] ?? '')->setOptions(Form::setOptions($options))->filterable(1);
        $field[] = Form::radio('is_default', '是否默认', $info['is_default'] ?? 0)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $field[] = Form::radio('status', '状态', $info['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form($id ? '修改语言类型' : '新增语言类型', $field, Url::buildUrl('/setting/lang_type/save/' . $id), 'POST');
    }

    /**
     * 保存语言类型
     * @param array $data
     * @return bool
     */
    public function langTypeSave(array $data)
    {
        if ($data['id']) {
            $this->dao->update($data['id'], $data);
            $id = $data['id'];
        } else {
            unset($data['id']);
            $res = $this->dao->save($data);
            if ($res) {
                //同步语言
                /** @var LangCodeServices $codeServices */
                $codeServices = app()->make(LangCodeServices::class);
                $list = $codeServices->selectList(['type_id' => 1])->toArray();
                foreach ($list as $key => $item) {
                    unset($list[$key]['id']);
                    $list[$key]['type_id'] = $res->id;
                }
                $codeServices->saveAll($list);
            } else {
                throw new AdminException(100006);
            }
            $id = $res->id;
        }
        //设置默认
        if ($data['is_default'] == 1) $this->dao->update([['id', '<>', $id]], ['is_default' => 0]);
        $this->setDefaultLangName();
        return true;
    }

    /**
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/10
     */
    public function setDefaultLangName()
    {
        $fileName = $this->dao->value(['is_default' => 1], 'file_name');
        CacheService::set('range_name', $fileName);
        CacheService::clear();
    }

    /**
     * 修改语言类型状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function langTypeStatus($id, $status)
    {
        $res = $this->dao->update(['id' => $id], ['status' => $status]);
        if (!$res) throw new AdminException(100015);
        $this->setDefaultLangName();
        return true;
    }

    /**
     * 删除语言类型
     * @param int $id
     * @return bool
     */
    public function langTypeDel(int $id = 0)
    {
        $this->dao->update(['id' => $id], ['is_del' => 1]);
        /** @var LangCountryServices $countryServices */
        $countryServices = app()->make(LangCountryServices::class);
        $countryServices->update(['type_id' => $id], ['type_id' => 0]);
        /** @var LangCodeServices $codeServices */
        $codeServices = app()->make(LangCodeServices::class);
        $codeServices->delete(['type_id' => $id]);
        $this->setDefaultLangName();
        return true;
    }
}
