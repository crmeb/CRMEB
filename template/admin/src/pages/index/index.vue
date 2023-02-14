<template>
  <div class="ivu-mt-16">
    <!--头部-->
    <base-info ref="baseInfo" />
    <!--小方块-->
    <grid-menu />
    <!--订单统计-->
    <visit-chart ref="visitChart" />
    <!--用户-->
    <user-chart ref="userChart" />
    <!--版本升级-->
    <upgrade v-if="force_reminder == 1" />
  </div>
</template>

<script>
import baseInfo from './components/baseInfo';
import gridMenu from './components/gridMenu';
import visitChart from './components/visitChart';
import userChart from './components/userChart';
import hotSearch from './hot-search';
import userPreference from './user-preference';
import { auth } from '@/api/system';
import { Notice } from 'iview';
import { getCookies, setCookies } from '@/libs/util';
import { upgradeStatusApi } from '@/api/system';

export default {
  name: 'index',
  components: {
    baseInfo,
    gridMenu,
    visitChart,
    userChart,
    hotSearch,
    userPreference,
  },
  data() {
    return {
      visitType: 'day', // day, month, year
      visitDate: [new Date(), new Date()],
      force_reminder: null,
    };
  },
  mounted() {
    this.getAuth();
  },
  methods: {
    getAuth() {
      auth()
        .then((res) => {
          let data = res.data || {};
          this.force_reminder = data.force_reminder;
          if (data.auth_code && data.auth) {
            this.authCode = data.auth_code;
            this.auth = true;
          }
        })
        .catch((res) => {});
    },
  },
};
</script>

<style lang="less">
.dashboard-console-visit {
  .ivu-radio-group-button .ivu-radio-wrapper {
    border: none !important;
    box-shadow: none !important;
    padding: 0 12px;
  }
  .ivu-radio-group-button .ivu-radio-wrapper:before,
  .ivu-radio-group-button .ivu-radio-wrapper:after {
    display: none;
  }
}
</style>
