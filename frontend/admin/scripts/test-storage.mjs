import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';
import { parse as parseJavaScript } from '@babel/parser';
import { compileScript, compileTemplate, parse } from '@vue/compiler-sfc';
import { reactive, ref } from 'vue';

// 运行真实页面脚本，API、表单实例和生命周期使用替身；不连接后台或云账户。
const sourceRoot = new URL('../src/', import.meta.url);
let passed = 0;
const flush = async () => {
  for (let i = 0; i < 20; i++) await Promise.resolve();
};
function deferred() {
  let resolve;
  let reject;
  const promise = new Promise((yes, no) => {
    resolve = yes;
    reject = no;
  });
  return { promise, resolve, reject };
}
function loadPage(name, bindings, exported) {
  const url = new URL(`pages/driver/${name}.vue`, sourceRoot);
  const source = readFileSync(url, 'utf8');
  const { descriptor, errors } = parse(source, { filename: fileURLToPath(url) });
  assert.deepEqual(errors, []);
  const compiled = compileScript(descriptor, { id: name });
  const template = compileTemplate({
    source: descriptor.template.content,
    filename: fileURLToPath(url),
    id: name,
    compilerOptions: { bindingMetadata: compiled.bindings },
  });
  assert.deepEqual(template.errors, []);
  const body = descriptor.scriptSetup.content;
  const ast = parseJavaScript(body, { sourceType: 'module' });
  const imports = ast.program.body.filter((node) => node.type === 'ImportDeclaration');
  const messages = [];
  const events = [];
  const context = {
    ref,
    reactive,
    URL,
    useRoute: () => ({ params: {}, query: {} }),
    getCurrentInstance: () => ({ proxy: { $copyText: async () => {} } }),
    defineOptions: () => {},
    defineExpose: () => {},
    defineProps: () => ({ driverId: 7 }),
    defineEmits: () => (event) => events.push(event),
    onMounted: () => {},
    watch: () => {},
    ElMessage: Object.fromEntries(
      ['success', 'error', 'warning'].map((type) => [type, (msg) => messages.push([type, msg])]),
    ),
    ElMessageBox: async () => {},
    ...bindings,
  };
  for (const item of imports) {
    for (const specifier of item.specifiers) {
      if (!(specifier.local.name in context)) context[specifier.local.name] = {};
    }
  }
  let script = body;
  for (const item of [...imports].reverse()) {
    script = script.slice(0, item.start) + ' '.repeat(item.end - item.start) + script.slice(item.end);
  }
  vm.runInNewContext(`${script}\nthis.page = { ${exported.join(', ')} };`, context, { filename: fileURLToPath(url) });
  return { ...context.page, messages, events };
}
function compressionFixture(mode = 'success') {
  const source = readFileSync(new URL('utils/compressImg.js', sourceRoot), 'utf8');
  const ast = parseJavaScript(source, { sourceType: 'module' });
  const exported = ast.program.body.find((node) => node.type === 'ExportDefaultDeclaration');
  const timers = new Map();
  const readers = [];
  const images = [];
  let aborts = 0;
  let encode;
  class FixtureReader {
    constructor() {
      this.readyState = 0;
      readers.push(this);
    }
    readAsDataURL() {
      if (mode === 'read-throw') throw new Error('fixture read failure');
      this.readyState = mode === 'read-timeout' ? 1 : 2;
      this.result = 'data:image/png;base64,isolated';
      if (mode === 'read-timeout') return;
      if (mode === 'read-error') this.onerror();
      else if (mode === 'read-abort') this.onabort();
      else this.onload();
    }
    abort() {
      aborts++;
      this.readyState = 2;
      this.onabort?.();
    }
  }
  class FixtureImage {
    constructor() {
      if (mode === 'image-throw') throw new Error('fixture image failure');
      this.naturalWidth = mode === 'too-large' ? 20000001 : mode === 'zero-width' ? 0 : 100;
      this.naturalHeight = mode === 'too-large' ? 1 : 100;
      images.push(this);
    }
    set src(value) {
      if (mode === 'src-throw') throw new Error('fixture source failure');
      if (mode === 'image-timeout') return;
      if (mode === 'image-error') this.onerror();
      else this.onload();
    }
  }
  const context2d = {
    fillRect() {
      if (mode === 'fill-throw') throw new Error('fixture fill failure');
    },
    drawImage() {
      if (mode === 'draw-throw') throw new Error('fixture draw failure');
    },
  };
  const canvas = {
    width: 0,
    height: 0,
    getContext() {
      if (mode === 'context-throw') throw new Error('fixture context failure');
      return mode === 'context-null' ? null : context2d;
    },
    toBlob(callback, type, quality) {
      assert.equal(type, 'image/jpeg');
      assert.equal(quality, 0.8);
      encode = callback;
      if (mode === 'encode-throw') throw new Error('fixture encode failure');
      if (mode === 'encode-timeout') return;
      if (mode === 'blob-null') callback(null);
      else
        callback(
          new Blob([mode === 'blob-empty' ? '' : 'isolated-jpeg'], { type: mode === 'blob-type' ? 'image/png' : type }),
        );
    },
  };
  const context = {
    FileReader: FixtureReader,
    Image: FixtureImage,
    File:
      mode === 'file-throw'
        ? function () {
            throw new Error('fixture file failure');
          }
        : File,
    document: {
      createElement(name) {
        assert.equal(name, 'canvas');
        if (mode === 'canvas-throw') throw new Error('fixture canvas failure');
        return canvas;
      },
    },
    setTimeout(callback, duration) {
      assert.equal(duration, 30000);
      timers.set(1, callback);
      return 1;
    },
    clearTimeout: (id) => timers.delete(id),
  };
  const script = source.slice(0, exported.start) + source.slice(exported.declaration.start);
  vm.runInNewContext(`${script}\nthis.compressImg = compressImg;`, context);
  return {
    compressImg: context.compressImg,
    timers,
    readers,
    images,
    canvas,
    aborts: () => aborts,
    lateEncode: () => encode?.(new Blob(['late'], { type: 'image/jpeg' })),
  };
}
async function scenario(name, run) {
  await run();
  passed++;
  console.log(`PASS: ${name}`);
}
const editExports = [
  'props',
  'formRef',
  'editForm',
  'configGroups',
  'commonConfig',
  'editLoading',
  'loadFailed',
  'submitLoading',
  'getDetail',
  'handleSubmit',
];
const detail = (id = 7, key = 'oss') => ({
  data: {
    id,
    driver_key: key,
    driver_name: key,
    sort: 0,
    status: 0,
    is_builtin: key === 'local' ? 1 : 0,
    config_fields: [
      { field: 'accessKey', type: 'text', group: 'keys', value: '********', required: true },
      { field: 'secretKey', type: 'password', group: 'keys', value: '********', required: true },
    ],
    bucket_fields: [{ field: 'name', type: 'text', value: '', required: true }],
  },
});

