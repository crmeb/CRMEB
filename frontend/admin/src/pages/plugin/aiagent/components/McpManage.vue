<template>
  <div class="mcp-page">
    <el-card shadow="never" class="mt16">
      <div class="mcp-hero">
        <div class="mcp-hero-icon"><i class="el-icon-connection" /></div>
        <div class="mcp-hero-main">
          <div class="mcp-hero-title">MCP 服务管理</div>
          <div class="mcp-hero-sub">安装 MCP 服务，为 AI Agent 扩展更多工具能力</div>
        </div>
        <!-- icon prop 需传全局注册的 EP 图标名（PascalCase）；el-icon-* 字符串不会解析成组件 -->
        <el-button type="primary" icon="Setting" v-db-click @click="openEditor()">配置 MCP</el-button>
      </div>

      <div class="mcp-toolbar">
        <el-input v-model="keyword" placeholder="搜索 MCP" prefix-icon="Search" clearable class="mcp-search" />
      </div>

      <div class="mcp-section">
        <span class="mcp-section-title">我的 MCP</span>
        <span class="mcp-count-badge">{{ filteredList.length }}</span>
        <span class="mcp-enabled-count">{{ enabledCount }} 启用</span>
      </div>

      <div v-loading="loading" class="mcp-list">
        <div v-for="row in filteredList" :key="row.id" class="mcp-card" :class="{ 'is-expanded': isExpanded(row.id) }">
          <div class="mcp-card-head">
            <span class="mcp-chevron" @click="toggleExpand(row.id)">
              <i :class="isExpanded(row.id) ? 'el-icon-arrow-down' : 'el-icon-arrow-right'" />
            </span>
            <span class="mcp-avatar" :style="{ background: avatarColor(row) }">{{ avatarText(row) }}</span>
            <div class="mcp-meta">
              <div class="mcp-name">
                <span class="mcp-name-text">{{ row.name }}</span>
                <span class="mcp-dot" :class="dotClass(row)" :title="dotTitle(row)" />
              </div>
              <div class="mcp-sub">{{ toolSummary(row) }}</div>
            </div>
            <div class="mcp-actions">
              <el-tooltip content="测试连接" placement="top">
                <i
                  class="mcp-action-icon el-icon-link"
                  :class="{ 'is-loading': testingId === row.id }"
                  @click="testServer(row)"
                />
              </el-tooltip>
              <el-tooltip content="同步工具" placement="top">
                <i
                  class="mcp-action-icon el-icon-refresh"
                  :class="{ 'is-loading': syncingId === row.id }"
                  @click="syncTools(row)"
                />
              </el-tooltip>
              <el-tooltip content="编辑" placement="top">
                <i class="mcp-action-icon el-icon-edit" @click="openEditor(row)" />
              </el-tooltip>
              <el-tooltip content="删除" placement="top">
                <i class="mcp-action-icon el-icon-delete danger" @click="removeServer(row)" />
              </el-tooltip>
              <el-switch
                v-model="row.status"
                :active-value="1"
                :inactive-value="0"
                class="mcp-switch"
                @change="changeStatus(row)"
              />
            </div>
          </div>

          <div v-if="isExpanded(row.id)" class="mcp-card-body">
            <div class="mcp-url mono">
              <span class="muted">接口地址</span>
              <span class="mcp-url-text">{{ row.url }}</span>
              <span v-if="row.tools_sync_time" class="muted">{{ formatTime(row.tools_sync_time) }}</span>
            </div>
            <div v-if="!row.tools.length" class="muted mcp-no-tools">
              尚未同步工具，点击 <i class="el-icon-refresh" /> 拉取远端工具清单。
            </div>
            <div v-else class="mcp-tools">
              <el-tooltip
                v-for="tool in row.tools"
                :key="tool.name"
                :content="toolTip(tool)"
                placement="top"
                popper-class="ai-mcp-tool-tip"
              >
                <span class="tool-chip mono">{{ tool.label || tool.name }}</span>
              </el-tooltip>
            </div>
          </div>
        </div>

        <div v-if="!loading && !filteredList.length" class="mcp-empty">
          <template v-if="list.length">未找到匹配「{{ keyword }}」的 MCP 服务</template>
          <template v-else>还没有 MCP 服务，点击右上角「配置 MCP」粘贴 mcpServers JSON 接入</template>
        </div>
      </div>
    </el-card>

    <el-dialog
      :title="editId ? '编辑 MCP Server' : '新增 MCP Server'"
      v-model="editorVisible"
      width="720px"
      top="8vh"
      append-to-body
      :close-on-click-modal="false"
    >
      <div class="dialog-tip">
        直接粘贴 mcpServers JSON 配置即可，url 需支持 Streamable HTTP。
        <template v-if="editId"
          >编辑时配置中只能包含一条记录，<span class="mono">__KEEP__</span> 或留空表示保持原密钥。</template
        >
        <template v-else>支持一次粘贴多个 Server，本地进程（command）类型暂不支持。</template>
        <el-button v-if="!editId" link type="primary" @click="loadExample">加载示例</el-button>
      </div>
      <el-input
        v-model="jsonText"
        type="textarea"
        :rows="16"
        class="json-input"
        placeholder='{"mcpServers":{"example":{"url":"https://example.com/mcp","headers":{"Authorization":"Bearer xxx"}}}}'
      />
      <template #footer>
        <el-button @click="editorVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" v-db-click @click="saveServer">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  aiMcpServerDeleteApi,
  aiMcpServerDetailApi,
  aiMcpServerListApi,
  aiMcpServerSaveApi,
  aiMcpServerStatusApi,
  aiMcpServerSyncApi,
  aiMcpServerTestApi,
} from '../api';

