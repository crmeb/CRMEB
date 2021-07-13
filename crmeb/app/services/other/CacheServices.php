<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\other;


use app\dao\other\CacheDao;
use app\services\BaseServices;

/**
 * Class CacheServices
 * @package app\services\other
 * @method delectDeOverdueDbCache() 删除过期缓存
 */
class CacheServices extends BaseServices
{

    public function __construct(CacheDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取数据缓存
     * @param string $key
     * @param $default 默认值不存在则写入
     * @return mixed|null
     */
    public function getDbCache(string $key, $default, int $expire = 0)
    {
        $this->delectDeOverdueDbCache();
        $result = $this->dao->value(['key' => $key], 'result');
        if ($result) {
            return json_decode($result, true);
        } else {
            if ($default instanceof \Closure) {
                // 获取缓存数据
                $value = $default();
                if ($value) {
                    $this->setDbCache($key, $value, $expire);
                    return $value;
                }
            } else {
                $this->setDbCache($key, $default, $expire);
                return $default;
            }
            return null;
        }

    }

    /**
     * 设置数据缓存存在则更新，没有则写入
     * @param string $key
     * @param string | array $result
     * @param int $expire
     * @return void
     */
    public function setDbCache(string $key, $result, $expire = 0)
    {
        $this->delectDeOverdueDbCache();
        $addTime = $expire ? time() + $expire : 0;
        if ($this->dao->count(['key' => $key])) {
            return $this->dao->update($key, [
                'result' => json_encode($result),
                'expire_time' => $addTime,
                'add_time' => time()
            ], 'key');
        } else {
            return $this->dao->save([
                'key' => $key,
                'result' => json_encode($result),
                'expire_time' => $addTime,
                'add_time' => time()
            ]);
        }
    }


    /**
     * 删除某个缓存
     * @param string $key
     */
    public function delectDbCache(string $key = '')
    {
        if ($key)
            return $this->dao->delete($key, 'key');
        else
            return false;
    }

}
