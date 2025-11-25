<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
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
          <el-form-item label="搜索：" label-for="status">
            <el-input
              clearable
              placeholder="请输入姓名、电话、UID"
              v-model="formValidate.nickname"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['export-userAgent']" class="export" v-db-click @click="exports">导出</el-button>
      <el-table
        ref="selection"
        :data="tableList"
        class="mt14"
        v-loading="loading"
        empty-text="暂无数据"
        highlight-current-row
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.headimgurl ? scope.row.headimgurl : require('../../assets/images/moren.jpg')" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" width="150">
          <template slot-scope="scope">
            <div class="name">
              <div class="item">昵称:{{ scope.row.nickname }}</div>
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">电话:{{ scope.row.phone }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="分销等级" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.agentLevel ? scope.row.agentLevel.name : '--' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="推广用户数量" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.spread_count }}</span>
          </template>
        </el-table-column>
        <el-table-column label="推广订单数量" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.spread_order.order_count }}</div>
          </template>
        </el-table-column>
        <el-table-column label="推广订单金额" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.spread_order.order_price || '0.00' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="佣金总金额" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.brokerage_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="已提现金额" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.extract_count_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现次数" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.extract_count_num }}</div>
          </template>
        </el-table-column>
        <el-table-column label="未提现金额" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.new_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="上级推广人" min-width="120">
          <template slot-scope="scope">
            <div>{{ scope.row.spread_name }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <a v-db-click @click="promoters(scope.row, 'man')">推广人</a>
            <el-divider direction="vertical"></el-divider>
            <template>
              <el-dropdown size="small" @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="1">推广订单</el-dropdown-item>
                  <el-dropdown-item command="2">推广二维码</el-dropdown-item>
                  <el-dropdown-item command="3">修改上级推广人</el-dropdown-item>
                  <el-dropdown-item command="4" v-if="scope.row.spread_uid">清除上级推广人</el-dropdown-item>
                  <el-dropdown-item command="5">取消推广资格</el-dropdown-item>
                  <el-dropdown-item command="6">修改分销等级</el-dropdown-item>
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
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!-- 推广人列表-->
    <promoters-list ref="promotersLists"></promoters-list>
    <!-- 推广二维码-->
    <el-dialog :visible.sync="modals" title="推广二维码" :close-on-click-modal="false" width="540px">
      <div class="acea-row row-around" v-loading="spinShow">
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_src"><img v-lazy="code_src" /></div>
          <span class="QRpic_sp1 mt10" v-db-click @click="getWeChat">公众号推广二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_xcx"><img v-lazy="code_xcx" /></div>
          <span class="QRpic_sp2 mt10" v-db-click @click="getXcx">小程序推广二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_h5"><img v-lazy="code_h5" /></div>
          <span class="QRpic_sp2 mt10" v-db-click @click="getH5">H5推广二维码</span>
        </div>
      </div>
    </el-dialog>
    <!--修改推广人-->
    <el-dialog :visible.sync="promoterShow" title="修改推广人" width="540px" :show-close="true">
      <el-form ref="formInline" :model="formInline" label-width="100px" @submit.native.prevent>
        <el-form-item label="用户头像：" prop="image">
          <div class="picBox" v-db-click @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="cancel('formInline')">取 消</el-button>
        <el-button type="primary" v-db-click @click="putSend('formInline')">提交</el-button>
      </span>
    </el-dialog>
    <el-dialog :visible.sync="customerShow" title="请选择商城用户" :show-close="true" width="1000px">
      <customerInfo v-if="customerShow" @imageObject="imageObject"></customerInfo>
    </el-dialog>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import searchFrom from '@/components/publicSearchFrom';
import { mapState } from 'vuex';
import {
  agentListApi,
  statisticsApi,
  lookCodeApi,
  lookxcxCodeApi,
  lookh5CodeApi,
  userAgentApi,
  agentSpreadApi,
} from '@/api/agent';
import promotersList from './handle/promotersList';
import customerInfo from '@/components/customerInfo';
import { membershipDataAddApi } from '@/api/membershipLevel';
export default {
  name: 'agentManage',
  components: { cardsData, searchFrom, promotersList, customerInfo },
  data() {
    return {
      customerShow: false,
      promoterShow: false,
      modals: false,
      spinShow: false,
      pickerOptions: this.$timeOptions,
      rows: {},
      formValidate: {
        nickname: '',
        data: '',
        page: 1,
        limit: 15,
      },
      date: 'all',
      total: 0,
      cardLists: [],
      loading: false,
      tableList: [],
      timeVal: [],
      code_src: '',
      code_xcx: '',
      code_h5: '',
      formInline: {
        uid: 0,
        spread_uid: 0,
        image: '',
      },
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
    // 提交
    putSend(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (!this.formInline.spread_uid) {
            return this.$message.error('请上传用户');
          }
          agentSpreadApi(this.formInline)
            .then((res) => {
              this.promoterShow = false;
              this.$message.success(res.msg);
              this.getList();
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        }
      });
    },
    // 导出
    exports() {
      let formValidate = this.formValidate;
      let data = {
        data: formValidate.data,
        nickname: formValidate.nickname,
      };
      userAgentApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 操作
    changeMenu(row, name, index) {
      switch (name) {
        case '1':
          this.promoters(row, 'order'); //推广人订单
          break;
        case '2':
          this.spreadQR(row); //推广方式二维码
          break;
        case '3':
          this.editS(row); //修改上级推广人
          break;
        case '4': //清除上级推广人
          this.del_parent(row, '清除【 ' + row.nickname + ' 】的上级推广人', index);
          break;
        case '5': //取消推广资格
          this.del_agent(row, '取消【 ' + row.nickname + ' 】的推广资格', index);
          break;
        case '6': //修改推广等级
          this.$modalForm(membershipDataAddApi({ uid: row.uid }, '/agent/get_level_form')).then(() => this.getList());
          break;
        default:
          break;
      }
    },
    editS(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    customer() {
      this.customerShow = true;
    },
    imageObject(e) {
      this.customerShow = false;
      this.formInline.spread_uid = e.uid;
      this.formInline.image = e.image;
    },
    // 清除上级关系
    del_parent(rows, titile, num) {
      let delfromDatap = {
        title: titile,
        num: num,
        url: `agent/stair/delete_spread/${rows.uid}`,
        method: 'PUT',
        ids: '',
      };
      this.$modalSure(delfromDatap)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 取消自己推广资格
    del_agent(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `agent/stair/delete_system_spread/${row.uid}`,
        method: 'PUT',
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
    edit(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    cancel(name) {
      this.promoterShow = false;
      this.$refs[name].resetFields();
    },
    // 推广人列表 订单
    promoters(row, tit) {
      this.$refs.promotersLists.modals = true;
      this.$refs.promotersLists.getList(row, tit);
    },
    // 统计
    getStatistics() {
      let data = {
        nickname: this.formValidate.nickname,
        data: this.formValidate.data,
      };
      statisticsApi(data)
        .then(async (res) => {
          let data = res.data;
          this.cardLists = data.res;
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
      if (!e[0]) {
        this.formValidate.data = '';
      }
      this.getList();
      this.getStatistics();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.page = 1;
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
      this.getStatistics();
    },
    // 列表
    getList() {
      this.loading = true;
      agentListApi(this.formValidate)
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
      this.getStatistics();
    },
    // 二维码
    spreadQR(row) {
      this.modals = true;
      this.rows = row;
      this.getWeChat();
      this.getXcx();
      this.getH5();
    },
    // 公众号推广二维码
    getWeChat() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
        action: 'wechant_code',
      };
      lookCodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_src = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    // 小程序推广二维码
    getXcx() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
      };
      lookxcxCodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_xcx = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    getH5() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
      };
      lookh5CodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_h5 = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
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
.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}
.QRpic_sp1 {
  font-size: 13px;
  color: #19be6b;
  cursor: pointer;
}
.QRpic_sp2 {
  font-size: 13px;
  color: #2d8cf0;
  cursor: pointer;
}

img {
  height: 36px;
  display: block;
}
.ivu-mt .name .item {
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

/* .ivu-mt ::v-deep .ivu-table-header */
/* border-top:1px dashed #ddd!important */
</style>
