<template>
  <div class="routine-ci-upload">
    <!-- 页面标题头（52px 紧凑标准） -->
    <pages-header title="小程序一键上传" description="使用 miniprogram-ci 自动化部署小程序代码到微信服务器" />

    <!-- 进度指示器 -->
    <div class="progress-steps">
      <div class="step" :class="{ active: true, completed: envStatus.ready }">
        <div class="step-number">1</div>
        <div class="step-label">环境配置</div>
      </div>
      <div class="step-line" :class="{ completed: envStatus.ready }"></div>
      <div
        class="step"
        :class="{
          active: envStatus.ready,
          completed: uploadConfig.app_id_configured && uploadConfig.private_key_exists,
        }"
      >
        <div class="step-number">2</div>
        <div class="step-label">上传配置</div>
      </div>
      <div
        class="step-line"
        :class="{ completed: uploadConfig.app_id_configured && uploadConfig.private_key_exists }"
      ></div>
      <div
        class="step"
        :class="{ active: envStatus.ready && uploadConfig.app_id_configured && uploadConfig.private_key_exists }"
      >
        <div class="step-number">3</div>
        <div class="step-label">上传代码</div>
      </div>
    </div>

    <!-- 使用须知 - 可折叠 -->
    <div class="notice-banner" :class="{ expanded: showNotice }">
      <div class="notice-header" @click="showNotice = !showNotice">
        <div class="notice-title">
          <i class="el-icon-info"></i>
          <span>使用前准备</span>
        </div>
        <i :class="showNotice ? 'el-icon-arrow-up' : 'el-icon-arrow-down'"></i>
      </div>
      <transition name="slide-fade">
        <div v-show="showNotice" class="notice-content">
          <div class="notice-item">
            <div class="notice-step">1</div>
            <div class="notice-text">
              <strong>生成代码上传密钥</strong>
              <p>
                访问 <a href="https://mp.weixin.qq.com/" target="_blank" rel="noopener">微信公众平台</a> → 开发管理 →
                开发设置 → 小程序代码上传 → 生成密钥
              </p>
            </div>
          </div>
          <div class="notice-item">
            <div class="notice-step">2</div>
            <div class="notice-text">
              <strong>配置 IP 白名单</strong>
              <p>在同一页面将服务器公网 IP 添加到白名单中</p>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- 主内容区域 -->
    <div class="main-content">
      <!-- 环境状态卡片 -->
      <div class="card env-card" :class="{ 'card-success': envStatus.ready, 'card-warning': !envStatus.ready }">
        <div class="card-header">
          <div class="card-title">
            <i class="el-icon-cpu"></i>
            <span>运行环境</span>
          </div>
          <el-button v-db-click class="refresh-btn" :disabled="loading.environment" @click="checkEnvironment">
            <i :class="loading.environment ? 'el-icon-loading' : 'el-icon-refresh'"></i>
          </el-button>
        </div>

        <div class="card-body" v-loading="loading.environment">
          <!-- 环境状态概览 -->
          <div class="status-overview">
            <div class="status-badge" :class="envStatus.ready ? 'success' : 'warning'">
              <i :class="envStatus.ready ? 'el-icon-check' : 'el-icon-warning'"></i>
              {{ envStatus.ready ? '环境就绪' : '环境未就绪' }}
            </div>
          </div>

          <!-- 环境详情 -->
          <div class="env-grid">
            <div class="env-item">
              <div class="env-icon os">
                <i class="el-icon-monitor"></i>
              </div>
              <div class="env-info">
                <span class="env-label">操作系统</span>
                <span class="env-value">{{ envStatus.os?.type || '-' }} {{ envStatus.os?.version || '' }}</span>
              </div>
            </div>
            <div class="env-item">
              <div class="env-icon" :class="envStatus.node?.installed ? 'success' : 'error'">
                <i class="el-icon-connection"></i>
              </div>
              <div class="env-info">
                <span class="env-label">Node.js</span>
                <span class="env-value" :class="envStatus.node?.installed ? 'text-success' : 'text-error'">
                  {{ envStatus.node?.installed ? 'v' + envStatus.node.version : '未安装' }}
                </span>
              </div>
            </div>
            <div class="env-item">
              <div class="env-icon" :class="envStatus.miniprogram_ci?.installed ? 'success' : 'error'">
                <i class="el-icon-box"></i>
              </div>
              <div class="env-info">
                <span class="env-label">miniprogram-ci</span>
                <span class="env-value" :class="envStatus.miniprogram_ci?.installed ? 'text-success' : 'text-error'">
                  {{ envStatus.miniprogram_ci?.installed ? 'v' + envStatus.miniprogram_ci.version : '未安装' }}
                </span>
              </div>
            </div>
          </div>

          <!-- exec 被禁用的警告 -->
          <div v-if="envStatus.exec_enabled === false" class="alert alert-error">
            <i class="el-icon-warning"></i>
            <div class="alert-content">
              <strong>无法使用小程序上传功能</strong>
              <p>服务器禁用了 <code>exec</code> 函数。请在 PHP 配置中启用该函数。</p>
              <p class="alert-hint">宝塔面板：软件商店 → PHP → 设置 → 禁用函数 → 删除 exec</p>
            </div>
          </div>

          <!-- 安装按钮 -->
          <div v-if="!envStatus.ready && envStatus.exec_enabled !== false" class="action-buttons">
            <el-button v-db-click @click="showGuide = true">
              <i class="el-icon-document"></i>
              <span>手动安装指南</span>
            </el-button>
          </div>
        </div>
      </div>

      <!-- 上传配置卡片 -->
      <div v-if="envStatus.ready" class="card config-card">
        <div class="card-header">
          <div class="card-title">
            <i class="el-icon-setting"></i>
            <span>上传配置</span>
          </div>
        </div>

        <div class="card-body">
          <div class="config-grid">
            <!-- AppId 配置 -->
            <div class="config-item">
              <div class="config-icon">
                <i class="el-icon-key"></i>
              </div>
              <div class="config-info">
                <span class="config-label">小程序 AppId</span>
                <div class="config-value">
                  <template v-if="uploadConfig.app_id_configured">
                    <span class="value-text">{{ uploadConfig.app_id }}</span>
                    <span class="status-dot success"></span>
                  </template>
                  <template v-else>
                    <span class="value-text text-muted">未配置</span>
                    <router-link
                      :to="{ path: $routeProStr + '/setting/system_config/' + uploadConfig.config_tab_id }"
                      class="config-link"
                    >
                      去配置 <i class="el-icon-arrow-right"></i>
                    </router-link>
                  </template>
                </div>
              </div>
            </div>

            <!-- 上传密钥配置 -->
            <div class="config-item">
              <div class="config-icon">
                <i class="el-icon-lock"></i>
              </div>
              <div class="config-info">
                <span class="config-label">上传密钥</span>
                <div class="config-value">
                  <template v-if="uploadConfig.private_key_exists">
                    <span class="value-text">已配置</span>
                    <span class="status-dot success"></span>
                    <a v-db-click class="link-btn" @click="showKeyUpload = true">重新上传</a>
                  </template>
                  <template v-else>
                    <span class="value-text text-muted">未配置</span>
                    <el-button v-db-click type="primary" @click="showKeyUpload = true">
                      <i class="el-icon-upload2"></i> 上传密钥
                    </el-button>
                  </template>
                </div>
              </div>
              <div class="config-hint">
                <a href="https://mp.weixin.qq.com/" target="_blank" rel="noopener">前往微信公众平台获取</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 一键上传卡片 -->
      <div
        v-if="envStatus.ready && uploadConfig.app_id_configured && uploadConfig.private_key_exists"
        class="card upload-card"
      >
        <div class="card-header">
          <div class="card-title">
            <i class="el-icon-upload"></i>
            <span>一键上传</span>
          </div>
          <el-tag size="small">无需开发者工具</el-tag>
        </div>

        <div class="card-body">
          <!-- 提示信息 -->
          <div class="info-banner">
            <i class="el-icon-info"></i>
            <span>系统将自动生成小程序代码包并上传到微信服务器</span>
          </div>

          <!-- 上传表单 -->
          <el-form
            :model="uploadForm"
            :rules="uploadRules"
            ref="uploadFormRef"
            class="upload-form"
            label-position="top"
          >
            <div class="form-row">
              <el-form-item label="版本号" prop="version" class="form-item-half">
                <el-input v-model="uploadForm.version" placeholder="例如：1.0.0">
                  <template #prefix><i class="el-icon-price-tag"></i></template>
                </el-input>
              </el-form-item>

              <el-form-item label="直播功能" class="form-item-half">
                <!-- <el-radio-group v-model="uploadForm.is_live" class="radio-group-custom">
                  <el-radio-button :label="0" :value="0">未开通</el-radio-button>
                  <el-radio-button :label="1" :value="1">已开通</el-radio-button>
                </el-radio-group> -->
                <el-switch
                  v-model="uploadForm.is_live"
                  :active-value="1"
                  :inactive-value="0"
                  class="defineSwitch"
                  size="large"
                  width="200"
                  active-text="已开通"
                  inactive-text="未开通"
                />
              </el-form-item>
            </div>

            <el-form-item label="版本描述">
              <el-input v-model="uploadForm.desc" type="textarea" :rows="3" placeholder="简要描述本次更新内容（可选）">
              </el-input>
            </el-form-item>

            <div class="form-actions">
              <el-button type="primary" v-db-click :loading="loading.upload" @click="handleUpload">
                <i v-if="!loading.upload" class="el-icon-upload2"></i>
                <span>上传到微信</span>
              </el-button>
              <el-button v-db-click :loading="loading.preview" @click="handlePreview">
                <i v-if="!loading.preview" class="el-icon-mobile-phone"></i>
                <span>获取预览码</span>
              </el-button>
            </div>
          </el-form>

          <!-- 预览二维码 -->
          <transition name="fade-slide">
            <div v-if="previewQrcode" class="preview-section">
              <div class="preview-card">
                <img :src="previewQrcode" alt="预览二维码" />
                <p>使用微信扫码预览</p>
              </div>
            </div>
          </transition>

          <!-- 上传结果 -->
          <transition name="fade-slide">
            <div v-if="uploadResult" class="result-section">
              <div class="result-card" :class="uploadResult.success ? 'success' : 'error'">
                <div class="result-icon">
                  <i :class="uploadResult.success ? 'el-icon-check' : 'el-icon-close'"></i>
                </div>
                <div class="result-content">
                  <h4>{{ uploadResult.success ? '上传成功' : '上传失败' }}</h4>
                  <p v-if="uploadResult.success">版本 {{ uploadResult.version }} 已上传到微信服务器</p>
                  <p v-if="uploadResult.success">
                    请前往 <a href="https://mp.weixin.qq.com/" target="_blank" rel="noopener">微信公众平台</a> 提交审核
                  </p>
                  <p v-else class="error-msg">{{ uploadResult.message }}</p>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- 手动安装指南弹窗 -->
    <el-dialog title="手动安装指南" v-model="showGuide" width="650px">
      <div v-if="installGuide" class="install-guide">
        <!-- 一键安装脚本 -->
        <div v-if="installGuide.script_url" class="guide-section recommended">
          <div class="section-badge">
            <i class="el-icon-star-on"></i>
            <span>推荐方式</span>
          </div>
          <div class="section-header">
            <div class="header-icon">
              <i class="el-icon-magic-stick"></i>
            </div>
            <div class="header-text">
              <h3>一键自动安装</h3>
              <p>最简单的方式，自动完成所有配置</p>
            </div>
          </div>
          <div class="install-instruction">
            <span class="instruction-label">在服务器终端执行：</span>
          </div>
          <div class="code-block">
            <div class="code-content">
              <span class="code-prompt">$</span>
              <code>curl -fsSL {{ installGuide.script_url }} | bash</code>
            </div>
            <button
              class="copy-btn"
              @click="copyToClipboard(`curl -fsSL ${installGuide.script_url} | bash`)"
              title="复制命令"
            >
              <i class="el-icon-document-copy"></i>
              <span class="copy-text">复制</span>
            </button>
          </div>
          <div class="section-footer">
            <i class="el-icon-download"></i>
            <span
              >或 <a :href="installGuide.script_url" target="_blank" rel="noopener">下载脚本文件</a> 后手动执行</span
            >
          </div>
        </div>

        <div class="divider-section">
          <div class="divider-line"></div>
          <span class="divider-text">或者手动安装</span>
          <div class="divider-line"></div>
        </div>

        <div class="guide-section manual">
          <div class="section-header">
            <div class="header-icon manual-icon">
              <i class="el-icon-document"></i>
            </div>
            <div class="header-text">
              <h3>{{ installGuide.title || '手动安装步骤' }}</h3>
              <p>按照以下步骤逐一执行</p>
            </div>
          </div>
          <div class="guide-steps">
            <div v-for="(step, index) in installGuide.steps" :key="index" class="step-item">
              <div class="step-number">{{ index + 1 }}</div>
              <div class="step-content">
                <code>{{ step }}</code>
              </div>
              <button class="step-copy-btn" @click="copyToClipboard(step)" title="复制">
                <i class="el-icon-document-copy"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button v-db-click @click="showGuide = false">关闭</el-button>
      </template>
    </el-dialog>

    <!-- 上传密钥弹窗 -->
    <el-dialog title="上传代码上传密钥" v-model="showKeyUpload" width="650px">
      <div class="key-upload-content">
        <div class="info-card">
          <div class="info-card-header">
            <i class="el-icon-info"></i>
            <span>密钥获取方式</span>
          </div>
          <ol class="info-steps">
            <li>登录 <a href="https://mp.weixin.qq.com/" target="_blank" rel="noopener">微信公众平台</a></li>
            <li>进入 开发管理 → 开发设置 → 小程序代码上传</li>
            <li>点击「生成」按钮，下载 private.key 文件</li>
            <li>用文本编辑器打开，复制全部内容粘贴到下方</li>
          </ol>
        </div>

        <div class="info-card warning">
          <div class="info-card-header">
            <i class="el-icon-warning"></i>
            <span>IP 白名单配置</span>
          </div>
          <p>在同一页面将服务器公网 IP 添加到白名单</p>
        </div>

        <div class="key-input-section">
          <label>密钥内容</label>
          <el-input
            v-model="keyContent"
            type="textarea"
            :rows="10"
            class="key-textarea"
            placeholder="请粘贴 private.key 文件的完整内容
