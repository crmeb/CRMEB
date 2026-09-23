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
namespace app\jobs;

use app\services\system\lang\LangCodeServices;
use crmeb\basic\BaseJobs;
use crmeb\utils\Translate;
use think\facade\Log;

/**
 * 翻译任务
 * Class TranslateJob
 * @package app\jobs
 */
class TranslateJob extends BaseJobs
{

    public function doJob($data, $langType)
    {
        $translator = Translate::getInstance();
        $translator->setAccessKey(sys_config('hs_accesskey'));
        $translator->setSecretKey(sys_config('hs_secretkey'));
        try {
            $remarksData = [];
            foreach ($data as $value) {
                $remarksData[] = $value['remarks'];
            }
            $lang = $translator->translateText("", $langType, $remarksData);
            $num = 0;
            foreach ($data as &$value) {
                $value['lang_explain'] = $lang[$num]['Translation'];
                $num++;
            }

            app()->make(LangCodeServices::class)->saveAll($data);
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }
}