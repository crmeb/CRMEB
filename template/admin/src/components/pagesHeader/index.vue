<template>
  <el-card shadow="never" class="box-card">
    <div class="acea-row row-middle">
      <div v-if="backUrl" class="font-sm after-line" v-db-click @click="goBack">
        <span class="el-icon-arrow-left"></span>
        <span class="pl10">返回</span>
      </div>
      <span class="ht_title ml10">{{ title }}</span>
    </div>
    <slot></slot>
  </el-card>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: '',
    },
    backUrl: {
      type: String,
      default: '',
    },
  },
  methods: {
    goBack() {
      if (this.backUrl) {
        this.$router.push({
          path: this.backUrl,
        });
      } else {
        this.$router.back();
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.box-card {
  ::v-deep .el-card__body {
    display: flex;
    justify-content: space-between;
    width: 100%;
  }
}
.after-line {
  display: inline-block;
  position: relative;
  margin-right: 16px;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
}
.after-line:after {
  content: '';
  position: absolute;
  top: 0;
  right: -16px;
  width: 1px;
  height: 16px;
  background: #eee;
}
.font-sm {
  font-size: 14px;
}
.ht_title {
  font-weight: 500;
  font-size: 18px;
  color: #303133;
}
</style>
