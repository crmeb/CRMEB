<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <el-tabs v-model="isChecked" @tab-click="onChangeType">
        <el-tab-pane label="短信" name="1"></el-tab-pane>
        <el-tab-pane label="商品采集" name="4"></el-tab-pane>
        <el-tab-pane label="物流查询" name="3"></el-tab-pane>
        <el-tab-pane label="电子面单打印" name="2"></el-tab-pane>
      </el-tabs>
      <!--短信列表-->
      <div class="note" v-if="isChecked === '1' && sms.open === 1">
        <div class="acea-row row-between-wrapper">
          <div>
            <span>短信状态：</span>
            <el-radio-group type="button" v-model="tableFrom.type" @input="selectChange(tableFrom.type)">
              <el-radio-button label="">全部</el-radio-button>
              <el-radio-button label="1">成功</el-radio-button>
              <el-radio-button label="2">失败</el-radio-button>
              <el-radio-button label="0">发送中</el-radio-button>
            </el-radio-group>
          </div>
          <div>
            <el-button type="primary" v-db-click @click="shortMes">短信模板</el-button>
            <el-button style="margin-left: 20px" v-db-click @click="editSign">修改签名</el-button>
          </div>
        </div>
        <el-table
          :data="tableList"
          v-loading="loading"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
          class="mt14"
        >
          <el-table-column label="手机号" width="100">
            <template slot-scope="scope">
              <span>{{ scope.row.phone }}</span>
            </template>
          </el-table-column>
          <el-table-column label="模板内容" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.content }}</span>
            </template>
          </el-table-column>
          <el-table-column label="条数(每67/+1)" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.num }}</span>
            </template>
          </el-table-column>
          <el-table-column label="发送时间" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.add_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="状态码" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row._resultcode }}</span>
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
      </div>
      <!--商品采集，物流，电子面单列表-->
      <div
        v-else-if="
          (isChecked === '3' && query.open === 1) ||
          (isChecked === '4' && copy.open === 1) ||
          (isChecked === '2' && dump.open === 1)
        "
      >
        <el-table
          :data="tableList"
          v-loading="loading"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
          class="mt14"
        >
          <el-table-column
            :label="item.title"
            :min-width="item.minWidth"
            v-for="(item, index) in columns2"
            :key="index"
          >
            <template slot-scope="scope">
              <template v-if="item.key">
                <div>
                  <span>{{ scope.row[item.key] }}</span>
                </div>
              </template>
              <template v-else-if="item.slot === 'num' && isChecked === '3' && query.open === 1">
                <div>{{ scope.row.content.num }}</div>
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
            @pagination="getRecordList"
          />
        </div>
      </div>
      <!--无开通-->
      <div v-else>
        <!--开通按钮-->
        <div
          v-if="
            (isChecked === '1' && !isSms) ||
            (isChecked === '2' && !isDump) ||
            (isChecked === '3' && !isLogistics) ||
            (isChecked === '4' && !isCopy)
          "
          class="wuBox acea-row row-column-around row-middle"
        >
          <div class="wuTu"><img src="../../../assets/images/wutu.png" /></div>
          <span v-if="isChecked === '1'">
            <span class="wuSp1">短信服务未开通哦</span>
            <span class="wuSp2">点击立即开通按钮，即可使用短信服务哦～～～</span>
          </span>
          <span v-if="isChecked === '4'">
            <span class="wuSp1">商品采集服务未开通哦</span>
            <span class="wuSp2">点击立即开通按钮，即可使用商品采集服务哦～～～</span>
          </span>
          <span v-if="isChecked === '3'">
            <span class="wuSp1">物流查询未开通哦</span>
            <span class="wuSp2">点击立即开通按钮，即可使用物流查询服务哦～～～</span>
          </span>
          <span v-if="isChecked === '2'">
            <span class="wuSp1">电子面单打印未开通哦</span>
            <span class="wuSp2">点击立即开通按钮，即可使用电子面单打印服务哦～～～</span>
          </span>
          <el-button size="default" type="primary" v-db-click @click="onOpen">立即开通</el-button>
        </div>
        <!--短信立即开通-->
        <div class="smsBox" v-if="isSms && isChecked === '1'">
          <div class="index_from page-account-container">
            <div class="page-account-top">
              <span class="page-account-top-tit">开通短信服务</span>
            </div>
            <el-form
              ref="formInline"
              :model="formInline"
              :rules="ruleInline"
              @submit.native.prevent
              @keyup.enter="handleSubmit('formInline')"
            >
              <el-form-item prop="sign" class="maxInpt">
                <el-input
                  type="text"
                  v-model="formInline.sign"
                  prefix="ios-contact-outline"
                  placeholder="请输入短信签名"
                />
              </el-form-item>
              <el-form-item class="maxInpt">
                <el-button type="primary" long size="default" v-db-click @click="handleSubmit('formInline')" class="btn"
                  >登录</el-button
                >
              </el-form-item>
            </el-form>
          </div>
        </div>
        <!--电子面单立即开通-->
        <div class="smsBox" v-if="isDump && isChecked === '2'">
          <div class="index_from page-account-container">
            <div class="page-account-top">
              <span class="page-account-top-tit" v-if="isChecked === '2'">开通电子面单服务</span>
              <span class="page-account-top-tit" v-if="isChecked === '3'">开通物流查询服务</span>
            </div>
            <el-form
              ref="formInlineDump"
              :model="formInlineDump"
              :rules="ruleInlineDump"
              @submit.native.prevent
              @keyup.enter="handleSubmitDump('formInlineDump')"
            >
              <el-form-item prop="com" class="maxInpt">
                <el-select
                  v-model="formInlineDump.com"
                  placeholder="请选择快递公司"
                  @change="onChangeExport"
                  style="text-align: left"
                >
                  <el-option
                    v-for="(item, index) in exportList"
                    :value="item.code"
                    :key="index"
                    :label="item.name"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item prop="temp_id" class="tempId maxInpt">
                <div class="acea-row">
                  <el-select
                    v-model="formInlineDump.temp_id"
                    placeholder="请选择电子面单模板"
                    style="text-align: left"
                    :class="[formInlineDump.temp_id ? 'width9' : 'width10']"
                    @change="onChangeImg"
                  >
                    <el-option
                      v-for="(item, index) in exportTempList"
                      :value="item.temp_id"
                      :key="index"
                      :label="item.title"
                    ></el-option>
                  </el-select>
                  <div v-if="formInlineDump.temp_id">
                    <span class="tempImg">预览</span>
                    <div class="tabBox_img" v-viewer>
                      <img v-lazy="tempImg" />
                    </div>
                  </div>
                </div>
              </el-form-item>
              <el-form-item prop="to_name" class="maxInpt">
                <el-input
                  type="text"
                  v-model="formInlineDump.to_name"
                  prefix="ios-contact-outline"
                  placeholder="请填写寄件人姓名"
                />
              </el-form-item>
              <el-form-item prop="to_tel" class="maxInpt">
                <el-input
                  type="text"
                  v-model="formInlineDump.to_tel"
                  prefix="ios-contact-outline"
                  placeholder="请填写寄件人电话"
                />
              </el-form-item>
              <el-form-item prop="to_address" class="maxInpt">
                <el-input
                  type="text"
                  v-model="formInlineDump.to_address"
                  prefix="ios-contact-outline"
                  placeholder="请填写寄件人详细地址"
                />
              </el-form-item>
              <el-form-item prop="siid" class="maxInpt">
                <el-input
                  type="text"
                  v-model="formInlineDump.siid"
                  prefix="ios-contact-outline"
                  placeholder="请填写云打印编号"
                />
              </el-form-item>
              <el-form-item class="maxInpt">
                <el-button
                  type="primary"
                  long
                  size="default"
                  v-db-click
                  @click="handleSubmitDump('formInlineDump')"
                  class="btn"
                  >立即开通</el-button
                >
              </el-form-item>
            </el-form>
          </div>
        </div>
      </div>
    </el-card>
    <el-dialog
      :visible.sync="modals"
      title="短信账户签名修改"
      width="540px"
      class="order_box"
      @closed="cancel('formInline')"
    >
      <el-form ref="formInline" :model="formInline" :rules="ruleInline" label-width="100px" @submit.native.prevent>
        <el-form-item>
          <el-input
            v-model="accountInfo.account"
            disabled
            prefix="ios-person-outline"
            size="large"
            style="width: 87%"
          ></el-input>
        </el-form-item>
        <el-form-item prop="sign">
          <el-input
            v-model="formInline.sign"
            prefix="ios-document-outline"
            placeholder="请输入短信签名，例如：CRMEB"
            size="large"
            style="width: 87%"
          ></el-input>
        </el-form-item>
        <el-form-item prop="phone">
          <el-input
            v-model="formInline.phone"
            prefix="ios-call-outline"
            placeholder="请输入您的手机号"
            size="large"
            style="width: 87%"
          ></el-input>
        </el-form-item>
        <el-form-item prop="code">
          <div class="code acea-row row-middle" style="width: 87%">
            <el-input
              type="text"
              v-model="formInline.code"
              prefix="ios-keypad-outline"
              placeholder="验证码"
              size="large"
              style="width: 75%"
            />
            <el-button :disabled="!this.canClick" v-db-click @click="cutDown" size="large">{{ cutNUm }}</el-button>
          </div>
        </el-form-item>
        <el-form-item>
          <el-button
            type="primary"
            long
            size="large"
            v-db-click
            @click="editSubmit('formInline')"
            class="btn"
            style="width: 87%"
            >确认修改</el-button
          >
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import {
  smsRecordApi,
  serveInfoApi,
  serveSmsOpenApi,
  serveOpnExpressApi,
  serveOpnOtherApi,
  serveRecordListApi,
  exportTempApi,
  exportAllApi,
  serveSign,
  captchaApi,
  serveOpen,
} from '@/api/setting';
export default {
  name: 'tableList',
  props: {
    copy: {
      type: Object,
      default: null,
    },
    dump: {
      type: Object,
      default: null,
    },
    query: {
      type: Object,
      default: null,
    },
    sms: {
      type: Object,
      default: null,
    },
    accountInfo: {
      type: Object,
      default: null,
    },
  },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写手机号'));
      } else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error('手机号格式不正确!'));
      } else {
        callback();
      }
    };
    return {
      cutNUm: '获取验证码',
      canClick: true,
      spinShow: true,
      formInline: {
        sign: '',
        phone: '',
        code: '',
      },
      ruleInline: {
        sign: [{ required: true, message: '请输入短信签名', trigger: 'blur' }],
        phone: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        code: [{ required: true, message: '请输入验证码', trigger: 'blur' }],
      },
      isChecked: '1',
      columns2: [],
      tableFrom: {
        page: 1,
        limit: 20,
        type: '',
      },
      total: 0,
      loading: false,
      tableList: [],
      formInlineDump: {
        temp_id: '',
        com: '',
        to_name: '',
        to_tel: '',
        siid: '',
        to_address: '',
      },
      ruleInlineDump: {
        com: [{ required: true, message: '请选择快递公司', trigger: 'change' }],
        temp_id: [{ required: true, message: '请选择打印模板', trigger: 'change' }],
        to_name: [{ required: true, message: '请输寄件人姓名', trigger: 'blur' }],
        to_tel: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        siid: [{ required: true, message: '请输入云打印机编号', trigger: 'blur' }],
        to_address: [{ required: true, message: '请输寄件人地址', trigger: 'blur' }],
      },
      tempImg: '', // 图片
      exportTempList: [], // 电子面单模板
      exportList: [], // 快递公司列表
      isSms: false, // 是否开通短信
      isDump: false, // 是否开通电子面单
      isCopy: false, // 是否开通商品采集
      modals: false,
      isLogistics: false, //是否开通物流查询
    };
  },
  watch: {
    sms(n) {
      if (n.open === 1) this.getList();
    },
  },
  created() {
    if (this.isChecked === '1' && this.sms.open === 1) this.getList();
  },
  // mounted() {
  //     serveDumpOpen().then(res=>{
  //         this.isLogistics = res.data.isOpen
  //     })
  // },
  methods: {
    //短信模板页
    shortMes() {
      this.$router.push({
        path: this.$routeProStr + '/setting/sms/sms_template_apply/index',
      });
    },
    // 短信验证码
    cutDown() {
      if (this.formInline.phone) {
        if (!this.canClick) return;
        this.canClick = false;
        this.cutNUm = 60;
        let data = {
          phone: this.formInline.phone,
        };
        captchaApi(data)
          .then(async (res) => {
            this.$message.success(res.msg);
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
        let time = setInterval(() => {
          this.cutNUm--;
          if (this.cutNUm === 0) {
            this.cutNUm = '获取验证码';
            this.canClick = true;
            clearInterval(time);
          }
        }, 1000);
      } else {
        this.$message.warning('请填写手机号!');
      }
    },
    editSign() {
      this.formInline.sign = this.accountInfo.sms.sign;
      this.modals = true;
    },
    cancel(name) {
      this.modals = false;
      this.$refs[name].resetFields();
    },
    // 提交
    editSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          serveSign(this.formInline)
            .then((res) => {
              this.modals = false;
              this.$message.success(res.msg);
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        }
      });
    },
    onChangeImg(item) {
      this.exportTempList.map((i) => {
        if (i.temp_id === item) this.tempImg = i.pic;
      });
    },
    // 物流公司
    exportTempAllList() {
      exportAllApi()
        .then(async (res) => {
          this.exportList = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 快递公司选择
    onChangeExport(val) {
      this.formInlineDump.temp_id = '';
      this.exportTemp(val);
    },
    // 电子面单模板
    exportTemp(val) {
      exportTempApi({ com: val })
        .then(async (res) => {
          this.exportTempList = res.data.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    onChangeType() {
      if (this.isChecked === '1' && this.sms.open === 1) {
        this.tableFrom.type = '';
        this.getList();
      } else {
        // if ((this.isChecked === '2' && this.query.open === 0) || (this.dump.open === 0 && this.isChecked === '3')) this.isDump = false
        if (this.isChecked === '2' && this.query.open === 0) this.isDump = false;
        if (this.isChecked === '3' && this.query.open === 0) this.isLogistics = false;
        if (this.dump.open === 1 || this.query.open === 1 || this.copy.open === 1) this.getRecordList();
      }
    },
    // 其他列表
    getRecordList() {
      this.loading = true;
      this.tableFrom.type = this.isChecked;
      serveRecordListApi(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.data;
          this.total = res.data.count;
          switch (this.isChecked) {
            case '2':
              this.columns2 = [
                {
                  title: '订单号',
                  key: 'order_id',
                  minWidth: 150,
                },
                {
                  title: '发货人',
                  key: 'from_name',
                  minWidth: 120,
                },
                {
                  title: '收货人',
                  key: 'to_name',
                  minWidth: 120,
                },
                {
                  title: '快递单号',
                  key: 'num',
                  minWidth: 120,
                },
                {
                  title: '快递公司编码',
                  key: 'code',
                  minWidth: 120,
                },
                {
                  title: '状态',
                  key: '_resultcode',
                  minWidth: 100,
                },
                {
                  title: '打印时间',
                  key: 'add_time',
                  minWidth: 150,
                },
              ];
              break;
            case '3':
              this.columns2 = [
                {
                  title: '快递单号',
                  slot: 'num',
                  minWidth: 120,
                },
                {
                  title: '快递公司编码',
                  key: 'code',
                  minWidth: 120,
                },
                {
                  title: '状态',
                  key: '_resultcode',
                  minWidth: 120,
                },
                {
                  title: '添加时间',
                  key: 'add_time',
                  minWidth: 150,
                },
              ];
              break;
            default:
              this.columns2 = [
                {
                  title: '复制URL',
                  key: 'url',
                  minWidth: 400,
                },
                {
                  title: '请求状态',
                  key: '_resultcode',
                  minWidth: 120,
                },
                {
                  title: '添加时间',
                  key: 'add_time',
                  minWidth: 150,
                },
              ];
              break;
          }
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 开通短信提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          serveSmsOpenApi(this.formInline)
            .then(async (res) => {
              this.$message.success('开通成功!');
              this.getList();
              this.$emit('openService', 'sms');
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    // 首页去开通
    onOpenIndex(val) {
      switch (val) {
        case 'sms':
          this.isChecked = '1';
          this.isSms = true;
          break;
        case 'copy':
          this.isChecked = '4';
          this.openOther();
          break;
        case 'query':
          this.isChecked = '3';
          this.onDumpOpen();
          break;
        default:
          this.isChecked = '2';
          this.openDump();
          break;
      }
    },
    // 开通按钮
    onOpen() {
      if (this.isChecked === '1') this.isSms = true;
      if (this.isChecked === '2') this.openDump();
      if (this.isChecked === '3') this.onDumpOpen();
      if (this.isChecked === '4') this.openOther();
    },
    // 开通物流
    onDumpOpen() {
      this.$msgbox({
        title: '开通物流查询吗',
        message: '确定要开通物流查询吗？',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          serveOpen().then((res) => {
            this.getRecordList();
            this.isLogistics = true;
            this.$message.info(res.msg);
            this.$emit('openService', 'query');
          });
        })
        .catch(() => {});
    },
    // 开通其他
    openOther() {
      this.$msgbox({
        title: '开通商品采集吗',
        message: '确定要开通商品采集吗？',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          setTimeout(() => {
            serveOpnOtherApi({ type: 1 })
              .then(async (res) => {
                this.getRecordList();
                this.$emit('openService', 'copy');
              })
              .catch((res) => {
                this.$message.error(res.msg);
              });
          }, 300);
        })
        .catch(() => {});
    },
    // 开通电子面单
    openDump() {
      this.exportTempAllList();
      this.isDump = true;
    },
    // 选择
    selectChange(tab) {
      this.tableFrom.type = tab;
      this.tableFrom.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      smsRecordApi(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.data;
          this.total = res.data.count;
          this.spinShow = false;
          this.loading = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.getList();
    },
    handleSubmitDump(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          serveOpnExpressApi(this.formInlineDump)
            .then(async (res) => {
              this.$message.success('开通成功!');
              this.getRecordList();
              this.$emit('openService', 'dump');
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
  },
};
</script>
<style lang="scss" scoped>
.order_box ::v-deep .ivu-form-item-content {
  margin-left: 50px !important;
}
.maxInpt {
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}
.smsBox .page-account-top {
  text-align: center;
  margin: 70px 0 30px 0;
}
.note {
  margin-top: 15px;
}
.tempImg {
  cursor: pointer;
  margin-left: 11px;
  color: var(--prev-color-primary);
}
.tabBox_img {
  opacity: 0;
  width: 38px;
  height: 30px;
  margin-top: -30px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.width9 {
  width: 90%;
}
.width10 {
  width: 100%;
}
.wuBox {
  width: 100%;
}
.wuSp1 {
  display: block;
  text-align: center;
  color: #000000;
  font-size: 21px;
  font-weight: 500;
  line-height: 32px;
  margin-top: 23px;
  margin-bottom: 5px;
}
.wuSp2 {
  opacity: 45%;
  font-weight: 400;
  color: #000000;
  line-height: 22px;
  margin-bottom: 30px;
}
.page-account-top-tit {
  font-size: 21px;
  color: var(--prev-color-primary);
}
.wuTu {
  width: 295px;
  height: 164px;
  margin-top: 54px;
  img {
    width: 100%;
    height: 100%;
  }

  + span {
    margin-bottom: 20px;
  }
}
.tempId {
  cursor: pointer;
  margin-left: 11px;
  color: var(--prev-color-primary);
  ::v-deep .ivu-form-item-content {
    text-align: left !important;
  }
}
.tabBox_img {
  opacity: 0;
  width: 38px;
  height: 30px;
  margin-top: -30px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.width9 {
  width: 90%;
}
.width10 {
  width: 100%;
}
.wuBox {
  width: 100%;
}
.wuSp1 {
  display: block;
  text-align: center;
  color: #000000;
  font-size: 21px;
  font-weight: 500;
  line-height: 32px;
  margin-top: 23px;
  margin-bottom: 5px;
}
.wuSp2 {
  opacity: 45%;
  font-weight: 400;
  color: #000000;
  line-height: 22px;
  margin-bottom: 30px;
}
.page-account-top-tit {
  font-size: 21px;
  color: var(--prev-color-primary);
}
.wuTu {
  width: 295px;
  height: 164px;
  margin-top: 54px;
  img {
    width: 100%;
    height: 100%;
  }

  + span {
    margin-bottom: 20px;
  }
}
.tempId {
  cursor: pointer;
  margin-left: 11px;
  color: var(--prev-color-primary);
  ::v-deep .ivu-form-item-content {
    text-align: left !important;
  }
}
.tabBox_img {
  opacity: 0;
  width: 38px;
  height: 30px;
  margin-top: -30px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.width9 {
  width: 90%;
}
.width10 {
  width: 100%;
}
.wuBox {
  width: 100%;
}
.wuSp1 {
  display: block;
  text-align: center;
  color: #000000;
  font-size: 21px;
  font-weight: 500;
  line-height: 32px;
  margin-top: 23px;
  margin-bottom: 5px;
}
.wuSp2 {
  opacity: 45%;
  font-weight: 400;
  color: #000000;
  line-height: 22px;
  margin-bottom: 30px;
}
.page-account-top-tit {
  font-size: 21px;
  color: var(--prev-color-primary);
}
.wuTu {
  width: 295px;
  height: 164px;
  margin-top: 54px;
  img {
    width: 100%;
    height: 100%;
  }

  + span {
    margin-bottom: 20px;
  }
}
.tempId {
  ::v-deep .ivu-form-item-content {
    text-align: left !important;
  }
}
</style>
