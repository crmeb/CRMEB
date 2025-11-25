<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemPem;

class SystemPemDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemPem::class;
    }

    public function savePem($data)
    {
        $info = $this->getModel()->where('name', $data['name'])->find();
        if ($info) {
            $info = $info->toArray();
            $this->getModel()->where('id', $info['id'])->update($data);
        } else {
            $data['add_time'] = time();
            $this->getModel()->save($data);
        }
        return true;
    }
}