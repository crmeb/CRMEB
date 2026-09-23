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
namespace app\services\diy;

use app\dao\diy\ThemeDownloadDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 主题下载记录服务类
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2026/3/10
 */
class ThemeDownloadServices extends BaseService
{
    /**
     * @var ThemeDownloadDao
     */
    protected $dao;

    /**
     * 构造方法
     * @param ThemeDownloadDao $dao
     */
    public function __construct(ThemeDownloadDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取下载记录列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function getDownloadList(array $where, int $page = 0, int $limit = 0): array
    {
        $list = $this->dao->themeDownloadList($where, '*', $page, $limit);
        $count = $this->dao->themeDownloadCount($where);
        return compact('list', 'count');
    }

    /**
     * 获取下载记录详情
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function getDownloadInfo(int $id): array
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('下载记录不存在');
        }
        return $info->toArray();
    }

    /**
     * 新增下载记录
     * @param int $tid 主题ID
     * @param string $title 主题名称
     * @param string $downloadUrl 下载地址
     * @return int 新记录ID
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function addDownloadRecord(int $tid, string $title, string $downloadUrl): int
    {
        return $this->dao->insertGetId([
            'tid'           => $tid,
            'title'         => $title,
            'download_time' => time(),
            'download_url'  => $downloadUrl,
        ]);
    }

    /**
     * 删除下载记录
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function deleteDownloadRecord(int $id): bool
    {
        if (!$this->dao->get($id)) {
            throw new AdminException('下载记录不存在');
        }
        return (bool)$this->dao->delete($id);
    }

    /**
     * 更新下载地址
     * @param int $id 记录ID
     * @param string $downloadUrl 下载地址
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function updateDownloadUrl(int $id, string $downloadUrl): bool
    {
        return (bool)$this->dao->update($id, ['download_url' => $downloadUrl]);
    }
}
