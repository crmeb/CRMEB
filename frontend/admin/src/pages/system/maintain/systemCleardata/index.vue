<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          危险操作集中区：仅超级管理员在开发模式 (APP_DEBUG = true) 下可用，执行前须验证登录密码，删除后不可恢复。
        </span>
      </div>
      <span class="clear_tit">
        <i class="el-icon-info" style="color: #ed4014" />
        <span>清除数据请谨慎，清除就无法恢复哦！</span>
      </span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row :gutter="24">
        <el-col v-bind="grid" class="mb20" v-for="(item, index) in tabList" :key="index">
          <div class="clear_box">
            <span class="clear_box_sp1" v-text="item.title"></span>
            <span class="clear_box_sp2" v-text="item.tlt"></span>
            <el-button
              :type="item.typeName"
              v-text="item.typeName === 'primary' ? '立即更换' : item.type === 'factory' ? '立即恢复' : '立即清理'"
              v-db-click
              @click="openDangerDialog(item)"
            ></el-button>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <el-dialog
      v-model="danger.visible"
      class="cleardata-factory-dialog"
      width="640px"
      align-center
      :close-on-click-modal="false"
      :show-close="!danger.submitting"
      @closed="resetDangerForm"
    >
      <template #header>
        <div class="factory-dialog-header">
          <div class="factory-dialog-icon" :class="{ 'is-primary': danger.type === 'domain' }">
            <el-icon :size="22">
              <WarningFilled v-if="danger.type !== 'domain'" />
              <Link v-else />
            </el-icon>
          </div>
          <div class="factory-dialog-titles">
            <div class="factory-dialog-title">{{ danger.title }}</div>
            <div class="factory-dialog-sub">
              {{ danger.type === 'domain' ? '批量替换 · 请先备份 · 替换后请核对配置' : '高危操作 · 不可撤销 · 请先备份数据库' }}
            </div>
          </div>
        </div>
      </template>

      <div class="factory-dialog-body">
        <div class="factory-impact">
          <div class="factory-impact-card is-danger">
            <div class="factory-impact-label">
              <el-icon><Delete /></el-icon>
              <span>{{ danger.type === 'domain' ? '将会替换' : '将被清空 / 处理' }}</span>
            </div>
            <ul>
              <li v-for="(line, idx) in danger.dangerItems" :key="'d-' + idx">{{ line }}</li>
            </ul>
          </div>
          <div class="factory-impact-card is-keep">
            <div class="factory-impact-label">
              <el-icon><CircleCheckFilled /></el-icon>
              <span>将会保留</span>
            </div>
            <ul>
              <li v-for="(line, idx) in danger.keepItems" :key="'k-' + idx">{{ line }}</li>
            </ul>
          </div>
        </div>

        <div v-if="danger.steps.length" class="factory-steps">
          <template v-for="(step, idx) in danger.steps" :key="'s-' + idx">
            <div class="factory-step"><span>{{ idx + 1 }}</span>{{ step }}</div>
            <div v-if="idx < danger.steps.length - 1" class="factory-step-arrow">→</div>
          </template>
        </div>

        <el-form
          v-if="danger.type === 'domain'"
          ref="domainFormRef"
          class="domain-form"
          :model="domainForm"
          :rules="domainRules"
          label-width="120px"
          @submit.prevent
        >
          <el-form-item label="要被替换的域名" prop="old_url">
            <el-input
              v-model="domainForm.old_url"
              clearable
              placeholder="默认读取系统网站域名，可修改"
              :disabled="danger.submitting"
            />
          </el-form-item>
          <el-form-item label="新域名" prop="url">
            <el-input
              v-model="domainForm.url"
              clearable
              placeholder="请输入新域名，如 https://www.example.com"
              :disabled="danger.submitting"
            />
          </el-form-item>
          <p class="domain-tip">格式要求：以 http:// 或 https:// 开头，不要以 / 结尾。</p>
        </el-form>

        <div class="factory-gate">
          <div class="factory-gate-tip">
            仅超级管理员在开发模式 (APP_DEBUG = true) 下可执行。请输入当前账号
            <em>登录密码</em>
            以确认身份：
          </div>
          <el-form
            ref="dangerFormRef"
            :model="dangerForm"
            :rules="dangerRules"
            @submit.prevent
          >
            <el-form-item prop="pwd" class="factory-confirm-item">
              <el-input
                v-model="dangerForm.pwd"
                type="password"
                show-password
                clearable
                size="large"
                autocomplete="current-password"
                placeholder="请输入登录密码"
                :disabled="danger.submitting"
                @keyup.enter="submitDangerAction"
              >
                <template #prefix>
                  <el-icon class="factory-input-icon"><Lock /></el-icon>
                </template>
              </el-input>
            </el-form-item>
          </el-form>
        </div>
      </div>

      <template #footer>
        <div class="factory-dialog-footer">
          <el-button v-db-click :disabled="danger.submitting" @click="danger.visible = false">取 消</el-button>
          <el-button
            :type="danger.type === 'domain' ? 'primary' : 'danger'"
            :loading="danger.submitting"
            :disabled="!dangerPwdReady"
            v-db-click
            @click="submitDangerAction"
          >
            {{ submitButtonText }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { CircleCheckFilled, Delete, Link, Lock, WarningFilled } from '@element-plus/icons-vue';
import {
  clearDataApi,
  factoryResetApi,
  getSiteUrlApi,
  replaceSiteUrlApi,
} from '@/api/system';
import { removeCookies } from '@/libs/util';
import { useAppStore } from '@/store/module/app';

defineOptions({ name: 'systemCleardata' });

const router = useRouter();
const appStore = useAppStore();

const grid = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};

