<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="table_box">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          class="tabform"
          @submit.native.prevent
        >
          <el-row :gutter="24" justify="end">
            <el-col :span="24" class="ivu-text-left">
              <el-form-item label="时间选择：">
                <el-radio-group
                  v-model="formValidate.data"
                  type="button"
                  @change="selectChange(formValidate.data)"
                  class="mr"
                >
                  <el-radio-button :label="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{
                    item.text
                  }}</el-radio-button>
                </el-radio-group>
                <el-date-picker
                  clearable
                  :editable="false"
                  @change="onchangeTime"
                  :value="timeVal"
                  value-format="yyyy/MM/dd"
                  type="daterange"
                  placement="bottom-end"
                  range-separator="-"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  style="width: 200px"
                ></el-date-picker>
              </el-form-item>
            </el-col>
            <el-col :span="24" class="ivu-text-left">
              <el-col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <el-form-item label="操作名称：">
                  <el-select v-model="formValidate.type" style="width: 90%" clearable>
                    <el-option :value="1" label="男"></el-option>
                    <el-option :value="2" label="女"></el-option>
                    <el-option :value="0" label="保密"></el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <el-form-item label="操作用户：">
                  <el-input placeholder="请输入用户名称" v-model="formValidate.nickname" style="width: 90%"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xl="3" :lg="4" :md="12" :sm="24" :xs="24" class="btn_box">
                <el-form-item>
                  <el-button type="primary" label="default" class="userSearch" v-db-click @click="userSearchs"
                    >搜索</el-button
                  >
                </el-form-item>
              </el-col>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <el-table ref="selection" :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作用户" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.type_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联内容" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.headimgurl }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" min-width="130">
          <template slot-scope="scope">
            <span> {{ scope.row.add_time ? scope.row.add_time : '' | formatDate }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { formatDate } from '@/utils/validate';
import { mapState } from 'vuex';
import { wechatActionListApi } from '@/api/app';
export default {
  name: 'message',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      timeVal: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' },
        ],
      },
      formValidate: {
        limit: 15,
        page: 1,
        nickname: '',
        data: '',
        type: '',
      },
      loading: false,
      tabList: [],
      total: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('order', ['orderChartType']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.type = this.formValidate.type ? this.formValidate.type : '';
      wechatActionListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 搜索
    userSearchs() {
      this.getList();
    },
    timeChange() {},
    Refresh() {},
  },
};
</script>

<style lang="scss" scoped>
.btn_box ::v-deep .ivu-form-item-content {
  margin-left: 0 !important;
}
</style>
