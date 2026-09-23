const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const babel = require('@babel/parser');
const vue = require('@vue/compiler-sfc');
const { baseParse } = require('@vue/compiler-dom');
const postcss = require('postcss');

const root = path.resolve(__dirname, '..');
const printerIndex = 'src/pages/driver/printer/index.vue';
const printerEdit = 'src/pages/driver/printer/edit.vue';
const printerContent = 'src/pages/driver/printer/content.vue';
const driverIndex = 'src/pages/driver/printerDriver/index.vue';
const driverEdit = 'src/pages/driver/printerDriver/edit.vue';
const files = [printerIndex, printerEdit, printerContent, driverIndex, driverEdit];
const scripts = new Map();
const tests = [];
const ref = (value) => ({ value });

function test(name, run) {
  tests.push({ name, run });
}

function source(file) {
  if (!scripts.has(file)) {
    const text = fs.readFileSync(path.join(root, file), 'utf8');
    scripts.set(file, file.endsWith('.vue') ? vue.parse(text).descriptor.scriptSetup.content : text);
  }
  return scripts.get(file);
}

function extract(file, name) {
  const text = source(file);
  const ast = babel.parse(text, { sourceType: 'module' });
  for (const node of ast.program.body) {
    const declaration = node.type === 'ExportNamedDeclaration' ? node.declaration : node;
    if (declaration?.type === 'FunctionDeclaration' && declaration.id.name === name) {
      return text.slice(declaration.start, declaration.end);
    }
  }
  throw new Error(`Missing function ${file}:${name}`);
}

function extractVariable(file, name) {
  const text = source(file);
  const ast = babel.parse(text, { sourceType: 'module' });
  const declaration = ast.program.body.find(
    (node) => node.type === 'VariableDeclaration' && node.declarations.some((value) => value.id.name === name),
  );
  assert.ok(declaration, `Missing variable ${file}:${name}`);
  return text.slice(declaration.start, declaration.end);
}

function findElement(node, matches) {
  if (node.type === 1 && matches(node)) return node;
  for (const child of node.children || []) {
    const found = findElement(child, matches);
    if (found) return found;
  }
  return null;
}

function context(functions, state = {}) {
  const messages = [];
  const sandbox = vm.createContext({
    URL,
    hasPermission: () => true,
    ElMessage: {
      success: (message) => messages.push({ type: 'success', message }),
      warning: (message) => messages.push({ type: 'warning', message }),
      error: (message) => messages.push({ type: 'error', message }),
    },
    ...state,
  });
  vm.runInContext(functions.map(([file, name]) => extract(file, name)).join('\n'), sandbox);
  return { sandbox, messages };
}

function deferred() {
  let resolve;
  let reject;
  const promise = new Promise((res, rej) => {
    resolve = res;
    reject = rej;
  });
  return { promise, resolve, reject };
}

function editState(overrides = {}) {
  return {
    props: { isAdd: true, driverId: 1 },
    saveAuth: ref('admin-printer-create'),
    loaded: ref(true),
    editLoading: ref(false),
    submitLoading: ref(false),
    formRef: ref({ validate: () => Promise.resolve(true) }),
    editForm: ref({ print_name: ' Receipt ', printer_id: ' SN-1 ', id: 1 }),
    emit: () => {},
    ...overrides,
  };
}

for (const file of files) {
  test(`Vue compile ${file}`, () => {
    const filename = path.join(root, file);
    const { descriptor, errors } = vue.parse(fs.readFileSync(filename, 'utf8'), { filename });
    assert.equal(errors.length, 0);
    const script = vue.compileScript(descriptor, { id: 'printer-regression' });
    const result = vue.compileTemplate({
      source: descriptor.template.content,
      filename,
      id: 'printer-regression',
      compilerOptions: { bindingMetadata: script.bindings },
    });
    assert.equal(result.errors.length, 0, JSON.stringify(result.errors));
  });
}

