<template>
  <div class="task-page">
    <el-card shadow="never" class="mt16">
      <div class="task-hero">
        <div class="task-hero-icon">
          <el-icon><AlarmClock /></el-icon>
        </div>
        <div class="task-hero-main">
          <div class="task-hero-title">自动化任务</div>
          <div class="task-hero-sub">让 AI 按计划无人值守执行指令并推送结果（如每日经营日报）</div>
        </div>
        <el-button type="primary" icon="Plus" v-db-click @click="openEditor()">新建任务</el-button>
      </div>

      <div class="task-section">
        <span class="task-section-title">我的任务</span>
        <span class="task-count-badge">{{ list.length }}</span>
        <span class="task-enabled-count">{{ enabledCount }} 启用</span>
      </div>

      <div v-loading="loading" class="task-list">
        <div v-for="row in list" :key="row.id" class="task-card">
          <div class="task-card-head">
            <span class="task-avatar" :style="{ background: avatarColor(row) }">{{ avatarText(row) }}</span>
            <div class="task-meta">
              <div class="task-name">
                <span class="task-name-text">{{ row.name }}</span>
                <el-tag :type="autonomyTag(row.autonomy).type" size="small" effect="plain" class="task-tag">
                  {{ autonomyTag(row.autonomy).label }}
                </el-tag>
              </div>
              <div class="task-sub">
                {{ scheduleText(row) }}
                <template v-if="row.next_run_time"> · 下次 {{ formatTime(row.next_run_time) }}</template>
                <template v-if="row.last_run_time"> · 上次 {{ formatTime(row.last_run_time) }}</template>
              </div>
            </div>
            <div class="task-actions">
              <el-tooltip content="运行历史" placement="top">
                <i class="task-action-icon el-icon-tickets" @click="openRuns(row)" />
              </el-tooltip>
              <el-tooltip content="测试运行：立即执行一次并打开运行历史查看结果" placement="top">
                <i
                  class="task-action-icon el-icon-video-play"
                  :class="{ 'is-loading': triggeringId === row.id }"
                  @click="triggerTask(row)"
                />
              </el-tooltip>
              <el-tooltip content="编辑" placement="top">
                <i class="task-action-icon el-icon-edit" @click="openEditor(row)" />
              </el-tooltip>
              <el-tooltip content="删除" placement="top">
                <i class="task-action-icon el-icon-delete danger" @click="removeTask(row)" />
              </el-tooltip>
              <el-switch
                v-model="row.status"
                :active-value="1"
                :inactive-value="0"
                class="task-switch"
                @change="changeStatus(row)"
              />
            </div>
          </div>

          <div class="task-card-body">
            <div class="task-instruction" :title="row.instruction">{{ row.instruction }}</div>
            <div class="task-channel-row">
              <template v-if="row.notify_channels && row.notify_channels.length">
                <el-tag
                  v-for="channel in row.notify_channels"
                  :key="channel"
                  size="small"
                  type="info"
                  effect="plain"
                  class="channel-tag"
                >
                  {{ channelLabel(channel) }}
                </el-tag>
                <!-- 收件邮箱展示在 v-for 之外，channel 变量在标签循环外不可用 -->
                <span v-if="row.notify_channels.includes('is_email') && row.notify_email" class="muted">
                  {{ row.notify_email }}
                </span>
              </template>
              <span v-else class="muted">未配置通知渠道</span>
            </div>
          </div>
        </div>

        <div v-if="!loading && !list.length" class="task-empty">
          还没有自动化任务，点击右上角「新建任务」创建第一个定时任务
        </div>
      </div>

      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="loadList" />
      </div>
    </el-card>

    <!-- 新建/编辑任务 -->
    <el-dialog
      :title="editId ? '编辑任务' : '新建任务'"
      v-model="editorVisible"
      width="680px"
      top="6vh"
      append-to-body
      :close-on-click-modal="false"
    >
      <el-alert type="info" :closable="false" class="editor-tip">
        任务指令是 AI 每次无人值守执行时收到的完整说明，请写明数据范围、分析要求与交付动作（如生成日报并发送到邮箱）。
      </el-alert>
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px" class="task-form">
        <el-form-item label="任务名称" prop="name">
          <el-input v-model="form.name" maxlength="100" placeholder="如：每日经营日报" />
        </el-form-item>
        <el-form-item label="任务指令" prop="instruction">
          <el-input
            v-model="form.instruction"
            type="textarea"
            :rows="5"
            maxlength="4000"
            show-word-limit
            placeholder="如：查询昨天的订单数、销售额、退款金额、新增用户数与商品销量 Top5，生成经营日报（含环比分析）；完成后调用邮件工具把日报发送到登记的邮箱，并调用企业微信工具推送简版"
          />
        </el-form-item>
        <el-form-item label="执行时间" prop="schedule_value">
          <el-radio-group v-model="form.schedule_type" @change="onScheduleTypeChange">
            <el-radio :value="2">每天定时</el-radio>
            <el-radio :value="3">每周定时</el-radio>
            <el-radio :value="4">每月定时</el-radio>
            <el-radio :value="5">每年定时</el-radio>
            <el-radio :value="1">间隔循环</el-radio>
          </el-radio-group>
          <div class="schedule-input">
            <el-time-select
              v-if="form.schedule_type === 2"
              v-model="form.schedule_value"
              start="00:00"
              end="23:30"
              step="00:30"
              placeholder="选择每天执行时刻"
              class="schedule-field"
            />
            <template v-else-if="form.schedule_type === 3">
              <el-select v-model="form.schedule_week" class="schedule-field">
                <el-option v-for="(label, week) in WEEK_LABELS" :key="week" :label="label" :value="Number(week)" />
              </el-select>
              <el-time-select
                v-model="form.schedule_value"
                start="00:00"
                end="23:30"
                step="00:30"
                placeholder="选择执行时刻"
                class="schedule-field"
              />
            </template>
            <template v-else-if="form.schedule_type === 4">
              <span class="muted">每月</span>
              <el-input-number
                v-model="form.schedule_day"
                :min="1"
                :max="28"
                :controls="false"
                placeholder="几日"
                class="schedule-day-field"
              />
              <span class="muted">日</span>
              <el-time-select
                v-model="form.schedule_value"
                start="00:00"
                end="23:30"
                step="00:30"
                placeholder="选择执行时刻"
                class="schedule-field"
              />
            </template>
            <template v-else-if="form.schedule_type === 5">
              <el-select v-model="form.schedule_month" class="schedule-month-field">
                <el-option v-for="(label, month) in MONTH_LABELS" :key="month" :label="label" :value="Number(month)" />
              </el-select>
              <el-input-number
                v-model="form.schedule_day"
                :min="1"
                :max="28"
                :controls="false"
                placeholder="几日"
                class="schedule-day-field"
              />
              <span class="muted">日</span>
              <el-time-select
                v-model="form.schedule_value"
                start="00:00"
                end="23:30"
                step="00:30"
                placeholder="选择执行时刻"
                class="schedule-field"
              />
            </template>
            <template v-else>
              <el-input-number
                v-model="scheduleMinutes"
                :min="1"
                :max="43200"
                :controls="false"
                placeholder="间隔分钟"
                class="schedule-field"
              />
              <span class="muted">分钟（1-43200，即最多 30 天）</span>
            </template>
          </div>
        </el-form-item>
        <el-form-item label="自主级别">
          <el-radio-group v-model="form.autonomy">
            <el-radio :value="1">只读（推荐）</el-radio>
            <el-radio :value="3">全自动</el-radio>
            <el-radio :value="2" disabled>需审批（即将上线）</el-radio>
          </el-radio-group>
          <div class="muted autonomy-tip">{{ autonomyTip(form.autonomy) }}</div>
        </el-form-item>
        <el-form-item label="模型">
          <el-select v-model="form.model_id" placeholder="系统默认" clearable class="task-field">
            <el-option v-for="item in options.models" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="连接器">
          <el-select v-model="form.server_ids" multiple collapse-tags placeholder="默认挂载全部可用" class="task-field">
            <el-option v-for="item in options.mcp_servers" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="Skill">
          <el-select
            v-model="form.skill_keys"
            multiple
            collapse-tags
            placeholder="可选：挂载领域规则"
            class="task-field"
          >
            <el-option v-for="item in options.skills" :key="item.key" :label="item.name" :value="item.key" />
          </el-select>
        </el-form-item>
        <el-form-item label="通知渠道">
          <el-checkbox-group v-model="form.notify_channels">
            <el-checkbox value="is_email">邮件</el-checkbox>
            <el-checkbox value="is_ent_wechat">企业微信机器人</el-checkbox>
          </el-checkbox-group>
          <el-input
            v-if="form.notify_channels.includes('is_email')"
            v-model="form.notify_email"
            placeholder="收件邮箱"
            class="task-field email-field"
          />
        </el-form-item>
        <el-form-item label="高级参数">
          <div class="advanced-row">
            <span class="muted">工具轮数上限</span>
            <el-input-number v-model="form.max_steps" :min="3" :max="30" :controls="false" class="advanced-field" />
            <span class="muted">软超时（秒）</span>
            <el-input-number
              v-model="form.timeout"
              :min="60"
              :max="1800"
              :step="30"
              :controls="false"
              class="advanced-field"
            />
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="editorVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" v-db-click @click="saveTask">保存</el-button>
      </template>
    </el-dialog>

    <!-- 运行历史 -->
    <el-dialog
      :title="`运行历史 · ${currentTask?.name || ''}`"
      v-model="runsVisible"
      width="860px"
      top="6vh"
      append-to-body
    >
      <el-table v-loading="runsLoading" :data="runs" size="small">
        <el-table-column label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="runStatusTag(row.status).type" size="small" effect="plain">{{
              runStatusTag(row.status).label
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="触发" width="80">
          <template #default="{ row }">{{ row.trigger_type === 2 ? '手动' : '调度' }}</template>
        </el-table-column>
        <el-table-column label="开始时间" width="150">
          <template #default="{ row }">{{ row.started_at ? formatFullTime(row.started_at) : '—' }}</template>
        </el-table-column>
        <el-table-column label="耗时" width="80">
          <template #default="{ row }">{{ row.duration ? row.duration + 's' : '—' }}</template>
        </el-table-column>
        <el-table-column label="工具调用" width="80" prop="tool_calls" />
        <el-table-column label="结果 / 错误" min-width="220">
          <template #default="{ row }">
            <span v-if="row.summary" class="cell-ellipsis" :title="row.summary">{{ row.summary }}</span>
            <span v-else-if="row.error" class="cell-error cell-ellipsis" :title="row.error">{{ row.error }}</span>
            <span v-else class="muted">—</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="70" fixed="right">
          <template #default="{ row }">
            <a v-db-click @click="openRunDetail(row)">详情</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="runsTotal"
          :total="runsTotal"
          v-model:page="runsPage"
          v-model:limit="runsLimit"
          @pagination="loadRuns"
        />
      </div>
    </el-dialog>

    <!-- 运行详情（过程回放） -->
    <el-dialog
      title="运行详情"
      v-model="detailVisible"
      width="760px"
      top="5vh"
      append-to-body
      class="run-detail-dialog"
    >
      <div v-loading="detailLoading" class="run-detail">
        <template v-if="runDetail">
          <div class="run-detail-head">
            <el-tag :type="runStatusTag(runDetail.status).type" size="small" effect="plain">
              {{ runStatusTag(runDetail.status).label }}
            </el-tag>
            <span v-if="runDetail.started_at" class="muted">{{ formatFullTime(runDetail.started_at) }}</span>
            <span v-if="runDetail.duration" class="muted">耗时 {{ runDetail.duration }}s</span>
            <span class="muted">工具调用 {{ runDetail.tool_calls }} 次</span>
            <el-button v-if="runDetail.conversation_id" link type="primary" size="small" @click="openConversation">
              在对话中查看
            </el-button>
          </div>
          <div v-if="runDetail.error" class="run-detail-error">{{ runDetail.error }}</div>
          <div class="run-messages">
            <template v-for="(message, index) in runDetail.messages || []" :key="index">
              <div v-if="message.role === 'user'" class="msg-row user">
                <div class="msg-bubble user">{{ message.content }}</div>
              </div>
              <div v-else-if="message.role === 'tool'" class="msg-tool">
                <div class="tool-line">
                  <i class="el-icon-connection" />
                  <span>工具 {{ message.tool_name }}</span>
                </div>
                <div v-if="toolSummary(message)" class="tool-result mono">{{ toolSummary(message) }}</div>
              </div>
              <div v-else-if="message.role === 'assistant' && message.content" class="msg-row assistant">
                <div class="msg-content markdown-body" v-html="renderMarkdown(message.content)"></div>
              </div>
            </template>
          </div>
        </template>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useRouter } from 'vue-router';
import setting from '@/setting';
import {
  aiAgentOptionsApi,
  aiTaskDeleteApi,
  aiTaskDetailApi,
  aiTaskListApi,
  aiTaskRunDetailApi,
  aiTaskRunListApi,
  aiTaskRunsApi,
  aiTaskSaveApi,
  aiTaskStatusApi,
  aiTaskTriggerApi,
} from '../api';
import { renderMarkdown } from '../utils/markdown';

defineOptions({ name: 'TaskManage' });

const router = useRouter();

const AVATAR_COLORS = ['#ff7a45', '#9254de', '#40a9ff', '#73d13d', '#f759ab', '#ffc53d', '#36cfc9', '#597ef7'];

const WEEK_LABELS = { 1: '周一', 2: '周二', 3: '周三', 4: '周四', 5: '周五', 6: '周六', 7: '周日' };
const MONTH_LABELS = Object.fromEntries(Array.from({ length: 12 }, (_, i) => [i + 1, `${i + 1}月`]));
const AUTONOMY_LABELS = { 1: '只读', 2: '需审批', 3: '全自动' };
const CHANNEL_LABELS = { is_email: '邮件', is_ent_wechat: '企业微信' };
const RUN_STATUS = {
  success: { label: '成功', type: 'success' },
  failed: { label: '失败', type: 'danger' },
  timeout: { label: '超时', type: 'warning' },
  running: { label: '执行中', type: 'primary' },
};

const loading = ref(false);
const triggeringId = ref(0);
const list = ref([]);
const page = ref(1);
const limit = ref(15);
const total = ref(0);

/** 编辑弹窗 */
const editorVisible = ref(false);
const editId = ref(0);
const saving = ref(false);
const formRef = ref(null);
const options = reactive({ models: [], mcp_servers: [], skills: [] });
const scheduleMinutes = ref(30);
const emptyForm = () => ({
  id: 0,
  name: '',
  instruction: '',
  model_id: 0,
  server_ids: [],
  skill_keys: [],
  autonomy: 1,
  schedule_type: 2,
  schedule_value: '09:00',
  schedule_week: 1,
  schedule_day: 1,
  max_steps: 10,
  timeout: 300,
  notify_channels: [],
  notify_email: '',
  status: 1,
});
const form = reactive(emptyForm());
const rules = {
  name: [{ required: true, message: '请填写任务名称', trigger: 'blur' }],
  instruction: [{ required: true, message: '请填写任务指令', trigger: 'blur' }],
  schedule_value: [{ required: true, message: '请设置执行时间', trigger: 'change' }],
};

/** 运行历史弹窗 */
const runsVisible = ref(false);
const runsLoading = ref(false);
const currentTask = ref(null);
const runs = ref([]);
const runsPage = ref(1);
const runsLimit = ref(15);
const runsTotal = ref(0);

/** 运行详情弹窗 */
const detailVisible = ref(false);
const detailLoading = ref(false);
const runDetail = ref(null);

const enabledCount = computed(() => list.value.filter((row) => row.status === 1).length);

onMounted(() => {
  loadList();
  loadOptions();
});

function loadList() {
  loading.value = true;
  aiTaskListApi({ page: page.value, limit: limit.value })
    .then((res) => {
      const data = res.data || {};
      list.value = data.list || [];
      total.value = data.count || 0;
    })
    .finally(() => {
      loading.value = false;
    });
}

/** 拉取模型/连接器/Skill 选项（options 接口一次返回全部启用项） */
function loadOptions() {
  aiAgentOptionsApi().then((res) => {
    const data = res.data || {};
    options.models = data.models || [];
    options.mcp_servers = data.mcp_servers || [];
    options.skills = data.skills || [];
  });
}

function openEditor(row) {
  editorVisible.value = true;
  if (!row) {
    editId.value = 0;
    Object.assign(form, emptyForm());
    scheduleMinutes.value = 30;
    formRef.value?.clearValidate();
    return;
  }
  aiTaskDetailApi(row.id).then((res) => {
    const data = res.data || {};
    editId.value = data.id;
    const scheduleValue = String(data.schedule_value || '');
    // 每周/每月/每年模式的调度值带前缀（周几|HH:MM / 几日|HH:MM / 月|日|HH:MM），拆解后回显
    let week = 1;
    let day = 1;
    let month = 1;
    let time = scheduleValue || '09:00';
    const parts = scheduleValue.includes('|') ? scheduleValue.split('|') : [];
    if (data.schedule_type === 3 && parts.length === 2) {
      week = parseInt(parts[0], 10) || 1;
      time = parts[1] || '09:00';
    } else if (data.schedule_type === 4 && parts.length === 2) {
      day = parseInt(parts[0], 10) || 1;
      time = parts[1] || '09:00';
    } else if (data.schedule_type === 5 && parts.length === 3) {
      month = parseInt(parts[0], 10) || 1;
      day = parseInt(parts[1], 10) || 1;
      time = parts[2] || '09:00';
    }
    Object.assign(form, {
      id: data.id,
      name: data.name,
      instruction: data.instruction,
      model_id: data.model_id || 0,
      server_ids: data.server_ids || [],
      skill_keys: data.skill_keys || [],
      autonomy: data.autonomy || 1,
      schedule_type: data.schedule_type || 2,
      schedule_value: time,
      schedule_week: week,
      schedule_day: day,
      schedule_month: month,
      max_steps: data.max_steps || 10,
      timeout: data.timeout || 300,
      notify_channels: data.notify_channels || [],
      notify_email: data.notify_email || '',
      status: data.status,
    });
    scheduleMinutes.value = /^\d+$/.test(scheduleValue) ? parseInt(scheduleValue, 10) || 30 : 30;
    formRef.value?.clearValidate();
  });
}

/** 调度类型切换：定时类（每天/每周/每月）默认时刻，间隔类回到上次间隔值 */
function onScheduleTypeChange(type) {
  form.schedule_value = type === 1 ? String(scheduleMinutes.value) : '09:00';
}

/** 组装提交用调度值：每周/每月/每年模式在时刻前拼接前缀，与后端 computeNextRunTime 解析口径一致 */
function buildScheduleValue() {
  if (form.schedule_type === 1) return String(scheduleMinutes.value);
  if (form.schedule_type === 3) return `${form.schedule_week}|${form.schedule_value}`;
  if (form.schedule_type === 4) return `${form.schedule_day}|${form.schedule_value}`;
  if (form.schedule_type === 5) return `${form.schedule_month}|${form.schedule_day}|${form.schedule_value}`;
  return form.schedule_value;
}

function saveTask() {
  formRef.value?.validate((valid) => {
    if (!valid) return;
    if (form.notify_channels.includes('is_email') && !/^[\w.+-]+@[\w-]+\.[\w.]+$/.test(form.notify_email || '')) {
      return ElMessage.warning('请填写合法的收件邮箱');
    }
    saving.value = true;
    aiTaskSaveApi({ ...form, id: editId.value, schedule_value: buildScheduleValue() })
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        editorVisible.value = false;
        loadList();
      })
      .finally(() => {
        saving.value = false;
      });
  });
}

