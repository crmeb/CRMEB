<?php

namespace app\services\system\lang;

use app\dao\system\lang\LangCodeDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\utils\Translate;

class LangCodeServices extends BaseServices
{
    /**
     * @param LangCodeDao $dao
     */
    public function __construct(LangCodeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 语言列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langCodeList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, '*', $page, $limit, 'id desc', true)->toArray();
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $typeList = $langTypeServices->getColumn([['status', '=', 1], ['is_del', '=', 0]], 'language_name,file_name,id', 'id');
        $langType = [
            'isAdmin' => [
                ['title' => '用户端页面', 'value' => 0],
                ['title' => '后端接口', 'value' => 1]
            ]
        ];
        foreach ($typeList as $value) {
            $langType['langType'][] = ['title' => $value['language_name'] . '(' . $value['file_name'] . ')', 'value' => $value['id']];
        }
        foreach ($list as &$item) {
            $item['language_name'] = $typeList[$item['type_id']]['language_name'] . '(' . $typeList[$item['type_id']]['file_name'] . ')';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count', 'langType');
    }

    /**
     * 语言详情
     * @param $code
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function langCodeInfo($code)
    {
        if (!$code) throw new AdminException(100026);
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $typeList = $langTypeServices->getColumn([['status', '=', 1], ['is_del', '=', 0]], 'language_name,file_name,id', 'id');
        $list = $this->dao->selectList([['code', '=', $code], ['type_id', 'in', array_column($typeList, 'id')]])->toArray();
        foreach ($list as &$item) {
            $item['language_name'] = $typeList[$item['type_id']]['language_name'] . '(' . $typeList[$item['type_id']]['file_name'] . ')';
        }
        $remarks = $list[0]['remarks'];
        return compact('list', 'code', 'remarks');
    }

    /**
     * 保存修改语言
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function langCodeSave($data)
    {
        if ($data['edit'] == 0) {
            if ($data['is_admin'] == 1) {
                $code = $this->dao->getMax(['is_admin' => 1], 'code');
                if ($code < 500000) {
                    $code = 500000;
                } else {
                    $code = $code + 1;
                }
            } else {
                $code = $data['remarks'];
            }
        } else {
            $code = $data['code'];
        }
        $saveData = [];
        foreach ($data['list'] as $key => $item) {
            $saveData[$key] = [
                'code' => $code,
                'remarks' => $data['remarks'],
                'lang_explain' => $item['lang_explain'],
                'type_id' => $item['type_id'],
                'is_admin' => $data['is_admin'],
            ];
            if (isset($item['id']) && $item['id'] != 0) {
                $saveData[$key]['id'] = $item['id'];
            }
        }
        $this->dao->saveAll($saveData);
        $this->clearLangCache();
        return true;
    }

    /**
     * 删除语言
     * @param $id
     * @return bool
     */
    public function langCodeDel($id)
    {
        $code = $this->dao->value(['id' => $id], 'code');
        $res = $this->dao->delete(['code' => $code]);
        $this->clearLangCache();
        if ($res) return true;
        throw new AdminException(100008);
    }

    /**
     * 清除语言缓存
     * @return bool
     */
    public function clearLangCache()
    {
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $typeList = $langTypeServices->getColumn(['status' => 1, 'is_del' => 0], 'file_name');
        foreach ($typeList as $value) {
            $langStr = 'api_lang_' . str_replace('-', '_', $value);
            CacheService::delete($langStr);
        }
        return true;
    }

    /**
     * 机器翻译
     * @param string $text
     * @return array
     * @throws \Throwable
     */
    public function langCodeTranslate(string $text = ''): array
    {
        if (sys_config('hs_accesskey') == '' || sys_config('hs_secretkey') == '') {
            throw new AdminException('请先配置火山翻译key');
        }
        $translator = Translate::getInstance();
        $translator->setAccessKey(sys_config('hs_accesskey'));
        $translator->setSecretKey(sys_config('hs_secretkey'));
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $typeList = $langTypeServices->getColumn([['status', '=', 1], ['is_del', '=', 0]], 'language_name,file_name,id', 'id');
        $data = [];
        foreach ($typeList as $item) {
            if ($item['file_name'] == 'zh-Hant') {
                $lang = 'zh-Hant';
            } else {
                $lang = substr($item['file_name'], 0, 2);
            }
            $data[$item['id']] = $translator->translateText("", $lang, array($text))[0]['Translation'];
        }
        return $data;
    }
}