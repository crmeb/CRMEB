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
namespace app\services\diy;

use app\dao\diy\ThemeDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 * 主题服务类
 *
 * 功能概述:
 * 负责系统主题的管理，包括主题的增删改查、导入导出、应用切换等功能。
 * 提供了对首页、分类页、详情页、个人中心等页面数据的独立管理和组合使用能力。
 *
 * 主要功能:
 * 1. 主题管理 - 主题列表查询、详情获取、创建与编辑
 * 2. 主题应用 - 切换当前使用的主题，或单独应用某个主题的特定页面数据
 * 3. 数据导入 - 支持导入外部主题配置数据
 * 4. 资源管理 - 管理主题相关的图片、标题等资源
 * 5. 版本控制 - 记录主题数据的更新时间和版本信息
 *
 * @package app\services\diy
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2025/12/18
 */
class ThemeServices extends BaseService
{
    /**
     * @var ThemeDao
     */
    protected $dao;

    /**
     * 构造函数 - 初始化依赖
     *
     * 注入 ThemeDao 依赖，用于数据库操作。
     *
     * @param ThemeDao $dao 主题数据访问对象
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function __construct(ThemeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取主题列表
     *
     * 功能概述:
     * 根据传入的查询条件，分页获取主题列表数据，并对返回的数据进行格式化处理。
     * 处理内容包括：时间戳转日期字符串、图片路径转完整URL、JSON数据解析等。
     *
     * @param array $where 查询条件数组
     * @return array 包含列表数据 list 和总数 count 的数组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function getThemeList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $field = 'id,title,info,type,home_image,category_image,detail_image,user_image,theme_data,add_time,up_time,is_use,page_type';
        $order = 'id desc';
        if (($where['page_type'] ?? '') === 'all') {
            unset($where['page_type']);
        } else {
            $where['page_type'] = 'theme';
        }
        $list = $this->dao->themeList($where, $field, $page, $limit, $order);
        foreach ($list as &$item) {
            if (isset($item['add_time'])) $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
            if (isset($item['up_time'])) $item['up_time'] = date('Y-m-d H:i', $item['up_time']);
            if (isset($item['home_data_update_time'])) $item['home_data_update_time'] = date('Y-m-d H:i', $item['home_data_update_time']);
            if (isset($item['category_data_update_time'])) $item['category_data_update_time'] = date('Y-m-d H:i', $item['category_data_update_time']);
            if (isset($item['detail_data_update_time'])) $item['detail_data_update_time'] = date('Y-m-d H:i', $item['detail_data_update_time']);
            if (isset($item['user_data_update_time'])) $item['user_data_update_time'] = date('Y-m-d H:i', $item['user_data_update_time']);
            if (isset($item['theme_data_update_time'])) $item['theme_data_update_time'] = date('Y-m-d H:i', $item['theme_data_update_time']);
            if (isset($item['type'])) $item['type'] = $item['type'] == 0 ? '自建主题' : '广场主题';
            if (isset($item['theme_data'])) $item['theme_data'] = json_decode($item['theme_data'], true) ?? [];
            $item['home_image'] = set_file_url($item['home_image']);
            $item['category_image'] = set_file_url($item['category_image']);
            $item['detail_image'] = set_file_url($item['detail_image']);
            $item['user_image'] = set_file_url($item['user_image']);
        }
        $count = $this->dao->themeCount($where);
        return compact('list', 'count');
    }

    /**
     * 获取主题版本号
     *
     * 功能概述:
     * 根据主题ID获取该主题的当前版本号。
     * 如果ID为0，则获取当前正在使用的主题的版本号。
     *
     * @param int $id 主题ID，0表示当前使用的主题
     * @return mixed 版本号字符串
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function getThemeVersion($id)
    {
        $where = $id == 0 ? ['is_use' => 1] : ['id' => $id];
        return $this->dao->value($where, 'version');
    }

    /**
     * 获取主题信息
     *
     * 功能概述:
     * 根据主题ID和类型获取主题的详细信息。
     * 支持获取全部信息或指定类型（如首页、分类页、详情页等）的数据。
     * 对返回的数据进行必要的格式化和默认值填充。
     *
     * 返回数据结构:
     * 根据 $type 不同返回不同结构：
     * - 'all'/'base': 返回主题完整记录数组
     * - 'home'/'detail'/'user'/'theme': 返回解析后的配置数组
     * - 'category': 返回包含 status 的数组
     *
     * @param int $id 主题ID，0表示当前使用的主题
     * @param string $type 数据类型：all, home, category, detail, user, theme, base
     * @return array|int[]|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws AdminException 数据不存在时抛出
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function getThemeInfo($id, $type = 'all')
    {
        $where = $id == 0 ? ['is_use' => 1] : ['id' => $id];
        $info = $this->dao->get($where);
        if (!$info) throw new AdminException('数据不存在');
        $info = $info->toArray();
        if ($type == 'home') {
            return json_decode($info['home_data'], true) ?? [];
        } elseif ($type == 'category') {
            return ['status' => $info['category_data'] ?? 1];
        } elseif ($type == 'detail') {
            return json_decode($info['detail_data'], true) ?? [];
        } elseif ($type == 'user') {
            return json_decode($info['user_data'], true) ?? [];
        } elseif ($type == 'theme') {
            if ($info['theme_data'] === '' || $info['theme_data'] === null || $info['theme_data'] === 'null') {
                $info['theme_data'] = '{"theme_color":"#E93323","gradient_color":"#FF7931","sub_color":"#FE960F","light_color":"rgba(233, 51, 35, 0.1)"}';
            }
            return json_decode($info['theme_data'], true) ?? [];
        } elseif ($type == 'base') {
            return ['id' => $info['id'], 'type' => $info['type'], 'title' => $info['title'], 'info' => $info['info']];
        } else {
            $info['home_data_update_time'] = date('Y-m-d H:i:s', $info['home_data_update_time']);
            $info['category_data_update_time'] = date('Y-m-d H:i:s', $info['category_data_update_time']);
            $info['detail_data_update_time'] = date('Y-m-d H:i:s', $info['detail_data_update_time']);
            $info['user_data_update_time'] = date('Y-m-d H:i:s', $info['user_data_update_time']);
            $info['theme_data_update_time'] = date('Y-m-d H:i:s', $info['theme_data_update_time']);
            $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
            $info['up_time'] = date('Y-m-d H:i:s', $info['up_time']);
            $ids = [$info['home_data_id'], $info['category_data_id'], $info['detail_data_id'], $info['user_data_id'], $info['theme_data_id']];
            $titles = $this->dao->getColumn([['id', 'in', $ids]], 'title', 'id');
            $info['home_data_id_title'] = $titles[$info['home_data_id']] ?? '';
            $info['category_data_id_title'] = $titles[$info['category_data_id']] ?? '';
            $info['detail_data_id_title'] = $titles[$info['detail_data_id']] ?? '';
            $info['user_data_id_title'] = $titles[$info['user_data_id']] ?? '';
            $info['theme_data_id_title'] = $titles[$info['theme_data_id']] ?? '';
            return $info;
        }
    }

    /**
     * 保存主题数据
     *
     * 功能概述:
     * 创建新主题或更新现有主题的数据。
     * 支持从模板主题复制数据创建新主题。
     * 根据不同的页面类型（home, category, detail, user, theme）处理相应的数据保存逻辑。
     * 自动更新版本号和最后修改时间。
     *
     * @param int $id 主题主键，0 表示新增
     * @param array $data 待保存数据，必须包含 type、value，可选 tid、title
     * @return int 新增或更新后的主题 ID
     * @throws AdminException 当指定 tid 但主题不存在时抛出
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function saveTheme($id, $data)
    {
        // 初始化待写入数组
        $saveData = [];

        // 如果指定了模板主题 ID（tid），则先复制其数据作为基础
        if ($data['tid'] !== 0) {
            // 查询模板主题
            $tInfo = $this->dao->get($data['tid']);
            if (!$tInfo) {
                throw new AdminException('主题不存在');
            }
            // 将模板主题数据转为数组，并剔除主键 id，避免冲突
            $saveData = $tInfo->toArray();
            // 新主题默认未启用
            $saveData['is_use'] = 0;
            unset($saveData['id']);
        }

        // 如果传入了标题，则覆盖
        if ($data['title'] !== '') {
            $saveData['title'] = $data['title'];
        }

        if ($id == 0) {
            $type = 0;
            $saveData['category_data'] = 1;
            $saveData['category_data_update_time'] = time();
            $saveData['category_image'] = '/statics/images/cate1.png';
        } else {
            $type = $this->dao->value(['id' => $id], 'type');
        }

        // 将传入的 value 统一转为 JSON 字符串
        $value = json_encode($data['value']);

        // 根据模块类型分别处理数据、预览图及更新时间
        switch ($data['type']) {
            case 'home':
                // 首页
                $saveData['home_data'] = $value;
                $saveData['home_data_update_time'] = time();
                // 自建主题需要同步写入默认数据
                if ($type == 0) {
                    $saveData['home_default_data'] = $value;
                }
                break;

            case 'category':
                // 分类页
                $saveData['category_data'] = $value;
                $saveData['category_data_update_time'] = time();
                // 根据 value 生成对应预览图路径
                $saveData['category_image'] = '/statics/images/cate' . $value . '.png';
                if ($type == 0) {
                    $saveData['category_default_data'] = $value;
                    $saveData['category_default_image'] = '/statics/images/cate' . $value . '.png';
                }
                break;

            case 'detail':
                // 详情页
                $saveData['detail_data'] = $value;
                $saveData['detail_data_update_time'] = time();
                if ($type == 0) {
                    $saveData['detail_default_data'] = $value;
                }
                break;

            case 'user':
                // 用户中心
                $saveData['user_data'] = $value;
                $saveData['user_data_update_time'] = time();
                if ($type == 0) {
                    $saveData['user_default_data'] = $value;
                }
                break;

            case 'theme':
                // 主题自身数据
                $saveData['theme_data'] = $value;
                $saveData['theme_data_update_time'] = time();
                if ($type == 0) {
                    $saveData['theme_default_data'] = $value;
                }
                break;
        }

        // 每次保存都生成新的版本号
        $saveData['version'] = uniqid('', true);

        // 新增 or 更新
        if ($id) {
            // 更新
            $saveData['up_time'] = time();
            $this->dao->update($id, $saveData);
        } else {
            // 新增
            $saveData['page_type'] = $data['page_type'];
            $saveData['add_time'] = time();
            $saveData['up_time'] = time();
            $id = $this->dao->insertGetId($saveData);
        }

        // 返回最终主题 ID
        return $id;
    }

    /**
     * 保存主题标题信息
     *
     * 功能概述:
     * 更新主题的标题和简介信息，或创建新的主题记录（仅包含标题信息）。
     * 更新操作会同步更新版本号和最后修改时间。
     *
     * @param int $id 主题ID，0表示新增
     * @param array $data 包含 title 和 info 的数据数组
     * @return int|mixed|string 主题ID
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function saveThemeTitle($id, $data)
    {
        // 如果指定了模板主题 ID（tid），则先复制其数据作为基础
        if ($data['tid'] !== 0) {
            // 查询模板主题
            $tInfo = $this->dao->get($data['tid']);
            if (!$tInfo) {
                throw new AdminException('主题不存在');
            }
            // 将模板主题数据转为数组，并剔除主键 id，避免冲突
            $saveData = $tInfo->toArray();
            // 新主题默认未启用
            $saveData['is_use'] = 0;
            unset($saveData['id']);
        }
        $saveData['title'] = $data['title'];
        $saveData['info'] = $data['info'];
        $saveData['version'] = uniqid('', true);
        $saveData['page_type'] = $data['page_type'];
        if ($id) {
            $saveData['up_time'] = time();
            $this->dao->update($id, $saveData);
        } else {
            $saveData['add_time'] = time();
            $saveData['up_time'] = time();
            $id = $this->dao->insertGetId($saveData);
        }
        return $id;
    }

    /**
     * 保存主题图片信息
     *
     * 功能概述:
     * 更新主题各模块（首页、详情页、用户中心）的预览图片。
     * 如果是默认主题（type=0），会同步更新默认图片配置。
     * 自动更新版本号和最后修改时间。
     *
     * @param int $id 主题ID
     * @param array $data 包含 type (home/detail/user) 和 image 的数据数组
     * @return int|mixed|string 主题ID
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/12/18
     */
    public function saveThemeImage($id, $data)
    {
        $type = $id ? $this->dao->value(['id' => $id], 'type') : 0;
        switch ($data['type']) {
            case 'home':
                $saveData['home_image'] = $data['image'];
                if ($type == 0) $saveData['home_default_image'] = $data['image'];
                break;
            case 'detail':
                $saveData['detail_image'] = $data['image'];
                if ($type == 0) $saveData['detail_default_image'] = $data['image'];
                break;
            case 'user':
                $saveData['user_image'] = $data['image'];
                if ($type == 0) $saveData['user_default_image'] = $data['image'];
                break;
        }
        $saveData['version'] = uniqid('', true);
        if ($id) {
            $saveData['up_time'] = time();
            $this->dao->update($id, $saveData);
        } else {
            $saveData['page_type'] = 'theme';
            $saveData['add_time'] = time();
            $saveData['up_time'] = time();
            $id = $this->dao->insertGetId($saveData);
        }
        return $id;
    }