-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----"
          />
        </div>
      </div>
      <template #footer>
        <el-button v-db-click @click="showKeyUpload = false">取消</el-button>
        <el-button type="primary" v-db-click :loading="loading.saveKey" @click="savePrivateKey">
          <i v-if="!loading.saveKey" class="el-icon-check"></i>
          <span>保存密钥</span>
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  routineCIEnvironment,
  routineCIGuide,
  routineCIConfig,
  routineCISaveKey,
  routineCIUpload,
  routineCIPreview,
} from '../../../api';

defineOptions({ name: 'RoutineCIUpload' });

// 页面状态
const showNotice = ref(false);
const envStatus = ref({});
const uploadConfig = ref({});
const installGuide = ref(null);

// 加载状态
const loading = reactive({
  environment: false,
  saveKey: false,
  upload: false,
  preview: false,
});

// 弹窗状态
const showGuide = ref(false);
const showKeyUpload = ref(false);

// 表单数据
const keyContent = ref('');
const uploadForm = ref({
  version: '',
  desc: '',
  is_live: 0,
});
const uploadRules = ref({
  version: [
    { required: true, message: '请输入版本号', trigger: 'blur' },
    { pattern: /^\d+\.\d+\.\d+$/, message: '版本号格式错误，请使用 x.x.x 格式', trigger: 'blur' },
  ],
});

