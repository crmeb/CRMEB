// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export function authLapse(data) {
  return new Promise((resolve, reject) => {
    const h = this.$createElement;
    this.$notify.warning({
      title: data.title,
      duration: 3000,
      message: h('div', [
        h(
          'a',
          {
            attrs: {
              href: 'http://www.crmeb.com',
              target: '_blank',
            },
          },
          data.info,
        ),
      ]),
    });
  });
}
