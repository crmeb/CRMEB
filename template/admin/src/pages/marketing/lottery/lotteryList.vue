<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '20px 20px 0px' }">
      <el-form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        label-position="right"
        inline
        @submit.native.prevent
      >
        <el-form-item label="活动时间：">
          <el-date-picker
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
        <el-form-item label="活动状态：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.start"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="未开始" :value="0" />
            <el-option label="进行中" :value="1" />
            <el-option label="已结束" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="活动类型：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.factor"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="积分抽取" :value="1" />
            <el-option label="订单支付" :value="3" />
            <el-option label="订单评价" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.status"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="开启" :value="1" />
            <el-option label="关闭" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="搜索抽奖：">
          <el-input
            class="form_content_width"
            placeholder="请输入活动名称"
            v-model="tableFrom.keyword"
            @change="userSearchs"
          />
        </el-form-item>
        <el-button type="primary" v-db-click @click="userSearchs()">搜索</el-button>
      </el-form>
    </el-card>
    <el-card class="mt-20" :bordered="false" shadow="never">
      <el-button class="mb12" type="primary" v-db-click @click="openPage(0)">创建抽奖活动</el-button>
      <el-table :data="tableList" v-loading="loading" highlight-current-row no-userFrom-text="暂无数据">
        <el-table-column label="ID" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动类型" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.lottery_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="抽奖人数" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.records_total_user }}</span>
          </template>
        </el-table-column>
        <el-table-column label="中奖人数" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.records_wins_user }}</span>
          </template>
        </el-table-column>
        <el-table-column label="抽奖次数" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.records_total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="中奖次数" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.records_wins_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.status_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启状态" min-width="100">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="180">
          <template slot-scope="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.end_time }}</p>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template slot-scope="scope">
            <a @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" />
            <a @click="openPage(1, scope.row)">抽奖记录</a>
            <el-divider direction="vertical" />
            <template>
              <el-dropdown @command="(command) => changeMenu(scope.row, command, scope.$index)">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <el-dropdown-menu slot="dropdown">
                  <!-- <el-dropdown-item command="1">拉黑人员</el-dropdown-item>
                    <el-dropdown-item command="2">拉黑列表</el-dropdown-item> -->
                  <el-dropdown-item command="3">
                    <span class="copy copy-data" :data-clipboard-text="copyLink(scope.row)">复制链接</span>
                  </el-dropdown-item>
                  <el-dropdown-item command="4">删除抽奖</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </template>
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
import { lotteryList, lotteryStatus } from '@/api/lottery';
import { formatDate } from '@/utils/validate';
import { ruleShip, ruleMark } from './formRule/ruleShip';
import customerInfo from '@/components/customerInfo';
import ClipboardJS from 'clipboard';
export default {
  name: 'lotteryList',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let time = new time(time * 1000);
        return formatDate(time, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  components: {
    customerInfo,
  },
  data() {
    return {
      shipModel: false,
      loading: false,
      blackModel: false,
      pickerOptions: this.$timeOptions,
      locationList: [],
      timeVal: [],
      shipForm: {
        id: '',
        deliver_name: '',
        deliver_number: null,
      },
      markForm: {
        id: '',
        mark: '',
      },
      ruleShip: ruleShip,
      ruleMark: ruleMark,
      fromList: {
        title: '选择时间',
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
      typeList: [
        { text: '全部', val: '' },
        { text: '未中奖', val: '1' },
        { text: '积分', val: '2' },
        { text: '余额', val: '3' },
        { text: '红包', val: '4' },
        { text: '优惠券', val: '5' },
        { text: '商品', val: '6' },
      ],
      blackList: [],
      loading2: false,
      promoterShow: false,
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        keyword: '',
        time: [],
        page: 1,
        limit: 15,
        factor: '',
        status: '',
        start: '',
      },
      total: 0,
      time: [],
      modelType: 1,
      lottery_id: '',
      modelTitle: '',
      orderData: {
        id: 0,
        mark: '',
        pc_template_name: '',
        pc_template_sku: '',
      },
      orderModel: false,
      customerShow: false,
      formInline: {
        uid: 0,
        image: '',
      },
      total2: 0,
      receiveFrom: {
        lottery_id: 0,
        keyword: '',
        page: 1,
        limit: 15,
      },
    };
  },
  computed: {
    ...mapState('layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.$nextTick(function () {
      const clipboard = new ClipboardJS('.copy-data');
      clipboard.on('success', () => {
        console.log('11');
        this.$message.success('复制成功');
      });
    });
    this.getList();
  },
  methods: {
    // 操作
    changeMenu(row, name, index) {
      console.log(row, name, index);
      switch (name) {
        case '1':
          this.customer(row);
          break;
        case '3':
          this.onCopy(row);
          break;
        case '4':
          this.del(row, '删除活动', index);
          break;
      }
    },
    onCopy(row) {
      this.$copyText(this.copyLink(row))
        .then((message) => {
          this.$message.success('复制成功');
        })
        .catch((err) => {
          this.$message.error('复制失败');
        });
    },
    copyLink(row) {
      return `${window.location.origin}/pages/goods/lottery/grids/index?type=${row.factor}&lottery_id=${row.id}`;
    },
    customer(row) {
      this.promoterShow = true;
      this.formInline.lottery_id = row.id;
    },
    customerPle() {
      this.customerShow = true;
    },
    // 选择人员
    selectCustomer(e) {
      console.log(e);
      this.customerShow = false;
      this.formInline.uid = e.uid;
      this.formInline.image = e.image;
      this.formInline.nickname = e.nickname;
    },
    edit(row) {
      this.$router.push({
        path: '/admin/marketing/lottery/create',
        query: { lottery_id: row.id, type: row.type },
      });
    },
    openPage(type, row) {
      let url;
      if (type === 1) {
        url = `/admin/marketing/lottery/recording_list?id=${row.id}`;
      } else {
        url = `/admin/marketing/lottery/create`;
      }
      this.$router.push({
        path: url,
      });
    },
    // 具体日期
    onchangeTime(e) {
      this.time = e;
      if (!e || !e[0]) {
        this.tableFrom.time = [];
      } else {
        this.tableFrom.time = this.time[0] ? this.time.join('-') : '';
      }
      this.tableFrom.page = 1;
      this.getList();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/lottery/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      lotteryList(this.tableFrom)
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
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      lotteryStatus(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped lang="scss">
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

.prize {
  display: flex;
  align-items: center;
}

body .el-table ::-webkit-scrollbar {
  // z-index: 11111;
  // width: 6px;
  // height: 6px;
  // background-color: #F5F5F5;
}

.prize img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.trips {
  color: #ccc;
}
.picBox {
  display: inline-block;
  cursor: pointer;

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .iconfont {
    color: #898989;
  }
}
</style>
