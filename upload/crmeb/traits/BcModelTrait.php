<?php


namespace crmeb\traits;


trait BcModelTrait
{
    public static function bcInc($key, $incField, $inc, $keyField = null, $acc = 2)
    {
        if (!is_numeric($inc)) return false;
        $model = new self();
        if ($keyField === null) $keyField = $model->getPk();
        $result = self::where($keyField, $key)->field([$keyField, $incField])->find();
        if (!$result) return false;
        $new = bcadd($result[$incField], $inc, $acc);
        return false !== $model->where($keyField, $key)->update([$incField => $new]);
    }

    public static function bcDec($key, $decField, $dec, $keyField = null, $minus = false, $acc = 2)
    {
        if (!is_numeric($dec)) return false;
        $model = new self();
        if ($keyField === null) $keyField = $model->getPk();
        $result = self::where($keyField, $key)->field([$keyField, $decField])->find();
        if (!$result) return false;
        $new = bcsub($result[$decField], $dec, $acc);
        if (!$minus && $new < 0) return false;
        return false !== $model->where($keyField, $key)->update([$decField => $new]);
    }
}