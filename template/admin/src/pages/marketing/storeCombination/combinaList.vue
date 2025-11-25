<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mt ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
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
          <el-form-item label="拼团状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="进行中"></el-option>
              <el-option :value="2" label="已完成"></el-option>
              <el-option :value="3" label="未完成"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="头像" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="开团团长" min-width="150">
          <template slot-scope="scope">
            <span> {{ scope.row.nickname + ' / ' + scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开团时间" min-width="150">
          <template slot-scope="scope">
            <span> {{ scope.row.add_time | formatDate }}</span>
          </template>
        </el-table-column>
        <el-table-column label="拼团商品" min-width="180">
          <template slot-scope="scope">
            <span> {{ scope.row.title + ' / ' + scope.row.cid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="几人团" min-width="80">
          <template slot-scope="scope">
            <span> {{ scope.row.people }}</span>
          </template>
        </el-table-column>
        <el-table-column label="几人参加" min-width="80">
          <template slot-scope="scope">
            <span> {{ scope.row.count_people }}</span>
          </template>
        </el-table-column>
        <el-table-column label="结束时间" min-width="120">
          <template slot-scope="scope">
            <span> {{ scope.row.stop_time | formatDate }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="120">
          <template slot-scope="scope">
            <el-tag type="info" v-show="scope.row.status === 1">进行中</el-tag>
            <el-tag v-show="scope.row.status === 2">已完成</el-tag>
            <el-tag type="warning" v-show="scope.row.status === 3">未完成</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="150">
          <template slot-scope="scope">
            <a v-db-click @click="Info(scope.row)">查看详情</a>
            <el-divider v-if="scope.row.status === 1" direction="vertical"></el-divider>
            <a v-if="scope.row.status === 1" v-db-click @click="joinCombination(scope.row)">立即成团</a>
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
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.nickname + ' / ' + scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="150">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="订单编号" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="金额" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.total_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" min-width="100">
          <template slot-scope="scope">
            <el-tag v-show="scope.row.is_refund != 0">已退款</el-tag>
            <el-tag type="danger" v-show="scope.row.is_refund === 0">未退款</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import { combineListApi, orderPinkListApi, statisticsApi, combineJoinApi } from '@/api/marketing';
export default {
  name: 'combinalist',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  components: { cardsData },
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
    this.getStatistics();
  },
  methods: {
    // 拼团统计
    getStatistics() {
      statisticsApi()
        .then(async (res) => {
          let data = res.data;
          this.cardLists = data.res;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 查看详情
    Info(row) {
      this.modals = true;
      this.rows = row;
      orderPinkListApi(row.id)
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
    joinCombination(row) {
      this.$confirm('确认成团？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          combineJoinApi(row.id)
            .then((res) => {
              this.$message.success(res.msg);
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消',
          });
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      if (this.timeVal[0] === '') {
        this.formValidate.data = '';
      } else {
        this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      }
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
      combineListApi(this.formValidate)
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
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
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
.article-manager {
  margin-top: 3px;
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
