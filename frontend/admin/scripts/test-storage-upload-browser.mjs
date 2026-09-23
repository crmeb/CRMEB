import assert from 'node:assert/strict';
import { existsSync } from 'node:fs';
import { mkdtemp } from 'node:fs/promises';
import { createRequire } from 'node:module';
import { createServer as createPortProbe } from 'node:net';
import { homedir, tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createServer } from 'vite';
import vue from '@vitejs/plugin-vue';

// Real SFCs and browser compression; only transport/session and setup observation are isolated.
const adminRoot = fileURLToPath(new URL('../', import.meta.url));
const localRequire = createRequire(import.meta.url);
const runtimeRequire = createRequire(
  path.join(
    process.env.CRMEB_BROWSER_TEST_RUNTIME ||
      path.join(homedir(), '.cache/codex-runtimes/codex-primary-runtime/dependencies/node'),
    'package.json',
  ),
);
function testDependency(name) {
  try {
    return localRequire(name);
  } catch (error) {
    if (error.code !== 'MODULE_NOT_FOUND') throw error;
    return runtimeRequire(name);
  }
}
const { chromium } = testDependency('playwright');
const { PNG } = testDependency('pngjs');
const pickerPath = path.join(adminRoot, 'src/components/uploadImg/index.vue');
const phonePath = path.join(adminRoot, 'src/pages/app/upload/index.vue');
const scanToken = 'b'.repeat(64);
const labels = {
  confirm: '\u786e \u5b9a',
  cancel: '\u53d6 \u6d88',
  scan: '\u626b\u7801\u4e0a\u4f20',
  refresh: '\u5237\u65b0\u56fe\u5e93',
  upload: '\u786e\u8ba4\u4e0a\u4f20',
  again: '\u7ee7\u7eed\u4e0a\u4f20',
  success: '\u56fe\u7247\u4e0a\u4f20\u6210\u529f',
};

function makePng(width, height, noisy) {
  const png = new PNG({ width, height });
  let seed = 19571;
  for (let i = 0; i < png.data.length; i += 4) {
    for (let channel = 0; channel < 3; channel++) {
      seed = (Math.imul(seed, 1664525) + 1013904223) >>> 0;
      png.data[i + channel] = noisy ? seed >>> 24 : 40 + ((i / 4 + channel * 63) % 180);
    }
    png.data[i + 3] = 255;
  }
  return PNG.sync.write(png);
}
const bigPng = makePng(900, 900, true);
assert(bigPng.length >= 2097152 && bigPng.length < 3000000, 'Real PNG must exercise the large-file path');
const smallPng = makePng(48, 48, false);
const validFiles = [
  { name: 'large-fixture.png', mimeType: 'image/png', buffer: bigPng },
  { name: 'small-fixture.png', mimeType: 'image/png', buffer: smallPng },
];
const invalidFile = { name: 'broken-fixture.png', mimeType: 'image/png', buffer: Buffer.alloc(2097152, 120) };

async function freeLoopbackPort() {
  const probe = createPortProbe();
  try {
    await new Promise((resolve, reject) => {
      probe.once('error', reject);
      probe.listen(0, '127.0.0.1', resolve);
    });
    return probe.address().port;
  } finally {
    if (probe.listening)
      await new Promise((resolve, reject) => probe.close((error) => (error ? reject(error) : resolve())));
  }
}

