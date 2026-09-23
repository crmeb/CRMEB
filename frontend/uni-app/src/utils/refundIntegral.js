/** 商品数量对应的可退积分；服务端仍进行最终校验。 */
export function getRefundIntegral(item, selectedNum) {
  const quantity = Math.max(0, Number(item.cart_num) || 0);
  const selected = Math.max(0, Number(selectedNum) || 0);
  const total = Math.max(0, Number(item.deduction_integral_total) || 0);
  if (total > 0 && quantity > 0) {
    const before = Math.min(quantity, Math.max(0, Number(item.refunded_num) || 0));
    const after = Math.min(quantity, before + selected);
    return Math.floor(total * after / quantity) - Math.floor(total * before / quantity);
  }
  return Math.max(0, Number(item.integral_unit_price) || 0) * selected;
}
