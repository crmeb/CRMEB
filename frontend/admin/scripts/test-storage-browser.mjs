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

// Mount real SFCs, styles and API functions without loading the business app/session.
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
async function run() {
  const artifacts = await mkdtemp(path.join(tmpdir(), 'crmeb-storage-browser-'));
  const requests = [];
  const issues = [];
  const screenshots = [];
  const labels = {
    settings: '\u8bbe\u7f6e',
    save: '\u4fdd\u5b58',
    basic: '\u57fa\u7840\u914d\u7f6e',
    storage: '\u7a7a\u95f4\u914d\u7f6e',
    cancel: '\u53d6\u6d88',
    statusTitle: '\u5207\u6362\u72b6\u6001',
    configuration: '\u4fee\u6539\u914d\u7f6e\u4fe1\u606f',
    configurationTitle: '\u914d\u7f6e\u4fe1\u606f',
    addStorage: '\u6dfb\u52a0\u5b58\u50a8\u7a7a\u95f4',
    addStorageTitle: '\u6dfb\u52a0\u4e91\u7a7a\u95f4',
    return: '\u8fd4\u56de',
  };
  const commonConfig = {
    image_thumb_status: 1,
    thumb_big_width: 800,
    thumb_big_height: 800,
    thumb_mid_width: 300,
    thumb_mid_height: 300,
    thumb_small_width: 150,
    thumb_small_height: 150,
    image_watermark_status: 1,
    watermark_type: 1,
    watermark_opacity: 0,
    watermark_rotate: 0,
    watermark_position: 1,
    watermark_x: 0,
    watermark_y: 0,
    watermark_image: '',
    watermark_text: '',
    watermark_text_size: 16,
    watermark_text_color: '#000000',
    watermark_text_angle: 0,
  };
  function fixtureDriver(id = 7) {
    const builtin = Number(id) === 1;
    return {
      id: builtin ? 1 : 7,
      driver_key: builtin ? 'local' : 'oss',
      driver_name: builtin ? 'Fixture Local' : 'Fixture OSS',
      sort: builtin ? 0 : 1,
      status: builtin ? 1 : 0,
      is_builtin: builtin ? 1 : 0,
      icon: '',
      config_fields: builtin
        ? []
        : [
            {
              field: 'accessKey',
              label: 'AccessKey ID',
              type: 'text',
              group: 'Keys',
              value: '********',
              required: true,
            },
            {
              field: 'secretKey',
              label: 'AccessKey Secret',
              type: 'password',
              group: 'Keys',
              value: '********',
              required: true,
            },
          ],
      bucket_fields: [{ field: 'name', label: 'Name', type: 'text', value: '', required: true }],
    };
  }
  const storageRows = [
    {
      id: 71,
      name: 'fixture-bucket',
      _region: 'Fixture region',
      domain: 'https://cdn.fixture.invalid/assets/',
      cname: 'fixture-bucket.oss-cn-fixture.invalid',
      status: 0,
      _add_time: '2026-01-01',
      _update_time: '2026-01-02',
    },
  ];
  const virtualIds = {
    'virtual:storage-browser-entry': '\0storage-browser-entry',
    'virtual:storage-browser-request': '\0storage-browser-request',
    'virtual:storage-browser-upload': '\0storage-browser-upload',
    'virtual:storage-browser-modal': '\0storage-browser-modal',
    'virtual:storage-browser-public': '\0storage-browser-public',
    'virtual:storage-browser-util': '\0storage-browser-util',
  };
  const requestModule = `
export default async function request(config) {
  const url = new URL('/__storage_browser_api/' + config.url, location.origin);
  for (const [key, value] of Object.entries(config.params || {})) {
    if (value !== undefined && value !== null) url.searchParams.set(key, value);
  }
  const response = await fetch(url, {
    method: config.method.toUpperCase(),
    credentials: 'omit',
    headers: { 'Content-Type': 'application/json' },
    body: config.data === undefined ? undefined : JSON.stringify(config.data),
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
import UploadDriverList from '@/pages/driver/uploadDriver/index.vue';
import Pagination from '@/components/Pagination/index.vue';
import dbClick from '@/directive/module/dbClick.js';
const router = createRouter({
  history: createMemoryHistory(),
  routes: [{ path: '/', component: { render: () => null } }],
});
const app = createApp({
  render: () => h('main', { class: 'storage-browser-workspace' }, h(UploadDriverList)),
});
app.use(router);
app.use(ElementPlus, { locale: zhCn });
app.component('Pagination', Pagination);
for (const [name, component] of Object.entries(icons)) app.component(name, component);
app.directive('auth', {});
app.directive('db-click', dbClick);
app.config.globalProperties.$copyText = async () => {};
await router.push('/');
await router.isReady();
app.mount('#app');
`;
  const html = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Isolated storage browser test</title><style>html,body,#app{min-height:100%;margin:0}.storage-browser-workspace{padding:16px;box-sizing:border-box;background:var(--prev-bg-main-color);min-height:100vh}</style></head><body><div id="app"></div><script type="module">import 'virtual:storage-browser-entry';</script></body></html>`;
  function fixturePlugin() {
    return {
      name: 'isolated-storage-browser-fixtures',
      enforce: 'pre',
      resolveId(id) {
        return virtualIds[id];
      },
      load(id) {
        if (id === virtualIds['virtual:storage-browser-entry']) return entryModule;
        if (id === virtualIds['virtual:storage-browser-request']) return requestModule;
        if (id === virtualIds['virtual:storage-browser-upload']) return 'export default { render() { return null; } };';
        if (id === virtualIds['virtual:storage-browser-modal'])
          return "export default () => { throw Error('Fixture does not permit modalForm'); };";
        if (id === virtualIds['virtual:storage-browser-public'])
          return "export const modalSure = () => { throw Error('Fixture does not permit deletion'); };";
        if (id === virtualIds['virtual:storage-browser-util'])
          return "export const getCookies = () => { throw Error('Fixture does not permit business cookie access'); };";
      },
      configureServer(server) {
        server.middlewares.use(async (req, res, next) => {
          const url = new URL(req.url, 'http://127.0.0.1');
          if (url.pathname === '/') {
            res.setHeader('Content-Type', 'text/html');
            res.end(await server.transformIndexHtml('/', html));
            return;
          }
          if (!url.pathname.startsWith('/__storage_browser_api/')) return next();
          for (const header of ['cookie', 'authorization', 'authori-zation'])
            assert.equal(req.headers[header], undefined);
          let body = '';
          for await (const chunk of req) body += chunk;
          const api = url.pathname.slice('/__storage_browser_api/'.length);
          const data = body ? JSON.parse(body) : undefined;
          requests.push({ api, method: req.method, data, params: Object.fromEntries(url.searchParams) });
          let response;
          if (req.method === 'GET' && api === 'driver/upload_driver/list') {
            response = { list: [fixtureDriver(1), fixtureDriver(7)] };
          } else if (req.method === 'GET' && /^driver\/upload_driver\/detail\/(1|7)$/.test(api)) {
            response = fixtureDriver(api.split('/').at(-1));
          } else if (req.method === 'GET' && /^driver\/upload_driver\/common_config\/(local|oss)$/.test(api)) {
            response = { ...commonConfig };
          } else if (req.method === 'GET' && api === 'system/config/storage') {
            assert.equal(url.searchParams.get('driver_key'), 'oss');
            response = { list: storageRows.map((row) => ({ ...row })), count: storageRows.length };
          } else if (req.method === 'POST' && api === 'driver/upload_driver/save_config/7') {
            await new Promise((resolve) => setTimeout(resolve, 150));
            response = [];
          } else {
            res.statusCode = 500;
            res.setHeader('Content-Type', 'application/json');
            res.end(JSON.stringify({ status: 500, msg: 'Unsupported isolated fixture request', data: [] }));
            return;
          }
          res.setHeader('Content-Type', 'application/json');
          res.end(JSON.stringify({ status: 200, msg: 'Fixture success', data: response }));
        });
      },
    };
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
    assert(colors.size > 12 && ink > 400, `${name}: screenshot must contain rendered UI pixels`);
  }
  async function checkLayout(page, name, selector) {
    const result = await page.locator(selector).evaluate((scope) => {
      const problems = [];
      const visible = (element) => {
        const r = element.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && getComputedStyle(element).visibility !== 'hidden';
      };
      const describe = (element) =>
        (element.innerText || element.getAttribute('placeholder') || element.tagName)
          .trim()
          .replace(/\s+/g, ' ')
          .slice(0, 45);
      const overlap = (a, b) => {
        const x = Math.min(a.right, b.right) - Math.max(a.left, b.left);
        const y = Math.min(a.bottom, b.bottom) - Math.max(a.top, b.top);
        return x > 2 && y > 2;
      };
      for (const item of scope.querySelectorAll('.el-form-item')) {
        if (!visible(item)) continue;
        const label = item.querySelector('.el-form-item__label');
        const content = item.querySelector('.el-form-item__content');
        if (content && content.getBoundingClientRect().width < 20) {
          problems.push(
            `form content collapsed: ${describe(label || item)} (${Math.round(
              content.getBoundingClientRect().width,
            )}px)`,
          );
        }
        if (label && content && overlap(label.getBoundingClientRect(), content.getBoundingClientRect())) {
          problems.push(`form label overlaps content: ${describe(label)}`);
        }
      }
      for (const toolbar of scope.querySelectorAll('.el-row > .el-col, .form-footer, .el-message-box__btns')) {
        const buttons = [...toolbar.querySelectorAll('button')].filter(visible);
        for (let i = 0; i < buttons.length; i++) {
          for (let j = i + 1; j < buttons.length; j++) {
            if (overlap(buttons[i].getBoundingClientRect(), buttons[j].getBoundingClientRect())) {
              problems.push(`buttons overlap: ${describe(buttons[i])} / ${describe(buttons[j])}`);
            }
          }
        }
      }
      const bounds = scope.getBoundingClientRect();
      if (bounds.left < -2 || bounds.right > innerWidth + 2) {
        problems.push(
          `frame exceeds viewport: ${Math.round(bounds.width)}px, x=${Math.round(bounds.left)}..${Math.round(
            bounds.right,
          )}, viewport=${innerWidth}px`,
        );
      }
      for (const element of scope.querySelectorAll(
        'button, .el-input, .el-select, .el-input-number, .el-switch, .el-pagination, .pagination-container, .thumb-item, .position-container',
      )) {
        if (!visible(element) || element.closest('.el-table')) continue;
        const r = element.getBoundingClientRect();
        if (r.left < bounds.left - 2 || r.right > bounds.right + 2) {
          problems.push(
            `control exceeds content width: ${describe(element)} (${Math.round(r.width)}px, x=${Math.round(
              r.left,
            )}..${Math.round(r.right)}, content=${Math.round(bounds.left)}..${Math.round(bounds.right)})`,
          );
        }
      }
      for (const image of scope.querySelectorAll('img')) {
        if (visible(image) && (!image.complete || !image.naturalWidth))
          problems.push('visible bitmap asset failed to render');
      }
      for (const button of scope.querySelectorAll('button.el-button')) {
        if (!visible(button)) continue;
        const span = button.querySelector('span');
        if (!span) continue;
        const r = button.getBoundingClientRect();
        const text = span.getBoundingClientRect();
        if (text.left < r.left - 2 || text.right > r.right + 2 || text.top < r.top - 2 || text.bottom > r.bottom + 2) {
          problems.push(`button label exceeds button: ${describe(button)}`);
        }
      }
      return [...new Set(problems)];
    });
    for (const problem of result) issues.push(`${name}: ${problem}`);
  }
  let server;
  let browser;
  let activePage;
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
          { find: '@/libs/request', replacement: 'virtual:storage-browser-request' },
          { find: '@/libs/util', replacement: 'virtual:storage-browser-util' },
          { find: '@/components/uploadPictures', replacement: 'virtual:storage-browser-upload' },
          { find: '@/utils/modalForm', replacement: 'virtual:storage-browser-modal' },
          { find: '@/utils/public', replacement: 'virtual:storage-browser-public' },
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
    const origin = `http://127.0.0.1:${server.httpServer.address().port}`;
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
        page.on('pageerror', (error) => errors.push(error.message));
        page.on('console', (message) => {
          if (message.type() === 'error') errors.push(message.text());
        });
        await context.route('**/*', (route) => {
          if (new URL(route.request().url()).origin === origin) return route.continue();
          errors.push('Unexpected non-loopback network request');
          return route.abort();
        });
        await context.addInitScript(() => {
          Storage.prototype.setItem = () => {
            throw new Error('Isolated test must not write business browser storage');
          };
          Storage.prototype.removeItem = () => {
            throw new Error('Isolated test must not clear business browser storage');
          };
          Storage.prototype.clear = () => {
            throw new Error('Isolated test must not clear business browser storage');
          };
        });
        await page.goto(origin, { waitUntil: 'networkidle' });
        assert.deepEqual(errors, [], `${name}: isolated browser entry must load`);
        const row = page.locator('.el-table__body tr').filter({ hasText: 'Fixture OSS' });
        await row.getByRole('button', { name: labels.settings, exact: true }).waitFor();
        await screenshot(page, `${name}-list`);
        await checkLayout(page, `${name}-list`, '.storage-browser-workspace');
        await row.getByRole('button', { name: labels.settings, exact: true }).click();
        const drawer = page.locator('.el-drawer');
        await drawer.getByRole('tab', { name: labels.basic, exact: true }).waitFor();
        const secretInput = drawer.locator('.el-form-item').filter({ hasText: 'AccessKey Secret' }).locator('input');
        await secretInput.waitFor({ state: 'attached' });
        await page.waitForFunction(() => !document.querySelector('.el-drawer .el-loading-mask'));
        await screenshot(page, `${name}-edit`);
        await checkLayout(page, `${name}-edit`, '.el-drawer__body');
        await secretInput.fill('synthetic-browser-key');
        const beforeSave = requests.length;
        await drawer.getByRole('button', { name: labels.save, exact: true }).click();
        await drawer.waitFor({ state: 'hidden' });
        const writes = requests.slice(beforeSave).filter((request) => request.method !== 'GET');
        assert.equal(writes.length, 1, `${name}: settings save must be one atomic request`);
        assert.equal(writes[0].api, 'driver/upload_driver/save_config/7');
        assert.equal(writes[0].data.secretKey, 'synthetic-browser-key');
        assert.equal(writes[0].data.accessKey, '********');
        assert.equal(writes[0].data.thumb_watermark_config.watermark_opacity, 0);
        assert.equal(writes[0].data.thumb_watermark_config.watermark_x, 0);
        assert.equal(writes[0].data.thumb_watermark_config.image_thumb_status, 1);
        await row.getByRole('button', { name: labels.settings, exact: true }).click();
        await drawer.getByRole('tab', { name: labels.storage, exact: true }).click();
        await drawer.getByText('fixture-bucket', { exact: true }).waitFor();
        await screenshot(page, `${name}-storage`);
        await checkLayout(page, `${name}-storage`, '.el-drawer__body');
        const storageRow = drawer.locator('.el-table__body tr').filter({ hasText: 'fixture-bucket' });
        const storageSwitch = storageRow.getByRole('switch');
        assert.equal(await storageSwitch.getAttribute('aria-checked'), 'false');
        const beforeStatus = requests.length;
        await storageRow.locator('.el-switch').click();
        const confirmation = page.locator('.el-message-box');
        await confirmation.getByText(labels.statusTitle, { exact: true }).waitFor();
        await screenshot(page, `${name}-status-confirmation`);
        await checkLayout(page, `${name}-status-confirmation`, '.el-message-box');
        await confirmation.getByRole('button', { name: labels.cancel, exact: true }).click();
        await confirmation.waitFor({ state: 'hidden' });
        assert.equal(await storageSwitch.getAttribute('aria-checked'), 'false');
        assert.equal(
          requests.slice(beforeStatus).filter((request) => request.method !== 'GET').length,
          0,
          `${name}: cancelled status confirmation must not write`,
        );
        for (const [command, title, suffix] of [
          [labels.configuration, labels.configurationTitle, 'configuration-dialog'],
          [labels.addStorage, labels.addStorageTitle, 'add-dialog'],
        ]) {
          await drawer.getByRole('button', { name: command, exact: true }).click();
          const dialog = page.getByRole('dialog', { name: title, exact: true });
          await dialog.waitFor();
          await screenshot(page, `${name}-${suffix}`);
          await checkLayout(page, `${name}-${suffix}`, '.el-dialog:visible');
          await dialog.getByRole('button', { name: labels.cancel, exact: true }).click();
          await dialog.waitFor({ state: 'hidden' });
        }
        const beforeLocal = requests.length;
        await drawer.getByRole('button', { name: labels.return, exact: true }).click();
        await drawer.waitFor({ state: 'hidden' });
        const localRow = page.locator('.el-table__body tr').filter({ hasText: 'Fixture Local' });
        await localRow.getByRole('button', { name: labels.settings, exact: true }).click();
        await drawer.locator('.thumb-fields input').first().waitFor();
        assert.equal(
          await drawer.getByRole('tab', { name: labels.storage, exact: true }).count(),
          0,
          `${name}: local driver must not expose cloud spaces`,
        );
        assert.equal(
          await drawer.getByText('Keys', { exact: true }).count(),
          0,
          `${name}: local driver must not expose cloud keys`,
        );
        assert.equal(await drawer.locator('.thumb-fields input').count(), 6);
        assert(
          await drawer.locator('.watermark-config input').first().isVisible(),
          `${name}: local watermark controls must be visible`,
        );
        await screenshot(page, `${name}-local-edit`);
        await checkLayout(page, `${name}-local-edit`, '.el-drawer__body');
        await drawer.locator('.position-container').scrollIntoViewIfNeeded();
        await screenshot(page, `${name}-local-watermark`);
        await drawer.getByRole('button', { name: labels.return, exact: true }).click();
        await drawer.waitFor({ state: 'hidden' });
        assert.equal(
          requests.slice(beforeLocal).filter((request) => request.method !== 'GET').length,
          0,
          `${name}: returning from local settings must not write`,
        );
        assert.deepEqual(errors, [], `${name}: real components must render without browser errors`);
        console.log(
          `PASS: ${name} real atomic settings save, storage/status cancellation, dialogs, local settings/watermark, bitmap screenshots`,
        );
      } catch (error) {
        if (activePage && !activePage.isClosed()) await screenshot(activePage, `${name}-failure`).catch(() => {});
        throw error;
      } finally {
        activePage = undefined;
        await context.close();
      }
    }
    assert.deepEqual(issues, [], 'Visible controls must fit their content area without overlap');
    console.log(`PASS: 3 viewport workflows and render/nonoverlap checks; ${screenshots.length} screenshots`);
  } catch (error) {
    if (activePage && !activePage.isClosed()) await screenshot(activePage, 'failure').catch(() => {});
    console.error(error.message);
    for (const issue of issues) console.error(issue);
    process.exitCode = 1;
  } finally {
    try {
      await browser?.close();
    } finally {
      await server?.close();
      console.log(`Retained isolated screenshots: ${artifacts}`);
      for (const filename of screenshots) console.log(filename);
    }
  }
}
run().catch((error) => {
  console.error(error.message);
  process.exitCode = 1;
});