const actionMeta = {
  domain: {
    title: '更换域名',
    dangerItems: ['数据库中匹配到的图片/装修/配置等域名字符串', '原域名 → 新域名（含 JSON 转义形式）'],
    keepItems: ['表结构与业务数据本身', '未包含该域名的字段内容', '后台账号与权限'],
    steps: [],
  },
  temp: {
    title: '清除用户生成的临时附件',
    dangerItems: ['module_type=2 的临时附件记录与文件', '二维码表 qrcode 数据'],
    keepItems: ['商品图与后台上传的正式附件', '系统配置与业务主数据'],
    steps: [],
  },
  recycle: {
    title: '清除回收站商品',
    dangerItems: ['回收站中全部商品主记录', '关联描述/分类/优惠券/关系/评价', '各已注册产品类型扩展表数据'],
    keepItems: ['未进回收站的在售/仓库商品', '订单与用户数据'],
    steps: [],
  },
  user: {
    title: '清除用户数据',
    dangerItems: ['用户主档及账单/地址/分销等附属表', '订单/售后/支付等用户侧流水', '客服会话与用户生成内容'],
    keepItems: ['商品与活动主数据', '系统配置、装修、支付/通知通道配置'],
    steps: [],
  },
  store: {
    title: '清除商城数据',
    dangerItems: ['用户、订单、商品、优惠券、活动', '内容、客服、门店、微信用户侧数据', '产品类型扩展表（SKU/库存/订单）'],
    keepItems: ['后台超管/角色/菜单', '系统配置、装修主题、支付与通知配置'],
    steps: [],
  },
  category: {
    title: '清除商品分类',
    dangerItems: ['全部商品分类 store_category'],
    keepItems: ['商品主数据与其它业务表', '系统配置与后台账号'],
    steps: [],
  },
  order: {
    title: '清除订单数据',
    dangerItems: ['订单中心及明细/日志/发票', '售后、退款、支付流水、购物车', '各产品类型扩展订单与库存占用'],
    keepItems: ['用户主档与商品主数据', '系统配置与后台账号'],
    steps: [],
  },
  kefu: {
    title: '清除客服数据',
    dangerItems: ['客服账号、会话、话术、反馈', 'AI 客服会话与回复日志', '本地客服上传目录'],
    keepItems: ['用户与订单主数据', '系统配置与后台账号'],
    steps: [],
  },
  wechat: {
    title: '清除微信数据',
    dangerItems: ['微信关键字/回复/素材/菜单相关表', '渠道码及扫码记录', '微信用户与本地 wechat 上传目录'],
    keepItems: ['商城用户主档（user 表）', '系统配置与其它业务数据'],
    steps: [],
  },
  article: {
    title: '清除内容分类',
    dangerItems: ['文章、文章分类、文章内容'],
    keepItems: ['商品、订单、用户与系统配置'],
    steps: [],
  },
  attachment: {
    title: '清除所有附件',
    dangerItems: ['全部附件记录与分类', 'public/uploads 下本地文件'],
    keepItems: ['业务表中的数据记录（不含附件文件本身）', '系统配置与后台账号'],
    steps: [],
  },
  system: {
    title: '清除系统记录',
    dangerItems: ['系统日志、通知已读/日志', '升级/模块安装日志、短信与面单日志、缓存表'],
    keepItems: ['业务主数据与系统配置', '后台账号与权限'],
    steps: [],
  },
  factory: {
    title: '恢复出厂设置',
    dangerItems: ['当前前缀下全部业务表', '用户、订单、商品、插件业务数据', '按安装包 SQL 重建结构并回灌种子'],
    keepItems: ['当前超级管理员账号（写回 id=1）', '原系统配置（system_config 覆盖回去）', '安装包默认插件与驱动'],
    steps: ['删除旧表', '重建结构', '回灌种子', '装插件并写回'],
  },
};

