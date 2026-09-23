<?php


namespace Alipay\EasySDK\Kernel\Util;


use AlibabaCloud\Tea\Model;

class JsonUtil
{
    public function toJsonString(array $input)
    {
        $result = [];
        foreach ($input as $k => $v) {
            if ($v instanceof Model) {
                $result[$k] = $this->getTeaModelMap($v);
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    private function getTeaModelMap(Model $teaModel)
    {
        $result = [];
        foreach ($teaModel as $k => $v) {
            if ($v instanceof Model) {
                $k = $this->toUnderScore($k);
                $result[$k] = $this->getTeaModelMap($v);
            } else {
                if (empty($result)) {
                    $k = $this->toUnderScore($k);
                    $result[$k] = $v;
                } else {
                    $k = $this->toUnderScore($k);
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 驼峰命名转下划线命名
     * @param $str
     * @return string
     */
    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $str);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }
}