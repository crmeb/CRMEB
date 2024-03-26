<?php

namespace app\jobs;

use app\services\system\lang\LangCodeServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use crmeb\utils\Translate;
use think\facade\Log;

class TranslateJob extends BaseJobs
{
    use QueueTrait;

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