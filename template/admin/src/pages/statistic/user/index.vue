<template>
  <div class="article-manager">
    <div class="i-layout-page-header pt10">
      <Form ref="formInline" :model="formInline" inline>
        <FormItem class="mr20">
          用户渠道:
          <Select v-model="channel_type" style="width: 300px" placeholder="用户渠道" @on-change="changeTxt">
            <Option value="all">全部</Option>
            <Option value="wechat">公众号</Option>
            <Option value="routine">小程序</Option>
            <Option value="h5">H5</Option>
            <Option value="pc">PC</Option>
          </Select>
        </FormItem>
        <FormItem>
          选择时间:
          <DatePicker
            :editable="false"
            :clearable="false"
            @on-change="onchangeTime"
            :value="timeVal"
            format="yyyy/MM/dd"
            type="daterange"
            placement="bottom-start"
            placeholder="请选择时间"
            style="width: 300px"
            class="mr20"
            :options="options"
          ></DatePicker>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="handleSubmit('formInline')">查询</Button>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="excel">导出</Button>
        </FormItem>
      </Form>
    </div>
    <user-info :formInline="formInline" ref="userInfos" key="1"></user-info>
    <wechet-info :formInline="formInline" ref="wechetInfos" v-if="isShow" key="2"></wechet-info>
    <user-region :formInline="formInline" ref="userRegions" key="3"></user-region>
  </div>
</template>

<script>
import userInfo from './components/userInfo';
import wechetInfo from './components/wechetInfo';
import userRegion from './components/userRegion';
import { statisticUserExcel } from '@/api/statistic';
import { formatDate } from '@/utils/validate';
export default {
  name: 'index',
  components: {
    userInfo,
    wechetInfo,
    userRegion,
  },
  data() {
    return {
      options: this.$timeOptions,
      formInline: {
        channel_type: '',
        data: '',
      },
      channel_type: 'all',
      timeVal: [],
      isShow: false,
    };
  },
  created() {
    const end = new Date();
    const start = new Date();
    start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
    this.timeVal = [start, end];
    this.formInline.data = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');
  },
  methods: {
    changeTxt() {
      this.formInline.channel_type = this.channel_type === 'all' ? '' : this.channel_type;
    },
    // 导出
    excel() {
      statisticUserExcel(this.formInline).then(async (res) => {
        res.data.url.map((item) => {
          window.location.href = item;
        });
      });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formInline.data = this.timeVal.join('-');
    },
    handleSubmit() {
      this.$refs.userInfos.getStatistics();
      this.$refs.userInfos.getTrend();
      this.$refs.userRegions.getTrend();
      this.$refs.userRegions.getSex();
      if (this.formInline.channel_type === 'wechat') {
        this.isShow = true;
        this.$refs.wechetInfos.getStatistics();
        this.$refs.wechetInfos.getTrend();
      } else {
        this.isShow = false;
      }
    },
  },
};
</script>

<style scoped>
.pt10 {
  padding-top: 10px;
}
.i-layout-page-header {
  margin: 10px 0 10px 0;
}
.ivu-form-item {
  padding-bottom: 10px;
  margin-bottom: 0;
}
</style>
