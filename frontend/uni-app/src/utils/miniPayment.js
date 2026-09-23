/**
 * 区分用户主动取消和微信调起失败，避免把参数或签名错误误报为取消支付。
 * @param {object} error 微信支付失败信息
 * @returns {{canceled: boolean, message: string}} 取消标记与错误说明
 */
export function getMiniPaymentFailure(error) {
  const raw = String(error?.errMsg || error?.message || '');
  return {
    canceled: /^(?:requestPayment|requestOrderPayment):(?:fail\s+)?cancel$/i.test(raw.trim()),
    message: raw.replace(/^(?:requestPayment|requestOrderPayment):(?:fail\s*)?/i, '').trim(),
  };
}

/**
 * 根据服务端原支付模式调起微信，普通 JSAPI 不使用新版订单支付接口。
 * @param {object} config 服务端支付参数
 * @param {object} handlers 支付回调
 */
export function requestMiniPayment(config, handlers) {
  const method = config.paymentMode === 'mini_shop' ? 'requestOrderPayment' : 'requestPayment';
  const options = {
    ...handlers,
    timeStamp: String(config.timestamp || config.timeStamp || ''),
    nonceStr: config.nonceStr,
    package: config.package,
    signType: config.signType,
    paySign: config.paySign,
  };
  try {
    if (typeof uni[method] !== 'function') throw new Error('当前微信版本不支持该支付方式，请升级微信后重试');
    return uni[method](options);
  } catch (error) {
    handlers.fail?.(error);
    handlers.complete?.(error);
  }
}
