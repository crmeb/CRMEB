<?php
declare(strict_types=1);

namespace app\dao\driver\printer;

use app\dao\driver\email\EmailDriverDao;
use app\model\driver\printer\PrinterDriver;

/**
 * 打印机驱动数据访问层，只允许管理生命周期登记的驱动。
 */
class PrinterDriverDao extends EmailDriverDao
{
    /**
     * 设置打印机驱动模型。
     * @return string
     */
    protected function setModel(): string
    {
        return PrinterDriver::class;
    }

    /**
     * 获取安装日志确认已安装的打印机驱动列表。
     * @param array $where
     * @return array
     */
    public function getInstalledList(array $where = []): array
    {
        return $this->installedQuery()->where($where)->order('printer.sort', 'desc')->order('printer.id', 'asc')->select()->toArray();
    }

    /**
     * 获取安装日志确认已安装的打印机驱动。
     * @param int $id
     * @return PrinterDriver|null
     */
    public function getInstalledDriver(int $id): ?PrinterDriver
    {
        return $this->installedQuery()->where('printer.id', $id)->find();
    }

    /**
     * 构造安装状态受限查询，不在服务层直接访问安装日志表。
     * @return \think\db\Query
     */
    protected function installedQuery()
    {
        return $this->getModel()->alias('printer')
            ->join('module_install_log install_log', 'install_log.module_key = printer.owner_plugin_id')
            ->where('printer.driver_type', 'printer')->where('printer.owner_plugin_id', '<>', '')
            ->where('install_log.module_type', 'driver')->where('install_log.status', 1)
            ->field('printer.id,printer.driver_type,printer.driver_key,printer.driver_name,printer.class_name,printer.namespace,printer.owner_plugin_id,printer.icon,printer.description,printer.is_builtin,printer.config_fields,printer.capabilities,printer.help_doc,printer.sort,printer.status,printer.add_time,printer.update_time');
    }
}
