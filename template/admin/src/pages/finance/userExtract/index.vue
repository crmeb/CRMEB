<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
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
          <el-form-item label="提现状态：">
            <el-select
              clearable
              v-model="formValidate.status"
              placeholder="请选择状态"
              @change="selChange"
              class="form_content_width"
            >
              <el-option
                v-for="(item, index) in treeData.withdrawal"
                :key="index"
                :value="item.value"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="提现方式：">
            <el-select
              clearable
              v-model="formValidate.extract_type"
              placeholder="请选择状态"
              @change="selChange"
              class="form_content_width"
            >
              <el-option
                v-for="(item, index) in treeData.payment"
                :key="index"
                :value="item.value"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="微信昵称/姓名/支付宝账号/银行卡号"
              v-model="formValidate.nireid"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="extractStatistics"></cards-data>
    <el-card :bordered="false" shadow="never">
      <router-link :to="$routeProStr + '/finance/finance/commission'">
        <el-button type="primary">佣金记录</el-button>
      </router-link>
      <el-table ref="table" :data="tabList" v-loading="loading" empty-text="暂无数据" class="mt14">
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="130">
          <template slot-scope="scope">
            <div>
              用户昵称: {{ scope.row.nickname }} <br />
              用户id:{{ scope.row.uid }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="提现金额" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.extract_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现手续费" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.extract_fee }}</div>
          </template>
        </el-table-column>
        <el-table-column label="到账金额" min-width="100">
          <template slot-scope="scope">
            <div class="f-price">{{ scope.row.receive_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现方式" min-width="130">
          <template slot-scope="scope">
            <div class="type" v-if="scope.row.extract_type === 'bank'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">银行卡号:{{ scope.row.bank_code }}</div>
              <div class="item">银行开户地址:{{ scope.row.bank_address }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'weixin'">
              <div class="item">昵称:{{ scope.row.nickname }}</div>
              <div class="item">微信号:{{ scope.row.wechat }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'alipay'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">支付宝号:{{ scope.row.alipay_code }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'balance'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">提现方式：佣金转入余额</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="收款码" min-width="90">
          <template slot-scope="scope">
            <div
              class="tabBox_img"
              v-viewer
              v-if="scope.row.extract_type === 'weixin' || scope.row.extract_type === 'alipay'"
            >
              <img v-lazy="scope.row.qrcode_url" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="申请时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time | formatDate }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="130">
          <template slot-scope="scope">
            <div class="status" v-if="scope.row.status === 0">
              <div class="statusVal">申请中</div>
              <div></div>
            </div>
            <div class="statusVal" v-if="scope.row.status === 1">提现通过</div>
            <div class="statusVal" v-if="scope.row.status === -1">
              提现未通过<br />未通过原因：{{ scope.row.fail_msg }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope" v-if="scope.row.status == 0">
            <a href="javascript:void(0);" v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a class="item" v-db-click @click="adopt(scope.row, '审核通过', index)">通过</a>
            <el-divider direction="vertical"></el-divider>
            <a class="item" v-db-click @click="invalid(scope.row)">驳回</a>
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

    <!-- 编辑表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 拒绝通过-->
    <el-dialog :visible.sync="modals" title="未通过原因" :close-on-click-modal="false" width="540px">
      <el-input v-model="fail_msg.message" type="textarea" :rows="4" placeholder="请输入未通过原因" />
      <div slot="footer">
        <el-button type="primary" size="small" v-db-click @click="oks">确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import cardsData from '@/components/cards/cards';
import searchFrom from '@/components/publicSearchFrom';
import { mapState } from 'vuex';
import { cashListApi, cashEditApi, refuseApi } from '@/api/finance';
import { formatDate } from '@/utils/validate';
import editFrom from '@/components/from/from';
export default {
  name: 'cashApply',
  components: { cardsData, searchFrom, editFrom },
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
      fail_msg: {
        message: '输入信息不完整或有误!',
      },
      modals: false,
      total: 0,
      cardLists: [],
      loading: false,
      tabList: [],
      pickerOptions: this.$timeOptions,
      treeData: {
        withdrawal: [
          {
            title: '全部',
            value: '',
          },
          {
            title: '未通过',
            value: -1,
          },
          {
            title: '申请中',
            value: 0,
          },
          {
            title: '已通过',
            value: 1,
          },
        ],
        payment: [
          {
            title: '全部',
            value: '',
          },
          {
            title: '微信',
            value: 'wx',
          },
          {
            title: '支付宝',
            value: 'alipay',
          },
          {
            title: '银行卡',
            value: 'bank',
          },
        ],
      },
      formValidate: {
        status: '',
        extract_type: '',
        nireid: '',
        data: '',
        page: 1,
        limit: 20,
      },
      extractStatistics: {},
      timeVal: [],
      FromData: null,
      extractId: 0,
    };
  },
  watch: {
    $route() {
      if (this.$route.fullPath === this.$routeProStr + '/finance/user_extract/index?status=0') {
        this.getPath();
      }
    },
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
    if (this.$route.fullPath === this.$routeProStr + '/finance/user_extract/index?status=0') {
      this.getPath();
    } else {
      this.getList();
    }
  },
  methods: {
    getPath() {
      this.formValidate.page = 1;
      this.formValidate.status = parseInt(this.$route.query.status);
      this.getList();
    },
    // 无效
    invalid(row) {
      this.extractId = row.id;
      this.modals = true;
    },
    // 确定
    oks() {
      this.modal_loading = true;
      refuseApi(this.extractId, this.fail_msg)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.modal_loading = false;
          this.modals = false;
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 通过
    adopt(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `finance/extract/adopt/${row.id}`,
        method: 'put',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
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
    // 选择
    selChange() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      cashListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list.list;
          this.total = data.list.count;
          this.extractStatistics = data.extract_statistics;
          this.cardLists = [
            {
              col: 6,
              count: this.extractStatistics.brokerage_count,
              name: '佣金总金额',
              className: 'iconyuezhifujine',
            },
            { col: 6, count: this.extractStatistics.price, name: '待提现金额', className: 'iconfufeihuiyuanjine' },
            { col: 6, count: this.extractStatistics.priced, name: '已提现金额', className: 'iconzhifuyongjinjine' },
            {
              col: 6,
              count: this.extractStatistics.brokerage_not,
              name: '未提现金额',
              className: 'iconshangpintuikuanjine',
            },
          ];
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      cashEditApi(row.id)
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
      // this.getList();
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
