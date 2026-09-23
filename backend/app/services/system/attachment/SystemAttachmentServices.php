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
declare (strict_types=1);

namespace app\services\system\attachment;

use crmeb\basic\BaseService;
use app\dao\system\attachment\SystemAttachmentDao;
use app\services\product\product\CopyProductServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\UploadException;
use app\services\driver\upload\UploadService;
use crmeb\services\CacheService;
use think\facade\Config;

/**
 *
 * Class SystemAttachmentServices
 * @package app\services\attachment
 * @method getYesterday() 获取昨日生成数据
 * @method delYesterday() 删除昨日生成数据
 */
class SystemAttachmentServices extends BaseService
{
    /** 扫码上传能力的有效期，单位秒 */
    private const SCAN_UPLOAD_TTL = 600;

    /** 扫码能力摘要缓存前缀 */
    private const SCAN_UPLOAD_TOKEN_KEY = 'attachment:scan_upload:token:';

    /** 管理员当前扫码能力缓存前缀 */
    private const SCAN_UPLOAD_ADMIN_KEY = 'attachment:scan_upload:admin:';

    /**
     * @var SystemAttachmentDao
     */
    protected $dao;

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
        [$page, $limit, $defaultLimit] = $this->getPageValue();
        $page = max(1, $page);
        $limit = $limit > 0 ? $limit : max(1, $defaultLimit);
        $limit = min($limit, max(1, (int)Config::get('database.page.limitMax', 50)));
        $pid = $where['pid'] ?? 0;
        // 管理端分类树用空字符串表示“全部”，列表查询统一按不限制分类的 0 处理。
        $where['pid'] = $this->parseCategoryId($pid === '' ? 0 : $pid);
        $where['type'] = $this->parseCategoryId($where['type'] ?? 0);
        if (!in_array($where['type'], [0, 1], true) || !is_string($where['real_name'] ?? '')) {
            throw new AdminException('附件查询参数错误');
        }
        $where['module_type'] = 1;
        $list = $this->dao->getList($where, $page, $limit);
        $siteUrl = rtrim((string)sys_config('site_url'), '/');
        foreach ($list as &$item) {
            foreach (['att_dir', 'satt_dir'] as $field) {
                $path = (string)($item[$field] ?? '');
                if ($siteUrl !== '' && $path !== '' && !preg_match('~^(?:https?:)?//~i', $path)) {
                    $path = $siteUrl . '/' . ltrim($path, '/');
                }
                $item[$field] = $path;
            }
            $item['time'] = date('Y-m-d H:i:s', (int)$item['time']);
        }
        unset($item);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除图片
     * @param string $ids
     * @return void
     * @throws \Throwable
     */
    public function del(string $ids)
    {
        $ids = $this->parseAttachmentIds($ids);
        $attachments = $this->getManagedAttachments($ids, true);
        // 先确认整批素材归属，文件删除成功后才移除对应记录，失败项可安全重试。
        foreach ($attachments as $attachment) {
            $this->deleteAttachmentFile($attachment);
            if ($this->dao->delete(['att_id' => (int)$attachment['att_id'], 'module_type' => 1]) === false) {
                throw new \Exception(getLang('删除附件记录失败'));
            }
        }
    }

    /**
     * 图片上传
     * @param int $pid
     * @param string $file
     * @param int $upload_type
     * @param int $type
     * @param string $menuName
     * @param string $uploadToken 扫码上传能力摘要，普通上传为空
     * @return mixed
     */
    public function upload(int $pid, string $file, int $upload_type, int $type, $menuName, $uploadToken = '')
    {
        $this->checkCategory($this->parseCategoryId($pid), $type);
        $realName = false;
        if ($menuName == 'weixin_ckeck_file' || $menuName == 'ico_path') {
            $upload_type = 1;
            $realName = true;
        }

        if ($upload_type == 0) {
            // 从 eb_driver 表获取当前启用的驱动（status=1），不再使用旧的 sys_config('upload_type')
            /** @var \app\services\driver\upload\UploadDriverServices $driverServices */
            $driverServices = app()->make(\app\services\driver\upload\UploadDriverServices::class);
            $enabledDrivers = $driverServices->getEnabledDrivers();
            $driverKey = !empty($enabledDrivers) ? $enabledDrivers[0]['driver_key'] : 'local';
            $upload = UploadService::init($driverKey);
            $upload_type = $driverServices->getTypeByDriverKey($driverKey);
        } else {
            $upload = UploadService::init($upload_type);
        }
        try {
            $path = make_path('attach', 2, true);
            if ($path === '') {
                throw new AdminException('无法创建文件夹，请检查您的上传目录权限');
            }
            $res = $upload->to($path)->validate()->move($file, $realName);
            if ($res === false) {
                throw new UploadException($upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                $fileType = strtolower(pathinfo($fileInfo['name'] ?? '', PATHINFO_EXTENSION));
                if ($fileInfo && $type == 0 && in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'])) {
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
                    $data['scan_token'] = $uploadToken;
                    if (!$this->dao->save($data)) {
                        throw new UploadException('保存附件记录失败');
                    }
                }
                return $res->filePath;
            }
        } catch (UploadException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage(), [], 0, $e);
        }
    }

