<template>
  <Modal v-model="modals" scrollable title="备注" class="order_box" :closable="false">
    <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="80" @submit.native.prevent>
      <FormItem label="备注：" prop="remark">
        <Input
          v-model="formValidate.remark"
          :maxlength="200"
          show-word-limit
          type="textarea"
          placeholder="请输入备注信息"
          style="width: 100%"
        />
      </FormItem>
    </Form>
    <div slot="footer">
      <Button type="primary" @click="putRemark('formValidate')">提交</Button>
      <Button @click="cancel('formValidate')">取消</Button>
    </div>
  </Modal>
</template>

<script>
export default {
  name: 'orderMark',
  data() {
    return {
      formValidate: {
        remark: '',
      },
      modals: false,
      ruleValidate: {
        remark: [
          { required: true, message: '请输入备注信息', trigger: 'blur' },
          // { type: 'string', min: 20, message: 'Introduce no less than 20 words', trigger: 'blur' }
        ],
      },
    };
  },
  props: {
    remark: {
      default: '',
      type: String,
    },
  },
  methods: {
    cancel(name) {
      this.modals = false;
      this.$refs[name].resetFields();
    },
    putRemark(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.$emit('submitFail', this.formValidate.remark);
        } else {
          this.$Message.warning('请填写备注信息');
        }
      });
    },
  },
};
</script>

<style scoped></style>
