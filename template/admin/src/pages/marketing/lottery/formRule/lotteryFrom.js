const lotteryFrom = {
  name: [{ required: true, message: '请输入活动名称', trigger: 'blur' }],
  factor: [{ required: true, type: 'number', message: '请选择活动类型', trigger: 'change' }],
  attends_user: [{ required: true, type: 'number', message: '请选择参与用户', trigger: 'change' }],
  factor_num: [{ required: true, type: 'number', message: '请输入抽奖次数', trigger: 'blur' }],
  prize: [
    {
      required: true,
      type: 'array',
      message: '请添加抽奖奖品(8条)',
      trigger: 'change',
    },
    {
      type: 'array',
      min: 8,
      message: '请添加抽奖奖品(8条)',
      trigger: 'change',
    },
  ],
  lottery_num: [
    {
      required: true,
      type: 'number',
      message: '请输入邀请新用户最多可获得抽奖多少次',
      trigger: 'blur',
    },
  ],
  spread_num: [
    {
      required: true,
      type: 'number',
      message: '请输入关注额外抽多少次',
      trigger: 'blur',
    },
  ],
  image: [
    {
      required: true,
      message: '请上传活动背景图',
      trigger: 'change',
    },
  ],
  content: [
    {
      required: true,
      message: '请填写活动规则',
      trigger: 'blur',
    },
  ],
};
function validate(rule, value, callback) {
  if (Array.isArray(value)) {
    //格式为：daterange、datetimerange检测
    value.map(function (item) {
      if (item === '') {
        return callback('日期不能为空');
      }
    });
  } else {
    //格式为：date、datetime、year、month 检测
    if (value === '') {
      return callback('日期不能为空');
    }
  }
  return callback();
}

export { lotteryFrom };
