// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { tableDelApi } from '@/api/common';
export function modalSure(delfromData) {
  return new Promise((resolve, reject) => {
    let content = `<p>确定要${delfromData.title}吗？</p>`;
    if (!delfromData.info) {
      delfromData.info = '';
    }
    const h = this.$createElement;
    this.$msgbox({
      title: '提示',
      message: h('p', null, [h('div', null, `确定要${delfromData.title}吗？`), h('div', null, `${delfromData.info}`)]),
      showCancelButton: true,
      cancelButtonText: '取消',
      confirmButtonText: '确定',
      iconClass: 'el-icon-warning',
      confirmButtonClass: 'btn-custom-cancel',
    })
      .then(() => {
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
      })
      .catch(() => {});
  });
}

export function HandlePrice(num, type) {
  let obj = [];
  if (typeof num == 'number') {
    obj = num.toString().split('.');
  } else {
    obj = num.split('.');
  }
  if (type) {
    if (obj.length && obj[1]) {
      return '.' + obj[1];
    } else {
      return '';
    }
  } else {
    return obj[0];
  }
}
