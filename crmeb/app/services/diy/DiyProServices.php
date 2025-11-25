<?php

namespace app\services\diy;

use app\dao\diy\DiyDao;
use app\services\article\ArticleServices;
use app\services\BaseServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;

class DiyProServices extends BaseServices
{
    public function __construct(DiyDao $dao)
    {
        $this->dao = $dao;
    }

    public function getDiyProVersion(int $id)
    {
        if ($id) {
            $where = ['id' => $id];
        } else {
            $where = ['status' => 1, 'is_pro' => 1, 'is_del' => 0];
        }
        $data = $this->dao->getOne($where, 'version,is_diy');
        if (isset($data['version']) && isset($data['is_diy'])) {
            return $data;
        } else {
            return $this->dao->getOne($where, 'version,is_diy');
        }
    }

    public function getList($type = 'list')
    {
        [$page, $limit] = $this->getPageValue();
        if ($type == 'link') $limit = 1000;
        $where['is_pro'] = 1;
        $list = $this->dao->getDiyList($where, $page, $limit, ['id', 'name', 'type', 'add_time', 'update_time', 'is_diy', 'status', 'is_pro']);
        $count = $this->dao->count($where + ['is_del' => 0]);
        return compact('list', 'count');
    }

    public function getInfo($id)
    {
        if (!(int)$id) throw new AdminException('参数错误');
        $info = $this->dao->get((int)$id);
        if (!$info) throw new AdminException('模板不存在');
        $info = $info->toArray();

        $productServices = app()->make(StoreProductServices::class);
        $articleServices = app()->make(ArticleServices::class);
        $storeCategoryServices = app()->make(StoreCategoryServices::class);


        $info['value'] = json_decode($info['value'], true);
        if ($info['value']) {
            foreach ($info['value'] as &$item) {
                switch ($item['name']) {
                    case 'goodList'://商品列表

                        $typeConfig = $item['typeConfig']['activeValue'] ?? 0;
                        $where = [];
                        $num = $item['numberConfig']['val'] ?? 50;
                        $sort = $item['goodsSort']['type'] ?? 0;
                        if ($sort == 1) {
                            $where['salesOrder'] = 'desc';
                        } elseif ($sort == 2) {
                            $where['priceOrder'] = 'desc';
                        }
                        $item['goodsList']['list'] = [];
                        $where['is_show'] = 1;
                        $where['is_del'] = 0;
                        switch ($typeConfig) {
                            case 1://指定商品
                                $where['ids'] = $item['goodsList']['ids'] ?? [];
                                $item['goodsList']['list'] = $productServices->getSearchList($where, 0, 0, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
                                break;
                            case 3://指定分类
                                $cateIds = $item['classList']['classVal'] ?? [];
                                if ($cateIds) $where['cate_id'] = $cateIds;
                                $item['productList']['list'] = $productServices->getSearchList($where, 0, $num, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
                                break;
                            case 4://商品标签
                                $storeLabelIds = $item['goodsLabel']['activeValue'] ?? [];
                                if ($storeLabelIds) $where['store_label_id'] = $storeLabelIds;
                                $item['productList']['list'] = $productServices->getSearchList($where, 0, $num, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
                                break;
                        }
                        break;
                    case 'articleList'://文章

                        if ($item['selectConfig']['activeValue'] ?? 0) {
                            $data = $articleServices->getList(['cid' => $item['selectConfig']['activeValue'] ?? 0], 0, $item['numConfig']['val'] ?? 0);
                        }
                        $item['selectList']['list'] = $data['list'] ?? [];
                        break;
                    case 'promotionList'://促销列表
                        if (isset($item['tabConfig']['list']) && $item['tabConfig']['list']) {
                            $list = $item['tabConfig']['list'];
                            if ($list) {
                                foreach ($list as &$tabValue) {
                                    $where = [];
                                    //选择方式
                                    $selectValue = $tabValue['tabVal'] ?? 0;
                                    $num = $tabValue['numConfig']['val'] ?? 50;
                                    $sort = $tabValue['goodsSort'] ?? 0;
                                    if ($sort == 1) {
                                        $where['salesOrder'] = 'desc';
                                    } elseif ($sort == 2) {
                                        $where['priceOrder'] = 'desc';
                                    }
                                    if ($selectValue == 1 && isset($tabValue['goodsList']['ids']) && count($tabValue['goodsList']['ids'])) {//手动选商品
                                        $where['ids'] = $tabValue['goodsList']['ids'];
                                        $tabValue['goodsList']['list'] = $productServices->getSearchList($where, 0, 0, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
                                    } elseif ((isset($tabValue['selectConfig']['activeValue']) && $tabValue['selectConfig']['activeValue']) || (isset($tabValue['goodsLabel']['activeValue']) && $tabValue['goodsLabel']['activeValue'])) {//选分类 、标签
                                        $where['cate_id'] = $tabValue['selectConfig']['activeValue'] ?? 0;
                                        $storeLabelIds = $tabValue['goodsLabel']['activeValue'] ?? [];
                                        if ($storeLabelIds) {
                                            $where['store_label_id'] = $storeLabelIds;
                                        }
                                        $where['is_show'] = 1;
                                        $where['is_del'] = 0;
                                        $tabValue['goodsList']['list'] = $productServices->getSearchList($where, 0, $num, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
                                    }
                                }
                                $item['tabConfig']['list'] = $list;
                            }
                        }
                        break;
                }
            }
        }
        return compact('info');
    }

    public function saveInfo(int $id = 0, array $data = [])
    {
        if ($id) {
            $data['update_time'] = time();
            $res = $this->dao->update($id, $data);
            if (!$res) throw new AdminException(100007);
        } else {
            $data['add_time'] = time();
            $data['update_time'] = time();
            $data['is_diy'] = 1;
            $data['is_pro'] = 1;
            $data['type'] = 2;
            $res = $this->dao->save($data);
            if (!$res) throw new AdminException(100006);
            $id = $res->id;
        }

        return $id;
    }

    public function updateName($id, $name)
    {
        $this->dao->update($id, ['name' => $name]);
        return true;
    }

    public function exportDIYData($id)
    {
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException('数据不存在');
        return $info['value'];
    }

    public function importDIYData($content)
    {
        $data = [
            'name' => 'DIY导入数据',
            'version' => uniqid(),
            'value' => $content,
            'add_time' => time(),
            'update_time' => time(),
            'type' => 2,
            'is_show' => 1,
            'is_diy' => 1,
            'is_pro' => 1,
        ];
        $this->dao->save($data);
        return true;
    }
}