test('Notice textarea derives width from a constrained block wrapper', () => {
  const { descriptor } = vue.parse(fs.readFileSync(path.join(root, printerContent), 'utf8'));
  const ast = baseParse(descriptor.template.content);
  const wrapper = findElement(ast, (node) =>
    node.props.some((prop) => prop.type === 6 && prop.name === 'class' && prop.value.content === 'notice-field'),
  );
  assert.ok(wrapper, 'Notice must have a block width constraint');
  const input = findElement(wrapper, (node) => node.tag === 'el-input');
  assert.ok(input, 'Notice wrapper must contain the textarea');
  assert.ok(input.props.some((prop) => prop.type === 6 && prop.name === 'type' && prop.value.content === 'textarea'));
  const declarations = new Map();
  postcss.parse(descriptor.styles[0].content).walkRules((rule) => {
    if (!['.notice-field', '.notice-input'].includes(rule.selector)) return;
    rule.walkDecls((decl) => declarations.set(`${rule.selector}:${decl.prop}`, decl.value));
  });
  assert.equal(declarations.get('.notice-field:width'), '100%');
  assert.equal(declarations.get('.notice-field:max-width'), '500px');
  assert.equal(declarations.get('.notice-field:min-width'), '0');
  assert.equal(declarations.get('.notice-input:width'), '100%');
});

for (const file of [printerEdit, driverEdit]) {
  test(`${file} switches to full-width stacked labels on mobile`, () => {
    const ast = baseParse(vue.parse(fs.readFileSync(path.join(root, file), 'utf8')).descriptor.template.content);
    const form = findElement(ast, (node) => node.tag === 'el-form');
    for (const [attribute, variable] of [
      ['label-width', 'labelWidth'],
      ['label-position', 'labelPosition'],
    ]) {
      assert.ok(
        form.props.some(
          (prop) =>
            prop.type === 7 &&
            prop.name === 'bind' &&
            prop.arg?.content === attribute &&
            prop.exp?.content === variable,
        ),
        `Form must bind responsive ${attribute}`,
      );
    }
    const mediaStore = { isMobile: true };
    const sandbox = vm.createContext({
      mediaStore,
      computed: (run) => ({
        get value() {
          return run();
        },
      }),
    });
    vm.runInContext(extractVariable(file, 'labelWidth') + '\n' + extractVariable(file, 'labelPosition'), sandbox);
    assert.equal(vm.runInContext('labelWidth.value', sandbox), undefined);
    assert.equal(vm.runInContext('labelPosition.value', sandbox), 'top');
    mediaStore.isMobile = false;
    assert.equal(vm.runInContext('labelWidth.value', sandbox), 120);
    assert.equal(vm.runInContext('labelPosition.value', sandbox), 'right');
  });
}

test('Scoped printer permissions deny empty roles and preserve superadmin access', () => {
  const store = { userInfo: { level: 1 }, access: [] };
  const { sandbox } = context(
    [
      ['src/libs/auth.js', 'includeArray'],
      ['src/pages/driver/printer/usePrinterAuth.js', 'usePrinterAuth'],
    ],
    { useUserInfoStore: () => store },
  );
  const { hasPermission } = sandbox.usePrinterAuth();
  assert.equal(hasPermission('admin-printer-delete'), false);
  store.access = ['admin-printer-edit'];
  assert.equal(hasPermission('admin-printer-edit'), true);
  assert.equal(hasPermission('admin-printer-delete'), false);
  store.userInfo = { level: '0' };
  store.access = [];
  assert.equal(hasPermission('admin-printer-delete'), true);
  store.userInfo = null;
  assert.equal(hasPermission('admin-printer-delete'), false);
});

test('Delete confirmation sends exactly one valid DELETE request', async () => {
  const requests = [];
  let refreshes = 0;
  const { sandbox } = context(
    [
      ['src/api/common.js', 'tableDelApi'],
      ['src/utils/public.js', 'modalSure'],
      [printerIndex, 'handleDelete'],
    ],
    {
      h: () => ({}),
      ElMessageBox: () => Promise.resolve(),
      request: (data) => {
        requests.push(data);
        return Promise.resolve({ msg: 'Deleted' });
      },
      getList: () => refreshes++,
    },
  );
  await sandbox.handleDelete({ id: 42 }, 3);
  assert.equal(requests.length, 1);
  assert.equal(requests[0].url, 'driver/printer_channel/del/42');
  assert.equal(requests[0].method.toLowerCase(), 'delete');
  assert.equal(refreshes, 1);
});

