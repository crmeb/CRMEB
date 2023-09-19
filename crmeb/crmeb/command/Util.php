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
        $siteUrlJosn = str_replace('http://', 'http:\\\/\\\/', $siteUrl);
        $siteUrlJosn = str_replace('https://', 'https:\\\/\\\/', $siteUrlJosn);
        $valueJosn = str_replace('http://', 'http:\\\/\\\/', $url);
        $valueJosn = str_replace('https://', 'https:\\\/\\\/', $valueJosn);
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $sql = [
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}diy` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}'),`default_value` = replace(default_value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}luck_lottery` SET `image` = replace(image ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}luck_prize` SET `image` = replace(image ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$url}'),`big_pic` = replace(`big_pic` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_integral` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_product` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrlJosn}','{$valueJosn}'),`recommend_image` = replace(recommend_image ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_product_attr_result` SET `result` = replace(result ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_attr_value` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$url}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}user` SET `avatar` = replace(avatar ,'{$siteUrlJosn}','{$valueJosn}')",
        ];

        return Db::transaction(function () use ($sql) {
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
