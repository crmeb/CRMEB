import dayjs from 'dayjs';

/**
 * 系统日期范围快捷项（唯一真源，Element Plus 格式）
 * @description 供 el-date-picker 的 shortcuts 及 dateRadio 等组件使用；
 * value 返回原生 Date 数组（[start, end]），与 libs 时间语义保持一致。
 */
export default {
  shortcuts: [
    { text: '今天', value: () => [dayjs().startOf('day').toDate(), new Date()] },
    {
      text: '昨天',
      value: () => [
        dayjs().subtract(1, 'day').startOf('day').toDate(),
        dayjs().subtract(1, 'day').endOf('day').toDate(),
      ],
    },
    { text: '本月', value: () => [dayjs().startOf('month').toDate(), new Date()] },
    {
      text: '上月',
      value: () => [
        dayjs().subtract(1, 'month').startOf('month').toDate(),
        dayjs().subtract(1, 'month').endOf('month').toDate(),
      ],
    },
    { text: '最近7天', value: () => [dayjs().subtract(7, 'day').toDate(), new Date()] },
    { text: '最近30天', value: () => [dayjs().subtract(30, 'day').toDate(), new Date()] },
    { text: '最近90天', value: () => [dayjs().subtract(90, 'day').toDate(), new Date()] },
    { text: '最近1年', value: () => [dayjs().subtract(1, 'year').toDate(), new Date()] },
    { text: '本年', value: () => [dayjs().startOf('year').toDate(), new Date()] },
    {
      text: '去年',
      value: () => [
        dayjs().subtract(1, 'year').startOf('year').toDate(),
        dayjs().subtract(1, 'year').endOf('year').toDate(),
      ],
    },
  ],
};
