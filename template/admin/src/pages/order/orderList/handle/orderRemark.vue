<template>
  <el-dialog :visible.sync="modals" title="请修改内容" class="order_box" :show-close="true" width="540px">
    <el-form
      ref="formValidate"
      :model="formValidate"
      :rules="ruleValidate"
      label-width="80px"
      label-position="right"
      @submit.native.prevent
    >
      <el-form-item label="备注：" prop="remark">
        <el-input
          v-model="formValidate.remark"
          :maxlength="200"
          show-word-limit
          type="textarea"
          placeholder="订单备注"
          style="width: 414px"
        />
      </el-form-item>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button v-db-click @click="cancel('formValidate')">取消</el-button>
      <el-button type="primary" v-db-click @click="putRemark('formValidate')">提交</el-button>
    </span>
  </el-dialog>
</template>

<script>
import { putRemarkData, putRefundRemarkData } from '@/api/order';
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
    orderId: Number,
    remarkType: {
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
      let data = {
        id: this.orderId,
        remark: this.formValidate,
      };
      this.$refs[name].validate((valid) => {
        if (valid) {
          (this.remarkType ? putRefundRemarkData : putRemarkData)(data)
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
