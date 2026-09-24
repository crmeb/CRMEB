<template>
  <div class="article-manager">
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">一号通账户</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          配置一号通平台凭证，短信、物流查询等增值服务依赖该账户，可一键登录自动回填。
        </span>
      </div>
    </div>
    <el-card shadow="never" class="ivu-mt">
      <el-form :model="form" :label-width="labelWidth" :label-position="labelPosition" v-loading="loading">
        <el-form-item label="AppId：">
          <el-input v-model="form.yihaotong_appid" placeholder="请输入一号通账号 AppId" class="input-width" />
          <div class="tip">一号通平台账号，建议通过「点击登录一号通」自动回填</div>
        </el-form-item>
        <el-form-item label="AppSecret：">
          <el-input
            v-model="form.yihaotong_appsecret"
            type="password"
            show-password
            autocomplete="new-password"
            placeholder="请输入一号通 AppSecret"
            class="input-width"
          />
          <div class="tip">一号通平台密钥，建议通过「点击登录一号通」自动回填</div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="saveLoading" v-db-click @click="onSave">保存配置</el-button>
          <el-button v-db-click @click="openLoginDialog">点击登录一号通</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog v-model="loginVisible" title="登录一号通" width="1400px" top="6vh" destroy-on-close>
      <iframe :src="YIHAOTONG_LOGIN_URL" class="login-frame" frameborder="0"></iframe>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { getSystemConfigApi, saveBasicsApi } from '@/api/setting';

defineOptions({ name: 'yihaotong' });

// 一号通平台登录页，iframe 内登录成功后会 postMessage 出 AppId/AppSecret
const YIHAOTONG_LOGIN_URL = 'https://api.crmeb.com?token=AF37D4579721672220B08CA872586943';
// 仅接受一号通官方域名（含子域名）回传的凭证消息
const YIHAOTONG_ORIGIN = /^https:\/\/([\w-]+\.)*crmeb\.com$/;

const mediaStore = useMediaStore();
const labelWidth = computed(() => (mediaStore.isMobile ? undefined : '140px'));
const labelPosition = computed(() => (mediaStore.isMobile ? 'top' : 'right'));

const loading = ref(false);
const saveLoading = ref(false); // 手动保存中
const yihaotongSaving = ref(false); // iframe 登录凭证自动保存中（防重复提交）
const loginVisible = ref(false);

const form = reactive({
  yihaotong_appid: '',
  yihaotong_appsecret: '',
});

onMounted(() => {
  getConfig();
  window.addEventListener('message', handleLoginMessage);
});

onBeforeUnmount(() => {
  window.removeEventListener('message', handleLoginMessage);
});

// 读取已保存的一号通配置用于回显
function getConfig() {
  loading.value = true;
  Promise.all([getSystemConfigApi('yihaotong_appid'), getSystemConfigApi('yihaotong_appsecret')])
    .then(([appidRes, secretRes]) => {
      form.yihaotong_appid = appidRes?.data?.value || '';
      form.yihaotong_appsecret = secretRes?.data?.value || '';
    })
    .catch(() => {
      ElMessage.error('一号通配置读取失败');
    })
    .finally(() => {
      loading.value = false;
    });
}

// 手动保存表单中的一号通配置（允许清空保存）
function onSave() {
  saveLoading.value = true;
  saveBasicsApi({
    yihaotong_appid: form.yihaotong_appid.trim(),
    yihaotong_appsecret: form.yihaotong_appsecret.trim(),
  })
    .then((res) => {
      ElMessage.success(res.msg || '保存成功');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '保存失败');
    })
    .finally(() => {
      saveLoading.value = false;
    });
}

// 打开一号通登录弹窗
function openLoginDialog() {
  loginVisible.value = true;
}

// 接收 iframe（api.crmeb.com）登录成功后 postMessage 传出的凭证并自动保存
// accessKey/secretKey 即一号通签发的 AppId/AppSecret，写入本系统配置 yihaotong_appid/yihaotong_appsecret
function handleLoginMessage(event) {
  if (!YIHAOTONG_ORIGIN.test(event.origin)) return;
  const payload = event.data;
  if (!payload || !payload.accessKey || !payload.secretKey) return;
  if (yihaotongSaving.value) return;
  yihaotongSaving.value = true;
  // 只传两个配置键；save_basics 对 sms_save_type 有「appid 已配置则直接返回不落库」的旧逻辑，带上会导致重复登录时不更新
  saveBasicsApi({
    yihaotong_appid: payload.accessKey,
    yihaotong_appsecret: payload.secretKey,
  })
    .then((res) => {
      form.yihaotong_appid = payload.accessKey;
      form.yihaotong_appsecret = payload.secretKey;
      loginVisible.value = false;
      ElMessage.success(res.msg || '登录成功，一号通配置已自动保存');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '一号通配置自动保存失败');
    })
    .finally(() => {
      yihaotongSaving.value = false;
    });
}
</script>

<style lang="scss" scoped>
.tip {
  font-size: 12px;
  color: #999;
  line-height: 20px;
  // 独占一行，使说明显示在控件下方
  flex-basis: 100%;
  width: 100%;
}
.input-width {
  width: 300px;
}
.login-frame {
  width: 100%;
  height: 70vh;
  border: 0;
  display: block;
}
</style>
