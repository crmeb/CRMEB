<template>
  <div>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col :xl="6" :lg="10" :md="10" :sm="24" :xs="24">
            <el-form-item label="搜索：" label-for="store_name">
              <el-input
                search
                enter-button
                placeholder="请输入用户ID,标题"
                v-model="tableFrom.nickname"
                @on-search="userSearchs"
              />
            </el-form-item>
          </el-col>
          <el-col :xl="6" :lg="10" :md="10" :sm="24" :xs="24">
            <el-form-item label="选择时间：" label-for="user_time">
              <el-date-picker
                clearable
                :editable="false"
                @change="onchangeTime"
                v-model="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                value-format="yyyy-MM-dd"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                class="perW100"
              ></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :xl="4" :lg="4" :md="4" :sm="24" :xs="24">
            <el-button v-auth="['export-userPoint']" class="export" icon="ios-share-outline" v-db-click @click="exports"
              >导出</el-button
            >
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="tableList"
        ref="table"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="标题" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="积分变动" min-width="130">
          <template slot-scope="scope">
            <div v-if="scope.row.pm" class="z-price">+ {{ scope.row.number }}</div>
            <div v-else class="f-price">- {{ scope.row.number }}</div>
          </template>
        </el-table-column>
        <el-table-column label="变动后积分" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.balance }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="tableFrom.page"
          :limit.sync="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { integralListApi, integralStatisticsApi, userPointApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import cardsData from '@/components/cards/cards';
export default {
  name: 'userPoint',
  components: { cardsData },
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
      cardLists: [],
      loading: false,
      delfromData: {},
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        start_time: '',
        end_time: '',
        nickname: '',
        page: 1,
        limit: 15,
      },
      timeVal: [],
      total: 0,
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
    this.getStatistics();
  },
  methods: {
    // 拼团统计
    getStatistics() {
      integralStatisticsApi()
        .then(async (res) => {
          let data = res.data;
          let classList = ['ios-help-buoy', 'md-create', 'ios-help-buoy-outline', 'md-help-buoy'];
          this.cardLists = res.data.res.map((item, index) => {
            item.className = classList[index];
            return item;
          });
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.start_time = e[0];
      this.tableFrom.end_time = e[1];
      this.tableFrom.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      integralListApi(this.tableFrom)
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
      this.tableFrom.page = 1;
      this.getList();
    },
    // 导出
    exports() {
      let formValidate = this.tableFrom;
      let data = {
        start_time: formValidate.start_time,
        end_time: formValidate.end_time,
        nickname: formValidate.nickname,
      };
      userPointApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.tab_data ::v-deep .ivu-form-item-content {
  display: flex !important;
}
.z-price {
  color: red;
}
.f-price {
  color: green;
}
</style>
