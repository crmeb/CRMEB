import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { requestMiniPayment, getMiniPaymentFailure } from '../src/utils/miniPayment.js';

// 支付 API 替身，不调起微信，也不访问业务接口。
let selected;
globalThis.uni = {
  requestPayment: options => { selected = 'ordinary'; assert.equal(options.timeStamp, '123'); },
  requestOrderPayment: options => { selected = 'mini_shop'; assert.equal(options.timeStamp, '123'); },
};
const config = { timestamp: 123, nonceStr: 'N', package: 'prepay_id=TEST', signType: 'RSA', paySign: 'TEST' };
requestMiniPayment({ ...config, paymentMode: 'v2' }, {});
assert.equal(selected, 'ordinary');
requestMiniPayment({ ...config, paymentMode: 'v3' }, {});
assert.equal(selected, 'ordinary');
requestMiniPayment({ ...config, paymentMode: 'mini_shop' }, {});
assert.equal(selected, 'mini_shop');
delete uni.requestOrderPayment;
let failed = false;
let completed = false;
requestMiniPayment({ ...config, paymentMode: 'mini_shop' }, { fail: () => { failed = true; }, complete: () => { completed = true; } });
assert.ok(failed && completed);
for (const method of ['requestPayment', 'requestOrderPayment']) {
  assert.deepEqual(getMiniPaymentFailure({ errMsg: `${method}:fail cancel` }), { canceled: true, message: 'cancel' });
  assert.deepEqual(getMiniPaymentFailure({ errMsg: `${method}:cancel` }), { canceled: true, message: 'cancel' });
  assert.deepEqual(getMiniPaymentFailure({ errMsg: `${method}:fail invalid signature` }), { canceled: false, message: 'invalid signature' });
}
assert.deepEqual(getMiniPaymentFailure({ message: '当前微信版本不支持该支付方式' }), { canceled: false, message: '当前微信版本不支持该支付方式' });
assert.deepEqual(getMiniPaymentFailure(), { canceled: false, message: '' });
for (const path of ['dynamic-pages/engines/CashierEngine.vue', 'components/payment/index.vue', 'pages/users/user_payment/index.vue']) {
  const source = await readFile(new URL(`../src/${path}`, import.meta.url), 'utf8');
  assert.ok(source.includes('failure.canceled'), `${path}: shared canceled flag`);
  assert.ok(!source.includes('failure.cancelled'), `${path}: no obsolete flag`);
}

for (const path of ['pages/users/user_payment/index.vue', 'pages/annex/vip_paid/index.vue', 'pages/annex/offline_pay/index.vue', 'pages/users/payment_on_behalf/index.vue']) {
  const source = await readFile(new URL(`../src/${path}`, import.meta.url), 'utf8');
  assert.ok(source.includes('getPayConfig'), `${path}: dynamic channel dictionary`);
  assert.ok(source.includes('weixin_v3'), `${path}: V3 supported`);
  assert.ok(!/payMode\.value\[\d\]\.payStatus/.test(source), `${path}: no channel index assumptions`);
}
const response = { data: { type: 'weixin_v3', data: { pay_type: 'WECHAT_PAY', order_id: 'czTEST', jsConfig: config } } };
const payment = response.data.result || response.data.data || response.data;
assert.equal(response.data.status || payment.pay_type, 'WECHAT_PAY');
assert.equal(payment.orderId || payment.order_id, 'czTEST');
assert.equal(payment.jsConfig, config);
console.log('WeChat frontend mode, dynamic channels, failure and recharge contract assertions passed.');
