<template>
  <div class="outapi-doc">
    <el-card shadow="never" class="outapi-doc__tabs-card" :body-style="{ padding: 0 }">
      <el-tabs v-model="apiType" class="outapi-doc__tabs">
        <el-tab-pane label="管理端接口" name="adminapi" />
        <el-tab-pane label="用户端接口" name="api" />
      </el-tabs>
    </el-card>

    <div class="outapi-doc__body">
      <el-card shadow="never" class="outapi-doc__aside">
        <div class="outapi-doc__aside-head">
          <div class="outapi-doc__aside-title">接口目录</div>
          <el-input
            v-model="keyword"
            clearable
            placeholder="搜索接口名称 / 路径"
            class="outapi-doc__search"
          />
        </div>
        <el-scrollbar v-loading="treeLoading" class="outapi-doc__tree-scroll">
          <el-tree
            v-if="treeData.length"
            ref="treeRef"
            :data="treeData"
            node-key="treeKey"
            highlight-current
            :expand-on-click-node="false"
            :default-expanded-keys="expandedKeys"
            :filter-node-method="filterNode"
            :props="treeProps"
            @node-click="onNodeClick"
          >
            <template #default="{ data }">
              <div class="outapi-doc__tree-node" :class="{ 'is-api': data.isLeaf }">
                <span
                  v-if="data.isLeaf"
                  class="outapi-doc__method"
                  :class="'is-' + methodClass(data.method)"
                >{{ methodLabel(data.method) }}</span>
                <span class="outapi-doc__tree-name" :title="data.name">{{ data.name }}</span>
              </div>
            </template>
          </el-tree>
          <el-empty v-else description="暂无接口数据" :image-size="80" />
        </el-scrollbar>
      </el-card>

      <el-card shadow="never" class="outapi-doc__main" v-loading="detailLoading">
        <template v-if="detail.id">
          <div class="outapi-doc__hero">
            <div class="outapi-doc__hero-top">
              <span class="outapi-doc__method outapi-doc__method--lg" :class="'is-' + methodClass(detail.method)">
                {{ methodLabel(detail.method) }}
              </span>
              <h2 class="outapi-doc__hero-title">{{ detail.name || '未命名接口' }}</h2>
            </div>
            <div class="outapi-doc__url">
              <code>{{ fullUrl }}</code>
              <el-button link type="primary" v-db-click @click="copyText(fullUrl)">复制</el-button>
            </div>
            <p v-if="detail.describe" class="outapi-doc__desc">{{ detail.describe }}</p>
          </div>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">请求说明</div>
            <el-descriptions :column="1" border class="outapi-doc__meta">
              <el-descriptions-item label="请求方式">{{ methodLabel(detail.method) }}</el-descriptions-item>
              <el-descriptions-item label="请求路径">{{ detail.path || '-' }}</el-descriptions-item>
              <el-descriptions-item label="鉴权">{{ authText }}</el-descriptions-item>
              <el-descriptions-item label="文件地址">{{ detail.file_path || '-' }}</el-descriptions-item>
              <el-descriptions-item label="方法名">{{ detail.action || '-' }}</el-descriptions-item>
            </el-descriptions>
          </section>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">请求参数</div>
            <el-table :data="requestParams" border empty-text="无请求参数">
              <el-table-column prop="attribute" label="参数名" min-width="140" />
              <el-table-column prop="type" label="类型" width="110" />
              <el-table-column label="必填" width="80" align="center">
                <template #default="{ row }">
                  <el-tag size="small" :type="isRequired(row.must) ? 'danger' : 'info'" effect="plain">
                    {{ isRequired(row.must) ? '是' : '否' }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column prop="trip" label="说明" min-width="220" show-overflow-tooltip />
            </el-table>
          </section>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">返回参数</div>
            <el-table
              :data="returnParamsTree"
              row-key="id"
              border
              default-expand-all
              :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
              empty-text="无返回参数说明"
            >
              <el-table-column prop="attribute" label="字段名" min-width="160" />
              <el-table-column prop="type" label="类型" width="120" />
              <el-table-column prop="trip" label="说明" min-width="220" show-overflow-tooltip />
            </el-table>
          </section>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">
              <span>请求示例</span>
              <el-button link type="primary" v-db-click @click="copyText(requestExample)">复制</el-button>
            </div>
            <pre class="outapi-doc__code">{{ requestExample }}</pre>
          </section>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">
              <span>返回示例</span>
              <el-button link type="primary" v-db-click @click="copyText(returnExample)">复制</el-button>
            </div>
            <pre class="outapi-doc__code">{{ returnExample }}</pre>
          </section>

          <section class="outapi-doc__section">
            <div class="outapi-doc__section-title">错误码</div>
            <el-table :data="errorCodes" border empty-text="无错误码说明">
              <el-table-column prop="attribute" label="错误码" width="140" />
              <el-table-column prop="type" label="类型" width="110" />
              <el-table-column prop="trip" label="说明" min-width="220" show-overflow-tooltip />
            </el-table>
          </section>
        </template>

        <div v-else class="outapi-doc__empty">
          <el-empty description="请选择左侧接口查看文档" />
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { routeList, routeDet } from '@/api/systemBackendRouting';

defineOptions({ name: 'systemBackendRouting' });

const treeRef = ref(null);
const apiType = ref('adminapi');
const keyword = ref('');
const treeLoading = ref(false);
const detailLoading = ref(false);
const treeData = ref([]);
const expandedKeys = ref([]);
const currentKey = ref('');
const detail = ref({});

const treeProps = {
  label: 'name',
  children: 'children',
};

const requestParams = computed(() => {
  const path = asArray(detail.value.query).filter((item) => item.from === 'path' || String(item.attribute || '').includes('<'));
  const query = asArray(detail.value.query).filter((item) => item.from !== 'path' && !String(item.attribute || '').includes('<'));
  const body = asArray(detail.value.request);
  const header = asArray(detail.value.header);
  // 与对外文档一致：合并为一张「请求参数」表，路径/Query/Body/Header 均展示
  return [...header, ...path, ...query, ...body].map((item) => ({
    ...item,
    attribute: item.attribute || item.name || '',
    type: item.type || 'string',
    must: item.must,
    trip: item.trip || item.description || '',
  }));
});
const returnParamsTree = computed(() => {
  const list = asArray(detail.value.response).map((item) => ({
    ...item,
    id: item.id || `resp_${item.attribute || Math.random()}`,
    attribute: item.attribute || item.name || '',
    type: item.type || 'string',
    trip: item.trip || item.description || '',
    parentId: item.parentId || '',
    children: [],
  }));
  return buildParamTree(list);
});
const errorCodes = computed(() => {
  const list = asArray(detail.value.error_code);
  return list.map((item) => {
    if (item.attribute || item.trip) return item;
    return {
      attribute: item.code ?? item.attribute ?? '',
      type: item.type || 'string',
      trip: item.solution || item.value || item.trip || '',
    };
  });
});
const requestExample = computed(() => {
  if (detail.value.request_example != null && detail.value.request_example !== '' && detail.value.request_example !== '[]') {
    return prettyJson(detail.value.request_example);
  }
  const body = {};
  asArray(detail.value.request).forEach((item) => {
    if (!item.attribute) return;
    body[item.attribute] = item.value ?? defaultExampleValue(item.type);
  });
  const query = {};
  asArray(detail.value.query)
    .filter((item) => item.from !== 'path')
    .forEach((item) => {
      if (!item.attribute) return;
      query[item.attribute] = item.value ?? defaultExampleValue(item.type);
    });
  const method = String(detail.value.method || 'GET').toUpperCase();
  if (method === 'GET' || method === 'DELETE') {
    return prettyJson(Object.keys(query).length ? query : {});
  }
  return prettyJson(Object.keys(body).length ? body : query);
});
const returnExample = computed(() => {
  const examples = asArray(detail.value.response_example);
  if (examples.length) {
    if (examples.length === 1) return prettyJson(examples[0].data ?? examples[0]);
    return examples
      .map((item, index) => `// ${item.name || `示例 ${index + 1}`}\n${prettyJson(item.data ?? item)}`)
      .join('\n\n');
  }
  return prettyJson({
    status: 200,
    msg: 'ok',
    data: {},
  });
});
const fullUrl = computed(() => {
  const prefix = apiType.value === 'api' ? '/api' : '/adminapi';
  const path = String(detail.value.path || '').replace(/^\//, '');
  return `${prefix}/${path}`;
});
const authText = computed(() => {
  const headers = asArray(detail.value.header);
  const needAuth = headers.some((item) => String(item.attribute || '').toLowerCase().includes('authori'));
  if (!needAuth || Number(detail.value.type) === 1) return '公共接口，无需登录 Token';
  if (apiType.value === 'adminapi') return '需要 Authori-zation: Bearer {admin_token}';
  return '需要 Authori-zation: Bearer {user_token}';
});

watch(keyword, (val) => {
  treeRef.value?.filter(val);
});

watch(apiType, () => {
  keyword.value = '';
  detail.value = {};
  currentKey.value = '';
  loadTree();
});

loadTree();

function asArray(value) {
  if (Array.isArray(value)) return value;
  if (value == null || value === '') return [];
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  return [];
}

function buildParamTree(list) {
  const map = {};
  const roots = [];
  list.forEach((item) => {
    map[item.id] = item;
  });
  list.forEach((item) => {
    const parentId = item.parentId;
    if (parentId && map[parentId]) {
      map[parentId].children.push(item);
    } else {
      roots.push(item);
    }
  });
  // 兼容旧数据：只有 status/msg/data 且无子节点时，不额外处理
  return roots.map((item) => {
    if (!item.children?.length) delete item.children;
    return item;
  });
}

function prettyJson(value) {
  if (value == null || value === '') return '-';
  if (typeof value === 'object') {
    return JSON.stringify(value, null, 2);
  }
  const text = String(value);
  try {
    return JSON.stringify(JSON.parse(text), null, 2);
  } catch (e) {
    return text;
  }
}

function defaultExampleValue(type) {
  const t = String(type || '').toLowerCase();
  if (t.includes('array') || t.includes('object')) return [];
  if (t.includes('int') || t.includes('number')) return 0;
  if (t.includes('bool')) return false;
  return '';
}

function isRequired(must) {
  return must === 1 || must === '1' || must === true;
}

function methodLabel(method) {
  const text = String(method || '').toUpperCase();
  return text === 'DELETE' ? 'DEL' : text || '-';
}

function methodClass(method) {
  return String(method || '').toLowerCase().replace('delete', 'del');
}

function filterNode(value, data) {
  if (!value) return true;
  const key = String(value).toLowerCase();
  const name = String(data.name || '').toLowerCase();
  const path = String(data.path || '').toLowerCase();
  return name.includes(key) || path.includes(key);
}

function normalizeNodes(list) {
  return (Array.isArray(list) ? list : []).map((item) => {
    const isLeaf = !!item.method;
    if (isLeaf) {
      return {
        treeKey: `route-${item.id}`,
        id: item.id,
        name: item.name || item.path || '未命名接口',
        method: item.method || '',
        path: item.path || '',
        isLeaf: true,
      };
    }
    return {
      treeKey: `cate-${item.id}`,
      id: item.id,
      name: item.name || '未命名分类',
      isLeaf: false,
      children: normalizeNodes(item.children || []),
    };
  });
}

function findFirstLeaf(nodes) {
  for (const node of nodes || []) {
    if (node.isLeaf) return node;
    const child = findFirstLeaf(node.children);
    if (child) return child;
  }
  return null;
}

function collectExpandKeys(nodes, leafKey, parents = []) {
  for (const node of nodes || []) {
    if (node.treeKey === leafKey) return parents;
    if (!node.isLeaf && node.children?.length) {
      const hit = collectExpandKeys(node.children, leafKey, [...parents, node.treeKey]);
      if (hit) return hit;
    }
  }
  return null;
}

function loadTree() {
  treeLoading.value = true;
  routeList(apiType.value)
    .then((res) => {
      treeData.value = normalizeNodes(res.data || []);
      const first = findFirstLeaf(treeData.value);
      if (!first) {
        expandedKeys.value = [];
        return;
      }
      expandedKeys.value = collectExpandKeys(treeData.value, first.treeKey) || [];
      currentKey.value = first.treeKey;
      nextTick(() => {
        treeRef.value?.setCurrentKey(first.treeKey);
      });
      loadDetail(first.id);
    })
    .catch((err) => {
      treeData.value = [];
      detail.value = {};
      ElMessage.error(err.msg || '加载接口目录失败');
    })
    .finally(() => {
      treeLoading.value = false;
    });
}

function onNodeClick(data) {
  if (!data?.isLeaf) return;
  treeRef.value?.setCurrentKey(data.treeKey);
  if (currentKey.value === data.treeKey && detail.value.id === data.id) return;
  currentKey.value = data.treeKey;
  loadDetail(data.id);
}

function loadDetail(id) {
  if (!id) {
    detail.value = {};
    return;
  }
  detailLoading.value = true;
  routeDet(id)
    .then((res) => {
      detail.value = res.data || {};
    })
    .catch((err) => {
      detail.value = {};
      ElMessage.error(err.msg || '加载接口详情失败');
    })
    .finally(() => {
      detailLoading.value = false;
    });
}

async function copyText(text) {
  const value = String(text || '');
  if (!value || value === '-') {
    ElMessage.warning('没有可复制的内容');
    return;
  }
  try {
    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(value);
    } else {
      const input = document.createElement('textarea');
      input.value = value;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
    }
    ElMessage.success('已复制');
  } catch (e) {
    ElMessage.error('复制失败');
  }
}
</script>

<style scoped lang="scss">
.outapi-doc {
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-height: calc(100vh - 120px);
}

.outapi-doc__tabs-card {
  border: 0;
}

.outapi-doc__tabs {
  padding: 0 20px;
}

.outapi-doc__body {
  display: flex;
  gap: 14px;
  flex: 1;
  min-height: 0;
}

.outapi-doc__aside,
.outapi-doc__main {
  border: 0;
}

.outapi-doc__aside {
  width: 320px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
}

.outapi-doc__aside :deep(.el-card__body) {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 196px);
  padding: 16px;
}