const tabList = [
  { title: '更换域名', tlt: '替换所有本地上传的图片域名', typeName: 'primary', type: 'domain' },
  { title: '清除用户生成的临时附件', tlt: '清除用户生成的临时附件，不会影响商品图', typeName: 'error', type: 'temp' },
  { title: '清除回收站商品', tlt: '清除回收站商品，谨慎操作', typeName: 'error', type: 'recycle' },
  { title: '清除用户数据', tlt: '用户相关的所有表都将被清除，谨慎操作', typeName: 'error', type: 'user' },
  { title: '清除商城数据', tlt: '清除所有商城数据，谨慎操作', typeName: 'error', type: 'store' },
  { title: '清除商品分类', tlt: '会清除所有商品分类，谨慎操作', typeName: 'error', type: 'category' },
  { title: '清除订单数据', tlt: '清除用户所有订单数据，谨慎操作', typeName: 'error', type: 'order' },
  { title: '清除客服数据', tlt: '清除添加的客服数据，谨慎操作', typeName: 'error', type: 'kefu' },
  { title: '清除微信数据', tlt: '清除微信菜单保存数据，微信关键字无效回复', typeName: 'error', type: 'wechat' },
  { title: '清除内容分类', tlt: '清除添加的文章和文章分类,谨慎操作', typeName: 'error', type: 'article' },
  { title: '清除所有附件', tlt: '清除所有附件用户生成和后台上传,谨慎操作', typeName: 'error', type: 'attachment' },
  { title: '清除系统记录', tlt: '清除系统记录,谨慎操作', typeName: 'error', type: 'system' },
  { title: '恢复出厂设置', tlt: '按安装包重建库表并回灌种子，仅保留超管与配置', typeName: 'error', type: 'factory' },
];

const danger = reactive({
  visible: false,
  submitting: false,
  type: '',
  title: '',
  dangerItems: [],
  keepItems: [],
  steps: [],
});
const dangerFormRef = ref(null);
const dangerForm = reactive({ pwd: '' });
const dangerPwdReady = computed(() => dangerForm.pwd.length > 0);
const dangerRules = {
  pwd: [{ required: true, message: '请输入登录密码', trigger: 'blur' }],
};

