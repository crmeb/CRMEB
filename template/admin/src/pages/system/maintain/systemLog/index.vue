<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
          class="tabform"
        >
          <el-form-item :label="fromList.title + '：'">
            <el-date-picker
              clearable
              :editable="false"
              @change="onchangeTime"
              v-model="timeVal"
              format="yyyy/MM/dd"
              type="daterange"
              value-format="yyyy/MM/dd"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              style="width: 250px"
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="名称：">
            <el-select v-model="formValidate.admin_id" clearable @change="userSearchs" class="form_content_width">
              <el-option
                :value="item.id"
                v-for="(item, index) in dataList"
                :key="index"
                :label="item.real_name"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="链接：">
            <el-input
              placeholder="请输入链接"
              v-model="formValidate.path"
              class="form_content_width"
              clearable
            ></el-input>
          </el-form-item>
          <el-form-item label="IP：">
            <el-input placeholder="请输入IP" v-model="formValidate.ip" clearable class="form_content_width"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="userSearch" v-db-click @click="userSearchs">搜索</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table ref="selection" :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="ID/名称" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.admin_id + ' / ' + scope.row.admin_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.path_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="链接" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.path }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作ip" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.ip }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.add_time | formatDate }}</span>
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
import { mapState } from 'vuex';
import { searchAdminApi, systemListApi } from '@/api/system';
import { formatDate } from '@/utils/validate';
export default {
  name: 'systemLog',
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
      timeVal: [],
      formValidate: {
        limit: 20,
        page: 1,
        pages: '',
        data: '',
        path: '',
        ip: '',
        admin_id: '',
      },
      loading: false,
      tabList: [],
      total: 0,

      dataList: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getSearchAdmin();
    this.getList();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
    },
    // 搜索条件
    getSearchAdmin() {
      searchAdminApi()
        .then(async (res) => {
          this.dataList = res.data.info;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      systemListApi(this.formValidate)
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
      this.formValidate.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped></style>
