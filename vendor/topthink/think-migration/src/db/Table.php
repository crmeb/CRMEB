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
    /**
     * 设置id
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->options['id'] = $id;
        return $this;
    }

    /**
     * 设置主键
     * @param $key
     * @return $this
     */
    public function setPrimaryKey($key)
    {
        $this->options['primary_key'] = $key;
        return $this;
    }

    /**
     * 设置引擎
     * @param $engine
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->options['engine'] = $engine;
        return $this;
    }

    /**
     * 设置表注释
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * 设置排序比对方法
     * @param $collation
     * @return $this
     */
    public function setCollation($collation)
    {
        $this->options['collation'] = $collation;
        return $this;
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
     * @param string $createdAtColumnName
     * @param string $updatedAtColumnName
     * @return \Phinx\Db\Table|Table
     */
    public function addTimestamps($createdAtColumnName = 'create_time', $updatedAtColumnName = 'update_time')
    {
        return parent::addTimestamps($createdAtColumnName, $updatedAtColumnName);
    }

    /**
     * @param \Phinx\Db\Table\Column|string $columnName
     * @param null                          $type
     * @param array                         $options
     * @return \Phinx\Db\Table|Table
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
     * @param null   $newColumnType
     * @param array  $options
     * @return \Phinx\Db\Table|Table
     */
    public function changeColumn($columnName, $newColumnType = null, $options = [])
    {
        if ($columnName instanceof \Phinx\Db\Table\Column) {
            return parent::changeColumn($columnName->getName(), $columnName, $options);
        }
        return parent::changeColumn($columnName, $newColumnType, $options);
    }
}