async function run() {
  const artifacts = await mkdtemp(path.join(tmpdir(), 'crmeb-storage-upload-browser-'));
  const requests = [];
  const screenshots = [];
  const issues = [];
  let checks = 0;
  let holdUploads = false;
  const pendingUploads = [];
  let origin;
  let server;
  let browser;
  let activePage;
  const scanRows = [1, 2, 3].map((att_id) => ({ att_id, att_dir: '/__storage_upload_bitmap.png' }));
  const virtualIds = {
    'virtual:storage-upload-entry': '\0storage-upload-entry',
    'virtual:storage-upload-request': '\0storage-upload-request',
    'virtual:storage-upload-setting': '\0storage-upload-setting',
    'virtual:storage-upload-util': '\0storage-upload-util',
  };
  const requestModule = `
export default async function request(config) {
  const url = new URL('/__storage_upload_api/' + config.url, location.origin);
  for (const [key, value] of Object.entries(config.params || {})) {
    if (value !== undefined && value !== null) url.searchParams.set(key, value);
  }
  const response = await fetch(url, {
    method: config.method.toUpperCase(),
    credentials: 'omit',
    headers: config.file ? undefined : { 'Content-Type': 'application/json' },
    body: config.data === undefined ? undefined : config.file ? config.data : JSON.stringify(config.data),
  });
  const result = await response.json();
  if (!response.ok || result.status !== 200) throw result;
  return result;
}
`;
  const entryModule = `
import { createApp, h } from 'vue';
import { createRouter, createMemoryHistory } from 'vue-router';
import ElementPlus from 'element-plus';
import zhCn from 'element-plus/es/locale/lang/zh-cn';
import * as icons from '@element-plus/icons-vue';
import 'element-plus/dist/index.css';
import '@/theme/index.scss';
import '@/styles/index.scss';
import '@/styles/el-icon-compat.css';
import UploadPicker from '@/components/uploadImg/index.vue';
import PhoneUpload from '@/pages/app/upload/index.vue';
import dbClick from '@/directive/module/dbClick.js';
const phone = new URLSearchParams(location.search).get('page') === 'phone';
const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', component: { render: () => null } }],
});
window.__uploadSuccesses = 0;
const app = createApp({
  render: () => h('main', { class: 'storage-upload-workspace' }, h(phone ? PhoneUpload : UploadPicker, {
    categoryId: 11,
    categoryList: [{ id: 11, title: 'Fixture images' }],
    onUploadSuccess: () => window.__uploadSuccesses++,
  })),
});
app.use(router);
app.use(ElementPlus, { locale: zhCn });
for (const [name, component] of Object.entries(icons)) app.component(name, component);
app.directive('db-click', dbClick);
await router.push('/?pid=11&token=${scanToken}');
await router.isReady();
app.mount('#app');
`;
  const html = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Isolated real upload browser test</title><style>html,body,#app{min-height:100%;margin:0}.storage-upload-workspace{min-height:100vh;background:var(--prev-bg-main-color)}</style></head><body><div id="app"></div><script type="module">import 'virtual:storage-upload-entry';</script></body></html>`;
  function fixturePlugin() {
    return {
      name: 'isolated-real-upload-browser-fixtures',
      enforce: 'pre',
      resolveId(id) {
        return virtualIds[id];
      },
      load(id) {
        if (id === virtualIds['virtual:storage-upload-entry']) return entryModule;
        if (id === virtualIds['virtual:storage-upload-request']) return requestModule;
        if (id === virtualIds['virtual:storage-upload-setting'])
          return "export default { apiBaseURL: '/__storage_upload_api' };";
        if (id === virtualIds['virtual:storage-upload-util'])
          return "export const getCookies = () => 'isolated-noncredential-fixture';";
      },
      transform(code, id) {
        const filename = id.split('?')[0];
        let observation;
        if (filename === pickerPath) {
          observation = `
window.__storageUploadHandle = {
  open: () => { uploadModal.value = true; },
  state: () => ({
    preparing: preparing.value, loading: loading.value, open: uploadModal.value, type: ruleForm.type,
    files: ruleForm.imgList.map(item => ({ id: item.att_id, name: item.raw?.name, type: item.raw?.type, size: item.raw?.size })),
  }),
  submit: submitUpload, poll: scanUploadGetFn,
};
`;
        } else if (filename === phonePath) {
          observation = `
window.__storageUploadHandle = {
  state: () => ({
    preparing: preparing.value, loading: loading.value, uploading: uploading.value, total: allSize.value,
    files: imgList.value.map(item => ({ name: item.raw?.name, type: item.raw?.type, size: item.raw?.size })),
  }),
  submit: submitUpload,
};
`;
        }
        if (observation && !id.includes('?')) return code.replace('</script>', observation + '</script>');
      },
      configureServer(viteServer) {
        viteServer.middlewares.use(async (req, res, next) => {
          const url = new URL(req.url, 'http://127.0.0.1');
          if (url.pathname === '/') {
            res.setHeader('Content-Type', 'text/html');
            res.end(await viteServer.transformIndexHtml('/', html));
            return;
          }
          if (url.pathname === '/favicon.ico') {
            res.statusCode = 204;
            res.end();
            return;
          }
          if (url.pathname === '/__storage_upload_bitmap.png') {
            res.setHeader('Content-Type', 'image/png');
            res.end(smallPng);
            return;
          }
          if (!url.pathname.startsWith('/__storage_upload_api/')) return next();
          try {
            for (const header of ['cookie', 'authorization', 'authori-zation'])
              assert.equal(req.headers[header], undefined, 'Fixture request must not carry session credentials');
            const chunks = [];
            for await (const chunk of req) chunks.push(chunk);
            const body = Buffer.concat(chunks);
            const api = url.pathname.slice('/__storage_upload_api/'.length);
            let data;
            if ((req.headers['content-type'] || '').startsWith('multipart/form-data')) {
              const form = await new Request(url, {
                method: req.method,
                headers: { 'Content-Type': req.headers['content-type'] },
                body,
              }).formData();
              data = Object.fromEntries(form);
              assert(data.file instanceof Blob, 'Actual upload API must receive a browser file');
              const buffer = Buffer.from(await data.file.arrayBuffer());
              data.file = { name: data.file.name, type: data.file.type, size: buffer.length, buffer };
            } else if (body.length) {
              data = JSON.parse(body.toString('utf8'));
            }
            requests.push({ api, method: req.method, data, params: Object.fromEntries(url.searchParams) });
            let response;
            if (req.method === 'GET' && api === 'file/scan_upload/qrcode') {
              response = { url: `${origin}/?page=phone&pid=11&token=${scanToken}` };
            } else if (req.method === 'GET' && api === `file/scan_upload/image/${scanToken}`) {
              response = scanRows.map((row) => ({ ...row }));
            } else if (req.method === 'DELETE' && api === 'file/scan_upload/qrcode') {
              response = [];
            } else if (req.method === 'POST' && ['file/upload', 'image/scan_upload'].includes(api)) {
              assert.equal(data.pid, '11');
              if (api === 'image/scan_upload') assert.equal(data.uploadToken, scanToken);
              if (holdUploads) await new Promise((resolve) => pendingUploads.push(resolve));
              response = { att_id: requests.length, att_dir: '/__storage_upload_bitmap.png' };
            } else if (req.method === 'PUT' && api === 'file/file/do_move') {
              assert.equal(data.pid, 11);
              response = [];
            } else {
              throw new Error('Unsupported isolated fixture API: ' + api);
            }
            res.setHeader('Content-Type', 'application/json');
            res.end(JSON.stringify({ status: 200, msg: 'Fixture success', data: response }));
          } catch (error) {
            issues.push(error.message);
            res.statusCode = 500;
            res.setHeader('Content-Type', 'application/json');
            res.end(JSON.stringify({ status: 500, msg: 'Isolated fixture failure', data: [] }));
          }
        });
      },
    };
  }
  function releaseUploads() {
    holdUploads = false;
    for (const resolve of pendingUploads.splice(0)) resolve();
  }
  function uploadRequests(start, api) {
    return requests.slice(start).filter((request) => request.api === api && request.method === 'POST');
  }
  function verifyUploadedFiles(start, api, name) {
    const uploads = uploadRequests(start, api);
    assert.equal(uploads.length, 2, `${name}: both selected files must upload exactly once`);
    const large = uploads.find((request) => request.data.file.name === 'large-fixture.jpg')?.data.file;
    assert(large, `${name}: large PNG must use a .jpg filename`);
    assert.equal(large.type, 'image/jpeg');
    assert(large.size > 1000 && large.size < bigPng.length, `${name}: JPEG encoding must contain compressed pixels`);
    assert.equal(large.buffer.subarray(0, 3).toString('hex'), 'ffd8ff');
    assert.equal(large.buffer.subarray(-2).toString('hex'), 'ffd9');
    const small = uploads.find((request) => request.data.file.name === 'small-fixture.png')?.data.file;
    assert(small, `${name}: small original PNG must remain selected`);
    assert.equal(small.type, 'image/png');
    assert.deepEqual(small.buffer, smallPng);
    checks++;
  }
  async function screenshot(page, name) {
    const filename = path.join(artifacts, name + '.png');
    const buffer = await page.screenshot({ path: filename, fullPage: false, animations: 'disabled' });
    screenshots.push(filename);
    const png = PNG.sync.read(buffer);
    const colors = new Set();
    let ink = 0;
    for (let offset = 0; offset < png.data.length; offset += 16) {
      const [r, g, b] = png.data.subarray(offset, offset + 3);
      colors.add(`${r >> 4},${g >> 4},${b >> 4}`);
      if (Math.min(r, g, b) < 210) ink++;
    }
    assert(colors.size > 12 && ink > 200, `${name}: screenshot must contain rendered bitmap/UI pixels`);
  }
  async function checkLayout(page, name, selector) {
    const result = await page.locator(selector).evaluate((scope) => {
      const problems = [];
      const bounds = scope.getBoundingClientRect();
      const visible = (element) => {
        const r = element.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && getComputedStyle(element).visibility !== 'hidden';
      };
      const overlap = (a, b) =>
        Math.min(a.right, b.right) - Math.max(a.left, b.left) > 2 &&
        Math.min(a.bottom, b.bottom) - Math.max(a.top, b.top) > 2;
      if (bounds.left < -2 || bounds.right > innerWidth + 2)
        problems.push(`frame exceeds viewport: x=${Math.round(bounds.left)}..${Math.round(bounds.right)}`);
      for (const element of scope.querySelectorAll('.el-cascader, .el-upload, .code, .footer .btn, .el-button')) {
        if (!visible(element)) continue;
        const r = element.getBoundingClientRect();
        if (r.left < bounds.left - 2 || r.right > bounds.right + 2)
          problems.push(`control exceeds frame: ${element.className}, x=${Math.round(r.left)}..${Math.round(r.right)}`);
      }
      for (const item of scope.querySelectorAll('.el-form-item')) {
        const label = item.querySelector('.el-form-item__label');
        const content = item.querySelector('.el-form-item__content');
        if (
          visible(item) &&
          label &&
          content &&
          overlap(label.getBoundingClientRect(), content.getBoundingClientRect())
        )
          problems.push('Form label overlaps its content');
      }
      for (const footer of scope.querySelectorAll('.footer, .dialog-footer')) {
        const buttons = [...footer.querySelectorAll('button, .btn')].filter(visible);
        for (let i = 0; i < buttons.length; i++) {
          for (let j = i + 1; j < buttons.length; j++) {
            if (overlap(buttons[i].getBoundingClientRect(), buttons[j].getBoundingClientRect()))
              problems.push('Footer commands overlap');
          }
        }
        if (footer.classList.contains('footer')) {
          const counter = footer.firstElementChild;
          const commands = footer.lastElementChild;
          if (counter && commands && overlap(counter.getBoundingClientRect(), commands.getBoundingClientRect()))
            problems.push('Phone counter overlaps upload commands');
        }
      }
      for (const image of scope.querySelectorAll('img')) {
        if (visible(image) && (!image.complete || !image.naturalWidth))
          problems.push('Visible bitmap failed to render');
        if (image.classList.contains('el-upload-list__item-thumbnail')) {
          const r = image.getBoundingClientRect();
          if (r.width < 32 || r.height < 32) problems.push('Selected image thumbnail collapsed or hidden');
          else if (Math.abs(r.width - r.height) > 2)
            problems.push('Selected image thumbnail must preserve its square frame');
        }
      }
      return [...new Set(problems)];
    });
    for (const problem of result) issues.push(`${name}: ${problem}`);
    checks++;
  }
  async function state(page) {
    return page.evaluate(() => window.__storageUploadHandle.state());
  }
  async function ready(page, phone) {
    await page.goto(`${origin}/?page=${phone ? 'phone' : 'picker'}`, { waitUntil: 'networkidle' });
    await page.waitForFunction(() => window.__storageUploadHandle);
    if (!phone) {
      await page.evaluate(() => window.__storageUploadHandle.open());
      try {
        await page.getByRole('dialog').waitFor();
      } catch (error) {
        console.error(
          await page.evaluate(() => ({
            state: window.__storageUploadHandle.state(),
            html: document.body.innerHTML.slice(-6000),
          })),
        );
        throw error;
      }
    }
  }
  async function releaseCompression(page) {
    await page.evaluate(() => {
      window.__holdRealCompression = false;
      for (const run of window.__pendingRealCompression.splice(0)) run();
    });
    await page.waitForFunction(() => window.__storageUploadHandle.state().preparing === 0);
  }
  async function waitForUpload(start, api) {
    const deadline = Date.now() + 10000;
    while (!uploadRequests(start, api).length && Date.now() < deadline)
      await new Promise((resolve) => setTimeout(resolve, 10));
    assert.equal(uploadRequests(start, api).length, 1, 'Fixture must receive and parse the first multipart upload');
  }
  async function settledUi(page) {
    await page.waitForFunction(() => !document.querySelector('.el-loading-mask'));
  }
  async function exerciseCompression(page, name, phone) {
    const api = phone ? 'image/scan_upload' : 'file/upload';
    const start = requests.length;
    await page.evaluate(() => {
      window.__holdRealCompression = true;
    });
    await page.locator('input[type="file"]').setInputFiles(validFiles);
    await page.waitForFunction(() => {
      const snapshot = window.__storageUploadHandle.state();
      return snapshot.preparing === 1 && snapshot.files.length === 2 && window.__pendingRealCompression.length === 1;
    });
    await page.evaluate(() => window.__storageUploadHandle.submit());
    assert.equal(uploadRequests(start, api).length, 0, `${name}: pending compression must block submission`);
    if (!phone) {
      assert.equal(await page.locator('.el-dialog__headerbtn').count(), 0, `${name}: pending compression hides close`);
      assert(await page.getByRole('button', { name: labels.cancel, exact: true }).isDisabled());
      await page.keyboard.press('Escape');
      assert.equal((await state(page)).open, true);
    }
    await screenshot(page, name + '-preparing');
    checks++;
    await releaseCompression(page);
    const snapshot = await state(page);
    assert.equal(snapshot.files.length, 2);
    assert.equal(snapshot.files[0].name, 'large-fixture.jpg');
    assert.equal(snapshot.files[0].type, 'image/jpeg');
    if (phone)
      assert.equal(
        snapshot.total,
        snapshot.files.reduce((sum, file) => sum + file.size, 0),
      );
    await settledUi(page);
    await page.waitForFunction(() => [...document.querySelectorAll('img')].every((img) => img.complete));
    await screenshot(page, name + '-prepared');
    await checkLayout(page, name + '-prepared', phone ? '.storage-upload-workspace' : '.el-dialog');
    holdUploads = true;
    if (phone) await page.getByText(labels.upload, { exact: true }).click();
    else await page.getByRole('button', { name: labels.confirm, exact: true }).click();
    await page.waitForFunction(() => window.__storageUploadHandle.state().loading);
    await waitForUpload(start, api);
    assert.equal(uploadRequests(start, api).length, 1);
    await page.evaluate(() => window.__storageUploadHandle.submit());
    assert.equal(uploadRequests(start, api).length, 1, `${name}: duplicate submit must not send another file`);
    if (!phone) {
      assert.equal(await page.locator('.el-dialog__headerbtn').count(), 0, `${name}: upload hides close`);
      assert(await page.getByRole('button', { name: labels.cancel, exact: true }).isDisabled());
      await page.keyboard.press('Escape');
      assert.equal((await state(page)).open, true);
    }
    await screenshot(page, name + '-submitting');
    checks++;
    releaseUploads();
    await page.waitForFunction(() => {
      const snapshot = window.__storageUploadHandle.state();
      return !snapshot.loading && (snapshot.uploading === false || snapshot.open === false);
    });
    verifyUploadedFiles(start, api, name);
    if (phone) {
      await page.getByText(labels.success, { exact: true }).waitFor();
      await settledUi(page);
      await screenshot(page, name + '-success');
      await checkLayout(page, name + '-success', '.storage-upload-workspace');
    } else {
      assert.equal(await page.evaluate(() => window.__uploadSuccesses), 1);
    }
  }
  async function exerciseInvalidImage(page, name, phone) {
    if (phone) await page.getByText(labels.again, { exact: true }).click();
    else {
      await page.evaluate(() => window.__storageUploadHandle.open());
      await page.getByRole('dialog').waitFor();
    }
    const start = requests.length;
    await page.locator('input[type="file"]').setInputFiles(invalidFile);
    await page
      .getByText('\u56fe\u7247\u5185\u5bb9\u65e0\u6cd5\u89e3\u6790\uff0c\u8bf7\u91cd\u65b0\u9009\u62e9', {
        exact: true,
      })
      .waitFor();
    await page.waitForFunction(() => {
      const snapshot = window.__storageUploadHandle.state();
      return snapshot.preparing === 0 && snapshot.files.length === 0;
    });
    assert.equal((await state(page)).loading, false, `${name}: invalid image must release loading`);
    assert.equal(uploadRequests(start, phone ? 'image/scan_upload' : 'file/upload').length, 0);
    await settledUi(page);
    await page.locator('input[type="file"]').setInputFiles(validFiles[1]);
    await page.waitForFunction(() => {
      const files = window.__storageUploadHandle.state().files;
      return files.length === 1 && files[0].name === 'small-fixture.png';
    });
    await page.waitForFunction(() =>
      [...document.querySelectorAll('img')].every((img) => img.complete && img.naturalWidth),
    );
    await screenshot(page, name + '-failure-recovery');
    await checkLayout(page, name + '-failure-recovery', phone ? '.storage-upload-workspace' : '.el-dialog');
    checks++;
  }
  async function exerciseScan(page, name) {
    await page.getByText(labels.scan, { exact: true }).click();
    await page.waitForFunction(
      () => window.__storageUploadHandle.state().type === 2 && document.querySelector('.code img'),
    );
    await page.evaluate(() => window.__storageUploadHandle.poll());
    await page.waitForFunction(() => window.__storageUploadHandle.state().files.length === 3);
    assert.deepEqual(
      (await state(page)).files.map((file) => file.id),
      [1, 2, 3],
    );
    await page.locator('.code-image .right .pictrue .btndel').nth(1).click();
    assert.deepEqual(
      (await state(page)).files.map((file) => file.id),
      [1, 3],
    );
    const refreshed = page.waitForResponse(
      (response) => new URL(response.url()).pathname === `/__storage_upload_api/file/scan_upload/image/${scanToken}`,
    );
    await page.getByRole('button', { name: labels.refresh, exact: true }).click();
    await refreshed;
    await page.evaluate(() => window.__storageUploadHandle.poll());
    assert.deepEqual(
      (await state(page)).files.map((file) => file.id),
      [1, 3],
      `${name}: refresh must keep removed second image excluded`,
    );
    await page.waitForFunction(() =>
      [...document.querySelectorAll('.code-image img')].every((img) => img.complete && img.naturalWidth),
    );
    await screenshot(page, name + '-scan-removed-refreshed');
    await checkLayout(page, name + '-scan-removed-refreshed', '.el-dialog');
    const canvasInk = await page.locator('.code canvas').evaluate((canvas) => {
      const pixels = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
      let dark = 0;
      for (let i = 0; i < pixels.length; i += 4) if (pixels[i] < 100) dark++;
      return dark;
    });
    assert(canvasInk > 1000, `${name}: actual QR canvas must encode nonblank pixels`);
    checks++;
    await page.getByRole('button', { name: labels.cancel, exact: true }).click();
    await page.getByRole('dialog').waitFor({ state: 'hidden' });
  }
  try {
    const port = await freeLoopbackPort();
    server = await createServer({
      configFile: false,
      root: adminRoot,
      cacheDir: path.join(artifacts, 'vite-cache'),
      plugins: [fixturePlugin(), vue()],
      optimizeDeps: {
        noDiscovery: true,
        entries: [],
        include: ['vue', 'vue-router', 'element-plus', '@element-plus/icons-vue'],
      },
      resolve: {
        alias: [
          { find: '@/libs/request', replacement: 'virtual:storage-upload-request' },
          { find: '@/libs/util', replacement: 'virtual:storage-upload-util' },
          { find: '@/setting', replacement: 'virtual:storage-upload-setting' },
          { find: /^vue$/, replacement: path.join(adminRoot, 'node_modules/vue/dist/vue.esm-bundler.js') },
          { find: '@', replacement: path.join(adminRoot, 'src') },
        ],
      },
      css: {
        preprocessorOptions: {
          scss: {
            api: 'modern-compiler',
            silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions', 'slash-div'],
          },
        },
      },
      server: { host: '127.0.0.1', port, hmr: false, strictPort: true, fs: { allow: [adminRoot] } },
      logLevel: 'warn',
    });
    await server.listen();
    origin = `http://127.0.0.1:${server.httpServer.address().port}`;
    const channel =
      process.env.CRMEB_BROWSER_TEST_CHANNEL || (existsSync(chromium.executablePath()) ? undefined : 'chrome');
    browser = await chromium.launch({ headless: true, channel });
    for (const [name, viewport] of Object.entries({
      desktop: { width: 1440, height: 900 },
      mobile: { width: 390, height: 844 },
      narrow: { width: 320, height: 844 },
    })) {
      const context = await browser.newContext({ viewport });
      try {
        const page = (activePage = await context.newPage());
        page.setDefaultTimeout(10000);
        const errors = [];
        page.on('pageerror', (error) => {
          errors.push(error.message);
          console.error(error.message);
        });
        await context.route('**/*', (route) => {
          if (new URL(route.request().url()).origin === origin) return route.continue();
          errors.push('Unexpected non-loopback network request');
          return route.abort();
        });
        await context.addInitScript(() => {
          for (const method of ['setItem', 'removeItem', 'clear']) {
            Storage.prototype[method] = () => {
              throw new Error('Isolated test must not mutate business browser storage');
            };
          }
          window.__pendingRealCompression = [];
          window.__holdRealCompression = false;
          const original = HTMLCanvasElement.prototype.toBlob;
          HTMLCanvasElement.prototype.toBlob = function (...args) {
            const run = () => original.apply(this, args);
            if (window.__holdRealCompression) window.__pendingRealCompression.push(run);
            else run();
          };
        });
        if (process.env.CRMEB_UPLOAD_BROWSER_ONLY_PHONE !== '1') {
          await ready(page, false);
          await exerciseCompression(page, name + '-picker', false);
          await exerciseInvalidImage(page, name + '-picker', false);
          await exerciseScan(page, name + '-picker');
        }
        await ready(page, true);
        await exerciseCompression(page, name + '-phone', true);
        await exerciseInvalidImage(page, name + '-phone', true);
        assert.deepEqual(errors, [], `${name}: real SFCs must run without uncaught browser errors`);
        console.log(
          process.env.CRMEB_UPLOAD_BROWSER_ONLY_PHONE === '1'
            ? `PASS: ${name} real phone compression, MIME/content, guarded submit and bad-image recovery`
            : `PASS: ${name} real picker/phone compression, MIME/content, guarded close/submit, bad-image recovery and scanner exclusion`,
        );
      } catch (error) {
        if (activePage && !activePage.isClosed()) await screenshot(activePage, name + '-failure').catch(() => {});
        throw error;
      } finally {
        releaseUploads();
        activePage = undefined;
        await context.close();
      }
    }
    assert.deepEqual(issues, [], 'Visible upload controls and assets must fit without overlap or fixture errors');
    console.log(
      `PASS: ${checks} upload/render scenario checks across 3 viewports; ${screenshots.length} nonblank screenshots`,
    );
  } catch (error) {
    console.error(error.stack);
    for (const issue of issues) console.error(issue);
    process.exitCode = 1;
  } finally {
    releaseUploads();
    try {
      await browser?.close();
    } finally {
      await server?.close();
      console.log(`Retained isolated upload screenshots: ${artifacts}`);
      for (const filename of screenshots) console.log(filename);
    }
  }
}
run().catch((error) => {
  console.error(error.stack);
  process.exitCode = 1;
});
