<?php

namespace crmeb\command;


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
            ->addArgument('type', Argument::REQUIRED, '类型replace')
            ->addOption('h', null, Option::VALUE_REQUIRED, '替换成当前域名')
            ->addOption('u', null, Option::VALUE_REQUIRED, '替换的域名')
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
                $this->replaceSiteUrl($host, $url);
                break;
        }

        $output->info('执行成功');
    }

    protected function replaceSiteUrl(string $url, string $siteUrl)
    {
        $siteUrlJosn = str_replace('http://', 'http:\\\/\\\/', $siteUrl);
        $valueJosn = str_replace('http://', 'http:\\\/\\\/', $url);
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $sql = [
            "UPDATE `{$prefix}system_attachment` SET `att_dir` = replace(att_dir ,'{$siteUrl}','{$url}'),`satt_dir` = replace(satt_dir ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_product` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`slider_image` = replace(slider_image ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_product_attr_value` SET `image` = replace(image ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_seckill` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_combination` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_bargain` SET `image` = replace(image ,'{$siteUrl}','{$url}'),`images` = replace(images,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}system_config` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}article_category` SET `image` = replace(`image` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article` SET `image_input` = replace(`image_input` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}article_content` SET `content` = replace(`content` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}store_category` SET `pic` = replace(`pic` ,'{$siteUrl}','{$url}')",
            "UPDATE `{$prefix}system_group_data` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}eb_diy` SET `value` = replace(value ,'{$siteUrlJosn}','{$valueJosn}')",
            "UPDATE `{$prefix}store_product_description` SET `description`= replace(description,'{$siteUrl}','{$url}')"
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