// 结果数据
const previewQrcode = ref('');
const uploadResult = ref(null);

const uploadFormRef = ref(null);

onMounted(() => {
  checkEnvironment();
});

// 复制到剪贴板
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    ElMessage.success('已复制到剪贴板');
  } catch (err) {
    ElMessage.error('复制失败');
  }
}

// 检查环境
async function checkEnvironment() {
  loading.environment = true;
  try {
    const res = await routineCIEnvironment();
    envStatus.value = res.data;

    if (res.data.ready) {
      getUploadConfig();
    } else {
      getInstallGuide();
    }
  } catch (err) {
    ElMessage.error(err.msg || '检查环境失败');
  } finally {
    loading.environment = false;
  }
}

// 获取上传配置
async function getUploadConfig() {
  try {
    const res = await routineCIConfig();
    uploadConfig.value = res.data;
  } catch (err) {
    console.error('获取上传配置失败', err);
  }
}

// 获取安装指南
async function getInstallGuide() {
  try {
    const res = await routineCIGuide();
    installGuide.value = res.data;
  } catch (err) {
    console.error('获取安装指南失败', err);
  }
}

// 保存密钥
async function savePrivateKey() {
  if (!keyContent.value.trim()) {
    ElMessage.warning('请输入密钥内容');
    return;
  }

  loading.saveKey = true;
  try {
    await routineCISaveKey({ key_content: keyContent.value });
    ElMessage.success('密钥保存成功');
    showKeyUpload.value = false;
    keyContent.value = '';
    getUploadConfig();
  } catch (err) {
    ElMessage.error(err.msg || '保存失败');
  } finally {
    loading.saveKey = false;
  }
}