    /**
     * 为当前管理员创建绑定图片分类的临时扫码能力
     * @param int|string $adminId
     * @param int|string $pid
     * @return array
     */
    public function scanUploadQrcode($adminId, $pid = 0): array
    {
        $adminId = $this->parseScanUploadAdminId($adminId);
        $pid = $this->parseCategoryId($pid);
        $this->checkCategory($pid, 0);
        return CacheService::lock('attachment:scan_upload:lock:' . $adminId, function () use ($adminId, $pid): array {
            $this->clearScanUploadSession($adminId);
            $token = bin2hex(random_bytes(32));
            $digest = hash('sha256', $token);
            $tokenKey = self::SCAN_UPLOAD_TOKEN_KEY . $digest;
            $adminKey = self::SCAN_UPLOAD_ADMIN_KEY . $adminId;
            $session = ['token_hash' => $digest, 'admin_id' => $adminId, 'pid' => $pid, 'expires_at' => time() + self::SCAN_UPLOAD_TTL];
            try {
                // 两个索引均写入并读回确认后，才向调用方签发扫码链接。
                if (CacheService::set($tokenKey, $session, self::SCAN_UPLOAD_TTL) !== true
                    || CacheService::set($adminKey, $digest, self::SCAN_UPLOAD_TTL) !== true
                    || $this->getScanUploadSession($token, $adminId) !== $session) {
                    throw new \Exception(getLang('创建扫码上传链接失败'));
                }
            } catch (\Throwable $e) {
                CacheService::delete($adminKey);
                CacheService::delete($tokenKey);
                throw new \Exception(getLang('创建扫码上传链接失败'), 0, $e);
            }
            $url = rtrim((string)sys_config('site_url'), '/') . '/app/upload?' . http_build_query(['pid' => $pid, 'token' => $token]);
            return ['url' => $url];
        });
    }

    /**
     * 校验扫码能力和绑定分类后，执行公共图片上传
     * @param int|string $pid
     * @param string $file
     * @param mixed $uploadToken
     * @param int $uploadType
     * @param int $type
     * @return mixed
     */
    public function scanUpload($pid, $file, $uploadToken, int $uploadType = 0, int $type = 0)
    {
        $session = $this->getScanUploadSession($uploadToken);
        $pid = $this->parseCategoryId($pid);
        if ($session['pid'] !== $pid || $type !== 0) {
            throw new AdminException('配置已更改或token已失效');
        }
        if (!is_string($file) || $file === '') {
            throw new AdminException('附件上传参数错误');
        }
        return $this->upload($pid, $file, $uploadType, 0, '', $session['token_hash']);
    }

    /**
     * 获取当前管理员拥有的有效扫码能力对应图片
     * @param mixed $scanToken
     * @param int|string $adminId
     * @return array
     */
    public function scanUploadImage($scanToken, $adminId): array
    {
        $session = $this->getScanUploadSession($scanToken, $this->parseScanUploadAdminId($adminId));
        return $this->dao->scanUploadImage($session['token_hash']);
    }

    /**
     * 删除当前管理员的扫码能力，不接受调用方指定其他能力
     * @param int|string $adminId
     * @return void
     */
    public function removeUploadQrcode($adminId): void
    {
        $adminId = $this->parseScanUploadAdminId($adminId);
        CacheService::lock('attachment:scan_upload:lock:' . $adminId, function () use ($adminId): void {
            $this->clearScanUploadSession($adminId);
        });
    }

