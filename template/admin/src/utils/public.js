// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { tableDelApi } from '@/api/common';
export function modalSure(delfromData) {
  return new Promise((resolve, reject) => {
    let content = `<p>确定要${delfromData.title}吗？</p>`;
    if (delfromData.info !== undefined) {
      content = content + `<p>${delfromData.info}</p>`;
    }
    this.$Modal.confirm({
      title: delfromData.title,
      content: content,
      loading: true,
      onOk: () => {
        setTimeout(() => {
          this.$Modal.remove();
          if (delfromData.success) {
            delfromData.success
              .then(async (res) => {
                resolve(res);
              })
              .catch((res) => {
                reject(res);
              });
          } else {
            tableDelApi(delfromData)
              .then(async (res) => {
                resolve(res);
              })
              .catch((res) => {
                reject(res);
              });
          }
        }, 300);
      },
      onCancel: () => {
        this.$Message.info('取消成功');
      },
    });
  });
}
