import assert from 'node:assert/strict';
import { getRefundIntegral } from '../src/utils/refundIntegral.js';

const ordinary = { cart_num: 3, deduction_integral_total: 100, refunded_num: 0 };
assert.equal(getRefundIntegral(ordinary, 3), 100);
assert.equal(getRefundIntegral(ordinary, 1), 33);
assert.equal(getRefundIntegral({ ...ordinary, refunded_num: 1 }, 1), 33);
assert.equal(getRefundIntegral({ ...ordinary, refunded_num: 2 }, 1), 34);
assert.equal(getRefundIntegral({ cart_num: 1, integral_unit_price: 50 }, 1), 50);
assert.equal(getRefundIntegral({}, 1), 0);
assert.equal(getRefundIntegral(ordinary, 0), 0);
console.log('Ordinary deduction-point and integral-activity refund contract assertions passed.');