.outapi-doc__aside-head {
  margin-bottom: 12px;
}

.outapi-doc__aside-title {
  font-size: 16px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  margin-bottom: 10px;
}

.outapi-doc__search {
  width: 100%;
}

.outapi-doc__tree-scroll {
  flex: 1;
  min-height: 0;
}

.outapi-doc__tree-node {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  overflow: hidden;
  padding-right: 8px;
}

.outapi-doc__tree-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--prev-color-text-regular);
}

.outapi-doc__method {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 42px;
  height: 20px;
  padding: 0 6px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1;
  color: #fff;
  background: var(--prev-color-text-secondary);
}

.outapi-doc__method--lg {
  min-width: 56px;
  height: 28px;
  font-size: 13px;
}

.outapi-doc__method.is-get {
  background: #61affe;
}
.outapi-doc__method.is-post {
  background: #49cc90;
}
.outapi-doc__method.is-put {
  background: #fca130;
}
.outapi-doc__method.is-delete,
.outapi-doc__method.is-del {
  background: #f93e3e;
}

.outapi-doc__main {
  flex: 1;
  min-width: 0;
}

.outapi-doc__main :deep(.el-card__body) {
  height: calc(100vh - 196px);
  overflow: auto;
  padding: 20px 24px;
}

.outapi-doc__hero {
  margin-bottom: 24px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--prev-border-color-lighter, #ebeef5);
}