test('Delete cancellation and missing permission send no request', async () => {
  let requests = 0;
  const { sandbox } = context(
    [
      ['src/api/common.js', 'tableDelApi'],
      ['src/utils/public.js', 'modalSure'],
      [printerIndex, 'handleDelete'],
    ],
    {
      h: () => ({}),
      ElMessageBox: () => Promise.reject('cancel'),
      request: () => requests++,
      getList: () => {},
    },
  );
  sandbox.handleDelete({ id: 1 }, 0);
  await new Promise((resolve) => setImmediate(resolve));
  assert.equal(requests, 0);
  sandbox.hasPermission = () => false;
  assert.equal(sandbox.handleDelete({ id: 1 }, 0), undefined);
  assert.equal(requests, 0);
});

test('Delete request failure is reported without refreshing', async () => {
  const { sandbox, messages } = context([[printerIndex, 'handleDelete']], {
    modalSure: () => Promise.reject({ msg: 'Denied' }),
    getList: () => assert.fail('Failed deletion must not refresh'),
  });
  await sandbox.handleDelete({ id: 1 }, 0);
  assert.equal(messages[0].type, 'error');
  assert.equal(messages[0].message, 'Denied');
});

test('Integral receipt API uses POST for the physical print action', () => {
  let request;
  const { sandbox } = context([['src/pages/plugin/integral/api.js', 'integralOrderPrint']], {
    request: (data) => {
      request = data;
    },
  });
  sandbox.integralOrderPrint(42);
  assert.equal(request.url, 'marketing/integral/order/print/42');
  assert.equal(request.method.toLowerCase(), 'post');
});

for (const [file, name, api] of [
  [printerIndex, 'handleStatusChange', 'printerChannelSetStatusApi'],
  [driverIndex, 'changeStatus', 'printerDriverSetStatusApi'],
]) {
  test(`${name} locks repeated changes and rolls back failed requests`, async () => {
    const pending = deferred();
    const row = { id: 7, status: 0 };
    let requests = 0;
    const { sandbox, messages } = context([[file, name]], {
      statusLoading: ref({}),
      [api]: () => {
        requests++;
        return pending.promise;
      },
    });
    const first = sandbox[name](row);
    sandbox[name](row);
    assert.equal(requests, 1);
    assert.equal(sandbox.statusLoading.value[7], true);
    pending.reject({ msg: 'Offline' });
    await first;
    assert.equal(row.status, 1);
    assert.equal(sandbox.statusLoading.value[7], false);
    assert.equal(messages[0].type, 'error');
  });

  test(`${name} rejects unauthorized status changes`, () => {
    const row = { id: 7, status: 0 };
    const { sandbox } = context([[file, name]], {
      hasPermission: () => false,
      statusLoading: ref({}),
      [api]: () => assert.fail('Unauthorized status mutation'),
    });
    sandbox[name](row);
    assert.equal(row.status, 1);
  });
}

test('Adding a printer waits for driver choices and survives loading failures', async () => {
  const pending = deferred();
  const { sandbox, messages } = context(
    [
      [printerIndex, 'loadDriverOptions'],
      [printerIndex, 'handleAdd'],
    ],
    {
      driverLoading: ref(false),
      driverOptions: ref([]),
      drawerVisible: ref(false),
      isAddMode: ref(false),
      currentDriverId: ref(1),
      printerChannelDriversApi: () => pending.promise,
    },
  );
  const first = sandbox.handleAdd();
  assert.equal(sandbox.drawerVisible.value, false);
  await sandbox.handleAdd();
  pending.reject({ msg: 'Unavailable' });
  await first;
  assert.equal(sandbox.driverLoading.value, false);
  assert.equal(sandbox.drawerVisible.value, false);
  assert.equal(messages[0].type, 'error');
  sandbox.printerChannelDriversApi = () => Promise.resolve({ data: [{ name: 'FeiEYun' }] });
  await sandbox.handleAdd();
  assert.equal(sandbox.drawerVisible.value, true);
  assert.equal(sandbox.isAddMode.value, true);
  assert.equal(sandbox.currentDriverId.value, null);
});

