<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form ref="formInline" label-width="80px" label-position="right" :model="formInline" inline>
          <el-form-item label="用户渠道：">
            <el-select
              clearable
              v-model="channel_type"
              placeholder="请选择用户渠道"
              @change="changeTxt"
              class="form_content_width"
            >
              <el-option value="all" label="全部"></el-option>
              <el-option value="wechat" label="公众号"></el-option>
              <el-option value="routine" label="小程序"></el-option>
              <el-option value="h5" label="H5"></el-option>
              <el-option value="pc" label="PC"></el-option>
              <el-option value="app" label="APP"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="选择时间:">
            <el-date-picker
              clearable
              v-model="timeVal"
              type="daterange"
              :editable="false"
              @change="onchangeTime"
              format="yyyy/MM/dd"
              value-format="yyyy/MM/dd"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              :picker-options="pickerOptions"
              style="width: 250px"
              class="mr20"
            ></el-date-picker>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="handleSubmit('formInline')">查询</el-button>
          </el-form-item>
          <el-form-item>
            <el-button v-db-click @click="excel">导出</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
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
      formInline: {
        channel_type: '',
        data: '',
      },
      channel_type: 'all',
      timeVal: [],
      isShow: false,
      pickerOptions: this.$timeOptions,
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
      this.formInline.data = this.timeVal ? this.timeVal.join('-') : '';
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
.ivu-form-item {
  padding-bottom: 10px;
  margin-bottom: 0;
}
</style>
