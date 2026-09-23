<template>
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
              v-model="formInline.password"
              prefix="ios-lock-outline"
              placeholder="请输入密码"
            />
          </el-form-item>
          <el-form-item class="maxInpt">
            <el-button type="primary" long size="large" v-db-click @click="handleSubmit" class="btn">登录</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-col>
  </el-row>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { opendirLoginApi } from '../api';

defineOptions({ name: 'file_login' });

const emit = defineEmits(['on-Login']);

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
          emit('on-Login', res.data);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
</script>

<style lang="scss" scoped>
.maxInpt {
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}
.page-account-container {
  text-align: center;
  padding: 50px 0;
}
.page-account-top {
  margin-bottom: 20px;
}
.page-account-top-tit {
  font-size: 21px;
  color: var(--prev-color-primary);
}
.page-account-other {
  text-align: center;
  color: var(--prev-color-primary);
  font-size: 12px;
  span {
    cursor: pointer;
  }
}
</style>
