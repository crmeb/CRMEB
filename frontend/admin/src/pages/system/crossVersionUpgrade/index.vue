<template>
  <div class="cross-version-upgrade">
    <!-- 功能 Tabs -->
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-tabs v-model="currentTab" @tab-change="handleTabClick">
        <el-tab-pane label="系统升级" name="upgrade"></el-tab-pane>
        <el-tab-pane label="升级记录" name="logs"></el-tab-pane>
      </el-tabs>

      <!-- 系统升级 Tab -->
      <div v-if="currentTab === 'upgrade'">
        <div v-if="showUpgradeComplete" class="new-version-summary">
          <div class="summary-header">
            <span class="summary-icon-wrap">
              <img src="@/assets/images/new.png" class="summary-icon-img" alt="upgrade" />
            </span>
            <div class="summary-title">版本升级完成</div>
            <div class="summary-sub">
              请上传小程序代码至微信服务器，完成后需在小程序后台发布，
              <a href="https://mp.weixin.qq.com" target="_blank" style="color: #409eff">点击发布</a>
            </div>
            <div class="summary-actions">
              <el-button type="primary" @click="handleUploadMini" :loading="uploadingMini">上传小程序</el-button>
              <el-button class="ml8" @click="handleRefreshStatus">暂不上传</el-button>
            </div>
          </div>
        </div>
        <!-- 发现新版本摘要（status = 1） -->
        <div
          v-if="remoteStatus.status === 1 && downloadStage === 'idle' && !showUpgradeComplete"
          class="new-version-summary"
        >
          <div class="summary-header">
            <span class="summary-icon-wrap">
              <img src="@/assets/images/upgrade.png" class="summary-icon-img" alt="upgrade" />
            </span>
            <div class="summary-title">
              发现新版本
              <span v-if="upgradeablePackage">
                v{{ upgradeablePackage.first_version }}.{{ upgradeablePackage.second_version }}.{{
                  upgradeablePackage.third_version
                }}
              </span>
            </div>
            <div class="summary-sub">发现可升级的新版本，点击立即升级</div>
            <div class="summary-actions">
              <el-button type="primary" @click="startDownload" :disabled="startingDownload" :loading="startingDownload"
                >立即升级</el-button
              >
              <el-button class="ml8 check-update-btn" @click="checkRemoteUpdate" :loading="checkingRemote"
                >检测更新</el-button
              >
            </div>
          </div>
          <div v-if="upgradeablePackage" class="summary-content">
            <div class="content-title">
              V{{ upgradeablePackage.first_version }}.{{ upgradeablePackage.second_version }}.{{
                upgradeablePackage.third_version
              }}
              更新说明
              <span class="release-time" v-if="upgradeablePackage.release_time"
                >更新日期：{{ upgradeablePackage.release_time }}</span
              >
            </div>
            <div class="content-desc" v-html="upgradeablePackage.content"></div>
          </div>
        </div>

        <div
          v-if="(remoteStatus.status === 0 || remoteStatus.status === -1) && downloadStage === 'idle'"
          class="new-version-summary"
        >
          <div class="summary-header">
            <span class="summary-icon-wrap">
              <img src="@/assets/images/new.png" class="summary-icon-img" alt="upgrade" />
            </span>
            <div class="summary-title">当前版本 {{ versionInfo.current_version_main || '-' }}</div>
            <div class="summary-sub">当前版本号为 {{ versionInfo.current_version || '-' }}</div>
            <div class="summary-actions">
              <el-button class="check-update-btn" @click="checkRemoteUpdate" :loading="checkingRemote"
                >检测更新</el-button
              >
            </div>
          </div>
        </div>

        <!-- 升级进度展示 -->
        <div v-if="downloadStage !== 'idle' && !showUpgradeComplete" class="upgrade-section">
          <div class="section-title">
            升级进度
            <el-button type="text" class="ml8" @click="handleRefreshStatus">刷新状态</el-button>
          </div>
          <!-- 顶部进度条（加粗蓝色） -->
          <el-progress :percentage="downloadProgress" :show-text="false" :stroke-width="10" class="progress-line" />
          <!-- 分段状态行 -->
          <div class="progress-status-row">
            <div class="status-item">
              <i
                :class="
                  downloadSteps[0].status === 'loading'
                    ? 'el-icon-loading status-loading'
                    : downloadSteps[0].status === 'success'
                    ? 'el-icon-circle-check status-ok'
                    : 'el-icon-time status-muted'
                "
              ></i>
              <span
                :class="
                  downloadSteps[0].status === 'loading'
                    ? 'status-loading'
                    : downloadSteps[0].status === 'success'
                    ? 'status-ok'
                    : 'status-muted'
                "
                >检测</span
              >
            </div>
            <div class="status-item">
              <i
                :class="
                  downloadSteps[1].status === 'loading'
                    ? 'el-icon-loading status-loading'
                    : downloadSteps[1].status === 'success'
                    ? 'el-icon-circle-check status-ok'
                    : 'el-icon-time status-muted'
                "
              ></i>
              <span
                :class="
                  downloadSteps[1].status === 'loading'
                    ? 'status-loading'
                    : downloadSteps[1].status === 'success'
                    ? 'status-ok'
                    : 'status-muted'
                "
                >备份</span
              >
            </div>
            <div class="status-item">
              <i
                :class="
                  downloadSteps[2].status === 'loading'
                    ? 'el-icon-loading status-loading'
                    : downloadSteps[2].status === 'success'
                    ? 'el-icon-circle-check status-ok'
                    : 'el-icon-time status-muted'
                "
              ></i>
              <span
                :class="
                  downloadSteps[2].status === 'loading'
                    ? 'status-loading'
                    : downloadSteps[2].status === 'success'
                    ? 'status-ok'
                    : 'status-muted'
                "
                >下载</span
              >
            </div>
            <div class="status-item">
              <i
                :class="
                  downloadSteps[3].status === 'loading'
                    ? 'el-icon-loading status-loading'
                    : downloadSteps[3].status === 'success'
                    ? 'el-icon-circle-check status-ok'
                    : 'el-icon-time status-muted'
                "
              ></i>
              <span
                :class="
                  downloadSteps[3].status === 'loading'
                    ? 'status-loading'
                    : downloadSteps[3].status === 'success'
                    ? 'status-ok'
                    : 'status-muted'
                "
                >{{ downloadType === 4 ? '完成' : '更新中' }}</span
              >
            </div>
            <!-- <div class="status-item">
              <span
                :class="downloadType === 4 ? 'status-ok' : downloadStage === 'error' ? 'status-error' : 'status-muted'"
                >{{ downloadType === 4 ? '完成' : '更新中' }}</span
              >
            </div> -->
          </div>
          <div class="mt10 download-msg">{{ downloadMessage }}</div>
        </div>
        <!-- 文件校验失败提示 -->
        <div
          class="upgrade-section"
          v-if="downloadType >= 0 && downloadSteps[0].status !== 'loading' && !showUpgradeComplete"
        >
          <div class="section-title">
            <span class="step-num">1</span>
            <span>检测</span>
          </div>
          <template v-if="downloadSteps[0].status === 'error'">
            <div class="check-error-box mt16">
              <div class="check-error-desc">
                {{ downloadSteps[0].message || '您的系统功能做了修改，导致升级失败' }}
              </div>
            </div>
            <!-- 变更文件列表 -->
            <div v-if="normalizedCheckErrorFiles.length && downloadType === 0" class="mt16">
              <div class="error-files-table-wrap">
                <el-table :data="normalizedCheckErrorFiles" class="error-files-table" :max-height="325">
                  <el-table-column type="index" label="序号" width="120"></el-table-column>
                  <el-table-column prop="path" label="文件路径" min-width="360" show-overflow-tooltip></el-table-column>
                </el-table>
              </div>
            </div>
            <div class="check-error-actions mt10" v-if="downloadType === 0">
              <el-button type="primary" @click="cancelUpgrade">取消升级</el-button>
              <el-button class="ml8" @click="ignoreAndProceed(0)">忽略并执行</el-button>
            </div>
          </template>
          <div v-else class="check-success-box mt16">{{ downloadSteps[0].message || '检测完成' }}</div>
        </div>
        <!-- 步骤2: 备份 -->
        <div
          class="upgrade-section"
          v-if="downloadType >= 1 && downloadSteps[1].status !== 'loading' && !showUpgradeComplete"
        >
          <div class="section-title">
            <span class="step-num">2</span>
            <span>备份</span>
          </div>
          <div v-if="downloadSteps[1].status === 'success'" class="check-success-box mt16">
            {{ downloadSteps[1].message || '数据库备份完成' }}
          </div>
          <template v-else>
            <div class="check-error-box mt16">
              <div class="check-error-desc">
                {{ downloadSteps[1].message || '数据库备份失败' }}
              </div>
            </div>
            <div class="check-error-actions mt10">
              <el-button type="primary" @click="cancelUpgrade">取消升级</el-button>
              <el-button class="ml8" @click="ignoreAndProceed(1)">忽略并执行</el-button>
              <el-button class="ml8" @click="reExecuteUpgrade">重新备份</el-button>
            </div>
          </template>
        </div>
        <!-- 步骤3: 执行数据库升级 -->
        <div
          class="upgrade-section"
          v-if="downloadType >= 2 && downloadSteps[2].status !== 'loading' && !showUpgradeComplete"
        >
          <div class="section-title">
            <span class="step-num">3</span>
            <span>下载更新</span>
          </div>
          <div class="check-success-box mt16">{{ downloadSteps[2].message || '更新文件下载完成' }}</div>
        </div>
        <div
          class="upgrade-section"
          v-if="
            sqlExecutionLogs.length > 0 &&
            downloadType >= 3 &&
            downloadSteps[3].status !== 'loading' &&
            !showUpgradeComplete
          "
        >
          <!-- 升级进度详情 -->
          <div class="section-title">
            <span class="step-num">4</span>
            <span>执行数据库升级</span>
          </div>
          <div class="upgrade-progress-detail">
            <!-- SQL执行详情 -->
            <div class="sql-execution-logs">
              <div class="logs-header">
                <span>SQL执行详情</span>
                <el-tag size="small" type="success">成功: {{ sqlSuccessCount }}</el-tag>
                <el-tag size="small" type="danger" v-if="sqlFailedCount > 0">失败: {{ sqlFailedCount }}</el-tag>
                <el-tag size="small" type="info" v-if="sqlSkippedCount > 0">跳过: {{ sqlSkippedCount }}</el-tag>
              </div>
              <div class="logs-content">
                <div
                  v-for="(log, index) in sqlExecutionLogs"
                  :key="index"
                  class="log-item"
                  :class="{
                    'log-success': log.status === 'success',
                    'log-failed': log.status === 'failed',
                    'log-skipped': log.status === 'skipped',
                  }"
                >
                  <span class="log-index">{{ index + 1 }}.</span>
                  <span class="log-version">[{{ log.version }}]</span>
                  <span class="log-type">[{{ getTypeName(log.type) }}]</span>
                  <span class="log-table">{{ log.table || '-' }}</span>
                  <span v-if="log.field" class="log-field">{{ log.field }}</span>
                  <el-tag
                    size="small"
                    :type="log.status === 'success' ? 'success' : log.status === 'failed' ? 'danger' : 'info'"
                  >
                    {{ log.status === 'success' ? '成功' : log.status === 'failed' ? '失败' : '跳过' }}
                  </el-tag>
                  <span v-if="log.message" class="log-message">{{ log.message }}</span>
                </div>
              </div>
            </div>

            <!-- 升级结果 -->
            <div class="upgrade-result">
              <el-alert
                :title="sqlFailedCount === 0 ? '升级成功' : '升级完成(有失败项)'"
                :type="sqlFailedCount === 0 ? 'success' : 'warning'"
                :closable="false"
                show-icon
              >
                <span>执行成功: {{ sqlSuccessCount }} 条；</span>
                <span v-if="sqlFailedCount > 0"> 执行失败: {{ sqlFailedCount }} 条；</span>
                <span v-if="sqlSkippedCount > 0"> 跳过: {{ sqlSkippedCount }} 条；</span>
              </el-alert>
            </div>
          </div>
        </div>
      </div>

      <!-- 升级记录 Tab -->
      <div v-if="currentTab === 'logs'" class="upgrade-logs">
        <el-table :data="upgradeLogList" style="width: 100%" v-loading="loadingLogs">
          <el-table-column prop="title" label="升级标题" min-width="120" show-overflow-tooltip />
          <el-table-column label="版本" width="100">
            <template #default="scope">
              v{{ scope.row.first_version }}.{{ scope.row.second_version }}.{{ scope.row.third_version }}
            </template>
          </el-table-column>
          <el-table-column prop="upgrade_time" label="升级时间" width="200" />
          <el-table-column label="备份状态" min-width="150">
            <template #default="scope">
              <el-tag size="small" :type="scope.row.file_status ? 'success' : 'danger'">
                项目: {{ scope.row.file_status ? scope.row.package_link : '无' }} </el-tag
              ><br />
              <el-tag size="small" :type="scope.row.data_status ? 'success' : 'danger'">
                数据库: {{ scope.row.data_status ? scope.row.data_link : '无' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="content" label="更新内容" min-width="200" show-overflow-tooltip>
            <template #default="scope">
              <span v-html="scope.row.content"></span>
            </template>
          </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="acea-row row-right page">
          <pagination
            v-if="logsTotal"
            :total="logsTotal"
            v-model:page="logsPage"
            v-model:limit="logsLimit"
            @pagination="loadUpgradeLogs"
          />
        </div>
      </div>
    </el-card>

    <!-- 升级协议弹窗 -->
    <el-dialog
      v-model="agreementVisible"
      title="系统升级协议"
      width="800px"
      destroy-on-close
      :close-on-click-modal="false"
      class="agreement-dialog"
    >
      <div class="agreement-content" v-loading="agreementLoading">
        <div v-html="agreementContent"></div>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button @click="agreementVisible = false">不同意</el-button>
          <el-button type="primary" @click="doStartDownload" :loading="startingDownload">同意并升级</el-button>
        </span></template
      >
    </el-dialog>
    <!-- 页面本地蒙层，仅在本页显示 -->
    <div v-if="agreementVisible" class="local-mask"></div>

    <!-- 上传成功弹窗 -->
    <el-dialog
      v-model="uploadSuccessVisible"
      width="400px"
      :show-close="false"
      :close-on-click-modal="false"
      class="upload-success-dialog"
    >
      <div class="upload-success-content">
        <i class="el-icon-circle-check" style="font-size: 48px; color: #67c23a; margin-bottom: 16px"></i>
        <div class="success-title">上传成功</div>
        <div class="success-desc">
          已上传小程序代码至微信服务器，<a href="https://mp.weixin.qq.com" target="_blank" style="color: #409eff"
            >点击发布</a
          >
        </div>
        <el-button type="primary" @click="handleUploadSuccessClose" style="margin-top: 24px; width: 120px"
          >我知道了</el-button
        >
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  upgradeStatusApi,
  upgradeableListApi,
  downloadApi,
  downloadProgressApi,
  checkCrossVersionUpgradeApi,
  upgradeLogListApi,
  upgradeAgreementApi,
  reExecuteUpgradeApi,
} from '@/api/system';
import { routineCIUpload } from '@/api/app';
import setting from '@/setting';

defineOptions({ name: 'CrossVersionUpgrade' });

const router = useRouter();

const currentTab = ref('upgrade');
const versionInfo = ref({});
// 远程检测
const checkingRemote = ref(false);
const remoteStatus = ref({
  status: -1, // -1未检测, 0无更新, 1有更新
  title: '',
  force_reminder: 0,
});
const upgradeablePackage = ref(null); // 可升级包信息
// 下载状态
const startingDownload = ref(false);
const downloadStage = ref('idle'); // idle，loading，error，success, complete
const downloadType = ref(-1); // 0:检测，1:备份，2:下载，3:更新
const downloadSteps = ref([
  { status: 'loading', progress: 0, message: '', errorFiles: [] }, // 检测
  { status: 'loading', progress: 0, message: '', errorFiles: [] }, // 备份
  { status: 'loading', progress: 0, message: '', errorFiles: [] }, // 下载
  { status: 'loading', progress: 0, message: '', errorFiles: [] }, // 升级
]);
const downloadProgress = ref(0);
const downloadMessage = ref('');
let downloadTimer = null;
const checkErrorFiles = ref([]);
const mpVersionData = ref(null); // 小程序版本数据
// 本地版本检测
const localCheckResult = ref(null);
const pendingSqlList = ref([]);
// 数据库升级
const upgrading = ref(false);
const upgradeProgress = ref(0);
const upgradeProgressStatus = ref('');
const sqlExecutionLogs = ref([]);
const sqlSuccessCount = ref(0);
const sqlFailedCount = ref(0);
const sqlSkippedCount = ref(0);
// 升级记录
const upgradeLogList = ref([]);
const loadingLogs = ref(false);
const logsPage = ref(1);
const logsLimit = ref(15);
const logsTotal = ref(0);
// 升级协议
const agreementVisible = ref(false);
const agreementLoading = ref(false);
const agreementContent = ref('');
const uploadSuccessVisible = ref(false);
const showUpgradeComplete = ref(false);
const uploadingMini = ref(false);

const normalizedCheckErrorFiles = computed(() => {
  const list = Array.isArray(checkErrorFiles.value) ? checkErrorFiles.value : [];
  return list.map((item) => {
    if (typeof item === 'string') {
      return { path: item, note: '' };
    } else if (item && typeof item === 'object') {
      return {
        path: item.path || item.full_path || item.filename || item.file || item.name || JSON.stringify(item),
        note: item.note || item.desc || '',
      };
    } else {
      return { path: String(item), note: '' };
    }
  });
});

// Tab 切换
function handleTabClick() {
  if (currentTab.value === 'logs') {
    loadUpgradeLogs();
  }
}

// 页面初始化
async function initPage() {
  // 同时检测远程更新和本地版本
  await Promise.all([checkLocalVersion()]);
}

// 检测远程更新
async function checkRemoteUpdate() {
  checkingRemote.value = true;
  try {
    const res = await upgradeStatusApi();
    remoteStatus.value = {
      status: res.data.status || 0,
      title: res.data.title || '',
      force_reminder: res.data.force_reminder || 0,
    };

    // 如果有更新，获取可升级包列表
    if (res.data.status === 1) {
      await loadUpgradeableList();
    }
  } catch (err) {
    ElMessage.error(err.msg || '检测远程更新失败');
  } finally {
    checkingRemote.value = false;
  }
}

// 获取可升级包列表
async function loadUpgradeableList() {
  try {
    const res = await upgradeableListApi();
    const list = res.data?.list || res.data || [];
    if (list.length > 0) {
      upgradeablePackage.value = list[0]; // 取第一个
    }
  } catch (err) {
    console.error('获取可升级包列表失败', err);
  }
}

// 检测本地版本
async function checkLocalVersion() {
  try {
    const res = await checkCrossVersionUpgradeApi();
    localCheckResult.value = res.data;
    versionInfo.value = {
      current_version: res.data.current_version,
      current_version_main: res.data.current_version_main,
      current_code: res.data.current_code,
    };
  } catch (err) {
    console.error('检测本地版本失败', err);
  }
}

// 开始下载更新包
async function startDownload() {
  // 打开协议弹窗
  await openAgreement();
}

async function openAgreement() {
  try {
    agreementLoading.value = true;
    const res = await upgradeAgreementApi();
    agreementContent.value = res.data?.content || res.data || '';
    agreementVisible.value = true;
  } catch (err) {
    ElMessage.error(err.msg || '获取升级协议失败');
  } finally {
    agreementLoading.value = false;
  }
}

async function doStartDownload() {
  if (!upgradeablePackage.value?.package_key) {
    ElMessage.error('未获取到升级包信息，请重新检测更新');
    return;
  }

  // 先关闭协议弹窗，显示页面内容
  agreementVisible.value = false;
  downloadStage.value = 'loading';

  startingDownload.value = true;
  try {
    await downloadApi(upgradeablePackage.value.package_key);
    downloadType.value = 0;
    startDownloadProgressPolling();
  } catch (err) {
    ElMessage.error(err.msg || '开始下载失败');
    downloadStage.value = 'error';
    downloadMessage.value = err.msg || '开始下载失败';
  } finally {
    startingDownload.value = false;
  }
}

// 轮询下载进度
function startDownloadProgressPolling() {
  downloadTimer = setInterval(async () => {
    try {
      const res = await downloadProgressApi({ type: downloadType.value });
      if (res.data) {
        const currentStage = res.data.stage || 'idle';
        const currentProgress = res.data.progress || 0;
        const currentMessage = res.data.message || '';
        const currentErrorFiles = res.data.data || [];

        downloadStage.value = currentStage;
        downloadProgress.value = currentProgress;
        downloadMessage.value = currentMessage;
        checkErrorFiles.value = currentErrorFiles;

        // 更新当前步骤状态
        if (downloadType.value >= 0 && downloadType.value < downloadSteps.value.length) {
          downloadSteps.value[downloadType.value] = {
            status: currentStage,
            progress: currentProgress,
            message: currentMessage,
            errorFiles: currentErrorFiles,
          };
        }
        if (res.data.stage === 'success' && downloadType.value === 3) {
          sqlExecutionLogs.value = res.data.data.sql_logs;
          sqlSuccessCount.value = res.data.data.executed || 0;
          sqlSkippedCount.value = res.data.data.skipped || 0;
          sqlFailedCount.value = res.data.data.failed || 0;
        }
        // 完成时停止轮询并自动刷新页面
        if (res.data.stage === 'complete' && downloadType.value === 4) {
          mpVersionData.value = res.data.routine_upload_data;
          clearDownloadTimer();
          checkLocalVersion();
          showUpgradeComplete.value = true;
        }

        // 失败时停止轮询
        if (['error'].includes(res.data.stage)) {
          clearDownloadTimer();
        }
        if (['success'].includes(res.data.stage) && downloadType.value !== 4) {
          clearDownloadTimer();
          // 标记当前步骤完成
          if (downloadType.value >= 0 && downloadType.value < downloadSteps.value.length) {
            downloadSteps.value[downloadType.value]['status'] = 'success';
          }
          downloadType.value++;
          startDownloadProgressPolling();
        }
      }
    } catch (err) {
      console.error('获取下载进度失败', err);
    }
  }, 2000);
}

function clearDownloadTimer() {
  if (downloadTimer) {
    clearInterval(downloadTimer);
    downloadTimer = null;
  }
}

// 重试下载
function retryDownload() {
  downloadStage.value = 'idle';
  downloadProgress.value = 0;
  downloadMessage.value = '';
}

// 刷新页面
function refreshPage() {
  window.location.reload();
}
// 取消升级，返回初始状态
function cancelUpgrade() {
  clearDownloadTimer();
  downloadStage.value = 'idle';
  downloadProgress.value = 0;
  downloadMessage.value = '';
  checkErrorFiles.value = [];
  remoteStatus.value.status = -1;
}
// 忽略并执行（尝试继续）
async function ignoreAndProceed(type) {
  if (type === 0) {
    // 原实现向 computed normalizedCheckErrorFiles 赋值（Vue2 下为无效赋值），此处保持等价省略
    downloadSteps.value[0].message = '忽略并执行!';
    downloadSteps.value[0].status = 'success';
    downloadMessage.value = '忽略并执行...';
  }
  downloadType.value += 1;
  startDownloadProgressPolling();
}
// 重新执行升级
async function reExecuteUpgrade() {
  try {
    await reExecuteUpgradeApi();
    startDownloadProgressPolling();
  } catch (err) {
    ElMessage.error(err.msg || '重新执行升级失败');
  }
}
// 判断阶段是否完成
function isStageComplete(stage) {
  const stageOrder = ['idle', 'error', 'loading', 'complete', 'success'];
  const currentIndex = stageOrder.indexOf(downloadStage.value);
  const checkIndex = stageOrder.indexOf(stage);
  return currentIndex > checkIndex;
}

// 获取步骤图标
function getStepIcon(stage) {
  if (isStageComplete(stage)) return 'el-icon-success';
  if (downloadStage.value === stage) return 'el-icon-loading';
  return 'el-icon-time';
}
// 刷新状态
function handleRefreshStatus() {
  location.reload();
}

// 加载升级记录
async function loadUpgradeLogs() {
  loadingLogs.value = true;
  try {
    const res = await upgradeLogListApi({ page: logsPage.value, limit: logsLimit.value });
    upgradeLogList.value = res.data.list || [];
    logsTotal.value = res.data.count || 0;
  } catch (err) {
    ElMessage.error(err.msg || '加载升级记录失败');
  } finally {
    loadingLogs.value = false;
  }
}

// SQL类型名称
function getTypeName(type) {
  const typeMap = {
    1: '建表',
    2: '删表',
    3: '添加字段',
    4: '修改字段',
    5: '删除字段',
    6: '添加数据',
    7: '修改数据',
    8: '删除数据',
    '-1': '执行SQL',
  };
  return typeMap[type] || '未知';
}

function getTypeTagType(type) {
  const typeColorMap = {
    1: 'success',
    2: 'danger',
    3: 'primary',
    4: 'warning',
    5: 'danger',
    6: 'success',
    7: 'warning',
    8: 'danger',
    '-1': 'info',
  };
  return typeColorMap[type] || 'info';
}

// 上传小程序
async function handleUploadMini() {
  uploadingMini.value = true;
  try {
    await routineCIUpload({
      version: mpVersionData.value.version,
      desc: mpVersionData.value.desc,
      is_live: mpVersionData.value.is_live,
    });
    uploadSuccessVisible.value = true;
  } catch (err) {
    ElMessage.error(err.msg || '上传小程序失败');
    router.push({ path: `${setting.routePre}/app/routine/ci_upload` });
  } finally {
    uploadingMini.value = false;
  }
}

function handleUploadSuccessClose() {
  uploadSuccessVisible.value = false;
  handleRefreshStatus();
}

onMounted(() => {
  initPage();
});

onBeforeUnmount(() => {
  clearDownloadTimer();
});
</script>

<style lang="scss" scoped>
.upload-success-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 0;

  .success-title {
    font-size: 18px;
    font-weight: 500;
    color: #303133;
    margin-bottom: 8px;
  }

  .success-desc {
    font-size: 14px;
    color: #606266;
  }
}
.cross-version-upgrade {
  .new-version-summary {
    padding: 24px 0;
    background: #fff;
    border-radius: 8px;
    margin-top: 16px;
    margin-bottom: 16px;

    .summary-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0;
      margin-top: 12px;
      margin-bottom: 12px;
    }

    .summary-icon-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .summary-icon-img {
      width: 36px;
      height: 36px;
      display: block;
    }

    .summary-title {
      font-size: 16px;
      font-weight: 600;
      text-align: center;
      margin-top: 16px;
    }

    .summary-sub {
      color: #666;
      text-align: center;
      font-size: 12px;
      margin-top: 6px;
    }

    .summary-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 20px;
    }

    .check-update-btn {
      color: #0256ff;
      border: 1px solid #0256ff;
      background-color: transparent;
    }

    .check-update-btn:hover,
    .check-update-btn:focus {
      color: #fff;
      background-color: #0256ff;
      border-color: #0256ff;
    }

    .summary-content {
      background: #fafafa;
      border-radius: 6px;
      padding: 16px;
      margin-top: 48px;
    }

    .content-title {
      font-weight: 500;
      font-size: 14px;
      color: #333;
      padding-bottom: 8px;
      border-bottom: 1px solid #eaeaea;
      margin-bottom: 12px;
    }

    .release-time {
      color: #666;
      font-size: 12px;
      margin-left: 8px;
    }

    .content-desc {
      white-space: pre-wrap;
      font-size: 12px;
      line-height: 18px;
      color: #333;
    }
  }

  .agreement-content {
    max-height: 60vh;
    overflow: auto;
    font-size: 12px;
    font-weight: 400;
    line-height: 20px;
    color: #333333;
  }

  :deep(.agreement-dialog) {
    .el-dialog__header {
      text-align: center;
    }

    .el-dialog__title {
      font-size: 16px !important;
      line-height: 16px !important;
      font-weight: 500;
      color: #333333;
    }
  }

  .local-mask {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1998;
  }

  .progress-line :deep(.el-progress-bar__inner) {
    background-color: var(--prev-color-primary);
  }

  .progress-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    font-size: 12px;
  }

  .status-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .status-ok {
    color: var(--prev-color-primary);
  }

  .status-loading {
    color: #ff8a00;
  }

  .status-error {
    color: #ff4d4f;
  }

  .status-muted {
    color: #999;
  }

  .check-error-box {
    border: 1px solid #ffd591;
    background: #fff7e6;
    border-radius: 6px;
    padding: 12px;
    color: #ad6800;
  }

  .check-error-head {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
  }

  .check-error-desc {
    font-size: 12px;
    font-weight: 400;
  }

  .check-error-actions {
    display: flex;
    align-items: center;
  }
  .check-success-box {
    border: 1px solid rgba(2, 86, 255, 1);
    background: #e8fdf5;
    border-radius: 6px;
    padding: 12px;
    background: #f0f5ff;
    color: rgba(51, 51, 51, 1);
  }
  .mt16 {
    margin-top: 16px;
  }

  align-items: center;

  .version-info {
    display: flex;
    align-items: center;
    gap: 30px;

    .current-version {
      display: flex;
      align-items: center;
      font-size: 16px;

      .label {
        color: #666;
      }

      .version-num {
        font-weight: bold;
        color: var(--prev-color-primary);
      }
    }
  }
}

