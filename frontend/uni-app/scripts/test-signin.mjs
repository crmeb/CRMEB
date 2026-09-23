import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';
import { parse as parseSfc } from '@vue/compiler-sfc';
import { parse } from '@babel/parser';
import { computed, reactive, ref, watch, nextTick } from 'vue';

const files = {
  page: '../src/pages/users/user_sgin/index.vue',
  list: '../src/pages/users/user_sgin_list/index.vue',
  diy: '../src/subpackage/diyComponents/signIn.vue',
};
const bindings = {
  page: 'refreshSignData, goSign, changeRemind, checkSign, configReady, loading, submitting, remindUpdating, remindStatus, loadError, continuousSignDays, signCount, active, integral',
  list: 'getSignMoneList, resetSignList, signList, loading, loadend, loadError, page',
  diy: 'getSignData, signList, continuousSignDays, checkSign, signEnabled',
};
const scripts = {};
for (const [name, path] of Object.entries(files)) {
  const source = await readFile(new URL(path, import.meta.url), 'utf8');
  let executable = parseSfc(source).descriptor.scriptSetup.content;
  const imports = parse(executable, { sourceType: 'module' }).program.body.filter(node => node.type === 'ImportDeclaration');
  for (const node of imports.reverse()) executable = executable.slice(0, node.start) + executable.slice(node.end);
  scripts[name] = executable;
}

function deferred() {
  let resolve, reject;
  const promise = new Promise((success, failure) => { resolve = success; reject = failure; });
  return { promise, resolve, reject };
}

async function flush() {
  await nextTick();
  await new Promise(resolve => setImmediate(resolve));
}

function component(name) {
  const state = {
    now: new Date(2026, 8, 14, 12).getTime(), hooks: {}, timers: new Map(), events: new Map(), tips: [],
    configCalls: 0, signCalls: 0, remindCalls: 0, diyCalls: 0, monthCalls: [], emitted: [],
    config: {
      signStatus: 1, checkSign: false, signRemindSwitch: 1, signRemindStatus: 0, signMode: 1,
      continuousSignDays: 2, cumulativeSignDays: 2, nextContinuousDays: 1, nextCumulativeDays: 3,
      signGivePoint: 10, signList: [[{ is_sign: false, type: 1, point: 10 }]],
    },
    monthPages: {},
  };
  const store = { isLogin: ref(true), uid: ref(1) };
  const props = reactive({ isPageVisible: true, dataConfig: {} });
  const stops = [];
  let timerId = 0;
  class Clock extends Date {
    constructor(...args) { super(...(args.length ? args : [state.now])); }
    static now() { return state.now; }
  }
  const hook = name => fn => { (state.hooks[name] ||= []).push(fn); };
  const response = () => ({ data: structuredClone(state.config) });
  const sandbox = {
    ref, computed, watch: (...args) => { const stop = watch(...args); stops.push(stop); return stop; },
    onLoad: hook('load'), onShow: hook('show'), onHide: hook('hide'), onUnload: hook('unload'),
    onReachBottom: hook('bottom'), onMounted: hook('mount'), onBeforeUnmount: hook('unmount'),
    defineProps: () => props, defineOptions: () => {}, storeToRefs: value => value, useAppStore: () => store,
    useColorTheme: () => ({ colorStyle: ref('') }), HTTP_REQUEST_URL: '',
    toLogin: () => { state.loggedOut = true; }, i18n: { t: value => value },
    util: { Tips: value => state.tips.push(value), SplitArray: (list, previous) => previous.concat(list) },
    colorChange: async () => ({ data: { status: 1 } }),
    getSignConfig: () => { state.configCalls++; return state.configHandler ? state.configHandler() : Promise.resolve(response()); },
    getSignListApi: async () => ({ data: [] }),
    setSignIntegral: () => {
      state.signCalls++;
      if (state.signHandler) return state.signHandler();
      state.config.checkSign = true;
      return Promise.resolve({ data: { integral: 10 } });
    },
    changeRemindStatus: status => {
      state.remindCalls++;
      if (state.remindHandler) return state.remindHandler(status);
      state.config.signRemindStatus = status;
      return Promise.resolve({ data: {} });
    },
    getSignMonthList: params => {
      state.monthCalls.push({ ...params });
      return state.monthHandler ? state.monthHandler(params) : Promise.resolve({ data: state.monthPages[params.page] || [] });
    },
    getSign: () => { state.diyCalls++; return state.diyHandler ? state.diyHandler() : Promise.resolve(response()); },
    Date: Clock,
    setTimeout: (fn, delay) => { const id = ++timerId; state.timers.set(id, { fn, delay }); return id; },
    clearTimeout: id => state.timers.delete(id),
    uni: {
      setNavigationBarColor: () => {}, navigateTo: () => {},
      $on: (event, handler) => { const listeners = state.events.get(event) || new Set(); listeners.add(handler); state.events.set(event, listeners); },
      $off: (event, handler) => state.events.get(event)?.delete(handler),
      $emit: (event, payload) => { state.emitted.push({ event, payload }); for (const handler of state.events.get(event) || []) handler(payload); },
    },
  };
  vm.createContext(sandbox);
  vm.runInContext(scripts[name] + `\nglobalThis.testApi = { ${bindings[name]} };`, sandbox);
  const run = name => { for (const fn of state.hooks[name] || []) fn(); };
  const runTimer = () => {
    const [id, timer] = state.timers.entries().next().value;
    state.timers.delete(id);
    timer.fn();
  };
  const dispose = () => { run('unmount'); run('unload'); for (const stop of stops) stop(); };
  return { state, store, props, api: sandbox.testApi, run, runTimer, dispose, emit: sandbox.uni.$emit };
}