defineOptions({ name: 'McpManage' });

const AVATAR_COLORS = ['#ff7a45', '#9254de', '#40a9ff', '#73d13d', '#f759ab', '#ffc53d', '#36cfc9', '#597ef7'];

const loading = ref(false);
const saving = ref(false);
const syncingId = ref(0);
const testingId = ref(0);
const editorVisible = ref(false);
const list = ref([]);
const editId = ref(0);
const jsonText = ref('');
const keyword = ref('');
const expandedIds = ref([]);

const filteredList = computed(() => {
  const kw = keyword.value.trim().toLowerCase();
  if (!kw) return list.value;
  return list.value.filter((row) => {
    const fields = [row.name, row.server_key, row.url, row.description];
    // 工具同时按英文名与中文名匹配
    const tools = (row.tools || []).map((t) => [t.name, t.label]);
    return fields.concat(...tools).some((v) =>
      String(v || '')
        .toLowerCase()
        .includes(kw),
    );
  });
});
const enabledCount = computed(() => list.value.filter((row) => row.status === 1).length);

onMounted(() => {
  loadList();
});

function loadList() {
  loading.value = true;
  aiMcpServerListApi()
    .then((res) => {
      list.value = res.data || [];
    })
    .finally(() => {
      loading.value = false;
    });
}
const isExpanded = (id) => keyword.value.trim() !== '' || expandedIds.value.includes(id);
function toggleExpand(id) {
  const idx = expandedIds.value.indexOf(id);
  if (idx >= 0) expandedIds.value.splice(idx, 1);
  else expandedIds.value.push(id);
}
const avatarText = (row) => {
  const name = String(row.name || row.server_key || '?');
  return name.charAt(0).toUpperCase();
};
function avatarColor(row) {
  const key = String(row.server_key || row.name || '');
  let hash = 0;
  for (let i = 0; i < key.length; i++) {
    hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
  }
  return AVATAR_COLORS[hash % AVATAR_COLORS.length];
}
const dotClass = (row) => {
  if (row.status !== 1) return 'is-off';
  return row.tools_sync_time ? 'is-on' : 'is-pending';
};
const dotTitle = (row) => {
  if (row.status !== 1) return '已停用';
  return row.tools_sync_time ? '已连接' : '未同步工具';
};
const toolSummary = (row) => {
  if (!row.tools_sync_time) return '未同步工具';
  return `${row.tool_count} 个工具 · ${formatTime(row.tools_sync_time)}`;
};
// 工具标签悬停：中文名展示后补充英文名（调用标识）与描述，便于对照
const toolTip = (tool) => [tool.name, tool.description].filter(Boolean).join(' · ');
function formatTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const pad = (n) => String(n).padStart(2, '0');
  return `同步于 ${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(
    date.getMinutes(),
  )}`;
}
function openEditor(row) {
  editorVisible.value = true;
  if (!row) {
    editId.value = 0;
    jsonText.value = '';
    return;
  }
  aiMcpServerDetailApi(row.id).then((res) => {
    const data = res.data || {};
    editId.value = data.id;
    const item = { name: data.name, url: data.url };
    // format 接口对已设置密钥回显 __KEEP__ 占位符，原样粘贴即可保持密钥不变
    if (data.api_key) item.api_key = data.api_key;
    if (data.headers && Object.keys(data.headers).length) item.headers = data.headers;
    if (data.timeout) item.timeout = data.timeout;
    if (data.description) item.description = data.description;
    if (data.status !== 1) item.disabled = true;
    jsonText.value = JSON.stringify({ mcpServers: { [data.server_key]: item } }, null, 2);
  });
}
function loadExample() {
  jsonText.value = JSON.stringify(
    {
      mcpServers: {
        'example-server': {
          url: 'https://example.com/mcp',
          headers: { Authorization: 'Bearer your-token' },
          timeout: 15,
          description: '示例远程 MCP Server',
        },
      },
    },
    null,
    2,
  );
}
function saveServer() {
  const text = jsonText.value.trim();
  if (!text) return ElMessage.warning('请粘贴 mcpServers JSON 配置');
  try {
    const parsed = JSON.parse(text);
    if (!parsed.mcpServers || !Object.keys(parsed.mcpServers).length) {
      return ElMessage.warning('配置需包含 mcpServers 节点');
    }
  } catch (e) {
    return ElMessage.error('JSON 格式不正确：' + e.message);
  }
  saving.value = true;
  const request = editId.value
    ? aiMcpServerSaveApi({ config: text }, editId.value)
    : aiMcpServerSaveApi({ config: text });
  request
    .then((res) => {
      ElMessage.success(res.msg || '保存成功');
      editorVisible.value = false;
      loadList();
    })
    .catch((e) => {
      if (e?.msg) ElMessage.error(e.msg);
    })
    .finally(() => {
      saving.value = false;
    });
}
function changeStatus(row) {
  aiMcpServerStatusApi(row.id, row.status)
    .then(() => {
      ElMessage.success('修改成功');
    })
    .catch((e) => {
      row.status = row.status === 1 ? 0 : 1;
      if (e?.msg) ElMessage.error(e.msg);
    });
}
function testServer(row) {
  if (testingId.value) return;
  testingId.value = row.id;
  aiMcpServerTestApi(row.id)
    .then((res) => {
      const data = res.data || {};
      if (data.ok) {
        ElMessage.success(`${data.message}，耗时 ${data.cost_ms}ms`);
      } else {
        ElMessage.error(data.message || '连接失败');
      }
    })
    .catch((e) => {
      if (e?.msg) ElMessage.error(e.msg);
    })
    .finally(() => {
      testingId.value = 0;
    });
}
function syncTools(row) {
  if (syncingId.value) return;
  syncingId.value = row.id;
  aiMcpServerSyncApi(row.id)
    .then((res) => {
      const data = res.data || {};
      ElMessage.success(`同步成功，共 ${data.count || 0} 个工具`);
      loadList();
    })
    .catch((e) => {
      if (e?.msg) ElMessage.error(e.msg);
    })
    .finally(() => {
      syncingId.value = 0;
    });
}
function removeServer(row) {
  ElMessageBox.confirm(`确定删除 MCP Server“${row.name}”吗？其远程工具将立即对 AI 对话不可用。`, '提示', {
    type: 'warning',
  })
    .then(() => aiMcpServerDeleteApi(row.id))
    .then(() => {
      ElMessage.success('删除成功');
      loadList();
    })
    .catch((e) => {
      // 确认框取消（cancel/close）不提示；业务失败展示后端 msg（网络错误已由全局请求层提示）
      if (e !== 'cancel' && e !== 'close' && e?.msg) ElMessage.error(e.msg);
    });
}
</script>