    /**
     * 校验管理员身份，不允许缺失或宽松数值转换
     * @param int|string $adminId
     * @return int
     */
    protected function parseScanUploadAdminId($adminId): int
    {
        $adminId = $this->parseCategoryId($adminId);
        if ($adminId === 0) {
            throw new AdminException('管理员身份无效');
        }
        return $adminId;
    }

    /**
     * 校验扫码能力或摘要的严格编码
     * @param mixed $value
     * @return bool
     */
    protected function isScanUploadHash($value): bool
    {
        return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) === 1;
    }

    /**
     * 校验完整扫码缓存和管理员当前索引，缓存缺失或损坏均拒绝
     * @param mixed $token
     * @param int|null $adminId 管理端轮询时必须提供当前身份
     * @return array
     */
    protected function getScanUploadSession($token, ?int $adminId = null): array
    {
        if (!$this->isScanUploadHash($token)) {
            throw new AdminException('配置已更改或token已失效');
        }
        $digest = hash('sha256', $token);
        $session = CacheService::get(self::SCAN_UPLOAD_TOKEN_KEY . $digest, null);
        $now = time();
        if (!is_array($session) || count($session) !== 4
            || !$this->isScanUploadHash($session['token_hash'] ?? null)
            || !hash_equals($session['token_hash'], $digest)
            || !is_int($session['admin_id'] ?? null) || $session['admin_id'] < 1
            || !is_int($session['pid'] ?? null) || $session['pid'] < 0
            || !is_int($session['expires_at'] ?? null) || $session['expires_at'] <= $now
            || $session['expires_at'] > $now + self::SCAN_UPLOAD_TTL
            || ($adminId !== null && $session['admin_id'] !== $adminId)) {
            throw new AdminException('配置已更改或token已失效');
        }
        $currentDigest = CacheService::get(self::SCAN_UPLOAD_ADMIN_KEY . $session['admin_id'], null);
        if (!$this->isScanUploadHash($currentDigest) || !hash_equals($currentDigest, $digest)) {
            throw new AdminException('配置已更改或token已失效');
        }
        return $session;
    }

    /**
     * 撤销自己的当前索引，只删除归属验证通过的能力缓存
     * @param int $adminId
     * @return void
     */
    protected function clearScanUploadSession(int $adminId): void
    {
        $adminKey = self::SCAN_UPLOAD_ADMIN_KEY . $adminId;
        $digest = CacheService::get($adminKey, null);
        if ($digest === null) {
            return;
        }
        $tokenKey = null;
        if ($this->isScanUploadHash($digest)) {
            $session = CacheService::get(self::SCAN_UPLOAD_TOKEN_KEY . $digest, null);
            if (is_array($session) && ($session['admin_id'] ?? null) === $adminId
                && $this->isScanUploadHash($session['token_hash'] ?? null)
                && hash_equals($session['token_hash'], $digest)) {
                $tokenKey = self::SCAN_UPLOAD_TOKEN_KEY . $digest;
            }
        }
        // 先撤销当前索引，即使后续过期记录清理失败也无法继续使用扫码能力。
        if (CacheService::delete($adminKey) === false && CacheService::get($adminKey, null) !== null) {
            throw new \Exception(getLang('删除扫码上传链接失败'));
        }
        if ($tokenKey !== null && CacheService::delete($tokenKey) === false && CacheService::get($tokenKey, null) !== null) {
            throw new \Exception(getLang('删除扫码上传链接失败'));
        }
    }

    /**
     * 移动素材分类
     * @param array $data
     * @return mixed
     */
    public function move(array $data)
    {
        $ids = $this->parseAttachmentIds($data['images'] ?? '');
        $pid = $this->parseCategoryId($data['pid'] ?? 0);
        $attachments = $this->getManagedAttachments($ids);
        $types = array_unique(array_map(static fn(array $attachment): int => (int)$attachment['type'], $attachments));
        foreach ($types as $type) {
            $this->checkCategory($pid, $type);
        }
        $result = $this->dao->move(['images' => $ids, 'pid' => $pid]);
        if ($result === false) {
            throw new \Exception(getLang('移动附件失败'));
        }
        return $result;
    }

    /**
     * 修改素材显示名称，不修改存储对象的实际键
     * @param int|string $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        $ids = $this->parseAttachmentIds([$id]);
        $this->getManagedAttachments($ids);
        $realName = $data['real_name'] ?? '';
        if (!is_string($realName) || trim($realName) === '' || mb_strlen($realName) > 255) {
            throw new AdminException('文件名称不能为空且不能超过255个字符');
        }
        $result = $this->dao->update(['att_id' => $ids[0], 'module_type' => 1], ['real_name' => trim($realName)]);
        if (!$result) {
            throw new \Exception(getLang('修改附件名称失败'));
        }
        return $result;
    }

    /**
     * 校验素材ID并去重
     * @param string|array $ids
     * @return int[]
     */
    protected function parseAttachmentIds($ids): array
    {
        $values = is_string($ids) ? explode(',', $ids) : $ids;
        if (!is_array($values) || !$values || count($values) > 1000) {
            throw new AdminException('请选择有效的附件');
        }
        $result = [];
        foreach ($values as $value) {
            if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-9][0-9]*$/D', (string)$value)) {
                throw new AdminException('附件ID错误');
            }
            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                throw new AdminException('附件ID错误');
            }
            $result[$id] = $id;
        }
        return array_values($result);
    }

    /**
     * 校验非负分类ID
     * @param int|string $id
     * @return int
     */
    protected function parseCategoryId($id): int
    {
        if ((!is_int($id) && !is_string($id)) || !preg_match('/^(?:0|[1-9][0-9]*)$/D', (string)$id)) {
            throw new AdminException('附件分类参数错误');
        }
        $value = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($value === false) {
            throw new AdminException('附件分类参数错误');
        }
        return $value;
    }

    /**
     * 批量查询可由素材管理操作的附件
     * @param int[] $ids
     * @param bool $allowMissing 删除时允许已经不存在的记录
     * @return array
     */
    protected function getManagedAttachments(array $ids, bool $allowMissing = false): array
    {
        $attachments = $this->dao->selectList([
            ['att_id', 'in', $ids],
        ], 'att_id,name,att_dir,image_type,module_type,type')->toArray();
        foreach ($attachments as $attachment) {
            if ((int)$attachment['module_type'] !== 1) {
                throw new AdminException('附件不存在或不可操作');
            }
        }
        if (!$allowMissing && count($attachments) !== count($ids)) {
            throw new AdminException('附件不存在或不可操作');
        }
        return $attachments;
    }

    /**
     * 确认分类存在且与素材类型一致，零表示未分类
     * @param int $pid
     * @param int $type
     * @return void
     */
    protected function checkCategory(int $pid, int $type): void
    {
        if ($pid === 0) {
            return;
        }
        $category = app()->make(SystemAttachmentCategoryServices::class)->get($pid, ['id', 'type']);
        if (!$category || (int)$category['type'] !== $type) {
            throw new AdminException('附件分类不存在或类型不匹配');
        }
    }

    /**
     * 删除附件对应的真实文件，不猜测当前启用的云空间
     * @param array $attachment
     * @return void
     * @throws \Throwable
     */
    protected function deleteAttachmentFile(array $attachment): void
    {
        $isLocal = (int)$attachment['image_type'] === 1;
        $key = (string)($isLocal ? $attachment['att_dir'] : $attachment['name']);
        if ($key === '' || (!$isLocal && empty($attachment['att_dir']))) {
            throw new UploadException('附件文件路径为空，无法安全删除');
        }
        $upload = $isLocal ? UploadService::init('local') : UploadService::getOssInit((string)$attachment['att_dir']);
        if ($upload->delete($key) === false) {
            throw new UploadException($upload->getError() ?: '删除附件文件失败');
        }
    }

    /**
     * 添加信息
     * @param array $data
     * @return void
     */
    public function save(array $data)
    {
        $data['real_name'] = $data['real_name'] ?? basename((string)($data['name'] ?? ''));
        if (!$this->dao->save($data)) {
            throw new UploadException('保存附件记录失败');
        }
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
    public function attachmentAdd($name, $att_size, $att_type, $att_dir, $satt_dir = '', $pid = 0, $imageType = 1, $time = 0, $module_type = 1, $type = 0, $real_name = '')
    {
        $data['name'] = $name;
        $data['att_dir'] = $att_dir;
        $data['satt_dir'] = $satt_dir;
        $data['att_size'] = $att_size;
        $data['att_type'] = $att_type;
        $data['image_type'] = $imageType;
        $data['module_type'] = $module_type;
        $data['time'] = $time ?: time();
        $data['pid'] = $pid;
        $data['type'] = $type;
        $data['real_name'] = $real_name !== '' ? $real_name : basename((string)$name);
        if (!$this->dao->save($data)) {
            throw new ApiException('添加失败');
        }
        return true;
    }

    /**
     * 按逻辑名称前缀获取用户生成海报，非分页最多1000条
     * @param string $name
     * @return array
     */
    public function getLikeNameList($name)
    {
        return $this->dao->getLikeNameList(['like_real_name' => $name], 0, 0);
    }

    /**
     * 清除昨日海报
     * @return bool
     * @throws \Exception
     */
    public function emptyYesterdayAttachment(): bool
    {
        $list = $this->dao->getYesterday();
        foreach ($list as $item) {
            $attachment = is_array($item) ? $item : $item->toArray();
            $this->deleteAttachmentFile($attachment);
            if ($this->dao->delete(['att_id' => (int)$attachment['att_id'], 'module_type' => 2]) === false) {
                throw new \Exception(getLang('删除附件记录失败'));
            }
        }
        return true;
    }

    /**
     * 分批清理全部用户生成的临时附件，保留未成功删除的记录
     * @return bool
     * @throws \Throwable
     */
    public function clearTemporaryAttachments(): bool
    {
        $afterId = 0;
        do {
            $attachments = $this->dao->selectList([
                ['module_type', '=', 2], ['att_id', '>', $afterId],
            ], 'att_id,name,att_dir,image_type,module_type', 1, 100, 'att_id ASC')->toArray();
            foreach ($attachments as $attachment) {
                $this->deleteAttachmentFile($attachment);
                if ($this->dao->delete(['att_id' => (int)$attachment['att_id'], 'module_type' => 2]) === false) {
                    throw new \Exception(getLang('删除附件记录失败'));
                }
                $afterId = (int)$attachment['att_id'];
            }
        } while (count($attachments) === 100);
        return true;
    }

    /**
     * 视频分片上传
     * @param $data
     * @param $file
     * @return mixed
     */
    public function videoUpload($data, $file)
    {
        $originalName = $data['filename'] ?? '';
        if (!is_string($originalName) || $originalName === '' || strlen($originalName) > 255
            || preg_match('~[/\\\\\x00-\x1f\x7f]~', $originalName)) {
            throw new AdminException('视频文件名称错误');
        }
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['avi', 'mp4', 'wmv', 'rm', 'mpg', 'mpeg', 'mov', 'flv'], true)) {
            throw new AdminException('格式错误');
        }
        $md5 = $data['md5'] ?? '';
        if (!is_string($md5) || !preg_match('/^[a-f0-9]{32}$/iD', $md5)) {
            throw new AdminException('视频文件校验参数错误');
        }
        $md5 = strtolower($md5);
        $chunkNumber = $this->parseCategoryId($data['chunkNumber'] ?? 0);
        $totalChunks = $this->parseCategoryId($data['totalChunks'] ?? 0);
        $chunkSize = $this->parseCategoryId($data['chunkSize'] ?? 0);
        $currentChunkSize = $this->parseCategoryId($data['currentChunkSize'] ?? 0);
        $maxSize = max(1, (int)Config::get('upload.filesize', 52428800));
        if ($chunkNumber < 1 || $totalChunks < 1 || $chunkNumber > $totalChunks || $totalChunks > 10000
            || $chunkSize < 1 || $chunkSize > $maxSize || $currentChunkSize < 1 || $currentChunkSize > $chunkSize
            || ($totalChunks - 1) > intdiv($maxSize - 1, $chunkSize)
            || ($chunkNumber < $totalChunks && $currentChunkSize !== $chunkSize)) {
            throw new AdminException('视频分片参数错误或文件超过大小限制');
        }
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK
            || empty($file['tmp_name']) || filesize($file['tmp_name']) !== $currentChunkSize) {
            throw new UploadException('视频分片上传失败或大小不匹配');
        }
        $public_dir = app()->getRootPath() . 'public';
        $dir = '/uploads/attach/' . date('Y/m/d');
        $all_dir = $public_dir . $dir;
        if (!is_dir($all_dir) && !mkdir($all_dir, 0755, true) && !is_dir($all_dir)) {
            throw new \Exception(getLang('无法创建视频上传目录'));
        }
        // 使用内容摘要隔离同名视频分片，最终对象名不受用户原始名称影响。
        $filename = $all_dir . '/' . $md5 . '.' . $extension;
        $chunkPrefix = $filename . '__';
        if (!move_uploaded_file($file['tmp_name'], $chunkPrefix . $chunkNumber)) {
            throw new UploadException('保存视频分片失败');
        }
        $res = ['code' => 1, 'msg' => 'waiting', 'file_path' => ''];
        if ($chunkNumber === $totalChunks) {
            $blob = '';
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkFile = $chunkPrefix . $i;
                if (!file_exists($chunkFile)) {
                    throw new UploadException('视频分片不完整，请重新上传');
                }
                $size = filesize($chunkFile);
                if ($size === false || $size < 1 || $size > $chunkSize
                    || ($i < $totalChunks && $size !== $chunkSize) || $size > $maxSize - strlen($blob)) {
                    throw new UploadException('视频分片大小错误或文件超过大小限制');
                }
                $chunk = file_get_contents($chunkFile);
                if ($chunk === false || strlen($chunk) !== $size) {
                    throw new UploadException('读取视频分片失败');
                }
                $blob .= $chunk;
            }
            if (md5($blob) !== $md5) {
                throw new UploadException('视频文件校验失败，请重新上传');
            }
            $pendingFile = $filename . '.part';
            if (file_put_contents($pendingFile, $blob, LOCK_EX) !== strlen($blob)) {
                throw new UploadException('合并视频分片失败');
            }
            $mime = mime_content_type($pendingFile);
            if (!in_array($mime, ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/avi', 'video/x-ms-wmv',
                'video/mpeg', 'video/x-flv', 'application/vnd.rn-realmedia', 'application/vnd.rn-realmedia-vbr'], true)) {
                if (!unlink($pendingFile)) {
                    throw new \Exception(getLang('清理非法视频文件失败'));
                }
                throw new UploadException('视频文件内容格式错误');
            }
            if (!rename($pendingFile, $filename)) {
                throw new UploadException('保存视频文件失败');
            }
            for ($i = 1; $i <= $totalChunks; $i++) {
                if (!unlink($chunkPrefix . $i)) {
                    throw new \Exception(getLang('清理视频分片失败'));
                }
            }
            $res = ['code' => 2, 'msg' => 'success', 'file_path' => rtrim((string)sys_config('site_url'), '/') . $dir . '/' . $md5 . '.' . $extension];
        }
        return $res;
    }

    /**
     * 保存SDK或本地分片上传的视频，名称与类型取自实际存储路径
     * @param array $data
     * @return bool
     */
    public function videoDataSave(array $data): bool
    {
        $pid = $this->parseCategoryId($data['pid'] ?? 0);
        $this->checkCategory($pid, 1);
        $realName = $data['video_name'] ?? '';
        $path = $data['video_path'] ?? '';
        if (!is_string($realName) || trim($realName) === '' || mb_strlen($realName) > 255
            || !is_string($path) || $path === '') {
            throw new AdminException('视频名称或路径错误');
        }
        $upload = UploadService::getOssInit($path);
        $storage = $upload->getStorageInfo();
        $driverKey = $storage['driver_key'] ?? '';
        $urlPath = parse_url($path, PHP_URL_PATH);
        if (!is_string($urlPath) || $urlPath === '') {
            throw new AdminException('视频文件路径错误');
        }
        $key = $driverKey === 'local' ? basename(rawurldecode($urlPath)) : $this->getStorageObjectKey($path, $storage);
        $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $mimeTypes = ['mp4' => 'video/mp4', 'avi' => 'video/x-msvideo', 'wmv' => 'video/x-ms-wmv',
            'rm' => 'application/vnd.rn-realmedia', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
            'mov' => 'video/quicktime', 'flv' => 'video/x-flv'];
        if (!isset($mimeTypes[$extension])) {
            throw new AdminException('视频文件格式错误');
        }
        $imageType = $driverKey === 'local' ? 1 : app()->make(\app\services\driver\upload\UploadDriverServices::class)->getTypeByDriverKey($driverKey);
        $this->save([
            'name' => $key, 'real_name' => trim($realName), 'att_dir' => $path, 'satt_dir' => $path,
            'att_size' => 0, 'att_type' => $mimeTypes[$extension], 'pid' => $pid, 'image_type' => $imageType,
            'module_type' => 1, 'type' => 1, 'time' => time(),
        ]);
        return true;
    }

    /**
     * 从已匹配存储域名或CDN前缀中提取真实云对象键
     * @param string $path
     * @param array $storage
     * @return string
     */
    protected function getStorageObjectKey(string $path, array $storage): string
    {
        $url = parse_url(str_starts_with($path, '//') ? 'https:' . $path : $path);
        $matchedKey = null;
        $matchedPrefixLength = -1;
        foreach (['domain', 'cdn'] as $field) {
            $base = parse_url((string)($storage[$field] ?? ''));
            if (!$url || !$base || empty($url['host']) || empty($base['host'])
                || strtolower($url['host']) !== strtolower($base['host'])
                || strtolower($url['scheme'] ?? '') !== strtolower($base['scheme'] ?? '')
                || ($url['port'] ?? (strtolower($url['scheme'] ?? '') === 'https' ? 443 : 80))
                    !== ($base['port'] ?? (strtolower($base['scheme'] ?? '') === 'https' ? 443 : 80))) {
                continue;
            }
            $urlPath = rawurldecode($url['path'] ?? '');
            $prefix = rtrim(rawurldecode($base['path'] ?? ''), '/');
            if ($prefix !== '' && !str_starts_with($urlPath, $prefix . '/')) {
                continue;
            }
            $key = ltrim(substr($urlPath, strlen($prefix)), '/');
            if ($key !== '' && !preg_match('~[\\\\\x00-\x1f\x7f]~', $key)
                && !array_intersect(['.', '..', ''], explode('/', $key))
                && strlen($prefix) > $matchedPrefixLength) {
                $matchedKey = $key;
                $matchedPrefixLength = strlen($prefix);
            }
        }
        if ($matchedKey !== null) {
            return $matchedKey;
        }
        throw new UploadException('无法从原存储空间安全识别视频对象');
    }

    /**
     * 网络图片上传
     * @param $data
     * @return bool
     * @throws \Exception
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/06/13
     */
    public function onlineUpload($data)
    {
        $pid = $this->parseCategoryId($data['pid'] ?? 0);
        $this->checkCategory($pid, 0);
        $images = $data['images'] ?? [];
        if (!is_array($images) || !$images || count($images) > 100) {
            throw new AdminException('请选择有效的网络图片');
        }
        foreach ($images as $image) {
            if (!is_string($image) || $image === '') {
                throw new AdminException('网络图片地址错误');
            }
        }
        //生成附件目录
        if (make_path('attach', 3, true) === '') {
            throw new AdminException('无法创建文件夹，请检查您的上传目录权限');
        }

        //上传图片
        $siteUrl = rtrim((string)sys_config('site_url'), '/');

        foreach ($images as $image) {
            $uploadValue = app()->make(CopyProductServices::class)->downloadImage($image);
            if (!is_array($uploadValue) || empty($uploadValue['path'])) {
                throw new UploadException('网络图片上传失败');
            }
            $imagePath = $uploadValue['path'];
            if ((int)$uploadValue['image_type'] === 1 && !preg_match('~^(?:https?:)?//~i', $imagePath)) {
                $imagePath = $siteUrl . '/' . ltrim($imagePath, '/');
            }
            if (!$uploadValue['is_exists']) {
                $this->save([
                    'name' => $uploadValue['name'],
                    'real_name' => $uploadValue['real_name'] ?? basename($uploadValue['name']),
                    'att_dir' => $imagePath,
                    'satt_dir' => $imagePath,
                    'att_size' => $uploadValue['size'],
                    'att_type' => $uploadValue['mime'],
                    'image_type' => $uploadValue['image_type'],
                    'module_type' => 1,
                    'time' => time(),
                    'pid' => $pid
                ]);
            }
        }
        return true;
    }
}
