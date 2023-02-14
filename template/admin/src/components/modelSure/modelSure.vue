<template>
  <Modal v-model="modals" class="paymentFooter" scrollable width="400" :z-index="99999" v-if="delfromData">
    <p slot="header" style="color: #f60">
      <Icon type="md-alert" />
      <span>{{ `${delfromData.title}` }}</span>
    </p>
    <div>
      <p>{{ `您确定要${delfromData.title}吗?` }}</p>
      <p v-if="delfromData.info !== undefined">{{ `${delfromData.info}` }}</p>
    </div>
    <div slot="footer" class="acea-row row-right">
      <Button type="warning" :loading="modal_loading" @click="ok">确定</Button>
      <Button type="primary" @click="cancel">取消</Button>
    </div>
  </Modal> 
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
            this.$Message.success(res.msg);
            this.modal_loading = false;
            this.modals = false;
            this.$emit('submitModel');
          })
          .catch((res) => {
            this.modal_loading = false;
            this.$Message.error(res.msg);
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

<style scoped lang="stylus">
.acea-row >>> .ivu-btn-primary
    background-color: rgb(170, 170, 170);
    border-color: rgb(170, 170, 170);
</style>