<style scoped lang="scss">
.mcp-page {
  .mt16 {
    margin-top: 0;
  }
}
.mt16 {
  margin-top: 16px;
}
.mono {
  font-family: Menlo, Consolas, monospace;
  font-size: 12px;
}
.muted {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
}
.danger {
  color: #f56c6c;
}

/* 顶部说明区 */
.mcp-hero {
  display: flex;
  align-items: center;
  padding: 4px 0 20px;
}
.mcp-hero-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  margin-right: 14px;
  border-radius: 10px;
  background: var(--prev-bg-color);
  color: var(--prev-color-text-regular);
  font-size: 22px;
}
.mcp-hero-main {
  flex: 1;
}
.mcp-hero-title {
  color: #1d2129;
  font-size: 17px;
  font-weight: 600;
}
.mcp-hero-sub {
  margin-top: 4px;
  color: #86909c;
  font-size: 13px;
}

/* 搜索行 */
.mcp-toolbar {
  display: flex;
  align-items: center;
  margin-bottom: 14px;
}
.mcp-search {
  width: 100%;
}

/* 分组标题 */
.mcp-section {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}
.mcp-section-title {
  color: #1d2129;
  font-size: 15px;
  font-weight: 600;
}
.mcp-count-badge {
  margin-left: 8px;
  padding: 1px 9px;
  border-radius: 10px;
  background: var(--prev-bg-color);
  color: #4e5969;
  font-size: 12px;
}
.mcp-enabled-count {
  margin-left: auto;
  color: #86909c;
  font-size: 13px;
}