const domainFormRef = ref(null);
const domainForm = reactive({
  old_url: '',
  url: '',
});
const domainUrlPattern =
  /^(?=^.{3,255}$)(http(s)?:\/\/)(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*$/;
const domainRules = {
  old_url: [
    { required: true, message: '请输入要被替换的域名', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        if (!value) return callback();
        if (!domainUrlPattern.test(value)) {
          callback(new Error('格式为：http://或https://域名'));
        } else {
          callback();
        }
      },
      trigger: 'blur',
    },
  ],
  url: [
    { required: true, message: '请输入新域名', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        if (!value) return callback();
        if (!domainUrlPattern.test(value)) {
          callback(new Error('格式为：http://或https://域名'));
        } else if (value === domainForm.old_url) {
          callback(new Error('新域名不能与要被替换的域名相同'));
        } else {
          callback();
        }
      },
      trigger: 'blur',
    },
  ],
};

const submitButtonText = computed(() => {
  if (danger.submitting) {
    if (danger.type === 'factory') return '正在恢复出厂…';
    if (danger.type === 'domain') return '正在替换…';
    return '正在清理…';
  }
  if (danger.type === 'factory') return '确认恢复出厂';
  if (danger.type === 'domain') return '确认更换域名';
  return '确认清理';
});

function resetDangerForm() {
  dangerForm.pwd = '';
  dangerFormRef.value?.clearValidate();
  domainForm.old_url = '';
  domainForm.url = '';
  domainFormRef.value?.clearValidate();
  danger.type = '';
  danger.title = '';
  danger.dangerItems = [];
  danger.keepItems = [];
  danger.steps = [];
}

async function openDangerDialog(item) {
  const meta = actionMeta[item.type];
  if (!meta) return;
  danger.type = item.type;
  danger.title = meta.title;
  danger.dangerItems = meta.dangerItems;
  danger.keepItems = meta.keepItems;
  danger.steps = meta.steps;
  danger.visible = true;
  if (item.type === 'domain') {
    try {
      const res = await getSiteUrlApi();
      domainForm.old_url = res.data?.site_url || '';
    } catch (err) {
      ElMessage.error(err.msg || '获取网站域名失败');
    }
  }
}

async function submitDangerAction() {
  if (danger.submitting) return;
  if (danger.type === 'domain') {
    const domainValid = await domainFormRef.value?.validate().catch(() => false);
    if (!domainValid) return;
  }
  const pwdValid = await dangerFormRef.value?.validate().catch(() => false);
  if (!pwdValid) return;

  danger.submitting = true;
  try {
    let res;
    if (danger.type === 'domain') {
      res = await replaceSiteUrlApi({
        old_url: domainForm.old_url.trim(),
        url: domainForm.url.trim(),
        pwd: dangerForm.pwd,
      });
    } else if (danger.type === 'factory') {
      res = await factoryResetApi({ pwd: dangerForm.pwd });
      danger.visible = false;
      ElMessage.success(res.msg || '恢复出厂成功，请重新登录');
      forceRelogin();
      return;
    } else {
      res = await clearDataApi(danger.type, { pwd: dangerForm.pwd });
    }
    danger.visible = false;
    ElMessage.success(res.msg || '操作成功');
  } catch (err) {
    ElMessage.error(err.msg || '操作失败');
  } finally {
    danger.submitting = false;
  }
}

/** 恢复出厂后服务端已清缓存，前端同步清登录态并跳转登录页 */
function forceRelogin() {
  appStore.clearAll();
  localStorage.clear();
  removeCookies('token');
  removeCookies('expires_time');
  removeCookies('uuid');
  router.replace({ name: 'login' }).catch(() => {
    window.location.replace(router.resolve({ name: 'login' }).href);
  });
}
</script>

