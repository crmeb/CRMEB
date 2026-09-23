<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\migration;

use Phinx\Migration\AbstractMigration;
use think\migration\db\Table;

class Migrator extends AbstractMigration
{
    /**
     * @param string $tableName
     * @param array $options
     * @return Table
     */
    public function table($tableName, $options = []): \Phinx\Db\Table
    {
        return new Table($tableName, $options, $this->getAdapter());
    }
}
