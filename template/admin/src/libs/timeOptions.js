export default {
  shortcuts: [
    {
      text: '今天',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '昨天',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(
          start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
        );
        end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)));
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '本月',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '上月',
      onClick(picker) {
        const start = new Date();
        const end = new Date(start);
        end.setMonth(start.getMonth());
        start.setMonth(start.getMonth() - 1);
        end.setDate(0);
        start.setDate(1);
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '最近7天',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '最近30天',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '最近90天',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
        picker.$emit('pick', [start, end]);
      },
    },

    {
      text: '最近1年',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.getTime() - 3600 * 1000 * 24 * 365);
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '本年',
      onClick(picker) {
        const end = new Date();
        const start = new Date();
        start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
        picker.$emit('pick', [start, end]);
      },
    },
    {
      text: '去年',
      onClick(picker) {
        //获取当前时间
        let currentDate = new Date();
        //获得当前年份4位年
        let currentYear = currentDate.getFullYear() - 1;
        //本年第一天
        const start = new Date(currentYear, 0, 1);
        //本年最后一天
        const end = new Date(currentYear, 11, 31);
        //end.setHours(23, 59, 59, 0)
        picker.$emit('pick', [start, end]);
      },
    },
  ],
};
