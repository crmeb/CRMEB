<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="formValidate"
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
          <el-button type="primary" v-db-click @click="handleSubmit('formValidate')">提交</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog :visible.sync="avatarMoadl" title="头像上传" width="720px">
      <CropperImg v-if="avatarMoadl" @uploadImgSuccess="uploadImgSuccess"></CropperImg>
    </el-dialog>
  </div>
</template>

<script>
import { updtaeAdmin } from '@/api/user';
import { mapState } from 'vuex';
import CropperImg from '@/components/cropperImg';
export default {
  name: 'setting_user',
  components: { CropperImg },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  data() {
    return {
      account: '',
      avatarMoadl: false,
      formValidate: {
        avatar: '',
        real_name: '',
        pwd: '',
        new_pwd: '',
        conf_pwd: '',
      },
      ruleValidate: {
        real_name: [{ required: true, message: '您的姓名不能为空', trigger: 'blur' }],
      },
    };
  },
  mounted() {
    this.account = this.$store.state.userInfo.userInfo.account;
    this.formValidate.head_pic = this.$store.state.userInfo.userInfo.head_pic;
    this.formValidate.real_name = this.$store.state.userInfo.userInfo.real_name;
  },
  methods: {
    uploadImgSuccess(data) {
      this.avatarMoadl = false;
      this.formValidate.head_pic = data.src;
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          updtaeAdmin(this.formValidate)
            .then((res) => {
              this.$store.commit('userInfo/userRealName', this.formValidate.real_name);
              this.$store.commit('userInfo/userRealHeadPic', this.formValidate.head_pic);
              this.$message.success(res.msg);
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          if (this.formValidate.new_pwd !== this.formValidate.conf_pwd) {
            this.$message.error('您输入的新密码与旧密码不一致');
          }
        }
      });
    },
  },
};
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
