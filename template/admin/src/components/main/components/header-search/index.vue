<template>
  <div class="search">
    <Select
      v-model="currentVal"
      class="select"
      placeholder="菜单搜索"
      filterable
      remote
      :remote-method="remoteMethod"
      :loading="loading"
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
<style>
.search .ivu-select-selection {
  margin-right: 20px;
}
.search .ivu-select-visible .ivu-select-selection {
  box-shadow: unset !important;
}
.search li.ivu-select-item {
  text-align: left;
}
.search .select .ivu-select-input,
.search .select .ivu-select-item {
  font-size: 14px !important;
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