// 上传代码
async function handleUpload() {
  uploadFormRef.value.validate(async (valid) => {
    if (!valid) return;

    try {
      await ElMessageBox.confirm('确定要上传小程序代码吗？', '确认上传', {
        confirmButtonText: '确定上传',
        cancelButtonText: '取消',
        type: 'info',
      });

      loading.upload = true;
      uploadResult.value = null;

      const res = await routineCIUpload(uploadForm.value);

      uploadResult.value = {
        success: true,
        version: uploadForm.value.version,
        message: res.data?.message || '上传成功',
      };

      ElMessage.success('上传成功');
    } catch (err) {
      if (err !== 'cancel') {
        uploadResult.value = {
          success: false,
          message: err.msg || '上传失败',
        };
        ElMessage.error(err.msg || '上传失败');
      }
    } finally {
      loading.upload = false;
    }
  });
}

// 获取预览码
async function handlePreview() {
  loading.preview = true;
  try {
    const res = await routineCIPreview({});
    previewQrcode.value = res.data.qrcode_url;
    ElMessage.success('预览二维码生成成功');
  } catch (err) {
    ElMessage.error(err.msg || '获取预览码失败');
  } finally {
    loading.preview = false;
  }
}
</script>

<style lang="scss" scoped>
// ========================================
// 全部颜色只引用 --prev-* 令牌与 EP 桥接变量，跟随主题换肤
// ========================================
.routine-ci-upload {
  // 进度步骤
  .progress-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin-bottom: 16px;
    padding: 20px;
    background: var(--prev-bg-white);
    border-radius: 6px;

    .step {
      display: flex;
      align-items: center;
      gap: 10px;
      opacity: 0.4;
      transition: all 0.2s ease;

      &.active {
        opacity: 1;
      }

      &.completed .step-number {
        background: var(--prev-color-primary);
        border-color: var(--prev-color-primary);
      }

      .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--prev-bg-color);
        border: 2px solid var(--prev-border-color-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        font-size: 14px;
        color: var(--prev-color-text-primary);
        transition: all 0.2s ease;
      }

      &.active .step-number {
        background: var(--prev-color-primary);
        border-color: var(--prev-color-primary);
        color: var(--prev-color-text-white);
      }

      .step-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--prev-color-text-primary);
      }
    }

    .step-line {
      width: 60px;
      height: 2px;
      background: var(--prev-border-color-light);
      margin: 0 16px;
      transition: background 0.2s ease;

      &.completed {
        background: var(--prev-color-primary-light-5);
      }
    }
  }

  // 使用须知横幅
  .notice-banner {
    background: var(--prev-bg-white);
    border: 1px solid var(--prev-border-color-light);
    border-radius: 6px;
    margin-bottom: 16px;
    overflow: hidden;

    .notice-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      cursor: pointer;
      transition: background 0.15s ease;

      &:hover {
        background: var(--prev-bg-color);
      }

      .notice-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        font-size: 14px;
        color: var(--prev-color-text-regular);

        i {
          color: var(--prev-color-warning);
          font-size: 18px;
        }
      }

      > i {
        color: var(--prev-color-text-secondary);
        transition: transform 0.2s ease;
      }
    }

    .notice-content {
      padding: 0 20px 20px;
      border-top: 1px solid var(--prev-border-color-lighter);

      .notice-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 0;
        border-bottom: 1px solid var(--prev-border-color-lighter);

        &:last-child {
          border-bottom: none;
          padding-bottom: 0;
        }

        .notice-step {
          width: 24px;
          height: 24px;
          min-width: 24px;
          background: var(--prev-color-primary-light-9);
          color: var(--prev-color-primary);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 500;
          font-size: 12px;
        }

        .notice-text {
          strong {
            display: block;
            font-size: 14px;
            color: var(--prev-color-text-regular);
            margin-bottom: 4px;
          }

          p {
            font-size: 13px;
            color: var(--prev-color-text-secondary);
            margin: 0;
            line-height: 1.5;

            a {
              color: var(--prev-color-primary);
              text-decoration: none;
              font-weight: 500;

              &:hover {
                text-decoration: underline;
              }
            }
          }
        }
      }
    }
  }

  // 主内容区域
  .main-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  // 卡片基础样式
  .card {
    background: var(--prev-bg-white);
    border-radius: 6px;
    border: 1px solid var(--prev-border-color-lighter);
    overflow: hidden;
    transition: border-color 0.2s ease;

    &.card-success {
      border-color: var(--prev-color-primary-light-7);
    }

    &.card-warning {
      border-color: var(--el-color-warning-light-7);
    }

    .card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 15px 20px;
      border-bottom: 1px solid var(--prev-border-color-lighter);
      background: var(--prev-bg-white);

      .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
        color: var(--prev-color-text-regular);

        i {
          font-size: 18px;
          color: var(--prev-color-primary);
        }
      }

      .refresh-btn {
        padding: 7px 10px;

        i {
          font-size: 14px;
        }
      }
    }

    .card-body {
      padding: 20px;
    }
  }

  // 环境状态卡片
  .env-card {
    .status-overview {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;

      .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 14px;

        i {
          font-size: 16px;
        }

        &.success {
          background: var(--prev-color-primary-light-9);
          color: var(--prev-color-primary-dark-2);
        }

        &.warning {
          background: var(--el-color-warning-light-9);
          color: var(--el-color-warning-dark-2);
        }
      }
    }

    .env-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;

      @media (max-width: 768px) {
        grid-template-columns: 1fr;
      }

      .env-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: var(--prev-bg-color);
        border-radius: 4px;
        border: 1px solid transparent;
        transition: all 0.15s ease;

        &:hover {
          border-color: var(--prev-border-color-light);
        }

        .env-icon {
          width: 44px;
          height: 44px;
          border-radius: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: var(--prev-color-primary-light-9);
          border: 1px solid var(--prev-color-primary-light-7);

          i {
            font-size: 20px;
            color: var(--prev-color-primary);
          }

          &.success {
            background: var(--prev-color-primary-light-9);
            border-color: var(--prev-color-primary-light-7);

            i {
              color: var(--prev-color-primary);
            }
          }

          &.error {
            background: var(--el-color-danger-light-9);
            border-color: var(--el-color-danger-light-7);

            i {
              color: var(--prev-color-danger);
            }
          }
        }

        .env-info {
          display: flex;
          flex-direction: column;
          gap: 4px;

          .env-label {
            font-size: 12px;
            color: var(--prev-color-text-secondary);
          }

          .env-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--prev-color-primary);

            &.text-error {
              color: var(--prev-color-danger);
            }
          }
        }
      }
    }
  }

  // 警告样式
  .alert {
    display: flex;
    gap: 14px;
    padding: 16px 20px;
    border-radius: 4px;
    margin-top: 20px;

    > i {
      font-size: 22px;
      flex-shrink: 0;
      color: var(--prev-color-danger);
    }

    &.alert-error {
      background: var(--el-color-danger-light-9);
      border: 1px solid var(--el-color-danger-light-7);
    }

    .alert-content {
      strong {
        display: block;
        font-size: 14px;
        color: var(--prev-color-text-regular);
        margin-bottom: 6px;
      }

      p {
        font-size: 13px;
        color: var(--prev-color-text-primary);
        margin: 0 0 4px;
        line-height: 1.5;

        code {
          background: var(--prev-color-hover);
          padding: 2px 6px;
          border-radius: 4px;
          font-family: 'SF Mono', Monaco, monospace;
          font-size: 12px;
        }
      }

      .alert-hint {
        color: var(--prev-color-text-secondary);
        font-size: 12px;
      }
    }
  }

  // 操作按钮区域
  .action-buttons {
    display: flex;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--prev-border-color-lighter);
  }

  // 配置卡片
  .config-card {
    .config-grid {
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    .config-item {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 20px 0;
      border-bottom: 1px solid var(--prev-border-color-lighter);

      &:first-child {
        padding-top: 0;
      }

      &:last-child {
        border-bottom: none;
        padding-bottom: 0;
      }

      .config-icon {
        width: 40px;
        height: 40px;
        background: var(--prev-color-primary-light-9);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        i {
          font-size: 18px;
          color: var(--prev-color-primary);
        }
      }

      .config-info {
        flex: 1;
        min-width: 0;

        .config-label {
          display: block;
          font-size: 12px;
          color: var(--prev-color-text-secondary);
          margin-bottom: 6px;
        }

        .config-value {
          display: flex;
          align-items: center;
          gap: 10px;
          flex-wrap: wrap;

          .value-text {
            font-size: 14px;
            font-weight: 500;
            color: var(--prev-color-text-regular);

            &.text-muted {
              color: var(--prev-color-text-secondary);
            }
          }

          .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;

            &.success {
              background: var(--prev-color-primary);
              box-shadow: 0 0 0 3px var(--prev-color-primary-light-9);
            }
          }

          .config-link {
            font-size: 13px;
            color: var(--prev-color-primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;

            &:hover {
              text-decoration: underline;
            }
          }
        }
      }

      .config-hint {
        font-size: 12px;
        color: var(--prev-color-text-secondary);
        margin-top: 6px;

        a {
          color: var(--prev-color-primary);
          text-decoration: none;

          &:hover {
            text-decoration: underline;
          }
        }
      }
    }
  }

  // 上传卡片
  .upload-card {
    .info-banner {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: var(--prev-color-primary-light-9);
      border-radius: 4px;
      margin-bottom: 20px;

      i {
        font-size: 16px;
        color: var(--prev-color-primary);
      }

      span {
        font-size: 13px;
        color: var(--prev-color-primary-dark-2);
      }
    }

    .upload-form {
      .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;

        @media (max-width: 768px) {
          grid-template-columns: 1fr;
        }
      }

      .form-item-half {
        margin-bottom: 0;
      }

      :deep(.el-form-item__label) {
        font-weight: 500;
        color: var(--prev-color-text-regular);
        padding-bottom: 8px;
      }

      .form-actions {
        display: flex;
        margin-top: 8px;
      }
    }
  }

  // 预览区域
  .preview-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--prev-border-color-lighter);

    .preview-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      background: var(--prev-bg-color);
      border-radius: 4px;
      text-align: center;

      img {
        width: 180px;
        height: 180px;
        border-radius: 4px;
        margin-bottom: 12px;
      }

      p {
        font-size: 13px;
        color: var(--prev-color-text-secondary);
        margin: 0;
      }
    }
  }

  // 结果区域
  .result-section {
    margin-top: 20px;

    .result-card {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 20px;
      border-radius: 4px;

      &.success {
        background: var(--prev-color-primary-light-9);
        border: 1px solid var(--prev-color-primary-light-7);

        .result-icon {
          background: var(--prev-color-primary);
        }
      }

      &.error {
        background: var(--el-color-danger-light-9);
        border: 1px solid var(--el-color-danger-light-7);

        .result-icon {
          background: var(--prev-color-danger);
        }
      }

      .result-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        i {
          font-size: 20px;
          color: var(--prev-color-text-white);
        }
      }

      .result-content {
        h4 {
          font-size: 14px;
          font-weight: 500;
          color: var(--prev-color-text-regular);
          margin: 0 0 6px;
        }

        p {
          font-size: 13px;
          color: var(--prev-color-text-primary);
          margin: 0 0 4px;
          line-height: 1.5;

          a {
            color: var(--prev-color-primary);
            text-decoration: none;
            font-weight: 500;

            &:hover {
              text-decoration: underline;
            }
          }

          &.error-msg {
            color: var(--prev-color-danger);
          }
        }
      }
    }
  }
}

