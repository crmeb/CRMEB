<template>
  <div class="system-event">
    <pages-header
      title="系统事件"
      description="查看框架内置核心事件及其监听器，便于排查扩展与解耦关系。点击分类可筛选。"
    />

    <div class="stat-row" v-loading="categoryLoading">
      <el-card
        v-for="item in categories"
        :key="item.name"
        shadow="never"
        class="stat-card"
        :class="{ 'is-active': activeCategory === item.name }"
        @click="toggleCategory(item.name)"
      >
        <div class="stat-name">{{ item.name }}</div>
        <div class="stat-value">{{ item.event_count }}</div>
        <div class="stat-unit">个事件</div>
      </el-card>
    </div>

    <el-card shadow="never" class="ivu-mt mt16">
      <div class="list-toolbar acea-row row-middle row-between">
        <div class="acea-row row-middle">
          <el-input
            v-model="keyword"
            clearable
            placeholder="搜索事件名称 / 描述"
            class="form_content_width"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <template v-if="activeCategory">
            <el-tag class="ml14" type="primary" effect="plain" closable @close="activeCategory = ''">
              {{ activeCategory }}
            </el-tag>
          </template>
        </div>
        <span class="list-count">共 {{ flatEventList.length }} 个事件</span>
      </div>

      <el-table
        :data="flatEventList"
        v-loading="loading"
        highlight-current-row
        class="mt14 event-table"
        @row-click="showDetail"
      >
        <el-table-column label="分类" width="120">
          <template #default="{ row }">
            <el-tag size="small" effect="plain" type="info">{{ row.category }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="事件" width="350" show-overflow-tooltip>
          <template #default="{ row }">
            <div class="event-cell">
              <div class="event-name">{{ row.name }}</div>
              <div class="event-desc line1">{{ row.description || '暂无描述' }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="监听器" min-width="220">
          <template #default="{ row }">
            <div v-if="row.listeners?.length" class="listener-cell">
              <el-tag
                v-for="(listener, index) in row.listeners.slice(0, 5)"
                :key="index"
                :type="getListenerType(listener.source)"
                size="small"
                effect="plain"
              >
                {{ listener.short_class }}
              </el-tag>
              <el-tag v-if="row.listener_count > 5" size="small" type="info" effect="plain">
                +{{ row.listener_count - 5 }}
              </el-tag>
            </div>
            <span v-else class="empty-text">未注册</span>
          </template>
        </el-table-column>
        <el-table-column label="数量" width="72" align="center">
          <template #default="{ row }">
            <span v-if="row.listener_count" class="listener-count">{{ row.listener_count }}</span>
            <span v-else class="empty-text">0</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="72">
          <template #default="{ row }">
            <el-button class="op-link" link type="primary" v-db-click @click.stop="showDetail(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-drawer
      v-model="dialogVisible"
      :title="drawerTitle"
      direction="rtl"
      size="720"
      @closed="closeDialog"
    >
      <div v-if="currentEvent" class="detail-content">
        <el-descriptions :column="1" border>
          <el-descriptions-item label="所属分类">{{ currentEvent.category }}</el-descriptions-item>
          <el-descriptions-item label="事件名称">
            <code class="event-code">{{ currentEvent.name }}</code>
          </el-descriptions-item>
          <el-descriptions-item label="事件描述">{{ currentEvent.description || '—' }}</el-descriptions-item>
          <el-descriptions-item label="监听器数量">
            <el-tag :type="currentEvent.listener_count > 0 ? 'success' : 'info'" size="small">
              {{ currentEvent.listener_count }} 个
            </el-tag>
          </el-descriptions-item>
        </el-descriptions>

        <div class="detail-section-title">监听器列表</div>
        <el-table v-if="currentEvent.listeners?.length" :data="currentEvent.listeners">
          <el-table-column label="短类名" prop="short_class" min-width="160" />
          <el-table-column label="完整类名" prop="class" min-width="280" show-overflow-tooltip />
          <el-table-column label="来源" width="110">
            <template #default="{ row }">
              <el-tag :type="getListenerType(row.source)" size="small" effect="plain">
                {{ getSourceName(row.source) }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
        <div v-else class="empty-box">暂无监听器</div>
      </div>
    </el-drawer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { eventIndex, eventCategories } from '@/api/system';

defineOptions({ name: 'system_event' });

const loading = ref(false);
const categoryLoading = ref(false);
const eventList = ref([]);
const categories = ref([]);
const activeCategory = ref('');
const keyword = ref('');
const dialogVisible = ref(false);
const currentEvent = ref(null);

const flatEventList = computed(() => {
  const key = keyword.value.trim().toLowerCase();
  const rows = [];
  eventList.value.forEach((category) => {
    if (activeCategory.value && category.name !== activeCategory.value) return;
    (category.events || []).forEach((event) => {
      if (key) {
        const hitName = (event.name || '').toLowerCase().includes(key);
        const hitDesc = (event.description || '').toLowerCase().includes(key);
        if (!hitName && !hitDesc) return;
      }
      rows.push({ ...event, category: category.name });
    });
  });
  return rows;
});

const drawerTitle = computed(() => {
  return currentEvent.value ? `${currentEvent.value.name} - 事件详情` : '事件详情';
});

function getList() {
  loading.value = true;
  eventIndex({})
    .then((res) => {
      eventList.value = res.data || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      loading.value = false;
    });
}

function getCategories() {
  categoryLoading.value = true;
  eventCategories()
    .then((res) => {
      categories.value = res.data || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      categoryLoading.value = false;
    });
}

function toggleCategory(name) {
  activeCategory.value = activeCategory.value === name ? '' : name;
}

function showDetail(event) {
  currentEvent.value = event;
  dialogVisible.value = true;
}

function closeDialog() {
  currentEvent.value = null;
}

function getListenerType(source) {
  const types = {
    app: 'primary',
    modules: 'success',
    core: 'warning',
  };
  return types[source] || 'info';
}

function getSourceName(source) {
  const names = {
    app: '应用层',
    modules: '扩展模块',
    core: '核心层',
  };
  return names[source] || source;
}

getList();
getCategories();
</script>

<style lang="scss" scoped>
.stat-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
}

.stat-card {
  cursor: pointer;
  transition: background-color 0.2s ease;

  :deep(.el-card__body) {
    padding: 16px 20px;
  }

  &:hover {
    background: var(--prev-bg-color);
  }

  &.is-active {
    background: var(--prev-color-primary-light-9);

    .stat-name,
    .stat-unit,
    .stat-value {
      color: var(--prev-color-primary);
    }
  }
}

.stat-name {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  line-height: 1.4;
}

.stat-value {
  margin-top: 6px;
  font-size: 24px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  line-height: 1.2;
}

.stat-unit {
  margin-top: 4px;
  font-size: 12px;
  color: var(--prev-color-text-placeholder);
}

.list-toolbar {
  min-height: 32px;
}

.list-count {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.event-cell {
  padding: 2px 0;
}

.event-name {
  font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;
  font-size: 12px;
  font-weight: 500;
  line-height: 18px;
  color: var(--prev-color-text-regular);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-desc {
  margin-top: 2px;
  font-size: 12px;
  line-height: 18px;
  color: var(--prev-color-text-secondary);
}

.listener-cell {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  padding: 2px 0;
}

.listener-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 11px;
  font-size: 12px;
  font-weight: 500;
  color: var(--prev-color-primary);
  background: var(--prev-color-primary-light-9);
}

.empty-text {
  color: var(--prev-color-text-placeholder);
}

.event-table {
  :deep(.el-table__row) {
    cursor: pointer;
  }

  :deep(.op-link.el-button) {
    margin: 0 !important;
    padding: 0 !important;
    height: auto;
    min-height: auto;
  }
}

.event-code {
  display: inline-block;
  padding: 0 6px;
  border-radius: 4px;
  font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;
  font-size: 12px;
  line-height: 20px;
  color: var(--prev-color-primary);
  background: var(--prev-color-primary-light-9);
}

.detail-content {
  .detail-section-title {
    margin: 20px 0 10px;
    font-size: 14px;
    font-weight: 500;
    color: var(--prev-color-text-regular);
  }
}
</style>
