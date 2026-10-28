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

namespace app\services\system;


use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use think\facade\Config;
use think\facade\Db;

/**
 * 清除数据
 * Class SystemClearServices
 * 本类为系统数据清理工具，Db::execute 用于执行 TRUNCATE/DELETE 等清理 SQL，
 * 不属于业务数据操作，保留直接调用。
 * @package app\services\system
 */
class SystemClearServices extends BaseService
{
    /**
     * 清除表数据
     * @param string|array $table_name
     * @param $status
     */
    public function clearData($table_name, bool $status)
    {
        $prefix = config('database.connections.' . config('database.default'))['prefix'];
        if (is_string($table_name)) {
            $clearData = [$table_name];
        } else {
            $clearData = $table_name;
        }
        foreach ($clearData as $name) {
            if ($status) {
                Db::execute('TRUNCATE TABLE ' . $prefix . $name);
            } else {
                Db::execute('DELETE FROM' . $prefix . $name);
            }
        }
    }

    /**
     * 递归删除文件,只能删除 public/uploads下的文件
     * @param $dirName
     * @param bool $subdir
     */
    public function delDirAndFile(string $dirName, $subdir = true)
    {
        if (strstr($dirName, 'public/uploads') === false) {
            return true;
        }
        if ($handle = @opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item", false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if (!$subdir) @rmdir($dirName);
        }
    }

    /**
     * 替换域名
     * @param string $url
     * @return mixed
     */
    public function replaceSiteUrl(string $url)
    {
        // 获取站点 URL
        $siteUrl = sys_config('site_url');
        // 解析站点 URL 的协议
        $siteUrlScheme = parse_url($siteUrl)['scheme'];
        // 将站点 URL 中的协议替换为 JSON 格式
        $siteUrlJson = str_replace($siteUrlScheme . '://', $siteUrlScheme . ':\\\/\\\/', $siteUrl);

        // 获取当前 URL 的协议
        $urlScheme = parse_url($url)['scheme'];
        // 将当前 URL 中的协议替换为 JSON 格式
        $urlJson = str_replace($urlScheme . '://', $urlScheme . ':\\\/\\\/', $url);
        // 获取数据库表前缀
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');

        // 构建 SQL 语句数组
        $sql = [
            "UPDATE `{$prefix}distribution_level` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}agreement` SET `content` = replace(content ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}cache` SET `result` = replace(result ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}delivery_service` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}division_agent_apply` SET `images` = replace(images ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}'),`default_value` = replace(default_value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrl}','{$url}'),`default_value` = replace(default_value ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_anchor` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_goods` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}live_room` SET `cover_img` = replace(`cover_img` ,'{$siteUrl}','{$url}'),`share_img` = replace(`share_img` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}luck_lottery` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`content` = replace(content ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}luck_prize` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}member_card_batch` SET `qrcode` = replace(qrcode ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}member_right` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}out_interface` SET `return_params` = replace(return_params ,'{$siteUrl}','{$url}'),`request_example` = replace(request_example ,'{$siteUrl}','{$url}'),`return_example` = replace(return_example ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}qrcode` SET `url` = replace(url ,'{$siteUrl}','{$url}'),`qrcode_url` = replace(qrcode_url ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$url}'),`big_pic` = replace(`big_pic` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_integral` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_integral_order` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_pink` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}product_center` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrlJson}','{$urlJson}'),`recommend_image` = replace(recommend_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_reply` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}'),`pics` = replace(pics,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_service` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_service_log` SET `msn` = replace(msn ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_service_record` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}'),`message` = replace(message,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_admin` SET `head_pic` = replace(head_pic ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$url}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}system_store` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`oblong_image` = replace(oblong_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_store_staff` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_user_level` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`icon` = replace(icon ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}user` SET `avatar` = replace(avatar ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_qrcode` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_user` SET `headimgurl` = replace(headimgurl ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_data` = replace(home_data ,'{$siteUrlJson}','{$urlJson}'),`detail_data` = replace(detail_data ,'{$siteUrlJson}','{$urlJson}'),`user_data` = replace(user_data ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}theme` SET `home_default_data` = replace(home_default_data ,'{$siteUrlJson}','{$urlJson}'),`detail_default_data` = replace(detail_default_data ,'{$siteUrlJson}','{$urlJson}'),`user_default_data` = replace(user_default_data ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}theme` SET `home_data` = replace(home_data ,'{$siteUrl}','{$url}'),`detail_data` = replace(detail_data ,'{$siteUrl}','{$url}'),`user_data` = replace(user_data ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_default_data` = replace(home_default_data ,'{$siteUrl}','{$url}'),`detail_default_data` = replace(detail_default_data ,'{$siteUrl}','{$url}'),`user_default_data` = replace(user_default_data ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}theme` SET `home_image` = replace(home_image ,'{$siteUrl}','{$url}'),`category_image` = replace(category_image ,'{$siteUrl}','{$url}'),`detail_image` = replace(detail_image ,'{$siteUrl}','{$url}'),`user_image` = replace(user_image ,'{$siteUrl}','{$url}'),`home_default_image` = replace(home_default_image ,'{$siteUrl}','{$url}'),`category_default_image` = replace(category_default_image ,'{$siteUrl}','{$url}'),`detail_default_image` = replace(detail_default_image ,'{$siteUrl}','{$url}'),`user_default_image` = replace(user_default_image ,'{$siteUrl}','{$url}')",
        ];

        // 执行 SQL 语句
        return $this->transaction(function () use ($sql) {
            try {
                foreach ($sql as $item) {
                    Db::execute($item);
                }
            } catch (\Throwable $e) {
                throw new AdminException('替换失败,失败原因:{:msg}', ['msg' => $e->getMessage()]);
            }
        });
    }
}