{
  const test = component('page');
  const { state, api, run } = test;
  const initial = deferred();
  state.configHandler = () => initial.promise;
  run('load');
  run('show');
  run('show');
  await api.goSign();
  assert.equal(state.configCalls, 1, 'Initial load and show do not duplicate the configuration request');
  assert.equal(state.signCalls, 0, 'Signing is blocked before the authoritative configuration is available');
  initial.resolve({ data: structuredClone(state.config) });
  await api.refreshSignData();
  state.configHandler = null;
  assert.equal(api.configReady.value, true);

  const signing = deferred();
  state.signHandler = () => signing.promise;
  const first = api.goSign();
  await api.goSign();
  assert.equal(state.signCalls, 1, 'Double taps send only one sign mutation');
  assert.equal(api.submitting.value, true);
  state.config.checkSign = true;
  signing.resolve({ data: { integral: 25 } });
  await first;
  assert.equal(api.checkSign.value, true);
  assert.equal(api.integral.value, 25, 'The existing integral response contract is preserved');
  assert.equal(api.active.value, true);
  assert.equal(state.emitted[0].event, 'signInRefresh');
  await api.goSign();
  assert.equal(state.signCalls, 1, 'A confirmed daily sign prevents another mutation');

  state.config.checkSign = false;
  await api.refreshSignData();
  state.signHandler = async () => { throw new Error('Sign timeout'); };
  await api.goSign();
  assert.equal(api.submitting.value, false, 'Failed submissions release the lock');
  assert.equal(state.tips.at(-1).title, 'Sign timeout');
  state.signHandler = null;

  const reminder = deferred();
  state.remindHandler = () => reminder.promise;
  const change = api.changeRemind({ detail: { value: true } });
  assert.equal(api.remindStatus.value, true, 'The reminder uses an optimistic controlled switch');
  await api.changeRemind({ detail: { value: false } });
  assert.equal(state.remindCalls, 1, 'Pending reminder changes are not duplicated');
  reminder.reject(new Error('Reminder timeout'));
  await change;
  assert.equal(api.remindStatus.value, false, 'A failed reminder change restores the last server value');
  assert.equal(api.remindUpdating.value, false);

  const successfulReminder = deferred();
  state.remindHandler = () => successfulReminder.promise;
  const updatingReminder = api.changeRemind({ detail: { value: true } });
  const staleReminderConfig = deferred();
  state.configHandler = () => staleReminderConfig.promise;
  const staleRefresh = api.refreshSignData();
  const reminderSnapshot = structuredClone(state.config);
  state.config.signRemindStatus = 1;
  successfulReminder.resolve({ data: {} });
  await updatingReminder;
  staleReminderConfig.resolve({ data: reminderSnapshot });
  await staleRefresh;
  assert.equal(api.remindStatus.value, true, 'A configuration response started during the mutation cannot undo the successful reminder change');

  state.configHandler = async () => { throw new Error('Config timeout'); };
  await api.refreshSignData();
  assert.equal(api.configReady.value, false);
  assert.equal(api.loadError.value, 'Config timeout');
  const signCalls = state.signCalls;
  await api.goSign();
  assert.equal(state.signCalls, signCalls, 'Unknown server state never allows signing');
  state.configHandler = null;
  await api.refreshSignData();
  assert.equal(api.loadError.value, '');

  state.config.checkSign = true;
  await api.refreshSignData();
  state.now = new Date(2026, 8, 15, 0, 0, 1).getTime();
  state.config.checkSign = false;
  test.runTimer();
  await flush();
  assert.equal(api.checkSign.value, false, 'Midnight refresh unlocks the next day');
  run('hide');
  assert.equal(state.timers.size, 0, 'Hidden pages stop their midnight timer');
  run('show');
  await flush();
  assert.equal(state.timers.size, 1, 'Showing the page rearms exactly one timer');

  const oldAccount = deferred();
  state.configHandler = () => {
    state.configHandler = null;
    return oldAccount.promise;
  };
  const oldRequest = api.refreshSignData(true);
  state.config.continuousSignDays = 1;
  test.store.uid.value = 2;
  await flush();
  oldAccount.resolve({ data: { ...state.config, continuousSignDays: 999, checkSign: true } });
  await oldRequest;
  assert.equal(api.continuousSignDays.value, 1, 'Late responses from the old account cannot replace the new account');
  assert.equal(api.checkSign.value, false);
  test.dispose();
  assert.equal(state.timers.size, 0, 'Unloaded pages release their timer');
}

