<?php

declare(strict_types=1);

namespace app\dao\driver\bot;

use app\model\driver\bot\BotDriver;
use crmeb\basic\BaseDao;

class BotDriverDao extends BaseDao
{
    protected function setModel(): string
    {
        return BotDriver::class;
    }

    public function getAllEnabled()
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->select();
    }

    public function getByDriverKey(string $driverKey): ?BotDriver
    {
        return $this->getModel()->where('driver_key', $driverKey)->find();
    }

    public function getActiveDriver(): ?BotDriver
    {
        return $this->getModel()->where('status', 1)->order('sort', 'desc')->find();
    }

    public function disableAllExcept(int $id): int
    {
        return $this->getModel()->where('id', '<>', $id)->update(['status' => 0]);
    }
}
