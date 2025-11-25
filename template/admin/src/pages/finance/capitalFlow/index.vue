<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          @submit.native.prevent
        >
          <el-form-item label="订单时间：">
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
          <el-form-item label="交易类型：">
            <el-select v-model="formValidate.status" @change="selChange" class="form_content_width">
              <el-option :label="item" :value="index" v-for="(item, index) in withdrawal" :key="index"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="流水搜索：">
            <div class="acea-row row-middle">
              <el-input
                clearable
                placeholder="订单号/昵称/电话/用户ID"
                v-model="formValidate.keywords"
                class="form_content_width"
              />
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-table ref="table" :data="tabList" class="ivu-mt" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="交易单号" width="180">
          <template slot-scope="scope">
            <span>{{ scope.row.flow_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联订单" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易金额" min-width="90">
          <template slot-scope="scope">
            <div v-if="scope.row.price >= 0" class="z-price">+{{ scope.row.price }}</div>
            <div v-if="scope.row.price < 0" class="f-price">{{ scope.row.price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="交易用户" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付方式" min-width="90">
          <template slot-scope="scope">
            <div v-for="item in payment" :key="item.value">
              <span v-if="scope.row.pay_type == item.value"> {{ item.title }} </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="setMark(scope.row)">备注</a>
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
    <!-- 拒绝通过-->
    <el-dialog :visible.sync="modals" title="备注" :close-on-click-modal="false" width="540px">
      <el-input v-model="mark_msg.mark" type="textarea" :rows="4" placeholder="请输入备注" />
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" v-db-click @click.prevent="oks">确定</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import searchFrom from '@/components/publicSearchFrom';
import { mapState } from 'vuex';
import { getFlowList, cashEditApi, setMarks } from '@/api/finance';
import { formatDate } from '@/utils/validate';
export default {
  name: 'cashApply',
  components: { searchFrom },
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
      images: ['1.jpg', '2.jpg'],
      modal_loading: false,
      pickerOptions: this.$timeOptions,
      mark_msg: {
        mark: '',
      },
      modals: false,
      total: 0,
      loading: false,
      tabList: [],
      withdrawal: [],
      payment: [
        {
          title: '全部',
          value: '',
        },
        {
          title: '微信',
          value: 'weixin',
        },
        {
          title: '支付宝',
          value: 'alipay',
        },
        {
          title: '银行卡',
          value: 'bank',
        },
        {
          title: '线下支付',
          value: 'offline',
        },
      ],
      formValidate: {
        trading_type: 0,
        time: '',
        keywords: '',
        page: 1,
        limit: 20,
      },
      timeVal: [],
      FromData: null,
      extractId: 0,
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
  mounted() {
    this.getList();
  },
  methods: {
    // 确定
    oks() {
      this.modal_loading = true;
      setMarks(this.extractId, this.mark_msg)
        .then((res) => {
          this.$message.success(res.msg);
          this.modal_loading = false;
          this.modals = false;
          this.getList();
        })
        .catch((err) => {
          this.modal_loading = false;
          this.$message.error(err.msg);
        });
    },
    // 备注
    setMark(row) {
      this.modals = true;
      this.extractId = row.id;
      this.mark_msg.mark = row.mark;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.time = this.timeVal ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择
    selChange(e) {
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      getFlowList(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.withdrawal = data.status;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 编辑提交成功
    submitFail() {
      this.getList();
    },
  },
};
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
  margin: 3px 0;
}
.tabform {
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}
.status ::v-deep .item ~ .item {
  margin-left: 6px;
}
.status ::v-deep .statusVal {
  margin-bottom: 7px;
}

/* .ivu-mt ::v-deep .ivu-table-header */
/* border-top:1px dashed #ddd!important */
.type {
  padding: 3px 0;
  box-sizing: border-box;
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
.z-price {
  color: red;
}
.f-price {
  color: green;
}
</style>