test('Device save locks before validation and preserves failure state', async () => {
  const validation = deferred();
  let validates = 0;
  let requests = 0;
  let successes = 0;
  const { sandbox, messages } = context(
    [[printerEdit, 'handleSubmit']],
    editState({
      formRef: ref({ validate: () => (validates++, validation.promise) }),
      printerChannelSaveApi: (data) => {
        requests++;
        assert.equal(data.print_name, 'Receipt');
        assert.equal(data.printer_id, 'SN-1');
        return Promise.reject({ msg: 'Invalid config' });
      },
      emit: (event) => {
        if (event === 'success') successes++;
      },
    }),
  );
  const first = sandbox.handleSubmit();
  await sandbox.handleSubmit();
  assert.equal(validates, 1);
  validation.resolve(true);
  await first;
  assert.equal(requests, 1);
  assert.equal(successes, 0);
  assert.equal(sandbox.submitLoading.value, false);
  assert.equal(messages[0].type, 'error');
});

test('Device save requires loaded detail, permission and valid form', async () => {
  const { sandbox } = context(
    [[printerEdit, 'handleSubmit']],
    editState({
      loaded: ref(false),
      printerChannelSaveApi: () => assert.fail('Unexpected device save'),
    }),
  );
  await sandbox.handleSubmit();
  sandbox.loaded.value = true;
  sandbox.hasPermission = () => false;
  await sandbox.handleSubmit();
  sandbox.hasPermission = () => true;
  sandbox.formRef.value.validate = () => Promise.reject({});
  await sandbox.handleSubmit();
  assert.equal(sandbox.submitLoading.value, false);
});

test('Driver detail failure cannot overwrite credentials', async () => {
  const { sandbox, messages } = context(
    [
      [driverEdit, 'getDetail'],
      [driverEdit, 'submit'],
    ],
    {
      props: { driverId: 1 },
      loading: ref(false),
      loaded: ref(false),
      submitLoading: ref(false),
      formRef: ref({ validate: () => Promise.resolve(true) }),
      form: ref({ status: 0 }),
      fields: ref([]),
      printerDriverDetailApi: () => Promise.reject({ msg: 'Unavailable' }),
      printerDriverSaveConfigApi: () => assert.fail('Unexpected credential save'),
    },
  );
  await sandbox.getDetail();
  await sandbox.submit();
  assert.equal(sandbox.loading.value, false);
  assert.equal(sandbox.loaded.value, false);
  assert.equal(messages[0].type, 'error');
});

test('Driver save preserves masked values and blocks repeated submissions', async () => {
  const pending = deferred();
  let requests = 0;
  let successes = 0;
  const { sandbox } = context([[driverEdit, 'submit']], {
    props: { driverId: 1 },
    loading: ref(false),
    loaded: ref(true),
    submitLoading: ref(false),
    formRef: ref({ validate: () => Promise.resolve(true) }),
    form: ref({ status: 1, fey_ukey: '********' }),
    fields: ref([{ field: 'fey_ukey' }]),
    printerDriverSaveConfigApi: (id, data) => {
      requests++;
      assert.equal(data.fey_ukey, '********');
      return pending.promise;
    },
    emit: (event) => {
      if (event === 'success') successes++;
    },
  });
  const first = sandbox.submit();
  await sandbox.submit();
  pending.resolve({});
  await first;
  assert.equal(requests, 1);
  assert.equal(successes, 1);
  assert.equal(sandbox.submitLoading.value, false);
});

test('Driver validation follows required and switch metadata', () => {
  const { sandbox } = context([[driverEdit, 'configRules']]);
  assert.equal(sandbox.configRules({ label: 'Secret' }).length, 1);
  assert.equal(sandbox.configRules({ label: 'Optional', required: false }).length, 0);
  assert.equal(sandbox.configRules({ label: 'Switch', type: 'switch' }).length, 0);
});

