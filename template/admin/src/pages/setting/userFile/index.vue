<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="160" label-position="right">
        <FormItem label="账号" prop="">
          <Input type="text" v-model="account" :disabled="true" class="input"></Input>
        </FormItem>
        <FormItem label="文件管理新密码" prop="file_pwd">
          <Input type="password" v-model="formValidate.file_pwd" class="input"></Input>
        </FormItem>
        <FormItem label="文件管理确认新密码" prop="conf_file_pwd">
          <Input type="password" v-model="formValidate.conf_file_pwd" class="input"></Input>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="handleSubmit('formValidate')">提交</Button>
        </FormItem>
      </Form>
    </Card>
  </div>
</template>

<script>
import { setFilePassword } from '@/api/user';
import { mapState } from 'vuex';
export default {
  name: 'setting_files',
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
        file_pwd: '',
        conf_file_pwd: '',
      },
      ruleValidate: {
        file_pwd: [{ required: true, message: '请输入您的文件管理新密码', trigger: 'blur' }],
        conf_file_pwd: [{ required: true, message: '请确认您的文件管理新密码', trigger: 'blur' }],
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
          setFilePassword(this.formValidate)
            .then((res) => {
              this.$Message.success(res.msg);
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
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
