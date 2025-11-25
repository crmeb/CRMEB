<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="时间范围：">
            <el-date-picker
              clearable
              v-model="timeVal"
              type="daterange"
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
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-row class="ivu-mt box-wrapper">
        <el-col :xs="24" :sm="24" ref="rightBox">
          <el-tabs v-model="formValidate.type" @tab-click="userSearchs">
            <el-tab-pane
              v-for="(item, index) in statusList"
              :key="index"
              :label="item.status_name"
              :name="item.id"
            ></el-tab-pane>
          </el-tabs>
          <el-table
            :data="userLists"
            ref="table"
            v-loading="loading"
            highlight-current-row
            no-formValidate-text="暂无数据"
            no-filtered-formValidate-text="暂无筛选结果"
            @sort-change="handleSortChange"
          >
            <el-table-column label="用户UID" width="150">
              <template slot-scope="scope">
                <span>{{ scope.row.uid }}</span>
              </template>
            </el-table-column>
            <el-table-column label="头像" min-width="120">
              <template slot-scope="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="名称" min-width="130">
              <template slot-scope="scope">
                <div class="acea-row">
                  <div v-text="scope.row.name"></div>
                </div>
              </template>
            </el-table-column>
            <!-- <el-table-column
              v-if="formValidate.type == 1"
              label="代理商数量"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="agent_sum"
            >
              <template slot-scope="scope">
                <span>{{ scope.row.agent_sum }}</span>
              </template>
            </el-table-column>
            <el-table-column
              v-if="formValidate.type == 1 || formValidate.type == 2"
              label="员工数量"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="staff_sum"
            >
              <template slot-scope="scope">
                <span>{{ scope.row.staff_sum }}</span>
              </template>
            </el-table-column> -->
            <el-table-column
              label="订单数"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="order_sum"
            >
              <template slot-scope="scope">
                <span>{{ scope.row.order_sum }}</span>
              </template>
            </el-table-column>
            <el-table-column
              label="订单金额"
              min-width="150"
              sortable="custom"
              :sort-orders="['ascending', 'descending']"
              prop="order_sum_price"
            >
              <template slot-scope="scope">
                <span>{{ scope.row.order_sum_price }}</span>
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
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { divisionStatistics } from '@/api/agent';
import { formatDate } from '@/utils/validate';
import timeOptions from '@/libs/timeOptions';

export default {
  name: 'agent_extra',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      total: 0,
      total2: 0,
      statusList: [
        {
          status_name: '事业部',
          id: '1',
        },
        {
          status_name: '代理商',
          id: '2',
        },
        {
          status_name: '员工',
          id: '3',
        },
      ],
      userLists: [],
      formInline: {
        uid: 0,
        proportion: 0,
        image: '',
      },
      timeVal: [],

      FromData: null,
      loading: false,
      current: 0,
      formValidate: {
        page: 1,
        limit: 15,
        time: '',
        type: '1',
        sort: '', // 排序字段
        order: '', // 排序方式
      },
      staffModal: false,
      clerkReqData: {
        uid: 0,
        page: 1,
        limit: 15,
      },
      clerkLists: [],
      pickerOptions: timeOptions,
    };
  },
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '70px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.getList();
    },
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.time = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
      divisionStatistics(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.userLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 添加排序方法
    handleSortChange({ prop, order }) {
      this.formValidate.sort = prop;
      this.formValidate.order = order === 'ascending' ? 'asc' : 'desc';
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px;
  line-height: 54px;
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
