<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\crud;

/**
 * Class Validate
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/3/29
 * @package crmeb\services\crud
 */
class Validate extends Make
{
    /**
     * @var string
     */
    protected $name = 'validate';

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/4
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'validate' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Validate
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/23
     */
    public function handle(string $name, array $options = [])
    {
        $this->value['MODEl_NAME'] = $options['modelName'] ?? $name;

        $this->setRuleContent($options['field']);

        return parent::handle($name, $options);
    }

    /**
     * 设置规则内容
     * @param array $field
     * @return Validate
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/23
     */
    protected function setRuleContent(array $field)
    {
        $content = [];
        $message = [];
        foreach ($field as $item) {
            $item['name'] = addslashes($item['name']);
            if ($item['required']) {
                $content[] = $this->tab(2) . '\'' . $item['field'] . '\'=> \'require\',';
                $message[] = $this->tab(2) . '\'' . $item['field'] . '.require\'=> \'' . $item['name'] . '必须填写\',';
            }
        }

        $this->value['RULE_PHP'] = implode("\n", $content);
        $this->value['MESSAGE_PHP'] = implode("\n", $message);
        return $this;
    }

    /**
     * 模板文件配置
     * @param string $type
     * @return mixed
     */
    protected function getStub(string $type = '')
    {
        return __DIR__ . DS . 'stubs' . DS . 'validate' . DS . 'crudValidate.stub';
    }
}
