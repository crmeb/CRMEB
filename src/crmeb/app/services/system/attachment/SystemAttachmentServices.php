<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\system\attachment;

use app\services\BaseServices;
use app\dao\system\attachment\SystemAttachmentDao;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use crmeb\services\UploadService;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;

/**
 *
 * Class SystemAttachmentServices
 * @package app\services\attachment
 * @method getYesterday() 获取昨日生成数据
 * @method delYesterday() 删除昨日生成数据
 */
class SystemAttachmentServices extends BaseServices
{

    use ServicesTrait;

    /**
     * SystemAttachmentServices constructor.
     * @param SystemAttachmentDao $dao
     */
    public function __construct(SystemAttachmentDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单个资源
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(array $where, string $field = '*')
    {
        return $this->dao->getOne($where, $field);
    }

    /**
     * 获取图片列表
     * @param array $where
     * @return array
     */
    public function getImageList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $site_url = sys_config('site_url');
        foreach ($list as &$item) {
            if ($site_url) {
                $item['satt_dir'] = (strpos($item['satt_dir'], $site_url) !== false || strstr($item['satt_dir'], 'http') !== false) ? $item['satt_dir'] : $site_url . $item['satt_dir'];
                $item['att_dir'] = (strpos($item['att_dir'], $site_url) !== false || strstr($item['att_dir'], 'http') !== false) ? $item['satt_dir'] : $site_url . $item['att_dir'];
            }
        }
        $where['module_type'] = 1;
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除图片
     * @param string $ids
     */
    public function del(string $ids)
    {
        $ids = explode(',', $ids);
        if (empty($ids)) throw new AdminException('请选择要删除的图片');
        foreach ($ids as $v) {
            $attinfo = $this->dao->get((int)$v);
            if ($attinfo) {
                try {
                    $upload = UploadService::init($attinfo['image_type']);
                    if ($attinfo['image_type'] == 1) {
                        if (strpos($attinfo['att_dir'], '/') == 0) {
                            $attinfo['att_dir'] = substr($attinfo['att_dir'], 1);
                        }
                        if ($attinfo['att_dir']) $upload->delete($attinfo['att_dir']);
                    } else {
                        if ($attinfo['name']) $upload->delete($attinfo['name']);
                    }
                } catch (\Throwable $e) {
                }
                $this->dao->delete((int)$v);
            }
        }
    }

    /**
     * 图片上传
     * @param int $pid
     * @param string $file
     * @param int $upload_type
     * @param int $type
     * @return mixed
     */
    public function upload(int $pid, string $file, int $upload_type, int $type)
    {
        if ($upload_type == 0) {
            $upload_type = sys_config('upload_type', 1);
        }
        try {
            $path = make_path('attach', 2, true);
            $upload = UploadService::init($upload_type);
            $res = $upload->to($path)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                if ($fileInfo && $type == 0) {
                    $data['name'] = $fileInfo['name'];
                    $data['real_name'] = $fileInfo['real_name'];
                    $data['att_dir'] = $fileInfo['dir'];
                    $data['satt_dir'] = $fileInfo['thumb_path'];
                    $data['att_size'] = $fileInfo['size'];
                    $data['att_type'] = $fileInfo['type'];
                    $data['image_type'] = $upload_type;
                    $data['module_type'] = 1;
                    $data['time'] = $fileInfo['time'] ?? time();
                    $data['pid'] = $pid;
                    $this->dao->save($data);
                }
                return $res->filePath;
            }
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function move(array $data)
    {
        $res = $this->dao->move($data);
        if (!$res) throw new AdminException('移动失败或不能重复移动到同一分类下');
    }

    /**
     * 添加信息
     * @param array $data
     */
    public function save(array $data)
    {
        $this->dao->save($data);
    }

    /**
     * TODO 添加附件记录
     * @param $name
     * @param $att_size
     * @param $att_type
     * @param $att_dir
     * @param string $satt_dir
     * @param int $pid
     * @param int $imageType
     * @param int $time
     * @return SystemAttachment
     */
    public function attachmentAdd($name, $att_size, $att_type, $att_dir, $satt_dir = '', $pid = 0, $imageType = 1, $time = 0, $module_type = 1)
    {
        $data['name'] = $name;
        $data['att_dir'] = $att_dir;
        $data['satt_dir'] = $satt_dir;
        $data['att_size'] = $att_size;
        $data['att_type'] = $att_type;
        $data['image_type'] = $imageType;
        $data['module_type'] = $module_type;
        $data['time'] = $time ? $time : time();
        $data['pid'] = $pid;
        if (!$this->dao->save($data)) {
            throw new ValidateException('添加附件失败');
        }
        return true;
    }

    /**
     * 推广名片生成
     * @param $name
     */
    public function getLikeNameList($name)
    {
        return $this->dao->getLikeNameList(['like_name' => $name], 0, 0);
    }

    /**
     * 清除昨日海报
     * @return bool
     * @throws \Exception
     */
    public function emptyYesterdayAttachment()
    {
        try {
            $list = $this->dao->getYesterday();
            foreach ($list as $key => $item) {
                $upload = UploadService::init((int)$item['image_type']);
                if ($item['image_type'] == 1) {
                    $att_dir = $item['att_dir'];
                    if ($att_dir && strstr($att_dir, 'uploads') !== false) {
                        if (strstr($att_dir, 'http') === false)
                            $upload->delete($att_dir);
                        else {
                            $filedir = substr($att_dir, strpos($att_dir, 'uploads'));
                            if ($filedir) $upload->delete($filedir);
                        }
                    }
                } else {
                    if ($item['name']) $upload->delete($item['name']);
                }
            }
            $this->dao->delYesterday();
            return true;
        } catch (\Exception $e) {
            $this->dao->delYesterday();
            return true;
        }
    }
}
