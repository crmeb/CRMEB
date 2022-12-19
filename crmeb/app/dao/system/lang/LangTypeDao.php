<?php

namespace app\dao\system\lang;

use app\dao\BaseDao;
use app\model\system\lang\LangType;

class LangTypeDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return LangType::class;
    }
}