    /**
     * 移除 utf8mb3 表无法存储的四字节 UTF-8 字符（U+10000 ~ U+10FFFF）
     *
     * 功能概述:
     * 当数据库表的字符集为 utf8（即 utf8mb3）时，无法存储 4 字节的 UTF-8 字符（如 emoji 和部分生僻字）。
     * 该方法递归遍历数组和字符串，将四字节字符替换为空字符串，避免 MySQL 出现 3988 错误。
     * 若表本身已是 utf8mb4 字符集，则不做任何处理，直接返回。
     *
     * @param mixed $data 要处理的数据（字符串或数组，引用传递）
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/06/18
     */
    protected function removeUnsupportedUtf8mb3Chars(&$data): void
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $this->removeUnsupportedUtf8mb3Chars($value);
            }
            unset($value);
        } elseif (is_string($data)) {
            $data = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $data);
        }
    }

    /**
     * 导入主题数据
     *
     * 功能概述:
     * 将外部导入的主题配置数据保存到数据库中。
     * 包含主题的所有页面配置（首页、分类、详情、个人中心）及其对应的默认配置。
     *
     * @param array $config 主题配置数据数组
     * @return mixed 新增的主题ID
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function importThemeData($config)
    {
        $data = [];
        $data['version'] = uniqid('', true); // 版本号
        $data['title'] = $config['title']; // 标题
        $data['info'] = $config['info']; // 简介
        $data['type'] = 1; // 类型
        $data['home_data'] = $data['home_default_data'] = $config['home_data']; // 首页数据
        $data['home_image'] = $data['home_default_image'] = $config['home_image']; // 首页封面
        $data['home_data_id'] = $config['home_data_id']; // 首页数据ID
        $data['home_data_update_time'] = time(); // 首页数据更新时间
        $data['category_data'] = $data['category_default_data'] = $config['category_data']; // 分类页数据
        $data['category_image'] = $data['category_default_image'] = $config['category_image']; // 分类页封面
        $data['category_data_id'] = $config['category_data_id']; // 分类页数据ID
        $data['category_data_update_time'] = time(); // 分类页数据更新时间
        $data['detail_data'] = $data['detail_default_data'] = $config['detail_data']; // 详情页数据
        $data['detail_image'] = $data['detail_default_image'] = $config['detail_image']; // 详情页封面
        $data['detail_data_id'] = $config['detail_data_id']; // 详情页数据ID
        $data['detail_data_update_time'] = time(); // 详情页数据更新时间
        $data['user_data'] = $data['user_default_data'] = $config['user_data']; // 个人中心数据
        $data['user_image'] = $data['user_default_image'] = $config['user_image']; // 个人中心封面
        $data['user_data_id'] = $config['user_data_id']; // 个人中心数据ID
        $data['user_data_update_time'] = time(); // 个人中心数据更新时间
        $data['theme_data'] = $data['theme_default_data'] = json_encode($config['theme_data'], JSON_UNESCAPED_UNICODE); // 主题数据
        $data['theme_data_id'] = $config['theme_data_id']; // 主题数据ID
        $data['theme_data_update_time'] = time(); // 主题数据更新时间
        $data['page_type'] = 'theme'; // 页面类型
        $data['is_use'] = 0; // 是否使用
        $data['is_del'] = 0; // 是否删除
        $data['add_time'] = time(); // 添加时间
        $data['up_time'] = time(); // 更新时间
        // 过滤四字节 UTF-8 字符，避免 MySQL utf8mb3 表 3988 报错
        $this->removeUnsupportedUtf8mb3Chars($data);
        $id = $this->dao->insertGetId($data);
        return $id;
    }

    /**
     * 使用主题
     *
     * 功能概述:
     * 将指定主题设置为当前启用状态。
     * 该操作会先将所有主题设为未启用，然后启用指定ID的主题。
     *
     * @param int $id 要启用的主题ID
     * @return bool 操作成功返回 true
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function useTheme(int $id)
    {
        $this->dao->update(['is_use' => 1], ['is_use' => 0]);
        $this->dao->update($id, ['is_use' => 1]);
        return true;
    }

    /**
     * 使用主题数据
     *
     * 功能概述:
     * 将某个主题的特定模块数据（如首页、详情页等）应用到目标主题数据记录中。
     * 实现主题数据的局部复用或混搭。
     *
     * @param int $id 目标主题数据ID
     * @param int $theme_id 源主题ID
     * @param string $type 数据类型（home/category/detail/user/theme）
     * @return bool 操作成功返回 true
     * @throws AdminException 当源主题数据不存在时抛出
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function useThemeData(int $id, int $theme_id, string $type)
    {
        $data = $this->dao->get(['id' => $theme_id]);
        if (!$data) throw new AdminException('主题数据不存在');
        $this->dao->update(['id' => $id], [$type . '_data_update_time' => time(), $type . '_image' => $data[$type . '_image'], $type . '_data' => $data[$type . '_data'], $type . '_data_id' => $theme_id]);
        return true;
    }


    /**
     * 获取当前正在使用的主题信息
     *
     * 功能概述:
     * 查询当前启用的主题（is_use=1），并聚合其关联的各模块（首页、分类、详情等）数据。
     * 如果采用了混搭模式（引用了其他主题的模块），会解析出实际来源主题的标题和图片信息。
     *
     * 返回数据结构:
     * - id, title, info, version: 主题基础信息
     * - confuse: 是否混搭模式 (0/1)
     * - data_info: 各模块详情列表（包含 key, title, image, update_time）
     * - theme_data: 主题全局样式配置
     *
     * @return array 返回包含主题基础信息及各个模块详细配置的数据
     * @throws AdminException 当没有正在使用的主题时抛出异常
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function getUsingTheme()
    {
        // 查询当前正在使用的主题记录（is_use = 1）
        $data = $this->dao->get(['is_use' => 1]);
        if (!$data) throw new AdminException('没有正在使用的主题');

        // 收集各模块关联的主题ID，并过滤掉空值
        $themeIds = array_filter([
            $data['home_data_id'] ?? 0,      // 首页模块关联主题ID
            $data['category_data_id'] ?? 0,  // 分类页模块关联主题ID
            $data['detail_data_id'] ?? 0,    // 详情页模块关联主题ID
            $data['user_data_id'] ?? 0,      // 用户中心模块关联主题ID
            $data['theme_data_id'] ?? 0,     // 主题自身数据关联主题ID
        ]);


        // 组装最终返回的主题信息数组
        $theme = [];
        $theme['id'] = $data['id'];             // 主题ID
        $theme['title'] = $data['title'];         // 主题名称
        $theme['info'] = $data['info'];           // 主题简介
        $theme['version'] = $data['version'];   // 主题版本号
        $theme['confuse'] = 0;                  // 是否混搭使用主题：0否 1是
        $themeData = [];
        // 若存在关联主题ID，则批量查询其标题，供后续拼接使用
        if ($themeIds) {
            $themeData = $this->dao->getColumn([['id', 'in', $themeIds]], 'title', 'id');
            $theme['confuse'] = 1;
        }
        $theme['data_info'] = [
            [
                'key' => 'home',
                'title' => $themeData[$data['home_data_id'] ?? 0] ?? $data['title'], // 首页模块标题（优先取关联主题标题）
                'image' => set_file_url($data['home_image'] ?? ''), // 首页预览图
                'update_time' => !empty($data['home_data_update_time']) ? date('Y-m-d H:i:s', $data['home_data_update_time']) : '', // 首页数据更新时间
            ],
            [
                'key' => 'category',
                'title' => $themeData[$data['category_data_id'] ?? 0] ?? $data['title'], // 分类页模块标题（优先取关联主题标题）
                'image' => set_file_url($data['category_image'] ?? ''), // 分类页预览图
                'update_time' => !empty($data['category_data_update_time']) ? date('Y-m-d H:i:s', $data['category_data_update_time']) : '', // 分类页数据更新时间
            ],
            [
                'key' => 'detail',
                'title' => $themeData[$data['detail_data_id'] ?? 0] ?? $data['title'], // 详情页模块标题（优先取关联主题标题）
                'image' => set_file_url($data['detail_image'] ?? ''), // 详情页预览图
                'update_time' => !empty($data['detail_data_update_time']) ? date('Y-m-d H:i:s', $data['detail_data_update_time']) : '', // 详情页数据更新时间
            ],
            [
                'key' => 'user',
                'title' => $themeData[$data['user_data_id'] ?? 0] ?? $data['title'], // 用户中心模块标题（优先取关联主题标题）
                'image' => set_file_url($data['user_image'] ?? ''), // 用户中心预览图
                'update_time' => !empty($data['user_data_update_time']) ? date('Y-m-d H:i:s', $data['user_data_update_time']) : '', // 用户中心数据更新时间
            ],
        ];
        $themeDataJson = $data['theme_data'] ?: '{"theme_color":"#E93323","gradient_color":"#FF7931","sub_color":"#FE960F","light_color":"rgba(233, 51, 35, 0.1)"}';
        $theme['theme_data'] = json_decode($themeDataJson, true) ?: []; // 主题自身数据（JSON格式）

        return $theme;
    }

    /**
     * @description: 还原主题
     * @param int $id 主题ID
     * @return void
     */
    public function restoreTheme(int $id)
    {
        $data = $this->dao->get($id);
        if (!$data) throw new AdminException('主题不存在');
        $this->dao->update($id, [
            'home_data' => $data['home_default_data'],
            'home_data_id' => 0,
            'home_image' => $data['home_default_image'],
            'home_data_update_time' => time(),
            'category_data' => $data['category_default_data'],
            'category_data_id' => 0,
            'category_image' => $data['category_default_image'],
            'category_data_update_time' => time(),
            'detail_data' => $data['detail_default_data'],
            'detail_data_id' => 0,
            'detail_image' => $data['detail_default_image'],
            'detail_data_update_time' => time(),
            'user_data' => $data['user_default_data'],
            'user_data_id' => 0,
            'user_image' => $data['user_default_image'],
            'user_data_update_time' => time(),
            'theme_data' => $data['theme_default_data'],
            'theme_data_update_time' => time(),
            'version' => uniqid('', true), // 更新版本号
            'up_time' => time(), // 更新时间
        ]);
        return true;
    }

    /**
     * 删除主题
     *
     * 功能概述:
     * 软删除指定的主题（更新 is_del 字段）。
     *
     * @param int $id 主题ID
     * @return bool 操作成功返回 true
     * @throws AdminException 当主题不存在时抛出
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function deleteTheme(int $id)
    {
        $data = $this->dao->get($id);
        if (!$data) throw new AdminException('主题不存在');
        if ($data['is_use']) throw new AdminException('当前主题正在使用中，不能删除');
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }

    /**
     * 获取当前启用主题的底部导航配置
     *
     * 功能概述:
     * 解析当前启用主题的首页数据，提取其中的底部导航（pagefoot）组件配置。
     *
     * @return array 返回名为 pagefoot 的组件配置数组，未找到时返回空数组
     * @throws ApiException 当启用主题不存在首页数据时抛出
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function themeNavigation()
    {
        // 查询当前正在使用的主题的首页数据（JSON 字符串）
        $value = $this->dao->value(['is_use' => 1], 'home_data');
        if (!$value) {
            throw new ApiException('数据不存在');
        }

        // 初始化导航数据为空数组
        $navigation = [];

        // 若首页数据存在，则进行解析与遍历
        if ($value) {
            // 将 JSON 字符串解码为数组
            $value = json_decode($value, true);
            // 遍历首页组件，查找名称为 pagefoot 的底部导航组件
            foreach ($value['value'] as $item) {
                if (isset($item['name']) && strtolower($item['name']) === 'pagefoot') {
                    // 找到后赋值并终止循环
                    $navigation = $item;
                    break;
                }
            }
        }

        // 返回导航配置（可能为空数组）
        return $navigation;
    }

    /**
     * 获取微页面列表
     *
     * 功能概述:
     * 分页查询微页面（page_type='micro'）列表数据。
     *
     * 主要功能:
     * 1. 分页查询 - 根据系统分页参数获取数据
     * 2. 数据过滤 - 仅查询未删除且类型为微页面的记录
     * 3. 格式化 - 转换时间戳为可读日期格式
     *
     * @return array 包含列表数据 list 和总数 count 的数组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/02/03
     */
    public function getMicroPageList()
    {
        [$page, $limit] = $this->getPageValue(); // 获取分页参数
        $field = 'id,title,info,type,add_time,up_time,page_type'; // 查询字段
        $order = 'id desc'; // 排序
        $where = [
            'is_del' => 0, // 未删除
            'page_type' => 'micro', // 微页面类型
        ];
        $list = $this->dao->themeList($where, $field, $page, $limit, $order); // 查询列表
        foreach ($list as &$item) {
            // 格式化时间
            if (isset($item['add_time'])) $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
            if (isset($item['up_time'])) $item['up_time'] = date('Y-m-d H:i', $item['up_time']);
        }
        $count = $this->dao->themeCount($where); // 获取总数
        return compact('list', 'count');
    }

    /**
     * 导出主题数据（核心逻辑）
     * 将主题配置及相关图片打包成 Zip 文件，返回下载地址
     *
     * @param $themeInfo
     * @return string 下载地址
     * @throws    hinkdbexceptionDataNotFoundException
     * @throws    hinkdbexceptionDbException
     * @throws    hinkdbexceptionModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/3/10
     */
    public function exportThemePackage($info): string
    {
        // 1. 设置导出临时目录
        $dir = public_path() . 'theme/download/' . $info['id'] . '/';

        // 2. 处理主要图片（首页图、分类图、详情图、个人中心图）
        $images = ['home_image', 'category_image', 'detail_image', 'user_image'];
        $defaultImages = ['home_default_image', 'category_default_image', 'detail_default_image', 'user_default_image'];
        $i = 1;
        foreach ($images as $key => $image) {
            if (isset($info[$image]) && $info[$image]) {
                $originalUrl = $info[$image];
                $isRemote = (bool)preg_match('/^https?:\/\//i', $originalUrl);
                if ($isRemote) {
                    $urlPath = parse_url($originalUrl, PHP_URL_PATH) ?: $originalUrl;
                    $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION)) ?: 'jpg';
                    $newPath = $dir . $i . '_' . $image . '.' . $extension;
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $originalUrl,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_REFERER => rtrim(sys_config('site_url'), '/'),
                        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; CRMEB/1.0)',
                        CURLOPT_HTTPHEADER => ['Accept: image/webp,image/*,*/*'],
                    ]);
                    $content = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($content && $httpCode === 200 && file_put_contents($newPath, $content) !== false) {
                        $info[$image] = 'theme/download/' . $i . '_' . $image . '.' . $extension;
                        $info[$defaultImages[$key]] = 'theme/download/' . $i . '_' . $image . '.' . $extension;
                    }
                } else {
                    $localPath = public_path() . ltrim(preg_replace('/^https?:\/\/[^\/]+/', '', $originalUrl), '/');
                    if (!file_exists($localPath)) {
                        $i++;
                        continue;
                    }
                    $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION)) ?: 'jpg';
                    $newPath = $dir . $i . '_' . $image . '.' . $extension;
                    if (copy($localPath, $newPath)) {
                        $info[$image] = 'theme/download/' . $i . '_' . $image . '.' . $extension;
                        $info[$defaultImages[$key]] = 'theme/download/' . $i . '_' . $image . '.' . $extension;
                    }
                }
            }
            $i++;
        }

        // 3. 处理 home_data / detail_data / user_data 中的图片
        $imagesDir = $dir . 'images/';
        $index = 1;
        $map = [];
        $isAttachImage = function ($str) {
            if (!is_string($str) || $str === '') return false;
            if (strpos($str, 'uploads/attach') !== false) return true;
            if (preg_match('/^https?:\/\//i', $str)) return true;
            return false;
        };

        $process = function (&$value, $key) use (&$index, &$map, $imagesDir, $isAttachImage) {
            if (!is_string($value) || !$isAttachImage($value)) return;
            if (!isset($map[$value])) {
                $path = parse_url($value, PHP_URL_PATH);
                $path = $path ?: $value;
                $basename = basename($path);
                $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
                    $ext = 'jpg';
                    $basename = md5($value) . '.' . $ext;
                }
                $dest = $imagesDir . $basename;
                $rel = 'images/' . $basename;
                $ok = false;
                $isRemote = preg_match('/^https?:\/\//i', $value);
                if ($isRemote) {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $value,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_REFERER => rtrim(sys_config('site_url'), '/'),
                        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; CRMEB/1.0)',
                        CURLOPT_HTTPHEADER => ['Accept: image/webp,image/*,*/*'],
                    ]);
                    $content = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($content && $httpCode === 200) {
                        $ok = (file_put_contents($dest, $content) !== false);
                    }
                } else {
                    $src = public_path() . ltrim($path, '/');
                    if (file_exists($src)) $ok = @copy($src, $dest);
                }
                if ($ok) {
                    $map[$value] = $rel;
                    $index++;
                } else {
                    return;
                }
            }
            $value = $map[$value] ?? $value;
        };

        $homeData = json_decode($info['home_data'] ?? '[]', true);
        $detailData = json_decode($info['detail_data'] ?? '[]', true);
        $userData = json_decode($info['user_data'] ?? '[]', true);

        if (is_array($homeData)) array_walk_recursive($homeData, $process);
        if (is_array($detailData)) array_walk_recursive($detailData, $process);
        if (is_array($userData)) array_walk_recursive($userData, $process);

        $info['home_data'] = json_encode($homeData, JSON_UNESCAPED_UNICODE);
        $info['detail_data'] = json_encode($detailData, JSON_UNESCAPED_UNICODE);
        $info['user_data'] = json_encode($userData, JSON_UNESCAPED_UNICODE);
        $info['home_default_data'] = json_encode($homeData, JSON_UNESCAPED_UNICODE);
        $info['detail_default_data'] = json_encode($detailData, JSON_UNESCAPED_UNICODE);
        $info['user_default_data'] = json_encode($userData, JSON_UNESCAPED_UNICODE);
        $info['theme_data'] = $info['theme_default_data'] = json_decode($info['theme_data'], true);

        // 4. 写入 config.json
        file_put_contents($dir . 'config.json', json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 7. 打包成 zip
        $zip = new \ZipArchive();
        $zip->open($dir . $info['title'] . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $rootPath = realpath($dir);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $filePath = $file->getRealPath();
            if (basename($filePath) === $info['title'] . '.zip') continue;
            $relativePath = ltrim(str_replace($rootPath, '', $filePath), DIRECTORY_SEPARATOR);
            $zip->addFile($filePath, $relativePath);
        }
        $zip->close();

        return sys_config('site_url') . '/theme/download/' . $info['id'] . '/' . $info['title'] . '.zip';
    }
}