.upgrade-section {
  margin-bottom: 16px;
  padding: 16px;
  background: #f9fafc;

  .section-title {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    font-size: 16px;
    font-weight: 500;

    .step-num {
      width: 24px;
      height: 24px;
      background: var(--prev-color-primary);
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      margin-right: 10px;
    }
  }

  .section-content {
    .status-text {
      margin-left: 15px;
      color: #666;
    }
  }
}

.download-progress {
  .progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;

    .stage-text {
      color: #409eff;
      font-weight: 500;
    }

    .progress-text {
      color: #999;
    }
  }

  .progress-steps {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;

    .step-item {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #999;
      font-size: 13px;

      i {
        font-size: 16px;
      }

      &.active {
        color: #409eff;

        i {
          color: #409eff;
        }
      }

      &.done {
        color: #67c23a;

        i {
          color: #67c23a;
        }
      }
    }
  }
}

.download-complete,
.download-error {
  margin-top: 10px;
}

.upgrade-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  font-weight: 500;
}

.sql-list {
  margin-bottom: 20px;
}

.upgrade-progress-detail {
  .progress-header {
    margin-bottom: 20px;
  }

  .sql-execution-logs {
    margin-top: 20px;
    background: #f5f7fa;
    border-radius: 4px;
    padding: 12px;
    max-height: 300px;
    overflow-y: auto;

    .logs-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      font-weight: 500;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .logs-content {
      font-size: 12px;
      font-family: monospace;

      .log-item {
        padding: 4px 8px;
        border-radius: 2px;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 8px;

        &.log-success {
          background: #f0f9eb;
        }

        &.log-failed {
          background: #fef0f0;
        }

        &.log-skipped {
          background: #f4f4f5;
        }

        .log-index {
          color: #999;
          min-width: 30px;
        }

        .log-version {
          color: #409eff;
        }

        .log-type {
          color: #e6a23c;
          margin-right: 4px;
        }

        .log-table {
          color: #606266;
          min-width: 100px;
        }

        .log-field {
          color: #67c23a;
          margin-right: 8px;
        }

        .log-message {
          color: #909399;
          font-size: 11px;
          margin-left: auto;
        }
      }
    }
  }

  .upgrade-result {
    margin-top: 20px;
  }
}

/* 错误文件列表：每行高度 30px */
.error-files-table-wrap {
  max-height: none;
  overflow: visible;
}

.error-files-table :deep(.el-table__cell) {
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

.error-files-table :deep(.el-table__cell .cell) {
  line-height: 30px;
  padding-top: 0;
  padding-bottom: 0;
}

.download-msg {
  font-size: 12px;
  font-weight: 400;
  color: #999;
}
</style>
