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

namespace app\services\other;


use app\dao\other\AgreementDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\plugin\PluginManifestRepository;

/**
 * Class AgreementServices
 * @package app\services\other
 */
class AgreementServices extends BaseService
{
    /**
     * @var AgreementDao
     */
    protected $dao;

    public function __construct(AgreementDao $dao)
    {
        $this->dao = $dao;
    }

    /** 修改协议内容
     * @param array $where
     * @param $content
     * @return bool|\crmeb\basic\BaseModel
     */
    public function saveAgreement(array $data, $id = 0)
    {
        if (!$data) return false;
        if (!isset($data['type']) || !$data['type'] || $data['type'] == 0) throw new AdminException('协议类型缺失');
        if (!isset($data['title']) || !$data['title']) throw new AdminException('请填写协议名称');
        if (!isset($data['content']) || !$data['content']) throw new AdminException('请填写协议内容');
        if (!$id) {
            $getOne = $this->getAgreementBytype($data['type']);
            if ($getOne) throw new AdminException('该类型协议已经存在');
        }
        return $this->dao->saveAgreement($data, $id);
    }

    /**获取会员协议
     * @param $type
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgreementBytype($type)
    {
        if (!$type) return [];
        $data = $this->dao->getOne(['type' => $type]);
        return $data ? $data->toArray() : [];
    }

    /**
     * 协议列表（系统协议 + 已安装插件协议），供管理端协议设置页动态渲染
     * 插件协议由插件 Installer 安装时写入（带归属标识），卸载时随之清理
     * @return array
     */
    public function getAgreementList(): array
    {
        // 协议总量少（个位数），一次全量返回（含协议内容与 AI 提示词），前端切换协议无需再请求
        $list = $this->dao->selectList([], '*', 0, 0, 'sort desc, id asc');
        if (!$list) return [];
        $list = $list->toArray();

        // 插件名取自插件清单元数据：协议归属 owner_plugin_id 即清单 canonicalId，直接映射，
        // 无需查安装日志（协议数据仅在插件安装期间存在，卸载已随 cleanData 清理）
        $pluginNames = [];
        try {
            /** @var PluginManifestRepository $repository */
            $repository = app()->make(PluginManifestRepository::class);
            foreach ($repository->modules() as $manifest) {
                $pluginNames[$manifest->identity->canonicalId()] = $manifest->name;
            }
        } catch (\Throwable $e) {
            // 清单扫描失败时降级为空映射，仅影响列表插件名展示，不影响协议数据返回
        }

        foreach ($list as &$item) {
            $item['plugin_name'] = $pluginNames[$item['owner_plugin_id']] ?? '';
        }
        return $list;
    }
}
