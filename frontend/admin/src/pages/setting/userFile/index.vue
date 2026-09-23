<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="formValidateRef"
        :model="formValidate"
        :rules="ruleValidate"
        label-width="160px"
        label-position="right"
      >
        <el-form-item label="账号：" prop="">
          <el-input type="text" v-model="account" :disabled="true" class="input"></el-input>
        </el-form-item>
        <el-form-item label="文件管理新密码：" prop="file_pwd">
          <el-input type="password" v-model="formValidate.file_pwd" class="input"></el-input>
        </el-form-item>
        <el-form-item label="文件管理确认新密码：" prop="conf_file_pwd">
          <el-input type="password" v-model="formValidate.conf_file_pwd" class="input"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="handleSubmit()">提交</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { setFilePassword } from '@/api/user';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'setting_files' });

const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();
const userInfoStore = useUserInfoStore();

const formValidateRef = ref(null);

const account = ref('');
const formValidate = reactive({
  file_pwd: '',
  conf_file_pwd: '',
});
const ruleValidate = {
  file_pwd: [{ required: true, message: '请输入您的文件管理新密码', trigger: 'blur' }],
  conf_file_pwd: [{ required: true, message: '请确认您的文件管理新密码', trigger: 'blur' }],
};

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

onMounted(() => {
  account.value = userInfoStore.userInfo.account;
});

function handleSubmit() {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      setFilePassword(formValidate)
        .then((res) => {
          ElMessage.success(res.msg);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    }
  });
}
</script>

<style scoped>
.input {
  width: 400px;
}
</style>
