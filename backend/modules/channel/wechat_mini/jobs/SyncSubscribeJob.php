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

namespace modules\channel\wechat_mini\jobs;

use app\dao\message\NoticeTemplateDao;
use crmeb\basic\BaseJobs;
use crmeb\services\CacheService;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use think\facade\Log;

/**
 * 同步小程序订阅消息 Job
 */
class SyncSubscribeJob extends BaseJobs
{

    /**
     * 从模板内容提取微信模板关键字中文名称。
     *
     * 兼容“名称与占位符同一行”和“名称、占位符分行”两种格式，
     * 只为实际占位符生成关键字，避免空行被当成变量。
     *
     * @param array|string $content 模板内容
     * @return array<string>
     */
    protected function parseKeywordNames($content): array
    {
        // 数据库中的模板内容可能保存了字面量转义换行，先统一成真实换行再解析。
        $lines = [];
        foreach (is_array($content) ? $content : [$content] as $rawLine) {
            $normalized = str_replace(['\\r\\n', '\\n', '\\r'], "\n", (string)$rawLine);
            $normalized = preg_replace('/\\\\+[\r\n]+/', "\n", $normalized ?: '');
            $lines = array_merge($lines, preg_split('/\r\n|\n|\r/', $normalized ?: '') ?: []);
        }
        $names = [];
        $pendingLabel = '';

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            preg_match_all('/\{\{[^{}\r\n]+\.DATA\}\}/i', $line, $matches, PREG_OFFSET_CAPTURE);
            if (empty($matches[0])) {
                $pendingLabel = $line;
                continue;
            }

            $label = trim(substr($line, 0, (int)$matches[0][0][1]));
            $label = $label !== '' ? $label : $pendingLabel;
            if ($label !== '') {
                foreach ($matches[0] as $_match) {
                    $names[] = $label;
                }
            }
            $pendingLabel = '';
        }

        return $names;
    }

    /**
     * 同步小程序订阅消息
     * @param string $mark
     * @param array $data 模板数据
     * @param $tempkey
     * @return bool
     */
    public function sync(string $mark, array $data): bool
    {
        try {
            Log::error('订阅消息开始:'.$mark);
            $tempkey = $data['tempkey'];
            $works = MiniProgramService::getSubscribeTemplateKeyWords($tempkey);
            $kid = [];
            if ($works) {
                $works = array_combine(array_column($works, 'name'), $works);
                $content = is_array($data['routine_content']) ? $data['routine_content'] : explode("\n", $data['routine_content']);
                $content = $this->parseKeywordNames($content);
                foreach ($content as $name) {
                    if ($name && isset($works[$name])) {
                        $kid[] = $works[$name]['kid'];
                    }
                }
            }

            Log::error('订阅消息：' . json_encode([
                    'mark' => $mark,
                    'works' => $works,
                    'content' => $content ?? '',
                    'data' => $data,
                    'tempkey' => $tempkey,
                    'kid' => $kid,
                ], JSON_UNESCAPED_UNICODE));

            if ($kid) {


                $tempid = MiniProgramService::addSubscribeTemplate($tempkey, $kid, $data['name']);

                Log::error('订阅消息结束：' . json_encode([
                        'tempid' => $tempid,
                    ], JSON_UNESCAPED_UNICODE));

                if ($tempid) {
                    // 写入 eb_notice_template.extra
                    app()->make(NoticeTemplateDao::class)->updateExtraByMarkAndChannel($mark, 'is_routine', [
                        'tempid' => $tempid,
                        'kid' => $kid,
                    ]);

                    CacheService::delete('TEMP_IDS_LIST');
                }
            }
        } catch (\Throwable $e) {
            Log::error('同步订阅消息失败!：' . $e->getMessage() . '|' . $e->getFile());
        }
        return true;
    }
}
