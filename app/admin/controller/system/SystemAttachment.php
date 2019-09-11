<?php

namespace app\admin\controller\system;

use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use app\admin\controller\AuthController;
use crmeb\services\SystemConfigService;
use crmeb\services\UploadService as Upload;
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
        $res = Upload::image('upfile','editor/'.date('Ymd'));
        if(is_array($res)){
            SystemAttachmentModel::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],0,$res['image_type'],$res['time']);
            $info["originalName"] = $res['name'];
            $info["name"] = $res['name'];
            $info["url"] = $res['dir'];
            $info["size"] = $res['size'];
            $info["type"] = $res['type'];
            $info["state"] = "SUCCESS";
            if($res['image_type'] == 1) $info['url'] =  SystemConfigService::get('site_url').str_replace('\\','/',$res['dir']);
        }else
            $info = array(
                "msg" => $res,
                "state" => "ERROR"
            );
        echo json_encode($info);
    }
}