async function run() {
  await scenario('edit submits displayed driver fields and common config in one request', async () => {
    const calls = [];
    const pending = deferred();
    const page = loadPage(
      'uploadDriver/edit',
      {
        uploadDriverDetailApi: async () => detail(),
        getUploadCommonConfigApi: async () => ({ data: { watermark_opacity: 0, watermark_x: 0 } }),
        saveDriverConfigApi: (...args) => {
          calls.push(args);
          return pending.promise;
        },
        saveUploadCommonConfigApi: () => assert.fail('A second save must not be requested'),
      },
      editExports,
    );
    page.getDetail();
    await flush();
    assert.equal(page.commonConfig.value.watermark_opacity, 0);
    assert.equal(page.commonConfig.value.watermark_x, 0);
    page.configGroups.value[0].fields[1].value = 'synthetic-updated-key';
    page.formRef.value = { validate: (callback) => callback(true) };
    page.handleSubmit();
    page.handleSubmit();
    assert.equal(calls.length, 1);
    assert.equal(calls[0][1].secretKey, 'synthetic-updated-key');
    assert.equal(calls[0][1].accessKey, '********');
    assert.equal(calls[0][1].thumb_watermark_config.watermark_opacity, 0);
    assert.equal(page.submitLoading.value, true);
    pending.resolve({ msg: 'saved' });
    await flush();
    assert.deepEqual(page.events, ['success']);
    assert.equal(page.submitLoading.value, false);
  });
  await scenario('edit failed or invalid forms cannot submit and save failure stays open', async () => {
    let saves = 0;
    const page = loadPage(
      'uploadDriver/edit',
      {
        uploadDriverDetailApi: async () => {
          throw new Error('fixture load failure');
        },
        saveDriverConfigApi: async () => {
          saves++;
          throw new Error('fixture save failure');
        },
      },
      editExports,
    );
    page.getDetail();
    await flush();
    assert.equal(page.loadFailed.value, true);
    assert.equal(page.editLoading.value, false);
    page.formRef.value = { validate: (callback) => callback(true) };
    page.handleSubmit();
    assert.equal(saves, 0);
    page.loadFailed.value = false;
    page.formRef.value.validate = (callback) => callback(false);
    page.handleSubmit();
    assert.equal(saves, 0);
    page.formRef.value.validate = (callback) => callback(true);
    page.handleSubmit();
    await flush();
    assert.equal(saves, 1);
    assert.equal(page.submitLoading.value, false);
    assert.deepEqual(page.events, []);
  });
  await scenario('out-of-order edit responses cannot overwrite the selected driver', async () => {
    const requests = [deferred(), deferred()];
    let next = 0;
    const page = loadPage(
      'uploadDriver/edit',
      {
        uploadDriverDetailApi: () => requests[next++].promise,
        getUploadCommonConfigApi: async () => ({ data: {} }),
      },
      editExports,
    );
    page.getDetail();
    page.props.driverId = 8;
    page.getDetail();
    requests[1].resolve(detail(8, 'local'));
    await flush();
    requests[0].resolve(detail(7));
    await flush();
    assert.equal(page.editForm.value.id, 8);
    assert.equal(page.editLoading.value, false);
  });
  const storageExports = [
    'configFormRef',
    'addStorageFormRef',
    'driverInfo',
    'configFormData',
    'addStorageFormData',
    'loading',
    'storageList',
    'configSaving',
    'addSaving',
    'syncSaving',
    'configData',
    'showConfigModal',
    'showAddStorageModal',
    'getDriverDetail',
    'getlist',
    'synchro',
    'changeSwitch',
    'submitConfigForm',
    'submitAddStorageForm',
    'canConfigureCname',
    'config',
  ];
  await scenario('storage list failure clears loading and stale results cannot replace newer ones', async () => {
    const requests = [deferred(), deferred()];
    let next = 0;
    const page = loadPage(
      'uploadDriver/storage',
      {
        storageListApi: () => requests[next++].promise,
      },
      storageExports,
    );
    page.driverInfo.value = detail().data;
    page.getlist();
    page.getlist();
    requests[1].resolve({ data: { list: [{ id: 2 }], count: 1 } });
    await flush();
    requests[0].resolve({ data: { list: [{ id: 1 }], count: 1 } });
    await flush();
    assert.equal(page.storageList.value[0].id, 2);
    assert.equal(page.loading.value, false);
    const failure = loadPage(
      'uploadDriver/storage',
      {
        storageListApi: async () => {
          throw new Error('fixture list failure');
        },
      },
      storageExports,
    );
    failure.driverInfo.value = detail().data;
    await failure.getlist();
    assert.equal(failure.loading.value, false);
    assert.equal(failure.messages[0][0], 'error');
  });
  await scenario('storage detail failure disables actions and releases loading', async () => {
    const page = loadPage(
      'uploadDriver/storage',
      {
        uploadDriverDetailApi: async () => {
          throw new Error('fixture detail failure');
        },
        storageSaveApi: () => assert.fail('Do not submit before driver detail loads'),
      },
      storageExports,
    );
    await page.getDriverDetail();
    assert.equal(page.driverInfo.value, null);
    assert.equal(page.loading.value, false);
    page.submitAddStorageForm();
  });
  await scenario('storage sends explicit desired status and rolls back failure or cancellation', async () => {
    const calls = [];
    const page = loadPage(
      'uploadDriver/storage',
      {
        storageStatusApi: async (...args) => {
          calls.push(args);
          throw new Error('fixture status failure');
        },
      },
      storageExports,
    );
    const row = { id: 2, status: 0 };
    page.changeSwitch(row);
    await flush();
    assert.deepEqual(calls, [[2, 0]]);
    assert.equal(row.status, 1);
    assert.equal(row._statusSaving, false);
    const canceled = loadPage(
      'uploadDriver/storage',
      {
        ElMessageBox: async () => {
          throw 'cancel';
        },
        storageStatusApi: () => assert.fail('Canceled operation must not call API'),
      },
      storageExports,
    );
    const canceledRow = { id: 3, status: 1 };
    canceled.changeSwitch(canceledRow);
    await flush();
    assert.equal(canceledRow.status, 0);
    assert.equal(canceled.messages.length, 0);
  });
  await scenario('storage config and bucket forms prevent duplicate submits and release failure states', async () => {
    const pending = deferred();
    let configSaves = 0;
    let bucketSaves = 0;
    const page = loadPage(
      'uploadDriver/storage',
      {
        saveDriverConfigApi: () => {
          configSaves++;
          return pending.promise;
        },
        storageSaveApi: async () => {
          bucketSaves++;
          throw new Error('fixture bucket failure');
        },
      },
      storageExports,
    );
    page.driverInfo.value = detail().data;
    page.configFormRef.value = { validate: (callback) => callback(true) };
    page.addStorageFormRef.value = { validate: (callback) => callback(true) };
    page.showConfigModal.value = true;
    page.showAddStorageModal.value = true;
    page.submitConfigForm();
    page.submitConfigForm();
    page.submitAddStorageForm();
    page.submitAddStorageForm();
    assert.equal(configSaves, 1);
    assert.equal(bucketSaves, 1);
    pending.reject(new Error('fixture config failure'));
    await flush();
    assert.equal(page.configSaving.value, false);
    assert.equal(page.addSaving.value, false);
    assert.equal(page.showConfigModal.value, true);
    assert.equal(page.showAddStorageModal.value, true);
  });
  await scenario('default OSS endpoints do not display self-referencing CNAME instructions', async () => {
    const page = loadPage('uploadDriver/storage', {}, storageExports);
    assert.equal(
      page.canConfigureCname({
        domain: 'https://bucket.oss-cn-hangzhou.aliyuncs.com',
        cname: 'bucket.oss-cn-hangzhou.aliyuncs.com',
      }),
      false,
    );
    const row = { domain: 'https://cdn.test.invalid/assets', cname: 'bucket.oss-cn-hangzhou.aliyuncs.com' };
    assert.equal(page.canConfigureCname(row), true);
    page.config(row);
    assert.equal(page.configData.value.hostname, 'cdn.test.invalid');
  });
  const listExports = ['list', 'loading', 'statusSaving', 'getList', 'handleStatusChange'];
  await scenario('driver list ignores stale responses and serializes mutually exclusive switches', async () => {
    const requests = [deferred(), deferred()];
    let next = 0;
    const pending = deferred();
    let statusCalls = 0;
    const page = loadPage(
      'uploadDriver/index',
      {
        uploadDriverListApi: () => requests[next++].promise,
        uploadDriverSetStatusApi: () => {
          statusCalls++;
          return pending.promise;
        },
      },
      listExports,
    );
    page.getList();
    page.getList();
    requests[1].resolve({ data: { list: [{ id: 8 }] } });
    await flush();
    requests[0].resolve({ data: { list: [{ id: 7 }] } });
    await flush();
    assert.equal(page.list.value[0].id, 8);
    const row = { id: 7, status: 1 };
    page.handleStatusChange(row);
    page.handleStatusChange({ id: 8, status: 1 });
    assert.equal(statusCalls, 1);
    assert.equal(page.statusSaving.value, true);
    pending.reject(new Error('fixture switch failure'));
    await flush();
    assert.equal(row.status, 0);
    assert.equal(page.statusSaving.value, false);
  });
  await scenario('storage entry reuses the driver page and status/switch API contracts match routes', async () => {
    loadPage('storage/index', {}, []);
    const legacy = readFileSync(new URL('pages/driver/storage/index.vue', sourceRoot), 'utf8');
    assert.match(legacy, /import UploadDriverList from '\.\.\/uploadDriver\/index\.vue'/);
    const script = readFileSync(new URL('api/setting.js', sourceRoot), 'utf8');
    const ast = parseJavaScript(script, { sourceType: 'module' });
    for (const name of ['storageStatusApi', 'saveType']) {
      const node = ast.program.body.find(
        (item) => item.type === 'ExportNamedDeclaration' && item.declaration?.id?.name === name,
      );
      const request = node.declaration.body.body[0].argument.arguments[0];
      assert.equal(request.properties.find((item) => item.key.name === 'method').value.value, 'put');
      if (name === 'storageStatusApi') assert.ok(request.properties.some((item) => item.key.name === 'data'));
    }
    const uploads = parseJavaScript(readFileSync(new URL('api/uploadPictures.js', sourceRoot), 'utf8'), {
      sourceType: 'module',
    });
    const cancel = uploads.program.body.find((item) => item.declaration?.id?.name === 'scanUploadCode');
    const cancellation = cancel.declaration.body.body[0].argument.arguments[0];
    assert.equal(
      cancellation.properties.find((item) => item.key.name === 'url').value.value,
      'file/scan_upload/qrcode',
    );
    assert.equal(cancellation.properties.find((item) => item.key.name === 'method').value.value, 'delete');
    const selector = parse(readFileSync(new URL('components/uploadImg/index.vue', sourceRoot), 'utf8')).descriptor;
    assert.match(selector.template.content, /@click="scanUploadGetFn">刷新图库<\/el-button>/);
  });
  const scannerExports = [
    'imgList',
    'loading',
    'uploading',
    'submitUpload',
    'handleRemove',
    'fileChange',
    'preparing',
    'allSize',
  ];
  const scannerBindings = {
    FormData,
    document: { title: '' },
    Setting: { apiBaseURL: 'https://browser.test.invalid/adminapi' },
    useRoute: () => ({ params: {}, query: { token: 'a'.repeat(64), pid: '0' } }),
  };
  await scenario(
    'phone scanner failures settle, release loading and allow retry without a false completion',
    async () => {
      for (const responseFailure of [false, true]) {
        let fail = true;
        const page = loadPage(
          '../app/upload/index',
          {
            ...scannerBindings,
            scanUpload: async () => {
              if (fail) {
                if (responseFailure) return { status: 400, msg: 'fixture response failure' };
                throw new Error('fixture transport failure');
              }
              return { status: 200 };
            },
          },
          scannerExports,
        );
        page.imgList.value = [{ raw: new Blob(['isolated-upload-bytes']) }];
        await page.submitUpload();
        assert.equal(page.loading.value, false);
        assert.equal(page.uploading.value, true);
        assert.equal(page.messages.filter(([type]) => type === 'error').length, 1);
        fail = false;
        await page.submitUpload();
        assert.equal(page.loading.value, false);
        assert.equal(page.uploading.value, false);
      }
    },
  );
  await scenario(
    'phone scanner serializes files, rejects duplicate submission and only completes after all uploads',
    async () => {
      const pending = [deferred(), deferred()];
      const calls = [];
      const page = loadPage(
        '../app/upload/index',
        {
          ...scannerBindings,
          scanUpload: (data) => {
            calls.push(data);
            return pending[calls.length - 1].promise;
          },
        },
        scannerExports,
      );
      await page.submitUpload();
      assert.equal(calls.length, 0);
      assert.equal(page.messages[0][0], 'warning');
      page.imgList.value = [{ raw: new Blob(['first']) }, { raw: new Blob(['second']) }];
      const submission = page.submitUpload();
      await page.submitUpload();
      assert.equal(calls.length, 1);
      assert.equal(calls[0].get('uploadToken'), 'a'.repeat(64));
      assert.equal(calls[0].get('pid'), '0');
      assert.equal(page.loading.value, true);
      pending[0].resolve({ status: 200 });
      await flush();
      assert.equal(calls.length, 2);
      assert.equal(page.uploading.value, true);
      pending[1].resolve({ status: 200 });
      await submission;
      assert.equal(page.loading.value, false);
      assert.equal(page.uploading.value, false);
    },
  );
  await scenario('phone scanner retries only unsuccessful files after a partial batch failure', async () => {
    let calls = 0;
    const page = loadPage(
      '../app/upload/index',
      {
        ...scannerBindings,
        scanUpload: async () => {
          calls++;
          if (calls === 2) throw new Error('isolated second-file failure');
          return { status: 200 };
        },
      },
      scannerExports,
    );
    page.imgList.value = [{ raw: new Blob(['first']) }, { raw: new Blob(['second']) }];
    await page.submitUpload();
    assert.equal(calls, 2);
    assert.equal(page.uploading.value, true);
    assert.equal(page.imgList.value[0]._scanUploaded, true);
    assert.equal(page.imgList.value[1]._scanUploaded, undefined);
    await page.submitUpload();
    assert.equal(calls, 3);
    assert.equal(page.uploading.value, false);
    assert.equal(page.loading.value, false);
  });
  const selectorExports = [
    'ruleForm',
    'uploadModal',
    'scanToken',
    'qrCodeUrl',
    'radioChange',
    'scanUploadQrcodeFn',
    'scanUploadGetFn',
    'closed',
    'handleWebRemove',
    'fileChange',
    'preparing',
    'loading',
    'submitUpload',
    'beforeClose',
  ];
  function selectorPage(bindings = {}) {
    const timers = new Map();
    const codes = [];
    const unmounts = [];
    let timerId = 0;
    class FixtureQRCode {
      constructor(element, options) {
        codes.push(options.text);
      }
    }
    FixtureQRCode.CorrectLevel = { H: 2 };
    const page = loadPage(
      '../../components/uploadImg/index',
      {
        ...scannerBindings,
        location: { origin: 'https://browser.test.invalid' },
        defineProps: () => ({ categoryId: '' }),
        getCookies: () => '',
        onBeforeUnmount: (callback) => unmounts.push(callback),
        setInterval: (callback) => {
          timers.set(++timerId, callback);
          return timerId;
        },
        clearInterval: (id) => timers.delete(id),
        QRCode: FixtureQRCode,
        scanUploadQrcode: async () => ({ data: { url: `/app/upload?pid=0&token=${'a'.repeat(64)}` } }),
        scanUploadGet: async () => ({ data: [] }),
        scanUploadCode: async () => {},
        ...bindings,
      },
      selectorExports,
    );
    page.uploadModal.value = true;
    page.ruleForm.type = 2;
    page.qrCodeUrl.value = { innerHTML: '' };
    return { ...page, timers, codes, unmounts };
  }
  function fileFixture(uid, large = false) {
    const raw = new Blob([large ? new Uint8Array(2097152) : `isolated-image-${uid}`], { type: 'image/png' });
    return { uid, raw, size: raw.size, url: `blob:isolated-image-${uid}` };
  }
  await scenario(
    'real compression helper returns JPEG files with matching names and releases canvas resources',
    async () => {
      for (const name of ['photo.png', 'photo.jpeg', 'photo.JPG', 'image', '.png']) {
        const fixture = compressionFixture();
        const compressed = await fixture.compressImg(new File(['isolated-original'], name, { type: 'image/png' }));
        assert.equal(compressed.name, name === '.png' || name === 'image' ? 'image.jpg' : 'photo.jpg');
        assert.equal(compressed.type, 'image/jpeg');
        assert.equal(await compressed.text(), 'isolated-jpeg');
        assert.equal(fixture.timers.size, 0);
        assert.equal(fixture.canvas.width, 0);
        assert.equal(fixture.canvas.height, 0);
        assert.equal(fixture.readers[0].onload, null);
        assert.equal(fixture.images[0].onload, null);
      }
    },
  );
  await scenario('real compression helper settles every reader, decoder, canvas and encoder failure', async () => {
    for (const mode of [
      'read-throw',
      'read-error',
      'read-abort',
      'image-throw',
      'src-throw',
      'image-error',
      'too-large',
      'zero-width',
      'canvas-throw',
      'context-throw',
      'context-null',
      'fill-throw',
      'draw-throw',
      'encode-throw',
      'blob-null',
      'blob-empty',
      'blob-type',
      'file-throw',
      'read-timeout',
      'image-timeout',
      'encode-timeout',
    ]) {
      const fixture = compressionFixture(mode);
      const outcome = fixture.compressImg(new File(['isolated-invalid'], 'invalid.png')).then(
        () => assert.fail(`${mode} must not succeed`),
        (error) => error,
      );
      if (mode.endsWith('-timeout')) fixture.timers.get(1)();
      const error = await outcome;
      assert.equal(typeof error.message, 'string');
      assert.ok(error.message.length > 0);
      assert.equal(fixture.timers.size, 0);
      assert.equal(fixture.readers[0].onload, null);
      assert.equal(fixture.readers[0].onerror, null);
      assert.equal(fixture.readers[0].onabort, null);
      if (fixture.images.length) assert.equal(fixture.images[0].onerror, null);
      assert.equal(fixture.canvas.width, 0);
      assert.equal(fixture.canvas.height, 0);
      if (mode === 'read-timeout') assert.equal(fixture.aborts(), 1);
      fixture.lateEncode();
      assert.equal(fixture.timers.size, 0);
    }
  });
  await scenario(
    'picker cannot user-close during preparation or upload and all files keep their submitted group',
    async () => {
      const pending = deferred();
      const pids = [];
      const page = selectorPage({
        fileUpload: (data) => {
          pids.push(data.get('pid'));
          return pids.length === 1 ? pending.promise : Promise.resolve({ status: 200 });
        },
      });
      let closes = 0;
      page.preparing.value = 1;
      page.beforeClose(() => closes++);
      assert.equal(closes, 0);
      page.preparing.value = 0;
      page.ruleForm.type = 0;
      page.ruleForm.region = 10;
      page.ruleForm.imgList = [fileFixture(1), fileFixture(2)];
      const submission = page.submitUpload();
      page.beforeClose(() => closes++);
      assert.equal(closes, 0);
      page.ruleForm.region = 99;
      pending.resolve({ status: 200 });
      await submission;
      assert.deepEqual(pids, ['10', '10']);
      page.beforeClose(() => closes++);
      assert.equal(closes, 1);
      const template = parse(readFileSync(new URL('components/uploadImg/index.vue', sourceRoot), 'utf8')).descriptor
        .template.content;
      assert.match(template, /:before-close="beforeClose"/);
      assert.match(template, /:close-on-click-modal="false"/);
      assert.match(template, /:close-on-press-escape="!loading && !preparing"/);
    },
  );
  for (const phone of [true, false]) {
    const kind = phone ? 'phone' : 'picker';
    function uploadPage(bindings) {
      if (phone) return loadPage('../app/upload/index', { ...scannerBindings, ...bindings }, scannerExports);
      const page = selectorPage({ isPicUpload: () => true, ...bindings });
      page.ruleForm.type = 0;
      return page;
    }
    const images = (page) => (phone ? page.imgList.value : page.ruleForm.imgList);
    const removal = (page, file) => (phone ? page.handleRemove(file) : page.handleWebRemove(file));
    await scenario(
      `${kind} publishes selections while compression is pending and cannot complete an old batch`,
      async () => {
        const compression = deferred();
        const calls = [];
        const upload = async (data) => {
          calls.push(data);
          return { status: 200 };
        };
        const page = uploadPage({ compressImg: () => compression.promise, scanUpload: upload, fileUpload: upload });
        const first = fileFixture(1);
        const second = fileFixture(2, true);
        await page.fileChange(first, [first]);
        const preparation = page.fileChange(second, [first, second]);
        assert.equal(page.preparing.value, 1);
        assert.equal(images(page).length, 2);
        removal(page, first);
        assert.equal(images(page).length, 2);
        await page.submitUpload();
        assert.equal(calls.length, 0);
        assert.equal(page.messages.filter(([type]) => type === 'success').length, 0);
        const compressed = new Blob(['isolated-compressed-image'], { type: 'image/png' });
        compression.resolve(compressed);
        await preparation;
        assert.equal(page.preparing.value, 0);
        assert.equal(images(page)[1].raw, compressed);
        if (phone) assert.equal(page.allSize.value, first.raw.size + compressed.size);
        await page.submitUpload();
        assert.equal(calls.length, 2);
        assert.equal(calls[1].get('file').size, compressed.size);
        assert.equal(page.loading.value, false);
        if (phone) assert.equal(page.uploading.value, false);
        else assert.deepEqual(page.events, ['uploadSuccess']);
      },
    );
    await scenario(`${kind} concurrent compression completions preserve all file identities`, async () => {
      const pending = [deferred(), deferred()];
      let next = 0;
      const page = uploadPage({ compressImg: () => pending[next++].promise });
      const first = fileFixture(1, true);
      const second = fileFixture(2, true);
      const small = fileFixture(3);
      const firstChange = page.fileChange(first, [first]);
      const secondChange = page.fileChange(second, [first, second]);
      await page.fileChange(small, [first, second, small]);
      assert.equal(page.preparing.value, 2);
      const compressed = [new Blob(['first-compressed']), new Blob(['second-compressed'])];
      pending[1].resolve(compressed[1]);
      await secondChange;
      assert.equal(page.preparing.value, 1);
      assert.equal(images(page).length, 3);
      pending[0].resolve(compressed[0]);
      await firstChange;
      assert.equal(page.preparing.value, 0);
      assert.deepEqual(
        Array.from(images(page), (item) => item.uid),
        [1, 2, 3],
      );
      assert.equal(images(page)[0].raw, compressed[0]);
      assert.equal(images(page)[1].raw, compressed[1]);
      assert.equal(images(page)[2].raw, small.raw);
      if (phone) assert.equal(page.allSize.value, compressed[0].size + compressed[1].size + small.raw.size);
    });
    await scenario(`${kind} compression rejection releases preparation and removes only the failed file`, async () => {
      const pending = [deferred(), deferred()];
      let next = 0;
      const page = uploadPage({ compressImg: () => pending[next++].promise });
      const first = fileFixture(1, true);
      const second = fileFixture(2, true);
      const small = fileFixture(3);
      const firstChange = page.fileChange(first, [first]);
      const secondChange = page.fileChange(second, [first, second]);
      await page.fileChange(small, [first, second, small]);
      pending[0].reject(new Error('isolated compression failure'));
      await firstChange;
      assert.equal(page.preparing.value, 1);
      assert.deepEqual(
        Array.from(images(page), (item) => item.uid),
        [2, 3],
      );
      pending[1].resolve(new Blob(['second-compressed']));
      await secondChange;
      assert.equal(page.preparing.value, 0);
      assert.equal(page.messages.filter(([type]) => type === 'error').length, 1);
      assert.equal(page.loading.value, false);
      assert.deepEqual(
        Array.from(images(page), (item) => item.uid),
        [2, 3],
      );
    });
  }
  await scenario('picker local upload failures settle and retries do not repeat successful files', async () => {
    for (const responseFailure of [false, true]) {
      const calls = [];
      const page = selectorPage({
        fileUpload: async (data) => {
          calls.push(data.get('file').size);
          if (calls.length === 2) {
            if (responseFailure) return { status: 400, msg: 'isolated response failure' };
            throw new Error('isolated upload failure');
          }
          return { status: 200 };
        },
      });
      page.ruleForm.type = 0;
      page.ruleForm.imgList = [fileFixture(1), fileFixture(22)];
      await page.submitUpload();
      assert.equal(page.loading.value, false);
      assert.equal(page.uploadModal.value, true);
      assert.equal(page.ruleForm.imgList[0]._uploaded, true);
      assert.equal(page.ruleForm.imgList[1]._uploaded, undefined);
      assert.deepEqual(page.events, []);
      await page.submitUpload();
      assert.equal(calls.length, 3);
      assert.equal(calls[1], calls[2]);
      assert.equal(page.loading.value, false);
      assert.equal(page.uploadModal.value, false);
      assert.deepEqual(page.events, ['uploadSuccess']);
    }
  });
  await scenario('picker invalid image rejection removes only the matching file', async () => {
    const page = selectorPage({ isPicUpload: (file) => file.uid !== 2 });
    page.ruleForm.type = 0;
    const files = [fileFixture(1), fileFixture(2), fileFixture(3)];
    await page.fileChange(files[1], files);
    assert.deepEqual(
      Array.from(page.ruleForm.imgList, (item) => item.uid),
      [1, 3],
    );
  });
  await scenario('picker network and scanner failures stay open, settle and reject duplicate submits', async () => {
    for (const type of [1, 2]) {
      const pending = deferred();
      const calls = [];
      const submit = (data) => {
        calls.push(data);
        return calls.length === 1 ? pending.promise : Promise.resolve({ status: 200 });
      };
      const page = selectorPage({ onlineUpload: submit, moveApi: submit });
      page.ruleForm.type = type;
      page.ruleForm.region = 9;
      page.ruleForm.imgList = [{ att_id: 7, url: 'https://image.test.invalid/picture.png' }];
      const first = page.submitUpload();
      await page.submitUpload();
      assert.equal(calls.length, 1);
      assert.equal(calls[0].pid, 9);
      assert.equal(calls[0].images[0], type === 2 ? 7 : 'https://image.test.invalid/picture.png');
      pending.reject(new Error('isolated network failure'));
      await first;
      assert.equal(page.loading.value, false);
      assert.equal(page.uploadModal.value, true);
      assert.equal(page.ruleForm.imgList.length, 1);
      assert.deepEqual(page.events, []);
      assert.equal(page.messages.filter(([messageType]) => messageType === 'error').length, 1);
      await page.submitUpload();
      assert.equal(calls.length, 2);
      assert.equal(page.loading.value, false);
      assert.equal(page.uploadModal.value, false);
      assert.deepEqual(page.events, ['uploadSuccess']);
    }
  });
  await scenario(
    'scanner removal uses attachment identity and polling cannot restore excluded selections',
    async () => {
      let token = 'a';
      const files = [{ att_id: 1 }, { att_id: 2 }, { att_id: 3 }];
      const page = selectorPage({
        scanUploadQrcode: async () => ({ data: { url: `/app/upload?token=${token.repeat(64)}` } }),
        scanUploadGet: async () => ({ data: files }),
      });
      await page.scanUploadQrcodeFn();
      await page.scanUploadGetFn();
      page.handleWebRemove({ att_id: '2' });
      assert.deepEqual(
        Array.from(page.ruleForm.imgList, (item) => item.att_id),
        [1, 3],
      );
      await page.scanUploadGetFn();
      await page.scanUploadGetFn();
      assert.deepEqual(
        Array.from(page.ruleForm.imgList, (item) => item.att_id),
        [1, 3],
      );
      token = 'b';
      await page.scanUploadQrcodeFn();
      await page.scanUploadGetFn();
      assert.deepEqual(
        Array.from(page.ruleForm.imgList, (item) => item.att_id),
        [1, 2, 3],
      );
      page.unmounts[0]();
    },
  );
  await scenario('scanner polling preserves drag order, refreshes metadata and appends only new files', async () => {
    let files = [{ att_id: 1 }, { att_id: 2 }, { att_id: 3 }];
    const page = selectorPage({ scanUploadGet: async () => ({ data: files }) });
    await page.scanUploadQrcodeFn();
    await page.scanUploadGetFn();
    page.ruleForm.imgList = [page.ruleForm.imgList[2], page.ruleForm.imgList[0], page.ruleForm.imgList[1]];
    files = [{ att_id: 2, att_dir: 'updated' }, { att_id: 1 }, { att_id: 3 }, { att_id: 4 }];
    await page.scanUploadGetFn();
    assert.deepEqual(
      Array.from(page.ruleForm.imgList, (item) => item.att_id),
      [3, 1, 2, 4],
    );
    assert.equal(page.ruleForm.imgList[2].att_dir, 'updated');
    page.unmounts[0]();
  });
  await scenario('QR polling starts only after a valid link and parses token with URLSearchParams', async () => {
    const pending = deferred();
    const calls = [];
    const polls = [];
    const page = selectorPage({
      scanUploadQrcode: (pid) => {
        calls.push(pid);
        return pending.promise;
      },
      scanUploadGet: async (token) => {
        polls.push(token);
        return { data: [] };
      },
    });
    page.qrCodeUrl.value.innerHTML = 'isolated-previous-code';
    const generation = page.scanUploadQrcodeFn();
    await flush();
    assert.equal(page.qrCodeUrl.value.innerHTML, '');
    await page.scanUploadGetFn();
    assert.deepEqual(calls, [0]);
    assert.deepEqual(polls, []);
    assert.equal(page.timers.size, 0);
    pending.resolve({ data: { url: `/app/upload?pid=0&token=${'a'.repeat(64)}&after=1` } });
    await generation;
    assert.equal(page.scanToken.value, 'a'.repeat(64));
    assert.equal(page.timers.size, 1);
    assert.equal(page.codes.length, 1);
    await page.scanUploadGetFn();
    assert.deepEqual(polls, ['a'.repeat(64)]);
    page.unmounts[0]();
    assert.equal(page.timers.size, 0);
  });
  await scenario(
    'QR signing is serialized so an old request cannot invalidate the current category capability',
    async () => {
      const pending = [deferred(), deferred()];
      let calls = 0;
      const page = selectorPage({ scanUploadQrcode: () => pending[calls++].promise });
      const first = page.scanUploadQrcodeFn();
      await flush();
      page.ruleForm.region = 9;
      const second = page.scanUploadQrcodeFn();
      await flush();
      assert.equal(calls, 1);
      pending[0].resolve({ data: { url: `/app/upload?pid=0&token=${'a'.repeat(64)}` } });
      await first;
      await flush();
      assert.equal(calls, 2);
      pending[1].resolve({ data: { url: `/app/upload?pid=9&token=${'b'.repeat(64)}` } });
      await second;
      assert.equal(page.scanToken.value, 'b'.repeat(64));
      assert.equal(page.codes.length, 1);
      assert.equal(page.timers.size, 1);
      page.unmounts[0]();
    },
  );
  await scenario('QR transport and malformed-link failures settle without starting empty-token polling', async () => {
    for (const malformed of [false, true]) {
      const page = selectorPage({
        scanUploadQrcode: async () => {
          if (malformed) return { data: { url: '/app/upload?token=not-a-capability' } };
          throw new Error('isolated QR transport failure');
        },
        scanUploadGet: () => assert.fail('An invalid QR must not poll'),
      });
      await page.scanUploadQrcodeFn();
      await page.scanUploadGetFn();
      assert.equal(page.timers.size, 0);
      assert.equal(page.scanToken.value, '');
      assert.equal(page.messages.filter(([type]) => type === 'error').length, 1);
    }
  });
  await scenario('gallery polling rejects duplicates and ignores stale capability responses', async () => {
    const pending = deferred();
    let calls = 0;
    let qr = 0;
    const page = selectorPage({
      scanUploadQrcode: async () => ({ data: { url: `/app/upload?token=${(++qr === 1 ? 'a' : 'b').repeat(64)}` } }),
      scanUploadGet: () => {
        calls++;
        return pending.promise;
      },
    });
    await page.scanUploadQrcodeFn();
    const first = page.scanUploadGetFn();
    await page.scanUploadGetFn();
    assert.equal(calls, 1);
    await page.scanUploadQrcodeFn();
    pending.resolve({ data: [{ att_id: 99 }] });
    await first;
    assert.equal(page.ruleForm.imgList.length, 0);
    assert.equal(page.scanToken.value, 'b'.repeat(64));
    assert.equal(page.timers.size, 1);
    page.unmounts[0]();
  });
  await scenario('expired polling stops its timer and a manual gallery refresh can retry', async () => {
    let fail = true;
    const page = selectorPage({
      scanUploadGet: async () => {
        if (fail) throw { status: 400, msg: 'isolated expired capability' };
        return { data: [{ att_id: 7 }] };
      },
    });
    await page.scanUploadQrcodeFn();
    await page.scanUploadGetFn();
    assert.equal(page.timers.size, 0);
    assert.equal(page.messages.filter(([type]) => type === 'error').length, 1);
    fail = false;
    await page.scanUploadGetFn();
    assert.equal(page.ruleForm.imgList[0].att_id, 7);
    page.unmounts[0]();
  });
  await scenario('closing invalidates in-flight QR and reopening waits for own capability revocation', async () => {
    const qr = deferred();
    const revocation = deferred();
    let calls = 0;
    let deletions = 0;
    const page = selectorPage({
      scanUploadQrcode: () => {
        calls++;
        return qr.promise;
      },
      scanUploadCode: () => {
        deletions++;
        return revocation.promise;
      },
    });
    const first = page.scanUploadQrcodeFn();
    await flush();
    page.closed();
    qr.resolve({ data: { url: `/app/upload?token=${'a'.repeat(64)}` } });
    await first;
    assert.equal(page.scanToken.value, '');
    assert.equal(page.codes.length, 0);
    page.ruleForm.type = 2;
    const reopened = page.scanUploadQrcodeFn();
    await flush();
    assert.equal(calls, 1);
    assert.equal(deletions, 1);
    revocation.resolve();
    await reopened;
    assert.equal(calls, 2);
    assert.equal(page.timers.size, 1);
    page.unmounts[0]();
  });
  await scenario('failed capability revocation is visible and cannot leave QR regeneration pending', async () => {
    const page = selectorPage({
      scanUploadCode: async () => {
        throw new Error('isolated revocation failure');
      },
    });
    page.closed();
    page.ruleForm.type = 2;
    await page.scanUploadQrcodeFn();
    assert.equal(page.messages.filter(([type]) => type === 'error').length, 1);
    assert.equal(page.scanToken.value, 'a'.repeat(64));
    assert.equal(page.timers.size, 1);
    page.unmounts[0]();
  });
  console.log(`Storage admin regression: ${passed} scenarios passed`);
}

run().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
