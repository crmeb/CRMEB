<template>
  <el-row>
    <el-col :span="24">
      <div class="index_from page-account-container">
        <div class="page-account-top">
          <span class="page-account-top-tit">一号通账户登录</span>
        </div>
        <el-form
          ref="formInline"
          :model="formInline"
          :rules="ruleInline"
          @submit.native.prevent
          @keyup.enter="handleSubmit('formInline')"
        >
          <el-form-item prop="sms_account" class="maxInpt">
            <el-input
              type="text"
              v-model="formInline.account"
              prefix="ios-contact-outline"
              placeholder="请输入手机号"
            />
          </el-form-item>
          <el-form-item prop="sms_token" class="maxInpt">
            <el-input
              type="password"
              v-model="formInline.password"
              prefix="ios-lock-outline"
              placeholder="请输入密码"
            />
          </el-form-item>
          <el-form-item class="maxInpt">
            <el-button type="primary" long size="large" v-db-click @click="handleSubmit('formInline')" class="btn"
              >登录</el-button
            >
          </el-form-item>
        </el-form>
        <div class="page-account-other">
          <span v-db-click @click="changePassword">忘记密码 |</span>
          <span v-db-click @click="changeReg"> 注册账户</span>
        </div>
      </div>
    </el-col>
  </el-row>
</template>

<script>
import { configApi } from '@/api/setting';
export default {
  name: 'login',
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写手机号'));
      } else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error('手机号格式不正确!'));
      } else {
        callback();
      }
    };
    return {
      formInline: {
        account: '',
        password: '',
      },
      ruleInline: {
        account: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
      },
    };
  },
  created() {
    var _this = this;
    document.onkeydown = function (e) {
      let key = window.event.keyCode;
      if (key === 13) {
        _this.handleSubmit('formInline');
      }
    };
  },
  methods: {
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          configApi(this.formInline)
            .then(async (res) => {
              this.$message.success('登录成功!');
              this.$emit('on-Login');
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    // 休改密码
    changePassword() {
      this.$emit('on-change');
    },
    changeReg() {
      this.$emit('on-changes');
    },
  },
};
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
