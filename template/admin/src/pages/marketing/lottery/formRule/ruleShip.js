const ruleShip = {
  deliver_name: [
    {
      required: true,
      type: "string",
      message: "请选择快递公司",
      trigger: "select",
    },
  ],
  deliver_number: [
    {
      required: true,
      message: "请输入快递单号",
      trigger: "blur",
    },
  ],
}
const ruleMark = {
  mark: [
    {
      required: true,
      message: "请输入备注信息",
      trigger: "blur",
    },
  ],
}
export { ruleShip, ruleMark }