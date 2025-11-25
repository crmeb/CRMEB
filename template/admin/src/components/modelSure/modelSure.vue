<template>
  <el-dialog :visible.sync="modals" class="paymentFooter" width="470px" :destroy-on-close="true">
    <p slot="header" style="color: #f60">
      <i class="el-icon-warning"></i>
      <span>{{ `${delfromData.title}` }}</span>
    </p>
    <div>
      <p>{{ `您确定要${delfromData.title}吗?` }}</p>
      <p v-if="delfromData.info !== undefined">{{ `${delfromData.info}` }}</p>
    </div>
    <div slot="footer" class="acea-row row-right">
      <el-button type="primary" v-db-click @click="cancel">取消</el-button>
      <el-button type="warning" v-db-click @click="ok">确定</el-button>
    </div>
  </el-dialog>
</template>

<script>
import { tableDelApi } from '@/api/common';
export default {
  name: 'modelSure',
  props: {
    title: String,
    delfromData: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      modals: false,
      modal_loading: false,
    };
  },
  methods: {
    ok() {
      this.modal_loading = true;
      setTimeout(() => {
        tableDelApi(this.delfromData)
          .then(async (res) => {
            this.$message.success(res.msg);
            this.modal_loading = false;
            this.modals = false;
            this.$emit('submitModel');
          })
          .catch((res) => {
            this.modal_loading = false;
            this.$message.error(res.msg);
            this.modals = false;
          });
      }, 2000);
    },
    cancel() {
      this.modals = false;
    },
  },
};
</script>

<style lang="scss" scoped>
.acea-row ::v-deep .ivu-btn-primary {
  background-color: rgb(170, 170, 170);
  border-color: rgb(170, 170, 170);
}
</style>
