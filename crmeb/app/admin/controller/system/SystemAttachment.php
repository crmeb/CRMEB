<?php

namespace app\admin\controller\system;

use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use app\admin\controller\AuthController;
use crmeb\services\upload\Upload;

/**
 * 附件管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemAttachment extends AuthController
{
    /**
     * TODO 编辑器上传图片
     */
    public function upload()
    {
        $uploadType = (int)sys_config('upload_type', 1);
        $upload = new Upload($uploadType, [
            'accessKey' => sys_config('accessKey'),
            'secretKey' => sys_config('secretKey'),
            'uploadUrl' => sys_config('uploadUrl'),
            'storageName' => sys_config('storage_name'),
            'storageRegion' => sys_config('storage_region'),
        ]);
        $resInfo = $upload->to('editor/' . date('Ymd'))->validate()->move('upfile');
        if ($resInfo === false) {
            echo json_encode([
                'msg' => $upload->getError(),
                'state' => 'ERROR'
            ]);
        } else {
            $res = $upload->getUploadInfo();
            $res['image_type'] = $uploadType;
            SystemAttachmentModel::attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 0, $res['image_type'], $res['time']);
            $info["originalName"] = $res['name'];
            $info["name"] = $res['name'];
            $info["url"] = $res['dir'];
            $info["size"] = $res['size'];
            $info["type"] = $res['type'];
            $info["state"] = "SUCCESS";
            if ($res['image_type'] == 1) $info['url'] = sys_config('site_url') . str_replace('\\', '/', $res['dir']);
            echo json_encode($info);
        }
    }
}
