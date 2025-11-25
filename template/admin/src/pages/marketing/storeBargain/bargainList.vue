<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <el-form-item label="时间选择：">
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
          <el-form-item label="砍价状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="进行中"></el-option>
              <el-option :value="2" label="已失败"></el-option>
              <el-option :value="3" label="已成功"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="头像" width="80">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="发起用户" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.nickname + ' / ' + scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启时间" min-width="110">
          <template slot-scope="scope">
            <span> {{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价商品" min-width="300">
          <template slot-scope="scope">
            <span> {{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最低价" min-width="60">
          <template slot-scope="scope">
            <span> {{ scope.row.bargain_price_min }}</span>
          </template>
        </el-table-column>
        <el-table-column label="当前价" min-width="60">
          <template slot-scope="scope">
            <span> {{ scope.row.now_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="总砍价次数" min-width="70">
          <template slot-scope="scope">
            <span> {{ scope.row.people_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="剩余砍价次数" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="结束时间" min-width="150">
          <template slot-scope="scope">
            <span> {{ scope.row.datatime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template slot-scope="scope">
            <el-tag size="medium" type="info" v-show="scope.row.status === 1">进行中</el-tag>
            <el-tag size="medium" type="danger" v-show="scope.row.status === 2">已失败</el-tag>
            <el-tag size="medium" v-show="scope.row.status === 3">已成功</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="Info(scope.row)">查看详情</a>
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

    <!-- 详情模态框-->
    <el-dialog :visible.sync="modals" class="tableBox" title="查看详情" :close-on-click-modal="false" width="720px">
      <el-table
        ref="selection"
        :data="tabList3"
        v-loading="loading2"
        empty-text="暂无数据"
        highlight-current-row
        max-height="600"
        size="small"
      >
        <el-table-column label="用户ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="100">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价金额" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import { bargainUserListApi, bargainUserInfoApi } from '@/api/marketing';
export default {
  name: 'bargainList',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  // components: { cardsData },
  data() {
    return {
      cardLists: [],
      modals: false,
      pickerOptions: this.$timeOptions,
      loading: false,
      formValidate: {
        status: '',
        data: '',
        page: 1,
        limit: 15,
      },
      tableList: [],
      total: 0,
      timeVal: [],
      loading2: false,
      tabList3: [],
      rows: {},
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
    this.getList();
  },
  methods: {
    // 查看详情
    Info(row) {
      this.modals = true;
      this.rows = row;
      bargainUserInfoApi(row.id)
        .then(async (res) => {
          let data = res.data;
          this.tabList3 = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.formValidate.data = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.page = 1;
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.status = this.formValidate.status || '';
      bargainUserListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .ivu-tag-cyan .ivu-tag-text {
  color: #19be6b !important;
}
.ivu-tag-cyan {
  background: rgba(25, 190, 170, 0.1);
  border-color: #19be6b !important;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
