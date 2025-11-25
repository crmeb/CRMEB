<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <el-button
          class="btn-back"
          icon="el-icon-arrow-left"
          size="small"
          type="text"
          v-db-click
          @click="$router.go(-1)"
          >返回</el-button
        >
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="tableFrom"
          :model="tableFrom"
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
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="奖品类型：">
            <el-select type="button" v-model="tableFrom.type" @change="selectType" class="form_content_width" clearable>
              <el-option v-for="(item, i) in typeList" :key="i" :label="item.text" :value="item.val"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索用户：" label-for="store_name">
            <el-input clearable placeholder="请输入用户信息" v-model="tableFrom.keyword" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
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
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <div>{{ scope.row.id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.user.nickname }} </span>
          </template>
        </el-table-column>
        <el-table-column label="奖品信息" min-width="130">
          <template slot-scope="scope">
            <div class="prize">
              <img :src="scope.row.prize.image" alt="" />
              <span>{{ scope.row.prize.name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="抽奖时间" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.add_time }}</div>
          </template>
        </el-table-column>
        <el-table-column label="收货信息" min-width="100">
          <template slot-scope="scope">
            <div v-if="scope.row.receive_info.name">
              <div>姓名：{{ scope.row.receive_info.name }}</div>
              <div>电话：{{ scope.row.receive_info.phone }}</div>
              <div>地址：{{ scope.row.receive_info.address }}</div>
              <div v-if="scope.row.receive_info.mark">备注：{{ scope.row.receive_info.mark }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.deliver_info.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <a v-db-click @click="deliver(scope.row, 1)" v-if="scope.row.type == 6 && scope.row.is_deliver === 0"
              >发货</a
            >
            <a v-else-if="scope.row.type == 6 && scope.row.is_deliver === 1" v-db-click @click="isDeliver(scope.row)"
              >配送信息</a
            >
            <el-divider direction="vertical" v-if="scope.row.type == 6" />
            <a v-db-click @click="deliver(scope.row, 2)">备注</a>
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
    <!-- 发货-->
    <el-dialog
      :visible.sync="shipModel"
      width="540px"
      :title="!modelTitle ? (modelType === 1 ? '发货' : '备注') : modelTitle"
      :close-on-click-modal="false"
    >
      <el-form
        v-model="shipModel"
        :ref="modelType === 1 ? 'shipForm' : 'markForm'"
        :model="modelType === 1 ? shipForm : markForm"
        :rules="modelType === 1 ? ruleShip : ruleMark"
        label-width="90px"
      >
        <el-form-item v-if="modelType === 1" label="快递公司：" prop="deliver_name">
          <el-select v-model="shipForm.deliver_name" class="w100">
            <el-option v-for="item in locationList" :value="item.value" :key="item.id" :label="item.value"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item v-if="modelType === 1" label="快递单号：" prop="deliver_number">
          <el-input v-model="shipForm.deliver_number" placeholder="请输入快递单号" class="w100"></el-input>
          <div class="tips-info" v-if="shipForm.deliver_name == '顺丰速运'">
            <p>顺丰请输入单号 :收件人或寄件人手机号后四位</p>
            <p>例如：SF000000000000:3941</p>
          </div>
        </el-form-item>
        <el-form-item v-if="modelType === 2" label="备注：">
          <el-input v-model="markForm.mark" placeholder="请输入备注" class="w100"></el-input>
        </el-form-item>
        <el-form-item>
          <div class="acea-row row-right">
            <el-button v-db-click @click="cancel('formValidate')">关闭</el-button>
            <el-button type="primary" v-db-click @click="ok(modelType === 1 ? 'shipForm' : 'markForm')">提交</el-button>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { lotteryRecordList, lotteryRecordDeliver } from '@/api/lottery';
import { formatDate } from '@/utils/validate';
import { getExpressData } from '@/api/order';
import { ruleShip, ruleMark } from './formRule/ruleShip';
export default {
  name: 'lotteryRecordList',
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
      shipModel: false,
      loading: false,
      locationList: [],
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
      pickerOptions: this.$timeOptions,
      typeList: [
        { text: '全部', val: '' },
        { text: '未中奖', val: '1' },
        { text: '积分', val: '2' },
        { text: '余额', val: '3' },
        { text: '红包', val: '4' },
        { text: '优惠券', val: '5' },
        { text: '商品', val: '6' },
      ],
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
        lottery_id: 0,
      },
      total: 0,
      timeVal: [],
      modelType: 1,
      lottery_id: '',
      modelTitle: '',
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.tableFrom.lottery_id = this.$route.query.id;
    this.lottery_id = this.$route.query.id;
    this.getList();
    this.getExpressData();
  },
  methods: {
    deliver(row, type) {
      this.markForm.id = row.id;
      this.shipForm.id = row.id;
      this.shipForm.deliver_name = '';
      this.shipForm.deliver_number = '';
      this.markForm.mark = row.deliver_info.mark;
      this.modelType = type;
      this.shipModel = true;
    },
    isDeliver(row) {
      this.markForm.id = row.id;
      this.shipForm.id = row.id;
      this.modelType = 1;
      this.modelTitle = '配送信息';
      this.shipModel = true;
      this.shipForm.deliver_name = row.deliver_info.deliver_name;
      this.shipForm.deliver_number = row.deliver_info.deliver_number;
    },
    ok(name) {
      this.$refs[name].validate((valid) => {
        lotteryRecordDeliver(this.modelType == 1 ? this.shipForm : this.markForm)
          .then((res) => {
            this.$message.success('操作成功');
            this.shipModel = false;
            this.getList();
            this.shipForm = {
              id: '',
              deliver_name: '',
              deliver_number: null,
            };
            this.modelTitle = '';
            this.markForm = {
              id: '',
              mark: '',
            };
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      });
    },
    cancel() {
      this.modelType = 1;
      this.modelTitle = '';
      this.shipModel = false;
    },
    // 物流公司列表
    getExpressData() {
      getExpressData()
        .then(async (res) => {
          this.locationList = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.tableFrom.time = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
      this.tableFrom.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.page = 1;
      this.tableFrom.time = tab;
      this.timeVal = [];
      this.getList();
    },
    selectType(type) {
      this.tableFrom.page = 1;
      this.timeVal = [];
      this.getList();
    },
    selectChangeFactor() {
      this.tableFrom.page = 1;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      lotteryRecordList(this.tableFrom)
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
  },
};
</script>

<style lang="scss" scoped>
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
.w414 {
  width: 414px;
}
</style>