function changeStatus(row) {
  aiTaskStatusApi(row.id, row.status)
    .then(() => {
      ElMessage.success(row.status === 1 ? '已启用' : '已停用');
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
}

function triggerTask(row) {
  if (triggeringId.value) return;
  triggeringId.value = row.id;
  aiTaskTriggerApi(row.id)
    .then(() => {
      ElMessage.success('测试运行已触发，正在打开运行历史');
      // 自动打开运行历史，便于即时查看本次测试的过程与结果
      openRuns(row);
    })
    .catch(() => {})
    .finally(() => {
      triggeringId.value = 0;
    });
}

function removeTask(row) {
  ElMessageBox.confirm(`确定删除任务「${row.name}」吗？其全部运行历史将一并删除。`, '提示', { type: 'warning' })
    .then(() => aiTaskDeleteApi(row.id))
    .then(() => {
      ElMessage.success('删除成功');
      loadList();
    })
    .catch(() => {});
}

function openRuns(row) {
  currentTask.value = row;
  runsVisible.value = true;
  runsPage.value = 1;
  loadRuns();
}

function loadRuns() {
  if (!currentTask.value) return;
  runsLoading.value = true;
  aiTaskRunsApi(currentTask.value.id, { page: runsPage.value, limit: runsLimit.value })
    .then((res) => {
      const data = res.data || {};
      runs.value = data.list || [];
      runsTotal.value = data.count || 0;
    })
    .finally(() => {
      runsLoading.value = false;
    });
}

function openRunDetail(row) {
  detailVisible.value = true;
  detailLoading.value = true;
  runDetail.value = null;
  aiTaskRunDetailApi(row.id)
    .then((res) => {
      runDetail.value = res.data || null;
    })
    .finally(() => {
      detailLoading.value = false;
    });
}

/** 跳转对话页并打开对应会话（路由为 history 模式，经 router.resolve 生成带正确 base 的地址） */
function openConversation() {
  const { href } = router.resolve({
    path: setting.routePre + '/aiagent/index',
    query: { conversation_id: String(runDetail.value?.conversation_id || 0) },
  });
  window.open(href, '_blank');
}

/** 工具结果摘要：取 error 信息或截断后的内容 */
function toolSummary(message) {
  const payload = message.tool_payload || {};
  const result = payload.result;
  if (!result) return '';
  if (result.error) return `失败：${result.message || '未知错误'}`;
  const text = typeof result === 'string' ? result : JSON.stringify(result);
  return text.length > 260 ? text.slice(0, 260) + '…' : text;
}

function scheduleText(row) {
  const type = row.schedule_type;
  if (type === 1) return `每 ${row.schedule_value} 分钟`;
  const parts = String(row.schedule_value || '').split('|');
  if (type === 3) return `每${WEEK_LABELS[parts[0]] || ''} ${parts[1]}`;
  if (type === 4) return `每月 ${parts[0]} 日 ${parts[1]}`;
  if (type === 5) return `每年 ${parts[0]}月${parts[1]}日 ${parts[2]}`;
  return `每天 ${row.schedule_value}`;
}

function autonomyTag(autonomy) {
  const map = { 1: 'info', 2: 'warning', 3: 'danger' };
  return { label: AUTONOMY_LABELS[autonomy] || autonomy, type: map[autonomy] || 'info' };
}

function autonomyTip(autonomy) {
  if (autonomy === 3) return '全自动：AI 可执行全部挂载工具（含写操作），仅超级管理员可用，请谨慎使用';
  if (autonomy === 2) return '需审批：即将上线，上线前请选择「只读」或「全自动」';
  return '只读：AI 仅可查询数据与发送通知，无法修改业务数据';
}

function runStatusTag(status) {
  return RUN_STATUS[status] || { label: status || '未知', type: 'info' };
}

const channelLabel = (channel) => CHANNEL_LABELS[channel] || channel;
const avatarText = (row) =>
  String(row.name || '?')
    .charAt(0)
    .toUpperCase();
function avatarColor(row) {
  const key = String(row.name || row.id || '');
  let hash = 0;
  for (let i = 0; i < key.length; i++) {
    hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
  }
  return AVATAR_COLORS[hash % AVATAR_COLORS.length];
}

function formatTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const pad = (n) => String(n).padStart(2, '0');
  return `${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function formatFullTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(
    date.getMinutes(),
  )}:${pad(date.getSeconds())}`;
}
</script>

<style scoped lang="scss">
.mt16 {
  margin-top: 0;
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
.task-hero {
  display: flex;
  align-items: center;
  padding: 4px 0 20px;
}
.task-hero-icon {
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
.task-hero-main {
  flex: 1;
}
.task-hero-title {
  color: #1d2129;
  font-size: 17px;
  font-weight: 600;
}
.task-hero-sub {
  margin-top: 4px;
  color: #86909c;
  font-size: 13px;
}

/* 分组标题 */
.task-section {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}
.task-section-title {
  color: #1d2129;
  font-size: 15px;
  font-weight: 600;
}
.task-count-badge {
  margin-left: 8px;
  padding: 1px 9px;
  border-radius: 10px;
  background: var(--prev-bg-color);
  color: #4e5969;
  font-size: 12px;
}
.task-enabled-count {
  margin-left: auto;
  color: #86909c;
  font-size: 13px;
}

/* 任务卡片 */
.task-list {
  min-height: 80px;
}
.task-card {
  margin-bottom: 12px;
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 12px;
  background: var(--prev-bg-white);
  transition: box-shadow 0.2s;
  &:hover {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  }
}
.task-card-head {
  display: flex;
  align-items: center;
  padding: 14px 16px;
}
.task-avatar {
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
.task-meta {
  flex: 1;
  min-width: 0;
}
.task-name {
  display: flex;
  align-items: center;
}
.task-name-text {
  color: #1d2129;
  font-size: 14px;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.task-tag {
  margin-left: 8px;
}
.task-sub {
  margin-top: 3px;
  color: #86909c;
  font-size: 12px;
}
.task-actions {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.task-action-icon {
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
    animation: task-spin 1s linear infinite;
    pointer-events: none;
    opacity: 0.6;
  }
}
.task-switch {
  margin-left: 18px;
}
.task-test-btn {
  margin-left: 18px;
  font-weight: 600;
}
@keyframes task-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.task-card-body {
  padding: 0 16px 14px 64px;
}
.task-instruction {
  display: -webkit-box;
  overflow: hidden;
  color: #4e5969;
  font-size: 13px;
  line-height: 1.6;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}
.task-channel-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}
.channel-tag {
  flex-shrink: 0;
}

/* 空态与分页 */
.task-empty {
  padding: 40px 0;
  color: #86909c;
  font-size: 13px;
  text-align: center;
}

/* 表单弹窗 */
.editor-tip {
  margin-bottom: 14px;
}
.task-form {
  .schedule-input {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    margin-top: 8px;
    .schedule-field {
      width: 180px;
    }
    .schedule-day-field {
      width: 90px;
    }
    .schedule-month-field {
      width: 120px;
    }
  }
  .task-field {
    width: 100%;
  }
  .email-field {
    margin-top: 8px;
  }
  .autonomy-tip {
    width: 100%;
    margin-top: 4px;
    line-height: 1.6;
  }
  .advanced-row {
    display: flex;
    align-items: center;
    gap: 10px;
    .advanced-field {
      width: 110px;
    }
  }
}

/* 运行详情回放 */
.run-detail-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}
.run-detail-error {
  margin-bottom: 10px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #fef0f0;
  color: #f56c6c;
  font-size: 13px;
  word-break: break-all;
}
.run-messages {
  max-height: 55vh;
  overflow-y: auto;
  padding: 4px;
}
.msg-row {
  display: flex;
  margin-bottom: 14px;
  &.user {
    justify-content: flex-end;
  }
}
.msg-bubble.user {
  max-width: 78%;
  padding: 10px 14px;
  border-radius: 10px 2px 10px 10px;
  background: var(--prev-bg-color);
  color: #1d2129;
  font-size: 13px;
  line-height: 1.7;
  white-space: pre-wrap;
  word-break: break-word;
}
.msg-content {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 2px 10px 10px 10px;
  background: var(--prev-bg-color);
  font-size: 13px;
  line-height: 1.7;
  word-break: break-word;
}
.msg-tool {
  margin-bottom: 14px;
  padding: 8px 12px;
  border-radius: 8px;
  background: var(--prev-bg-color);
  .tool-line {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #4e5969;
    font-size: 12px;
    font-weight: 600;
  }
  .tool-result {
    margin-top: 6px;
    color: #86909c;
    line-height: 1.6;
    word-break: break-all;
    white-space: pre-wrap;
  }
}
.cell-ellipsis {
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.cell-error {
  color: #f56c6c;
}
</style>

<style lang="scss">
/* 运行详情弹窗壳层（非 scoped）：append-to-body 弹窗 teleport 到 body 后
   脱离组件样式作用域，scoped :deep 无法命中，必须用全局类 */
.run-detail-dialog .el-dialog__body {
  padding-top: 10px;
}
</style>
