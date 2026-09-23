import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { parse as parseSfc } from '@vue/compiler-sfc';
import { parse } from '@babel/parser';
import { computed, ref } from 'vue';

const require = createRequire(import.meta.url);
const { preprocess } = require('@dcloudio/uni-cli-shared/lib/preprocess/lib/preprocess.js');
const source = await readFile(new URL('../src/pages/users/user_spread_money/receiving.vue', import.meta.url), 'utf8');
const script = parseSfc(source).descriptor.scriptSetup.content;

function page(platform = 'MP-WEIXIN') {
  const platforms = { MP_WEIXIN: false, H5: false, APP_PLUS: false, [platform.replaceAll('-', '_')]: true };
  let executable = preprocess(script, platforms, { type: 'js' });
  const imports = parse(executable, { sourceType: 'module' }).program.body.filter(node => node.type === 'ImportDeclaration');
  for (const node of imports.reverse()) executable = executable.slice(0, node.start) + executable.slice(node.end);
  const state = { next: null, checks: 0, requests: [], tips: [], supported: true, miniApp: 'A' };
  const sandbox = {
    ref, computed, onLoad: fn => { state.load = fn; }, onShow: fn => { state.show = fn; },
    storeToRefs: store => store, useAppStore: () => ({ isLogin: ref(true) }),
    useColorTheme: () => ({ colorStyle: ref('') }), toLogin: () => { state.loggedOut = true; },
    checkExtractTransferApi: async () => {
      ++state.checks;
      if (state.next instanceof Error) throw state.next;
      return { data: state.next };
    },
    transferInfoApi: async () => ({ data: state.next }),
    util: { Tips: value => state.tips.push(value) }, i18n: { t: value => value },
    routes: { withdrawalList: '/withdrawals', lotteryRecord: '/lottery' },
    uni: { showToast: value => state.tips.push(value), reLaunch: value => { state.navigation = value; } },
    wx: {
      canIUse: () => state.supported, getAccountInfoSync: () => ({ miniProgram: { appId: state.miniApp } }),
      requestMerchantTransfer: options => state.requests.push(options),
    },
    Auth: { isWeixin: () => true },
    WeixinJSBridge: { invoke: (method, config, callback) => state.requests.push({ method, config, callback }) },
  };
  vm.createContext(sandbox);
  vm.runInContext(executable + '\nglobalThis.testPage = { getInfo, confrimTap, showBack, isFailed, canReceive, loading, submitting };', sandbox);
  return { state, api: sandbox.testPage };
}
const valid = { status: 0, state: 'WAIT_USER_CONFIRM', can_receive: true, package_info: 'P', mchid: 'M', wechat_appid: 'A' };
const pending = { ...valid, state: 'TRANSFERING', can_receive: false, package_info: '' };
const failed = { ...valid, status: -1, state: 'FAIL', can_receive: false, package_info: '' };
const success = { ...valid, status: 1, state: 'SUCCESS', can_receive: false, package_info: '' };

const { state, api } = page();
state.next = valid;
await api.getInfo();
assert.equal(api.canReceive.value, true);
await api.confrimTap();
assert.equal(state.checks, 2, 'Receipt is rechecked after opening the page');
assert.equal(state.requests.length, 1);
assert.equal(state.requests[0].mchId, 'M');
assert.equal(state.requests[0].package, 'P');
await api.confrimTap();
assert.equal(state.requests.length, 1, 'Repeated taps do not invoke WeChat twice');
state.next = pending;
await state.requests[0].success();
assert.equal(api.showBack.value, false, 'Client success is not proof of arrival');
assert.equal(api.canReceive.value, false);
assert.equal(api.submitting.value, false);
state.next = success;
await api.getInfo();
assert.equal(api.showBack.value, true, 'Only server-confirmed arrival shows success');

for (const result of [failed, new Error('TEST timeout'), pending]) {
  const test = page();
  test.state.next = valid;
  await test.api.getInfo();
  test.state.next = result;
  await test.api.confrimTap();
  assert.equal(test.state.requests.length, 0, 'Invalid or unknown latest state never invokes receipt');
  assert.equal(test.api.canReceive.value, false);
  assert.equal(test.api.submitting.value, false);
  if (result === failed) assert.equal(test.api.isFailed.value, true);
}
for (const scenario of ['unsupported', 'wrongApp', 'cancel']) {
  const test = page();
  test.state.next = valid;
  await test.api.getInfo();
  if (scenario === 'unsupported') test.state.supported = false;
  if (scenario === 'wrongApp') test.state.miniApp = 'WRONG';
  await test.api.confrimTap();
  if (scenario === 'cancel') test.state.requests[0].fail({ errMsg: 'requestMerchantTransfer:fail cancel' });
  else assert.equal(test.state.requests.length, 0);
  assert.equal(test.api.submitting.value, false);
}
const h5 = page('H5');
h5.state.next = valid;
await h5.api.getInfo();
await h5.api.confrimTap();
assert.equal(h5.state.requests[0].method, 'requestMerchantTransfer');
assert.equal(h5.state.requests[0].config.appId, 'A');
h5.state.next = pending;
h5.state.requests[0].callback({ err_msg: 'requestMerchantTransfer:ok' });
await new Promise(resolve => setImmediate(resolve));
assert.equal(h5.api.showBack.value, false, 'H5 also waits for server-confirmed arrival');
console.log('WeChat withdrawal page checks passed: recheck, expiry, unknown result, double tap, verified arrival, cancellation, app identity and H5.');