/* Server 卡片 */
.mcp-list {
  min-height: 80px;
}
.mcp-card {
  margin-bottom: 12px;
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 12px;
  background: var(--prev-bg-white);
  transition: box-shadow 0.2s;
  &:hover {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  }
  &.is-expanded {
    border-color: var(--prev-border-color-base);
  }
}
.mcp-card-head {
  display: flex;
  align-items: center;
  padding: 14px 16px;
}
.mcp-chevron {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  margin-right: 6px;
  border-radius: 4px;
  color: #86909c;
  font-size: 12px;
  cursor: pointer;
  &:hover {
    background: var(--prev-bg-color);
  }
}
.mcp-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  margin-right: 12px;
  border-radius: 10px;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
}
.mcp-meta {
  flex: 1;
  min-width: 0;
}
.mcp-name {
  display: flex;
  align-items: center;
}
.mcp-name-text {
  color: #1d2129;
  font-size: 14px;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.mcp-dot {
  flex-shrink: 0;
  width: 8px;
  height: 8px;
  margin-left: 8px;
  border-radius: 50%;
  &.is-on {
    background: #00b42a;
  }
  &.is-pending {
    background: #ff7d00;
  }
  &.is-off {
    background: #c9cdd4;
  }
}
.mcp-sub {
  margin-top: 3px;
  color: #86909c;
  font-size: 12px;
}
.mcp-actions {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.mcp-action-icon {
  margin-left: 18px;
  color: #4e5969;
  font-size: 16px;
  cursor: pointer;
  &:hover {
    color: #1d2129;
  }
  &.danger:hover {
    color: #f56c6c;
  }
  &.is-loading {
    animation: mcp-spin 1s linear infinite;
    pointer-events: none;
    opacity: 0.6;
  }
}
.mcp-switch {
  margin-left: 18px;
}
@keyframes mcp-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* 展开区 */
.mcp-card-body {
  padding: 0 16px 16px 76px;
}
.mcp-url {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  .mcp-url-text {
    color: #4e5969;
    word-break: break-all;
  }
}
.mcp-no-tools {
  font-size: 13px;
}
.mcp-tools {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.tool-chip {
  padding: 5px 12px;
  border-radius: 8px;
  background: var(--prev-bg-color);
  color: #1d2129;
  font-size: 12px;
  cursor: default;
  transition: background 0.15s;
  &:hover {
    background: var(--prev-bg-color);
  }
}

/* 空态 */
.mcp-empty {
  padding: 40px 0;
  color: #86909c;
  font-size: 13px;
  text-align: center;
}

/* 弹窗 */
.dialog-tip {
  margin-bottom: 10px;
  color: var(--prev-color-text-secondary);
  font-size: 13px;
  line-height: 1.6;
}
.json-input {
  :deep(textarea) {
    font-family: Menlo, Consolas, monospace;
    font-size: 12px;
    line-height: 1.7;
  }
}
</style>

<style lang="scss">
/* 工具提示 popper 挂载在 body 下，scoped 样式无法命中，用专用类全局限定宽度 */
.ai-mcp-tool-tip {
  max-width: 420px;
  line-height: 1.6;
}
</style>
