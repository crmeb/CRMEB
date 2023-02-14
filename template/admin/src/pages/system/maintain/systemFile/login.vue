<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Row type="flex">
      <Col span="24">
        <div class="index_from page-account-container">
          <div class="page-account-top">
            <span class="page-account-top-tit">文件管理登录</span>
          </div>
          <Form ref="formInline" :model="formInline" :rules="ruleInline" @submit.native.prevent>
            <!-- <FormItem prop="sms_account" class="maxInpt">
            <Input type="text" v-model="formInline.account" prefix="ios-contact-outline" placeholder="请输入手机号" />
          </FormItem> -->
            <FormItem prop="sms_token" class="maxInpt">
              <Input
                type="password"
                size="large"
                v-model="formInline.password"
                prefix="ios-lock-outline"
                placeholder="请输入密码"
              />
              <div class="trip">提示：config/filesystem.php中手动配置password后使用，不能为空</div>
            </FormItem>
            <FormItem class="maxInpt">
              <Button type="primary" long size="large" @click="handleSubmit('formInline')" class="btn">登录</Button>
            </FormItem>
          </Form>
        </div>
      </Col>
    </Row>
  </div>
</template>

<script>
import { opendirLoginApi } from '@/api/system';
import { setCookies } from '@/libs/util';

export default {
  name: 'file_login',
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
        // account: '',
        password: '',
      },
      ruleInline: {
        // account: [{ required: true, validator: validatePhone, trigger: 'blur' }],
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
          opendirLoginApi(this.formInline)
            .then(async (res) => {
              this.$Message.success('登录成功!');
              //   this.$emit('on-Login', res.data);
              let expires = this.getExpiresTime(res.data.expires_time);
              // 记录用户登陆信息
              setCookies('file_token', res.data.token, expires);
              this.$router.push({
                path: '/admin/system/maintain/system_file/opendir',
              });
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    //计算token过期时间
    getExpiresTime(expiresTime) {
      let nowTimeNum = Math.round(new Date() / 1000);
      let expiresTimeNum = expiresTime - nowTimeNum;
      return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
    },
  },
};
</script>

<style scoped lang="stylus">
.maxInpt{
    max-width 400px
    margin-left auto
    margin-right auto
}
.index_from{
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.trip{
    width 400px
    text-align: left;
    color #aaa
}
.page-account-container{
    text-align center
    padding 200px 0
}
.page-account-top{
    margin-bottom 50px
}
.page-account-top-tit
    font-size 30px
    color #1890FF
    font-weight 500
.page-account-other
    text-align center
    color #1890FF
    font-size 12px
    span
        cursor pointer
>>> .btn{
  font-size: 15px !important;
}
</style>
