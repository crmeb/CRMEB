<template>
  <div class="notice-log">
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="查看各渠道消息发送结果与成功率，按渠道、状态筛选排查发送失败原因。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="filter" :label-width="labelWidth" :label-position="labelPosition" inline @submit.prevent>
          <el-form-item label="渠道：">
            <el-select v-model="filter.channel_key" placeholder="请选择渠道" clearable class="form_content_width">
              <el-option label="全部渠道" value="" />
              <el-option label="系统站内信" value="is_system" />
              <el-option label="短信" value="is_sms" />
              <el-option label="公众号模板" value="is_wechat" />
              <el-option label="小程序订阅" value="is_routine" />
              <el-option label="企业微信" value="is_ent_wechat" />
              <el-option label="钉钉" value="is_dingtalk" />
              <el-option label="邮件" value="is_email" />
              <el-option label="打印机" value="is_printer" />
            </el-select>
          </el-form-item>
          <el-form-item label="状态：">
            <el-select v-model="filter.status" placeholder="请选择状态" clearable class="form_content_width">
              <el-option label="全部状态" value="" />
              <el-option label="待发送" :value="0" />
              <el-option label="成功" :value="1" />
              <el-option label="失败" :value="2" />
            </el-select>
          </el-form-item>
          <el-form-item label="消息标识：">
            <el-input v-model="filter.mark" placeholder="请输入消息标识" clearable class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getList">搜索</el-button>
            <el-button v-db-click @click="resetFilter">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>

    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-row class="mb14">
        <el-col :span="6">
          <el-card shadow="never" :body-style="{ padding: '10px' }">
            <div class="stat-item">
              <div class="stat-value">{{ statistics.total }}</div>
              <div class="stat-label">发送总数</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" :body-style="{ padding: '10px' }">
            <div class="stat-item success">
              <div class="stat-value">{{ statistics.success }}</div>
              <div class="stat-label">成功</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" :body-style="{ padding: '10px' }">
            <div class="stat-item failed">
              <div class="stat-value">{{ statistics.failed }}</div>
              <div class="stat-label">失败</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" :body-style="{ padding: '10px' }">
            <div class="stat-item rate">
              <div class="stat-value">{{ statistics.success_rate }}%</div>
              <div class="stat-label">成功率</div>
            </div>
          </el-card>
        </el-col>
      </el-row>

      <el-table
        :data="logList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="消息标识" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户ID" width="100">
          <template #default="scope">
            <span>{{ scope.row.uid || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="渠道" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.channel_name || scope.row.channel_key }}</span>
          </template>
        </el-table-column>
        <el-table-column label="标题" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.title || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.status === 1 ? 'success' : scope.row.status === 2 ? 'danger' : 'warning'">
              {{ scope.row.status === 1 ? '成功' : scope.row.status === 2 ? '失败' : '待发送' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="发送时间" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.send_time ? formatDate(scope.row.send_time) : '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="错误信息" min-width="200">
          <template #default="scope">
            <span v-if="scope.row.error_msg" class="error-msg">{{ scope.row.error_msg }}</span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-db-click @click="detail(scope.row)">详情</a>
            <el-divider direction="vertical" v-if="scope.row.status === 2" />
            <a v-db-click v-if="scope.row.status === 2" @click="retry(scope.row)">重试</a>
          </template>
        </el-table-column>
      </el-table>

      <el-row class="mt14" style="text-align: right">
        <el-button type="danger" v-db-click @click="clean">清理历史日志</el-button>
      </el-row>
    </el-card>

    <el-dialog title="发送记录详情" v-model="dialogVisible" width="700px">
      <el-form :model="detailData" label-width="100px" disabled>
        <el-form-item label="ID">
          <span>{{ detailData.id }}</span>
        </el-form-item>
        <el-form-item label="消息标识">
          <span>{{ detailData.mark }}</span>
        </el-form-item>
        <el-form-item label="用户ID">
          <span>{{ detailData.uid || '-' }}</span>
        </el-form-item>
        <el-form-item label="渠道">
          <span>{{ detailData.channel_name || detailData.channel_key }}</span>
        </el-form-item>
        <el-form-item label="标题">
          <span>{{ detailData.title || '-' }}</span>
        </el-form-item>
        <el-form-item label="内容">
          <textarea :value="detailData.content || ''" :rows="6" readonly class="detail-textarea" />
        </el-form-item>
        <el-form-item label="状态">
          <el-tag :type="detailData.status === 1 ? 'success' : detailData.status === 2 ? 'danger' : 'warning'">
            {{ detailData.status === 1 ? '成功' : detailData.status === 2 ? '失败' : '待发送' }}
          </el-tag>
        </el-form-item>
        <el-form-item label="发送时间">
          <span>{{ detailData.send_time ? formatDate(detailData.send_time) : '-' }}</span>
        </el-form-item>
        <el-form-item label="错误信息">
          <span class="error-msg" v-if="detailData.error_msg">{{ detailData.error_msg }}</span>
          <span v-else>-</span>
        </el-form-item>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button @click="dialogVisible = false">关闭</el-button>
        </div></template
      >
    </el-dialog>

    <el-dialog title="清理日志" v-model="cleanVisible" width="400px">
      <el-form :model="cleanForm" label-width="100px">
        <el-form-item label="清理天数">
          <el-input-number v-model="cleanForm.days" :min="1" :max="365" />
          <span style="margin-left: 10px">天前的日志</span>
        </el-form-item>
        <el-form-item>
          <span class="warning-text">注意：此操作不可恢复，请谨慎操作！</span>
        </el-form-item>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button @click="cleanVisible = false">取消</el-button>
          <el-button type="danger" @click="doClean">确定清理</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import {
  noticeLogList,
  noticeLogDetail,
  noticeLogStatistics,
  noticeLogRetry,
  noticeLogClean,
} from '@/api/notification.js';

function formatDate(timestamp) {
  const date = new Date(timestamp * 1000);
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
}

const logList = ref([]);
const loading = ref(false);
const mediaStore = useMediaStore();
const labelWidth = computed(() => (mediaStore.isMobile ? undefined : '80px'));
const labelPosition = computed(() => (mediaStore.isMobile ? 'top' : 'right'));
const filter = ref({
  channel_key: '',
  status: '',
  mark: '',
});
const statistics = ref({
  total: 0,
  success: 0,
  failed: 0,
  success_rate: 0,
});
const dialogVisible = ref(false);
const detailData = ref({});
const cleanVisible = ref(false);
const cleanForm = reactive({
  days: 30,
});

function getList() {
  loading.value = true;
  noticeLogList(filter.value)
    .then((res) => {
      logList.value = res.data;
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}
function getStatistics() {
  noticeLogStatistics(filter.value).then((res) => {
    statistics.value = res.data;
  });
}
function resetFilter() {
  filter.value = {
    channel_key: '',
    status: '',
    mark: '',
  };
  getList();
  getStatistics();
}
function detail(row) {
  noticeLogDetail(row.id).then((res) => {
    detailData.value = res.data;
    dialogVisible.value = true;
  });
}
function retry(row) {
  ElMessageBox.confirm('确定要重试发送吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  }).then(() => {
    noticeLogRetry(row.id)
      .then((res) => {
        ElMessage.success(res.msg);
        getList();
        getStatistics();
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  });
}
function clean() {
  cleanVisible.value = true;
}
function doClean() {
  noticeLogClean(cleanForm)
    .then((res) => {
      ElMessage.success(res.msg);
      cleanVisible.value = false;
      getList();
      getStatistics();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

getList();
getStatistics();
</script>

<style lang="scss" scoped>
.stat-item {
  text-align: center;
  .stat-value {
    font-size: 28px;
    font-weight: bold;
    color: var(--prev-color-text-regular);
  }
  &.success .stat-value {
    color: #67c23a;
  }
  &.failed .stat-value {
    color: #f56c6c;
  }
  &.rate .stat-value {
    color: #409eff;
  }
  .stat-label {
    font-size: 14px;
    color: var(--prev-color-text-secondary);
    margin-top: 5px;
  }
}
.error-msg {
  color: #f56c6c;
  font-size: 12px;
}
.detail-textarea {
  width: 100%;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 10px;
  resize: none;
}
.warning-text {
  color: #f56c6c;
}
</style>
