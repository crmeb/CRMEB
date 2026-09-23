<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services\activity;

use app\services\product\product\StoreDescriptionServices;
use app\services\product\CenterProductServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 活动基类服务
 * 封装活动模块通用的数据保存逻辑，各活动模块继承此类
 * 通过模板方法模式统一 saveData 流程
 *
 * Class BaseActivityService
 * @package app\services\activity
 */
abstract class BaseActivityService extends BaseService
{
    /**
     * 获取活动类型标识
     * 子类必须实现，返回对应的活动类型字符串标识
     * 砍价=bargain, 拼团=combination, 秒杀=seckill, 积分=integral
     * @return string
     */
    abstract protected function getActivityType(): string;

    /**
     * 验证子类特有的业务规则
     * @param array $data 已处理的数据
     * @param array $detail 规格明细
     */
    abstract protected function validateSpecificData(array $data, array $detail): void;

    /**
     * 准备子类特有的数据字段
     * 用于处理当前活动类型特有的字段计算和格式转换
     * @param array $data 引用传参，可直接修改
     * @param array $detail 规格明细数组
     */
    abstract protected function prepareSpecificData(array &$data, array &$detail): void;

    /**
     * 保存活动数据（模板方法）
     *
     * 固定流程：
     * 1. 提取通用字段
     * 2. 准备通用数据
     * 3. 准备子类特有数据（钩子）
     * 4. 清理临时字段
     * 5. 获取公共服务
     * 6. 子类特有验证（钩子）
     * 7. 执行保存事务
     *
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id, array $data)
    {
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];

        // 统一处理复制逻辑：将编辑转换为新增
        if (isset($data['copy']) && $data['copy'] == 1) {
            $id = 0;
            unset($data['copy']);
        }

        // 1. 通用数据准备：时间转换、图片处理、库存价格计算、物流处理
        $this->prepareCommonData($data, $detail);

        // 2. 子类特有数据准备
        $this->prepareSpecificData($data, $detail);

        // 3. 获取所需公共服务
        $services = $this->getRequiredServices();

        // 4. 子类特有验证（在清理前执行，部分模块需访问 section_time 等临时字段）
        $this->validateSpecificData($data, $detail);

        // 5. 清理临时字段
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);

        // 6. 执行保存事务
        return $this->executeSave($id, $data, $description, $detail, $items, $services);
    }

    /**
     * 准备通用数据
     * - 时间字段转换（section_time -> start_time/stop_time）
     * - 图片数组序列化
     * - 物流方式格式化
     * - 价格、库存、限量从规格明细计算
     *
     * @param array $data
     * @param array $detail
     */
    protected function prepareCommonData(array &$data, array &$detail): void
    {
        if (isset($data['section_time'])) {
            $data['start_time'] = strtotime($data['section_time'][0]);
            $data['stop_time'] = strtotime($data['section_time'][1]);
        }

        if (isset($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }

        if (isset($data['logistics']) && is_array($data['logistics'])) {
            $data['logistics'] = implode(',', $data['logistics']);
        }

        if (!empty($detail)) {
            $data['price'] = min(array_column($detail, 'price'));
            $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
            $data['stock'] = array_sum(array_column($detail, 'stock'));
        }
    }

    /**
     * 获取所需公共服务实例
     * @return array
     */
    protected function getRequiredServices(): array
    {
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var CenterProductServices $storeProductServices */
        $storeProductServices = app()->make(CenterProductServices::class);

        return compact('storeDescriptionServices', 'storeProductServices');
    }

    /**
     * 验证商品可添加
     * 子类可重写此方法实现不同的验证规则
     * @param CenterProductServices $productServices
     * @param int $productId
     */
    protected function validateProductForAdd(CenterProductServices $productServices, int $productId): void
    {
        if (!$productServices->getOne(['is_del' => 0, 'id' => $productId])) {
            throw new AdminException('无法添加回收站商品');
        }
    }

    /**
     * 添加商品后的回调
     * 子类可重写此方法实现添加商品后的额外逻辑
     * @param CenterProductServices $productServices
     * @param int $productId
     */
    protected function afterProductAdd(CenterProductServices $productServices, int $productId): void
    {
        // 默认不做任何操作
    }

    /**
     * 校验限量是否超过商品库存
     * @param CenterProductServices $storeProductServices
     * @param array $data
     */
    protected function validateQuotaLimit(CenterProductServices $storeProductServices, array $data): void
    {
        if (isset($data['quota']) && $data['quota'] > $storeProductServices->value(['id' => $data['product_id']], 'stock')) {
            throw new AdminException('限量不能超过商品库存');
        }
    }

    /**
     * 执行保存事务
     * 活动模块仅引用中台商品（product_id），不再复制 SKU 到旧表。
     * eb_store_product_attr / eb_store_product_attr_value / eb_store_product_attr_result 三张表已弃用。
     *
     * @param int $id
     * @param array $data
     * @param string $description
     * @param array $detail
     * @param array $items
     * @param array $services
     * @return mixed
     */
    protected function executeSave(int $id, array $data, string $description, array $detail, array $items, array $services)
    {
        $storeDescriptionServices = $services['storeDescriptionServices'];
        $storeProductServices = $services['storeProductServices'];
        $activityType = $this->getActivityType();

        // 通用库存验证（基于中台商品 stock 字段）
        $this->validateQuotaLimit($storeProductServices, $data);

        return $this->transaction(function () use ($id, $data, $description, $storeDescriptionServices, $storeProductServices, $activityType) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription((int)$id, $description, $activityType);
                if (!$res) throw new AdminException('修改失败');
            } else {
                $this->validateProductForAdd($storeProductServices, (int)$data['product_id']);
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $this->afterProductAdd($storeProductServices, (int)$data['product_id']);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, $activityType);
                if (!$res) throw new AdminException('添加失败');
            }
            return $res;
        });
    }
}
