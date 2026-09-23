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
        <el-form-item label="头像：">
          <div class="avatar" v-db-click @click="avatarMoadl = true">
            <img v-if="formValidate.head_pic" :src="formValidate.head_pic" alt="" />
            <img v-else src="../../../assets/images/f.png" alt="" />
          </div>
        </el-form-item>
        <el-form-item label="账号：" prop="">
          <el-input type="text" v-model="account" :disabled="true" class="input"></el-input>
        </el-form-item>
        <el-form-item label="姓名：" prop="real_name">
          <el-input type="text" v-model="formValidate.real_name" class="input"></el-input>
        </el-form-item>
        <el-form-item label="原始密码：">
          <el-input type="password" v-model="formValidate.pwd" class="input"></el-input>
        </el-form-item>
        <el-form-item label="新密码：">
          <el-input type="password" v-model="formValidate.new_pwd" class="input"></el-input>
        </el-form-item>
        <el-form-item label="确认新密码：">
          <el-input type="password" v-model="formValidate.conf_pwd" class="input"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="handleSubmit()">提交</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog v-model="avatarMoadl" title="头像上传" width="720px">
      <CropperImg v-if="avatarMoadl" @uploadImgSuccess="uploadImgSuccess"></CropperImg>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { updtaeAdmin } from '@/api/user';
import CropperImg from '@/components/cropperImg';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';

defineOptions({ name: 'setting_user' });

const userInfoStore = useUserInfoStore();
const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

const formValidateRef = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const account = ref('');
const avatarMoadl = ref(false);
const formValidate = reactive({
  avatar: '',
  real_name: '',
  pwd: '',
  new_pwd: '',
  conf_pwd: '',
  head_pic: '',
});
const ruleValidate = {
  real_name: [{ required: true, message: '您的姓名不能为空', trigger: 'blur' }],
};

onMounted(() => {
  account.value = userInfoStore.userInfo.account;
  formValidate.head_pic = userInfoStore.userInfo.head_pic;
  formValidate.real_name = userInfoStore.userInfo.real_name;
});

function uploadImgSuccess(data) {
  avatarMoadl.value = false;
  formValidate.head_pic = data.src;
}
function handleSubmit() {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      updtaeAdmin(formValidate)
        .then((res) => {
          userInfoStore.userRealName(formValidate.real_name);
          userInfoStore.userRealHeadPic(formValidate.head_pic);
          ElMessage.success(res.msg);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      if (formValidate.new_pwd !== formValidate.conf_pwd) {
        ElMessage.error('您输入的新密码与旧密码不一致');
      }
    }
  });
}
</script>

<style lang="scss" scoped>
.input {
  width: 400px;
}
.avatar {
  width: 80px;
  height: 80px;
  img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 1px solid #f2f2f2;
  }
}
</style>
