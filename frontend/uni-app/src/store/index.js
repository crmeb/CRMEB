// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { createPinia, setActivePinia } from 'pinia';

const pinia = createPinia();
// 关键：在 createSSRApp 之前创建并激活 Pinia，
// 保证 App.vue onLaunch 及 utils/* 模块调用 useAppStore() 时可用
setActivePinia(pinia);

export { pinia };