// 行内文字操作（存量 <a> 写法，主题色由全局 a 链接提供）
.link-btn {
  font-size: 13px;
  cursor: pointer;
}

// ========================================
// 弹窗内内容（弹窗容器样式走全局定制，不再覆盖）
// ========================================
.install-guide {
  .guide-section {
    padding: 24px;
    background: var(--prev-bg-color);
    border-radius: 6px;
    margin-bottom: 0;
    border: 1px solid var(--prev-border-color-lighter);
    transition: all 0.2s ease;

    &.recommended {
      background: var(--prev-color-primary-light-9);
      border: 1px solid var(--prev-color-primary-light-7);
      position: relative;
      overflow: visible;

      &:hover {
        border-color: var(--prev-color-primary-light-5);
      }

      .section-badge {
        position: absolute;
        top: -12px;
        left: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: var(--prev-color-primary);
        color: var(--prev-color-text-white);
        font-size: 12px;
        font-weight: 500;
        border-radius: 50px;

        i {
          font-size: 14px;
        }
      }

      .header-icon {
        background: var(--prev-color-primary);

        i {
          color: var(--prev-color-text-white);
        }
      }
    }

    &.manual {
      background: var(--prev-bg-white);
      border: 1px solid var(--prev-border-color-lighter);

      &:hover {
        border-color: var(--prev-border-color-base);
      }

      .manual-icon {
        background: var(--prev-color-text-secondary);

        i {
          color: var(--prev-color-text-white);
        }
      }
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
      padding-top: 8px;

      .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        i {
          font-size: 24px;
        }
      }

      .header-text {
        flex: 1;

        h3 {
          font-size: 14px;
          font-weight: 500;
          color: var(--prev-color-text-regular);
          margin: 0 0 4px;
        }

        p {
          font-size: 13px;
          color: var(--prev-color-text-secondary);
          margin: 0;
        }
      }
    }

    .install-instruction {
      margin-bottom: 12px;

      .instruction-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--prev-color-text-primary);
      }
    }

    .section-footer {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px dashed var(--prev-color-primary-light-7);
      font-size: 13px;
      color: var(--prev-color-text-secondary);

      i {
        font-size: 14px;
        color: var(--prev-color-text-secondary);
      }

      a {
        color: var(--prev-color-primary);
        font-weight: 500;
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
          text-decoration: underline;
        }
      }
    }
  }

  .code-block {
    display: flex;
    align-items: center;
    gap: 0;
    background: var(--prev-bg-menuBar);
    border-radius: 4px;
    padding: 0;
    overflow: hidden;

    .code-content {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 16px;
      overflow-x: auto;

      .code-prompt {
        font-family: 'SF Mono', Monaco, 'Consolas', monospace;
        font-size: 14px;
        color: var(--prev-color-text-placeholder);
        user-select: none;
      }

      code {
        font-family: 'SF Mono', Monaco, 'Consolas', monospace;
        font-size: 14px;
        color: var(--prev-color-success);
        word-break: break-all;
        line-height: 1.5;
      }
    }

    .copy-btn {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 14px 16px;
      background: rgba(255, 255, 255, 0.05);
      border: none;
      border-left: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--prev-color-text-secondary);
      cursor: pointer;
      transition: all 0.15s ease;
      height: 100%;

      i {
        font-size: 16px;
      }

      .copy-text {
        font-size: 12px;
        font-weight: 500;
      }

      &:hover {
        background: color-mix(in srgb, var(--prev-color-primary) 20%, transparent);
        color: var(--prev-color-text-white);

        i {
          color: var(--prev-color-primary-light-3);
        }
      }
    }
  }

  .divider-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 28px 0;
    padding: 0 10px;

    .divider-line {
      flex: 1;
      height: 1px;
      background: var(--prev-border-color-light);
    }

    .divider-text {
      font-size: 13px;
      font-weight: 500;
      color: var(--prev-color-text-secondary);
      white-space: nowrap;
      padding: 5px 14px;
      background: var(--prev-bg-white);
      border: 1px solid var(--prev-border-color-lighter);
      border-radius: 50px;
    }
  }

  .guide-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 8px;

    .step-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 16px;
      background: var(--prev-bg-color);
      border: 1px solid var(--prev-border-color-lighter);
      border-radius: 4px;
      transition: all 0.15s ease;

      &:hover {
        background: var(--prev-bg-white);
        border-color: var(--prev-border-color-base);

        .step-copy-btn {
          opacity: 1;
        }
      }

      .step-number {
        width: 28px;
        height: 28px;
        min-width: 28px;
        background: var(--prev-color-primary-light-9);
        color: var(--prev-color-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 500;
      }

      .step-content {
        flex: 1;
        overflow-x: auto;

        code {
          font-family: 'SF Mono', Monaco, 'Consolas', monospace;
          font-size: 13px;
          color: var(--prev-color-text-regular);
          line-height: 1.5;
          white-space: pre-wrap;
          word-break: break-all;
        }
      }

      .step-copy-btn {
        width: 32px;
        height: 32px;
        min-width: 32px;
        background: transparent;
        border: 1px solid var(--prev-border-color-light);
        border-radius: 4px;
        color: var(--prev-color-text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.15s ease;

        i {
          font-size: 14px;
        }

        &:hover {
          background: var(--prev-color-primary-light-9);
          border-color: var(--prev-color-primary);
          color: var(--prev-color-primary);
        }
      }
    }
  }
}