test('QR preview uses storefront for relative links and preserves absolute links', () => {
  const { sandbox } = context([[printerContent, 'resolveCodeUrl']], { siteUrl: ref('https://shop.example/h5/') });
  assert.equal(sandbox.resolveCodeUrl('/pages/index/index?x=1'), 'https://shop.example/h5/pages/index/index?x=1');
  assert.equal(sandbox.resolveCodeUrl('pages/index/index'), 'https://shop.example/h5/pages/index/index');
  assert.equal(sandbox.resolveCodeUrl(' https://outside.example/p?q=1 '), 'https://outside.example/p?q=1');
  assert.equal(sandbox.resolveCodeUrl('http://outside.example/p'), 'http://outside.example/p');
  for (const value of ['', 'https://', 'javascript:alert(1)', 'ftp://outside.example', '//outside.example']) {
    assert.equal(sandbox.resolveCodeUrl(value), '');
  }
  for (const value of ['', 'ftp://shop.example', 'https://']) {
    sandbox.siteUrl.value = value;
    assert.equal(sandbox.resolveCodeUrl('/pages/index/index'), '');
  }
});

test('Template load keeps defaults and excludes preview metadata from saves', async () => {
  const pending = deferred();
  let requests = 0;
  const { sandbox } = context(
    [
      [printerContent, 'getPrintContent'],
      [printerContent, 'resolveCodeUrl'],
      [printerContent, 'save'],
    ],
    {
      id: 1,
      loading: ref(false),
      loaded: ref(false),
      submitLoading: ref(false),
      siteUrl: ref(''),
      formItem: ref({ goods: [0], pay: [0, 1], order: [0, 1], code: 0, code_url: '' }),
      printerChannelContentApi: () => Promise.resolve({ data: { site_url: 'https://shop.example', header: 1 } }),
      printerChannelSaveContentApi: (id, data) => {
        requests++;
        assert.equal('site_url' in data, false);
        assert.equal(data.goods[0], 0);
        return pending.promise;
      },
    },
  );
  await sandbox.getPrintContent();
  assert.equal(sandbox.siteUrl.value, 'https://shop.example');
  assert.equal(sandbox.loaded.value, true);
  const first = sandbox.save();
  await sandbox.save();
  assert.equal(requests, 1);
  pending.resolve({ msg: 'Saved' });
  await first;
  assert.equal(sandbox.submitLoading.value, false);
});

test('Template failure, missing permission and invalid QR never submit', async () => {
  const { sandbox, messages } = context(
    [
      [printerContent, 'getPrintContent'],
      [printerContent, 'resolveCodeUrl'],
      [printerContent, 'save'],
    ],
    {
      id: 1,
      loading: ref(false),
      loaded: ref(false),
      submitLoading: ref(false),
      siteUrl: ref(''),
      formItem: ref({ code: 1, code_url: '/pages/index/index' }),
      printerChannelContentApi: () => Promise.reject({ msg: 'Unavailable' }),
      printerChannelSaveContentApi: () => assert.fail('Unexpected template save'),
    },
  );
  await sandbox.getPrintContent();
  await sandbox.save();
  assert.equal(sandbox.loaded.value, false);
  sandbox.loaded.value = true;
  await sandbox.save();
  assert.equal(messages.at(-1).type, 'warning');
  sandbox.hasPermission = () => false;
  await sandbox.save();
});

test('QR rendering tolerates an unmounted preview and clears disabled QR', () => {
  let draws = 0;
  function QRCode() {
    draws++;
  }
  QRCode.CorrectLevel = { H: 2 };
  const { sandbox } = context([[printerContent, 'drawCode']], {
    qrcodeRef: ref(null),
    formItem: ref({ code: 1 }),
    QRCode,
  });
  sandbox.drawCode('https://shop.example');
  assert.equal(draws, 0);
  sandbox.qrcodeRef.value = { innerHTML: 'stale' };
  sandbox.formItem.value.code = 0;
  sandbox.drawCode('https://shop.example');
  assert.equal(sandbox.qrcodeRef.value.innerHTML, '');
  assert.equal(draws, 0);
  sandbox.formItem.value.code = 1;
  sandbox.drawCode('https://shop.example');
  assert.equal(draws, 1);
});

async function main() {
  for (const { name, run } of tests) {
    await run();
    console.log(`PASS ${name}`);
  }
  console.log(`Printer frontend regression: ${tests.length} checks passed; no external requests sent.`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
