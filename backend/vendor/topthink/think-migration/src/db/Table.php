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

namespace think\migration\db;

use Phinx\Db\Table\Index;

class Table extends \Phinx\Db\Table
{

    protected function setOption($name, $value)
    {
        $options = $this->getOptions();

        $options[$name] = $value;

        $this->table->setOptions($options);

        return $this;
    }

    /**
     * 设置id
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setOption('id', $id);
    }

    /**
     * 设置主键
     * @param $key
     * @return $this
     */
    public function setPrimaryKey($key)
    {
        return $this->setOption('primary_key', $key);
    }

    /**
     * 设置引擎
     * @param $engine
     * @return $this
     */
    public function setEngine($engine)
    {
        return $this->setOption('engine', $engine);
    }

    /**
     * 设置表注释
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        return $this->setOption('comment', $comment);
    }

    /**
     * 设置排序比对方法
     * @param $collation
     * @return $this
     */
    public function setCollation($collation)
    {
        return $this->setOption('collation', $collation);
    }

    public function addSoftDelete()
    {
        $this->addColumn(Column::timestamp('delete_time')->setNullable());
        return $this;
    }

    public function addMorphs($name, $indexName = null)
    {
        $this->addColumn(Column::unsignedInteger("{$name}_id"));
        $this->addColumn(Column::string("{$name}_type"));
        $this->addIndex(["{$name}_id", "{$name}_type"], ['name' => $indexName]);
        return $this;
    }

    public function addNullableMorphs($name, $indexName = null)
    {
        $this->addColumn(Column::unsignedInteger("{$name}_id")->setNullable());
        $this->addColumn(Column::string("{$name}_type")->setNullable());
        $this->addIndex(["{$name}_id", "{$name}_type"], ['name' => $indexName]);
        return $this;
    }

    /**
     * @param string $createdAt
     * @param string $updatedAt
     * @return $this
     */
    public function addTimestamps($createdAt = 'create_time', $updatedAt = 'update_time', bool $withTimezone = false)
    {
        if ($createdAt) {
            $this->addColumn($createdAt, 'timestamp', [
                'null'     => false,
                'default'  => 'CURRENT_TIMESTAMP',
                'update'   => '',
                'timezone' => $withTimezone,
            ]);
        }
        if ($updatedAt) {
            $this->addColumn($updatedAt, 'timestamp', [
                'null'     => true,
                'default'  => null,
                'update'   => '',
                'timezone' => $withTimezone,
            ]);
        }

        return $this;
    }

    /**
     * @param \Phinx\Db\Table\Column|string $columnName
     * @param null $type
     * @param array $options
     * @return $this
     */
    public function addColumn($columnName, $type = null, $options = [])
    {
        if ($columnName instanceof Column && $columnName->getUnique()) {
            $index = new Index();
            $index->setColumns([$columnName->getName()]);
            $index->setType(Index::UNIQUE);
            $this->addIndex($index);
        }
        return parent::addColumn($columnName, $type, $options);
    }

    /**
     * @param string $columnName
     * @param null $newColumnType
     * @param array $options
     * @return $this
     */
    public function changeColumn($columnName, $newColumnType = null, $options = [])
    {
        if ($columnName instanceof \Phinx\Db\Table\Column) {
            return parent::changeColumn($columnName->getName(), $columnName, $options);
        }
        return parent::changeColumn($columnName, $newColumnType, $options);
    }
}
