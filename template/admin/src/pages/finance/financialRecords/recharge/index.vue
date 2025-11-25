<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.native.prevent
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
          <el-form-item label="支付类型：">
            <el-select
              clearable
              v-model="formValidate.paid"
              placeholder="请选择"
              @change="selChange"
              class="form_content_width"
            >
              <el-option value="" label="全部"></el-option>
              <el-option value="1" label="已支付"></el-option>
              <el-option value="0" label="未支付"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入用户昵称、订单号"
              v-model="formValidate.nickname"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['export-userRecharge']" class="mr" v-db-click @click="exports">导出</el-button>
      <el-table ref="table" :data="tabList" class="mt14" v-loading="loading" empty-text="暂无数据"
        ><el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar ? scope.row.avatar : require('../../../../assets/images/moren.jpg')" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户昵称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单号" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付金额" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否支付" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.paid_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="充值类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row._recharge_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row._pay_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template slot-scope="scope">
            <a
              href="javascript:void(0);"
              v-if="scope.row.refund_price <= 0 && scope.row.paid && scope.row.recharge_type != 'system'"
              v-db-click
              @click="refund(scope.row)"
              >退款</a
            >
            <!--                    <el-divider direction="vertical"  v-if="scope.row.paid"/>-->
            <a
              href="javascript:void(0);"
              v-if="scope.row.paid === 0"
              v-db-click
              @click="del(scope.row, '此条充值记录', scope.$index)"
              >删除</a
            >
            <span class="refund" v-if="scope.row.refund_price > 0">已退款</span>
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
    <!-- 退款表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>
<script>
import cardsData from '@/components/cards/cards';
import searchFrom from '@/components/publicSearchFrom';
import { mapState } from 'vuex';
import { rechargelistApi, userRechargeApi, refundEditApi, exportUserRechargeApi } from '@/api/finance';
import editFrom from '@/components/from/from';
export default {
  name: 'recharge',
  components: { cardsData, searchFrom, editFrom },
  data() {
    return {
      FromData: null,
      formValidate: {
        data: '',
        paid: '',
        nickname: '',
        excel: 0,
        page: 1,
        limit: 20,
      },
      formValidate2: {
        data: '',
        paid: '',
        nickname: '',
      },
      total: 0,
      cardLists: [],
      loading: false,
      tabList: [],
      pickerOptions: this.$timeOptions,
      timeVal: [],
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
    this.getUserRecharge();
  },
  methods: {
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `finance/recharge/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(delfromData.num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 退款
    refund(row) {
      refundEditApi(row.id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑提交成功
    submitFail() {
      this.getList();
      this.getUserRecharge();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 选择
    selChange(x) {
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 列表
    getList() {
      this.loading = true;
      rechargelistApi(this.formValidate)
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
    // 小方块
    getUserRecharge() {
      userRechargeApi({
        data: this.formValidate.data,
        paid: this.formValidate.paid,
        nickname: this.formValidate.nickname,
      })
        .then(async (res) => {
          let data = res.data;
          this.cardLists = data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 导出
    exports() {
      let formValidate = this.formValidate;
      let data = {
        data: formValidate.data,
        paid: formValidate.paid,
        nickname: formValidate.nickname,
      };
      exportUserRechargeApi(data)
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
.ivu-mt .type .item {
  margin: 3px 0;
}
.tabform {
  margin-bottom: 10px;
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
/*.ivu-mt ::v-deep .ivu-table-header*/
/*    border-top:1px dashed #ddd!important*/
</style>