// 密钥上传弹窗
.key-upload-content {
  .info-card {
    padding: 14px 16px;
    background: var(--prev-color-primary-light-9);
    border-radius: 4px;
    margin-bottom: 16px;

    &.warning {
      background: var(--el-color-warning-light-9);

      .info-card-header i {
        color: var(--prev-color-warning);
      }
    }

    .info-card-header {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      color: var(--prev-color-text-regular);
      margin-bottom: 10px;

      i {
        font-size: 18px;
        color: var(--prev-color-primary);
      }
    }

    ol.info-steps {
      margin: 0;
      padding-left: 20px;

      li {
        font-size: 13px;
        color: var(--prev-color-text-primary);
        line-height: 1.8;

        a {
          color: var(--prev-color-primary);
          text-decoration: none;

          &:hover {
            text-decoration: underline;
          }
        }
      }
    }

    p {
      font-size: 13px;
      color: var(--prev-color-text-primary);
      margin: 0;
    }
  }

  .key-input-section {
    margin-top: 20px;

    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: var(--prev-color-text-regular);
      margin-bottom: 8px;
    }

    :deep(.el-textarea__inner) {
      font-family: 'SF Mono', Monaco, monospace;
      font-size: 13px;
    }
  }
}

// ========================================
// 动画
// ========================================
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

:deep(.defineSwitch.el-switch .el-switch__core),
:deep(.defineSwitch.el-switch .el-switch__label) {
  width: 65px !important;
}

:deep(.el-switch__label) {
  height: 21px !important;
}
</style>
