// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed | CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权 |
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com> |
// +----------------------------------------------------------------------

// 图标选择列表：Element Plus 图标集（组件名，如 Search / Bell），
// 旧 element-ui 字体图标名在 EP 下无字体支撑，不再提供
import * as ElementPlusIconsVue from '@element-plus/icons-vue';

export default Object.keys(ElementPlusIconsVue).sort((a, b) => a.localeCompare(b));