.outapi-doc__hero-top {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.outapi-doc__hero-title {
  margin: 0;
  font-size: 22px;
  font-weight: 600;
  color: var(--prev-color-text-regular);
  line-height: 1.3;
}

.outapi-doc__url {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;

  code {
    display: inline-block;
    max-width: 100%;
    padding: 8px 12px;
    border-radius: 6px;
    background: var(--prev-bg-color, #f5f7fa);
    color: var(--prev-color-text-regular);
    font-size: 13px;
    word-break: break-all;
  }
}

.outapi-doc__desc {
  margin: 0;
  color: var(--prev-color-text-primary);
  line-height: 1.7;
  white-space: pre-wrap;
}

.outapi-doc__section {
  margin-bottom: 24px;
}

.outapi-doc__section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}

.outapi-doc__meta {
  max-width: 920px;
}

.outapi-doc__code {
  margin: 0;
  padding: 14px 16px;
  border-radius: 8px;
  background: var(--prev-bg-color, #f5f7fa);
  color: var(--prev-color-text-regular);
  font-size: 13px;
  line-height: 1.6;
  overflow: auto;
  white-space: pre-wrap;
  word-break: break-word;
}

.outapi-doc__empty {
  min-height: 420px;
  display: flex;
  align-items: center;
  justify-content: center;
}

:deep(.el-tree-node__content) {
  height: 34px;
}
</style>
