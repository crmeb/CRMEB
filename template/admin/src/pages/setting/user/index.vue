<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="160" label-position="right">
        <FormItem label="账号" prop="">
          <Input type="text" v-model="account" :disabled="true" class="input"></Input>
        </FormItem>
        <FormItem label="姓名" prop="real_name">
          <Input type="text" v-model="formValidate.real_name" class="input"></Input>
        </FormItem>
        <FormItem label="原始密码" prop="pwd">
          <Input type="password" v-model="formValidate.pwd" class="input"></Input>
        </FormItem>
        <FormItem label="新密码" prop="new_pwd">
          <Input type="password" v-model="formValidate.new_pwd" class="input"></Input>
        </FormItem>
        <FormItem label="确认新密码" prop="conf_pwd">
          <Input type="password" v-model="formValidate.conf_pwd" class="input"></Input>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="handleSubmit('formValidate')">提交</Button>
        </FormItem>
      </Form>
    </Card>
  </div>
</template>

<script>
import { updtaeAdmin } from '@/api/user';
import { mapState } from 'vuex';
export default {
  name: 'setting_user',
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  data() {
    return {
      account: '',
      formValidate: {
        real_name: '',
        pwd: '',
        new_pwd: '',
        conf_pwd: '',
      },
      ruleValidate: {
        real_name: [{ required: true, message: '您的姓名不能为空', trigger: 'blur' }],
        pwd: [{ required: true, message: '请输入您的原始密码', trigger: 'blur' }],
        new_pwd: [{ required: true, message: '请输入您的新密码', trigger: 'blur' }],
        conf_pwd: [{ required: true, message: '请确认您的新密码', trigger: 'blur' }],
      },
    };
  },
  mounted() {
    this.account = this.$store.state.userInfo.userInfo.account;
  },
  methods: {
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          updtaeAdmin(this.formValidate)
            .then((res) => {
              this.$Message.success(res.msg);
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          if (this.formValidate.new_pwd !== this.formValidate.conf_pwd) {
            this.$Message.error('您输入的新密码与旧密码不一致');
          }
        }
      });
    },
  },
};
</script>

<style scoped>
.input {
  width: 400px;
}
</style>