{
  const test = component('list');
  const { state, api, run } = test;
  state.monthPages = {
    1: Array.from({ length: 8 }, (_, index) => ({ month: `month-${index}`, list: [] })),
    2: [{ month: 'oldest-month', list: [] }],
  };
  run('show');
  await flush();
  await api.getSignMoneList();
  assert.deepEqual(state.monthCalls.map(value => value.page), [1, 2], 'The next page is requested instead of appending page one again');
  assert.equal(api.signList.value.length, 9);
  assert.equal(api.loadend.value, true);
  await api.getSignMoneList();
  assert.equal(state.monthCalls.length, 2, 'A final partial month page stops pagination');

  api.resetSignList();
  state.monthHandler = async () => { throw new Error('List timeout'); };
  await api.getSignMoneList();
  assert.equal(api.page.value, 1, 'A failed page remains available for retry');
  assert.equal(api.loading.value, false);
  state.monthHandler = null;
  await api.getSignMoneList();
  assert.equal(api.page.value, 2);

  api.resetSignList();
  const oldAccount = deferred();
  state.monthHandler = () => {
    state.monthHandler = null;
    return oldAccount.promise;
  };
  const oldRequest = api.getSignMoneList();
  state.monthPages[1] = [{ month: 'new-account', list: [] }];
  test.store.uid.value = 2;
  await flush();
  oldAccount.resolve({ data: [{ month: 'old-account', list: [] }] });
  await oldRequest;
  assert.equal(api.signList.value[0].month, 'new-account', 'Account changes reset months and ignore old pending responses');
  assert.equal(api.page.value, 2);
  test.store.isLogin.value = false;
  await flush();
  assert.equal(api.signList.value.length, 0, 'Logging out clears private sign history');
  assert.equal(api.page.value, 1);
  test.dispose();
}

{
  const test = component('diy');
  const { state, api, run, emit, props } = test;
  const initial = deferred();
  state.diyHandler = () => initial.promise;
  run('mount');
  emit('signInRefresh');
  assert.equal(state.diyCalls, 1, 'Mount and page visibility refresh share an in-flight request');
  initial.resolve({ data: structuredClone(state.config) });
  await api.getSignData();
  state.diyHandler = null;
  assert.equal(api.signEnabled.value, true);

  const oldCalendar = deferred();
  state.diyHandler = () => {
    state.diyHandler = null;
    return oldCalendar.promise;
  };
  const oldRequest = api.getSignData();
  state.config.continuousSignDays = 3;
  state.config.checkSign = true;
  state.config.signList[0][0].is_sign = true;
  emit('signInRefresh', { signed: true });
  await flush();
  oldCalendar.resolve({ data: { ...state.config, continuousSignDays: 2, checkSign: false } });
  await oldRequest;
  assert.equal(api.continuousSignDays.value, 3, 'Sign success replaces a stale in-flight calendar');
  assert.equal(api.checkSign.value, true);
  assert.equal(api.signList.value[0].is_sign, true);

  props.isPageVisible = false;
  await flush();
  assert.equal(state.timers.size, 0, 'Cached hidden DIY pages stop their timer');
  const beforeSwitch = state.diyCalls;
  state.config.continuousSignDays = 0;
  state.config.checkSign = false;
  test.store.uid.value = 2;
  await flush();
  assert.equal(api.signEnabled.value, false, 'Hidden old-account data is cleared immediately');
  assert.equal(state.diyCalls, beforeSwitch, 'Hidden account changes defer fetching until the page returns');
  state.now = new Date(2026, 8, 15, 0, 0, 1).getTime();
  props.isPageVisible = true;
  await flush();
  assert.equal(api.continuousSignDays.value, 0);
  assert.equal(api.checkSign.value, false, 'Returning after midnight gets the new day for the active account');
  assert.equal(state.timers.size, 1);
  state.now = new Date(2026, 8, 16, 0, 0, 1).getTime();
  const beforeMidnight = state.diyCalls;
  test.runTimer();
  await flush();
  assert.equal(state.diyCalls, beforeMidnight + 1, 'Visible DIY pages refresh at midnight');

  state.diyHandler = async () => ({ data: [] });
  await api.getSignData();
  assert.equal(api.signEnabled.value, false, 'An unavailable plugin response is safe and hides the sign block');
  state.diyHandler = async () => { throw new Error('DIY timeout'); };
  await api.getSignData();
  assert.equal(api.signEnabled.value, false, 'DIY failures are handled without an unhandled rejection');
  test.dispose();
  assert.equal(state.events.get('signInRefresh').size, 0, 'The exact registered refresh listener is removed on unmount');
  assert.equal(state.timers.size, 0);
}

console.log('Sign-in mobile checks passed: initialization, double tap, authoritative daily state, failure retry, reminder rollback, midnight, visibility, account isolation, month pagination and DIY refresh.');
