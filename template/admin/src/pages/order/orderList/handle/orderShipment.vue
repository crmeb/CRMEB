<template>
  <el-dialog :visible.sync="modals" title="取消寄件" class="order_box" :show-close="true" width="540px">
    <Form
      ref="formValidate"
      :model="formValidate"
      :rules="ruleValidate"
      label-width="80px"
      label-position="right"
      @submit.native.prevent
    >
      <FormItem label="备注：" prop="msg">
        <el-input
          v-model="formValidate.msg"
          :maxlength="200"
          show-word-limit
          type="textarea"
          placeholder="取消寄件备注"
          style="width: 414px"
        />
      </FormItem>
    </Form>
    <div class="acea-row row-right mt20">
      <el-button v-db-click @click="cancel('formValidate')">取消</el-button>
      <el-button type="primary" v-db-click @click="putRemark('formValidate')">提交</el-button>
    </div>
  </el-dialog>
</template>

<script>
import { shipmentCancelOrder } from '@/api/order';
export default {
  name: 'orderMark',
  data() {
    return {
      formValidate: {
        msg: '',
      },
      modals: false,
      ruleValidate: {
        msg: [
          { required: true, message: '请输入备注信息', trigger: 'blur' },
          // { type: 'string', min: 20, message: 'Introduce no less than 20 words', trigger: 'blur' }
        ],
      },
    };
  },
  props: {
    orderId: Number,
  },
  methods: {
    cancel(name) {
      this.modals = false;
      this.$refs[name].resetFields();
    },
    putRemark(name) {
      let data = {
        msg: this.formValidate.msg,
      };
      this.$refs[name].validate((valid) => {
        if (valid) {
          shipmentCancelOrder(this.orderId, data)
            .then(async (res) => {
              this.$message.success(res.msg);
              this.modals = false;
              this.$refs[name].resetFields();
              this.$emit('submitFail');
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          this.$message.warning('请填写备注信息');
        }
      });
    },
  },
};
</script>

<style scoped></style>
