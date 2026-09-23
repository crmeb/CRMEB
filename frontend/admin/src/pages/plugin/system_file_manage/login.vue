<template>
  <div>
    <el-row>
      <el-col :span="24">
        <div class="index_from page-account-container">
          <div class="page-account-top">
            <span class="page-account-top-tit">文件管理登录</span>
          </div>
          <el-form ref="formInlineRef" :model="formInline" :rules="ruleInline" @submit.prevent>
            <!-- <el-form-item prop="sms_account" class="maxInpt">
            <el-input type="text" v-model="formInline.account" prefix="ios-contact-outline" placeholder="请输入手机号" />
          </el-form-item> -->
            <el-form-item prop="sms_token" class="maxInpt">
              <el-input
                type="password"
                size="large"
                v-model="formInline.password"
                prefix="ios-lock-outline"
                placeholder="请输入密码"
              />
              <div class="trip">提示：密码配置在 /config/filesystem.php 文件中修改 'password' => '密码'</div>
            </el-form-item>
            <el-form-item class="maxInpt">
              <el-button type="primary" long size="large" v-db-click @click="handleSubmit" class="btn">登录</el-button>
            </el-form-item>
          </el-form>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { opendirLoginApi } from './api';
import { setCookies } from '@/libs/util';
import setting from '@/setting';

defineOptions({ name: 'file_login' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;

const formInlineRef = ref(null);
const formInline = ref({
  // account: '',
  password: '',
});
const ruleInline = {
  // account: [{ required: true, validator: validatePhone, trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
};

onMounted(() => {
  document.onkeydown = function (e) {
    let key = window.event.keyCode;
    if (key === 13) {
      handleSubmit();
    }
  };
});

onBeforeUnmount(() => {
  document.onkeydown = null;
});

function handleSubmit() {
  formInlineRef.value.validate((valid) => {
    if (valid) {
      opendirLoginApi(formInline.value)
        .then(async (res) => {
          ElMessage.success('登录成功!');
          //   emit('on-Login', res.data);
          let expires = getExpiresTime(res.data.expires_time);
          // 记录用户登录信息
          setCookies('file_token', res.data.token, expires);
          router.push({
            path: routePre + '/system/maintain/system_file/opendir',
          });
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
//计算token过期时间
function getExpiresTime(expiresTime) {
  let nowTimeNum = Math.round(new Date() / 1000);
  let expiresTimeNum = expiresTime - nowTimeNum;
  return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
}
</script>

<style lang="scss" scoped>
.maxInpt {
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}
.index_from {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.trip {
  width: 580px;
  text-align: left;
  color: #aaa;
}
.page-account-container {
  text-align: center;
  padding: 200px 0;
}
.page-account-top {
  margin-bottom: 50px;
}
.page-account-top-tit {
  font-size: 30px;
  color: var(--prev-color-primary);
  font-weight: 500;
}
.page-account-other {
  text-align: center;
  color: var(--prev-color-primary);
  font-size: 12px;
  span {
    cursor: pointe;
  }
}
:deep(.btn) {
  font-size: 15px !important;
}
</style>
