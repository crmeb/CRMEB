<?php

namespace crmeb\command;


use app\services\system\log\SystemFileInfoServices;
use app\services\system\SystemRouteServices;
use crmeb\exceptions\AdminException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Db;

class Util extends Command
{
    protected function configure()
    {
        $this->setName('util')
            ->addArgument('type', Argument::REQUIRED, '类型replace/route/file/apifox')
            ->addOption('h', null, Option::VALUE_REQUIRED, '替换成当前域名')
            ->addOption('u', null, Option::VALUE_REQUIRED, '替换的域名')
            ->addOption('a', null, Option::VALUE_REQUIRED, '应用名')
            ->addOption('f', null, Option::VALUE_REQUIRED, '导入文件路径，文件只能在项目根目录下或者根目录下的其他文件夹内')
            ->setDescription('工具类');
    }

    protected function execute(Input $input, Output $output)
    {
        $type = $input->getArgument('type');

        switch ($type) {
            case 'replace':
                $host = $input->getOption('h');
                $url = $input->getOption('u');
                if (!$host) {
                    return $output->error('缺少替换域名');
                }
                if (!$url) {
                    return $output->error('缺少替换的域名');
                }
                $this->replaceSiteUrl($url, $host);
                break;
            case 'route':
                $appName = $input->getOption('a');
                if (!$appName) {
                    return $output->error('缺少应用名称');
                }
                app()->make(SystemRouteServices::class)->syncRoute($appName);
                break;
            case 'file':
                app()->make(SystemFileInfoServices::class)->syncfile();
                break;
            case 'apifox':
                $filePath = $input->getOption('f');
                if (!$filePath) {
                    return $output->error('缺少导入文件地址');
                }
                app()->make(SystemRouteServices::class)->import($filePath);
                break;
        }

        $output->info('执行成功');
    }

    protected function replaceSiteUrl(string $url, string $siteUrl)
    {
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
            "UPDATE `{$prefix}agent_level` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}agreement` SET `content` = replace(content ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}cache` SET `result` = replace(result ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}delivery_service` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}division_agent_apply` SET `images` = replace(images ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrlJson}','{$urlJson}'),`default_value` = replace(default_value ,'{$siteUrlJson}','{$urlJson}')",
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
            "UPDATE `{$prefix}store_order_cart_info` SET `cart_info` = replace(cart_info ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_order_refund` SET `refund_img` = replace(refund_img ,'{$siteUrl}','{$url}'),`cart_info` = replace(cart_info,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_pink` SET `avatar` = replace(`avatar` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrlJson}','{$urlJson}'),`recommend_image` = replace(recommend_image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_attr_result` SET `result` = replace(result ,'{$siteUrlJson}','{$urlJson}')",
            "UPDATE `{$prefix}store_product_attr_value` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
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
            "UPDATE `{$prefix}user_extract` SET `qrcode_url` = replace(qrcode_url ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_qrcode` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}wechat_user` SET `headimgurl` = replace(headimgurl ,'{$siteUrl}','{$url}')",
        ];

        // 执行 SQL 语句
        return $this->transaction(function () use ($sql) {
            try {
                foreach ($sql as $item) {
                    Db::execute($item);
                }
            } catch (\Throwable $e) {
                throw new AdminException(400612, ['msg' => $e->getMessage()]);
            }
        });
    }
}