<style lang="scss" scoped>
.clear_tit {
  align-items: center;
  margin: 15px;
  span {
    font-size: 14px;
    color: #ed4014;
  }
}
.clear_box {
  border: 1px solid var(--prev-border-color-light);
  border-radius: 3px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 10px;
  box-sizing: border-box;
  .clear_box_sp1 {
    font-size: 16px;
    color: var(--prev-color-text-regular);
    display: block;
  }
  .clear_box_sp2 {
    font-size: 14px;
    color: var(--prev-color-text-secondary);
    display: block;
    margin: 12px 0;
  }
}
.domain-form {
  margin: 16px 0 0;
}
.domain-tip {
  margin: 0 0 12px 120px;
  font-size: 12px;
  line-height: 1.5;
  color: var(--prev-color-text-secondary);
}

.factory-dialog-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-right: 24px;
}
.factory-dialog-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c45656;
  background: linear-gradient(180deg, #fef0f0 0%, #fde2e2 100%);
  flex-shrink: 0;
  &.is-primary {
    color: #409eff;
    background: linear-gradient(180deg, #ecf5ff 0%, #d9ecff 100%);
  }
}
.factory-dialog-titles {
  min-width: 0;
}
.factory-dialog-title {
  font-size: 16px;
  font-weight: 600;
  line-height: 1.4;
  color: var(--prev-color-text-primary, #303133);
}
.factory-dialog-sub {
  margin-top: 2px;
  font-size: 12px;
  line-height: 1.4;
  color: #c45656;
}
.factory-dialog-body {
  padding-top: 2px;
}
.factory-impact {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.factory-impact-card {
  border-radius: 8px;
  padding: 14px 14px 10px;
  border: 1px solid transparent;
}
.factory-impact-card.is-danger {
  background: #fff7f7;
  border-color: #fbc4c4;
}
.factory-impact-card.is-keep {
  background: #f0f9eb;
  border-color: #c2e7b0;
}
.factory-impact-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
}
.factory-impact-card.is-danger .factory-impact-label {
  color: #c45656;
}
.factory-impact-card.is-keep .factory-impact-label {
  color: #67c23a;
}
.factory-impact-card ul {
  margin: 0;
  padding: 0 0 0 16px;
  color: var(--prev-color-text-regular, #606266);
  font-size: 12px;
  line-height: 1.7;
}
.factory-steps {
  display: flex;
  align-items: center;
  width: 100%;
  margin: 16px 0;
  padding: 12px 14px;
  border-radius: 8px;
  background: #f5f7fa;
  box-sizing: border-box;
}
.factory-step {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-width: 0;
  font-size: 12px;
  white-space: nowrap;
  color: var(--prev-color-text-regular, #606266);
  span {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #303133;
    color: #fff;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
}
.factory-step-arrow {
  flex: 0 0 auto;
  color: #c0c4cc;
  font-size: 12px;
  line-height: 1;
  padding: 0 2px;
}
.factory-gate {
  padding: 14px;
  border-radius: 8px;
  border: 1px dashed #f56c6c;
  background: linear-gradient(180deg, #fffafa 0%, #fff 100%);
  margin-top: 12px;
}
.factory-gate-tip {
  margin-bottom: 12px;
  font-size: 13px;
  line-height: 1.6;
  color: var(--prev-color-text-regular, #606266);
  em {
    font-style: normal;
    font-weight: 600;
    color: #f56c6c;
    padding: 0 2px;
  }
}
.factory-confirm-item {
  margin-bottom: 0;
}
.factory-input-icon {
  color: #c0c4cc;
}
.factory-dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  width: 100%;
}

@media (max-width: 640px) {
  .factory-impact {
    grid-template-columns: 1fr;
  }
  .factory-step {
    font-size: 11px;
  }
}
</style>

<style lang="scss">
.cleardata-factory-dialog.el-dialog {
  border-radius: 12px;
  overflow: hidden;
}
.cleardata-factory-dialog .el-dialog__header {
  margin-right: 0;
  padding: 18px 20px 12px;
  border-bottom: 1px solid #f0f2f5;
}
.cleardata-factory-dialog .el-dialog__body {
  padding: 16px 20px 8px;
}
.cleardata-factory-dialog .el-dialog__footer {
  padding: 12px 20px 18px;
  border-top: 1px solid #f0f2f5;
}
</style>
