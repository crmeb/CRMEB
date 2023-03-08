<template>
  <div class="search">
    <Select
      v-model="currentVal"
      class="select"
      placeholder="菜单搜索"
      filterable
      remote
      clearable
      :remote-method="remoteMethod"
      :loading="loading"
      @on-change="remoteMethod"
    >
      <Option
        v-for="(option, index) in menusList"
        :value="option.menu_path"
        :key="index"
        :disabled="option.type === 1"
        >{{ option.menu_name }}</Option
      >
    </Select>
  </div>
</template>
<style scoped>
.search /deep/ .ivu-select-selection {
  margin-right: 20px;
  border-radius: 30px;
  border: none !important;
}
.search /deep/ .ivu-select-visible .ivu-select-selection {
  box-shadow: unset !important;
  border: none !important;
}
.search /deep/ .ivu-select-selection:hover,
.search /deep/ .ivu-select-selection:active {
  border: none;
}
.search /deep/ li.ivu-select-item {
  text-align: left;
}
.search /deep/ .select .ivu-select-input,
.search /deep/ .select .ivu-select-item {
  font-size: 13px !important;
}
.search /deep/ .ivu-select-input {
  padding-left: 19px;
  border-radius: 30px;
  background-color: #f8f8f9;
}
</style>
<script>
import { menusListApi } from '@/api/account';

export default {
  name: 'iHeaderSearch',
  data() {
    return {
      currentVal: '',
      loading: false,
      menusList: [],
    };
  },
  computed: {},
  created() {
    this.getMenusList();
  },
  methods: {
    getMenusList() {
      this.loading = true;
      menusListApi().then((res) => {
        this.loading = false;
        this.menusList = res.data;
      });
    },
    remoteMethod() {
      this.$router.push({ path: this.currentVal });
    },
  },
};
</script>
