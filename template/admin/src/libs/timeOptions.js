// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

export default {
  shortcuts: [
    {
      text: '今天',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
        return [start, end];
      },
    },
    {
      text: '昨天',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(
          start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
        );
        end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)));
        return [start, end];
      },
    },
    {
      text: '最近7天',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(
          start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)),
        );
        return [start, end];
      },
    },
    {
      text: '最近30天',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(
          start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)),
        );
        return [start, end];
      },
    },
    {
      text: '本月',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
        return [start, end];
      },
    },
    {
      text: '本年',
      value() {
        const end = new Date();
        const start = new Date();
        start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
        return [start, end];
      },
    },
  ],
};
