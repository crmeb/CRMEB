// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { h } from 'vue';
import { ElNotification } from 'element-plus';

export function authLapse(data) {
  return new Promise((resolve, reject) => {
    ElNotification.warning({
      title: data.title,
      duration: 3000,
      message: h('div', [
        h(
          'a',
          {
            href: 'http://www.crmeb.com',
            target: '_blank',
          },
          data.info,
        ),
      ]),
    });
    resolve();
  });
}
