<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\system\log;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class SystemFileMd5 extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'system_file_md5';
}
