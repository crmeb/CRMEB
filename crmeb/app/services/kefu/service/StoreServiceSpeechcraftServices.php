<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\kefu\service;


use app\dao\service\StoreServiceSpeechcraftDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder;
use think\Model;

/**
 * 话术
 * Class StoreServiceSpeechcraftServices
 * @package app\services\kefu\service
 * @method array|Model|null get($id, ?array $field = [], ?array $with = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null) 更新数据
 */
class StoreServiceSpeechcraftServices extends BaseServices
{

    /**
     * StoreServiceSpeechcraftServices constructor.
     * @param StoreServiceSpeechcraftDao $dao
     */
    public function __construct(StoreServiceSpeechcraftDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpeechcraftList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getSpeechcraftList($where, $page, $limit);
        foreach ($list as &$item) {
            if (!$item['cate_name']) $item['cate_name'] = '系统默认';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 创建form表单
     * @return mixed
     */
    public function createForm()
    {
        return create_form('添加话术', $this->speechcraftForm(), $this->url('/app/wechat/speechcraft'), 'POST');
    }

    /**
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateForm(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(400461);
        }
        return create_form('编辑话术', $this->speechcraftForm($info->toArray()), $this->url('/app/wechat/speechcraft/' . $id), 'PUT');
    }

    /**
     * @param array $infoData
     * @return mixed
     */
    protected function speechcraftForm(array $infoData = [])
    {
        /** @var StoreServiceSpeechcraftCateServices $services */
        $services = app()->make(StoreServiceSpeechcraftCateServices::class);
        $cateList = $services->getCateList(['owner_id' => 0, 'type' => 1]);
        $data = [];
        $data[] = ['value' => 0, 'label' => '默认分类'];
        foreach ($cateList['data'] as $item) {
            $data[] = ['value' => $item['id'], 'label' => $item['name']];
        }
        $form[] = FormBuilder::select('cate_id', '话术分类', $infoData['cate_id'] ?? '')->setOptions($data);
        $form[] = FormBuilder::textarea('title', '话术标题', $infoData['title'] ?? '')->required();
        $form[] = FormBuilder::textarea('message', '话术内容', $infoData['message'] ?? '')->required();
        $form[] = FormBuilder::number('sort', '排序', (int)($infoData['sort'] ?? 0));
        return $form;
    }
}
