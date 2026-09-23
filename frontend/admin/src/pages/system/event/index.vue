<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <el-alert type="info" :closable="false">
        <template #title>
          系统事件说明：<br />
          1、系统事件是框架内置的核心事件，用于解耦业务逻辑和扩展功能。<br />
          2、事件由 app 层定义和触发，modules 层监听和处理。<br />
          3、监听器来源分为：app（应用层）、modules（扩展模块）、core（核心层）。<br />
        </template>
      </el-alert>
      <div class="mt14">
        <el-row :gutter="20">
          <el-col :span="6" v-for="category in categories" :key="category.name">
            <el-card class="category-card">
              <div class="category-title">{{ category.name }}</div>
              <div class="category-count">{{ category.event_count }} 个事件</div>
            </el-card>
          </el-col>
        </el-row>
      </div>
      <div class="mt14">
        <div v-for="category in eventList" :key="category.name">
          <div class="category-header">
            <i class="el-icon-folder-opened"></i>
            <span>{{ category.name }}</span>
            <span class="category-badge">{{ category.events.length }} 个事件</span>
          </div>
          <el-table :data="category.events" v-loading="loading" class="event-table">
            <el-table-column label="事件名称" min-width="200">
              <template #default="scope">
                <span class="event-name">{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="事件描述" min-width="300">
              <template #default="scope">
                <span class="event-desc">{{ scope.row.description }}</span>
              </template>
            </el-table-column>
            <el-table-column label="监听器数量" width="120">
              <template #default="scope">
                <el-tag :type="scope.row.listener_count > 0 ? 'success' : 'info'">
                  {{ scope.row.listener_count }} 个
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="监听器详情" min-width="300">
              <template #default="scope">
                <div v-if="scope.row.listeners.length > 0">
                  <el-tag
                    v-for="(listener, index) in scope.row.listeners"
                    :key="index"
                    :type="getListenerType(listener.source)"
                    size="small"
                    class="listener-tag"
                  >
                    {{ listener.short_class }}
                    <span class="listener-source">({{ getSourceName(listener.source) }})</span>
                  </el-tag>
                </div>
                <span v-else class="empty-text">暂无监听器</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="100">
              <template #default="scope">
                <a v-db-click @click="showDetail(scope.row)">详情</a>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
      <el-drawer
        v-model="dialogVisible"
        :title="currentEvent?.name + ' - 事件详情'"
        direction="rtl"
        size="50%"
        @closed="closeDialog"
      >
        <div v-if="currentEvent" class="detail-content">
          <el-form label-width="100px">
            <el-form-item label="事件名称">
              <span>{{ currentEvent.name }}</span>
            </el-form-item>
            <el-form-item label="事件描述">
              <span>{{ currentEvent.description }}</span>
            </el-form-item>
            <el-form-item label="监听器数量">
              <el-tag type="success">{{ currentEvent.listener_count }} 个</el-tag>
            </el-form-item>
            <el-form-item label="监听器列表">
              <div v-if="currentEvent.listeners.length > 0">
                <el-table :data="currentEvent.listeners" border>
                  <el-table-column label="类名" prop="class"></el-table-column>
                  <el-table-column label="短类名" prop="short_class"></el-table-column>
                  <el-table-column label="来源">
                    <template #default="scope">
                      <el-tag :type="getListenerType(scope.row.source)" size="small">
                        {{ getSourceName(scope.row.source) }}
                      </el-tag>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              <span v-else class="empty-text">暂无监听器</span>
            </el-form-item>
          </el-form>
        </div>
      </el-drawer>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { eventIndex, eventCategories } from '@/api/system';

defineOptions({ name: 'system_event' });

const loading = ref(false);
const eventList = ref([]);
const categories = ref([]);
const dialogVisible = ref(false);
const currentEvent = ref(null);

function getList() {
  loading.value = true;
  eventIndex({})
    .then((res) => {
      loading.value = false;
      eventList.value = res.data;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function getCategories() {
  eventCategories()
    .then((res) => {
      categories.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
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
.mt14 {
  margin-top: 14px;
}

.category-card {
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  margin: 10px 0;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .category-title {
    font-size: 16px;
    font-weight: bold;
    color: #303133;
    margin-bottom: 8px;
  }

  .category-count {
    font-size: 24px;
    color: #409eff;
    font-weight: bold;
  }
}

.category-header {
  display: flex;
  align-items: center;
  font-size: 16px;
  font-weight: bold;
  color: #303133;
  margin-top: 20px;
  margin-bottom: 10px;
  padding-left: 10px;
  border-left: 4px solid #409eff;

  .el-icon-folder-opened {
    margin-right: 8px;
    color: #409eff;
  }

  .category-badge {
    margin-left: auto;
    font-size: 12px;
    font-weight: normal;
    color: #909399;
  }
}

.event-table {
  margin-bottom: 20px;
}

.event-name {
  font-weight: 500;
  color: #303133;
}

.event-desc {
  color: #606266;
}

.listener-tag {
  margin-right: 8px;
  margin-bottom: 4px;

  .listener-source {
    font-size: 10px;
    opacity: 0.7;
    margin-left: 4px;
  }
}

.empty-text {
  color: #c0c4cc;
}

.detail-content {
  padding: 10px 0;
}
</style>
