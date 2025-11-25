<template>
  <div class="iconBox">
    <el-input
      v-model="iconVal"
      placeholder="输入关键词搜索,注意全是英文"
      clearable
      style="width: 300px"
      @change="upIcon(iconVal)"
      ref="search"
    />
    <div class="trees-coadd">
      <div class="scollhide">
        <div class="trees">
          <ul class="list-inline">
            <li class="icons-item" v-for="(item, i) in iconVal ? searchData : list" :key="i">
              <i :class="'el-icon-' + item" class="f-s-24" v-db-click @click="iconChange(item)"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import icon from '@/utils/icon';
export default {
  name: 'index',
  data() {
    return {
      iconVal: '',
      modals2: false,
      list: icon,
      searchData: [],
    };
  },
  methods: {
    // 搜索
    upIcon(n) {
      this.searchData = this.list.filter((item) => item.indexOf(this.iconVal) > -1);
    },
    iconChange(n) {
      if (this.$route.query.fodder === 'icon') {
        /* eslint-disable */
        form_create_helper.set('icon', n);
        form_create_helper.close('icon');
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.f-s-24 {
  font-size: 24px;
}
.iconBox {
  background: #fff;
}
.icons-item {
  float: left;
  margin: 6px 6px 6px 0;
  width: 53px;
  text-align: center;
  list-style: none;
  cursor: pointer;
  height: 50px;
  color: #5c6b77;
  transition: all 0.2s ease;
  position: relative;
  padding-top: 10px;
  ::v-deep .ivu-icon {
    font-size: 32px !important;
  }
}
.trees-coadd {
  width: 100%;
  height: 500px;
  border-radius: 4px;
  overflow: hidden;
}
.scollhide {
  width: 100%;
  height: 100%;
  overflow: auto;
  margin-left: 18px;
  padding: 10px 0 10px 0;
  box-sizing: border-box;
  .content {
    font-size: 12px;
  }
  .time {
    font-size: 12px;
    color: #2d8cf0;
  }
}
</style>
