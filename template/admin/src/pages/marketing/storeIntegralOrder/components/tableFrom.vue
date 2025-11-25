<template>
  <div class="table_box">
    <el-form
      ref="orderData"
      :model="orderData"
      :label-width="labelWidth"
      :label-position="labelPosition"
      @submit.native.prevent
      inline
    >
      <el-form-item label="订单状态：">
        <el-select
          placeholder="请选择"
          clearable
          v-model="orderData.is_show"
          @change="selectChange2"
          class="form_content_width"
        >
          <el-option value="" label="全部"></el-option>
          <el-option value="1" label="未发货"></el-option>
          <el-option value="2" label="待收货"></el-option>
          <el-option value="3" label="交易完成"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="创建时间：">
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
      <el-form-item label="订单搜索：" prop="real_name" label-for="real_name">
        <el-input clearable v-model="orderData.real_name" placeholder="请输入" class="form_content_width">
          <el-select v-model="orderData.field_key" slot="prepend" style="width: 100px">
            <el-option value="all" label="全部"></el-option>
            <el-option value="order_id" label="订单号"></el-option>
            <el-option value="uid" label="UID"></el-option>
            <el-option value="real_name" label="用户姓名"></el-option>
            <el-option value="user_phone" label="用户电话"></el-option>
            <el-option value="store_name" label="商品名称(模糊)"></el-option>
          </el-select>
        </el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-db-click @click="orderSearch">查询</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { integralGetOrdes } from '@/api/marketing';

import {
  putWrite,
  storeOrderApi,
  handBatchDelivery,
  otherBatchDelivery,
  exportExpressList,
  storeIntegralOrder,
} from '@/api/order';
import autoSend from '../handle/autoSend';
import queueList from '../handle/queueList';
import Setting from '@/setting';
import QueueList from '../handle/queueList.vue';
export default {
  name: 'table_from',
  components: {
    autoSend,
    queueList,
  },
  props: ['formSelection', 'autoDisabled', 'isAll'],
  data() {
    return {
      currentTab: '',
      // 搜索条件
      orderData: {
        status: '',
        data: '',
        real_name: '',
        field_key: 'all',
        pay_type: '',
      },
      modalTitleSs: '',
      statusType: '',
      time: '',
      value2: [],
      isDelIdList: [],
      modals2: false,
      timeVal: [],
      payList: [
        { label: '全部', val: '' },
        { label: '微信支付', val: '1' },
        { label: '支付宝支付', val: '4' },
        { label: '余额支付', val: '2' },
        { label: '线下支付', val: '3' },
      ],
      manualModal: false,
      uploadAction: `${Setting.apiBaseURL}/file/upload/1`,
      uploadHeaders: {},
      file: '',
      autoModal: false,
      isShow: false,
      recordModal: false,
      sendOutValue: '',
      exportList: [
        {
          name: '1',
          label: '导出发货单',
        },
        {
          name: '0',
          label: '导出订单',
        },
      ],
      exportListOn: 0,
      fileList: [],
      orderChartType: {},
      pickerOptions: this.$timeOptions,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('integralOrder', ['isDels', 'delIdList']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    today() {
      const end = new Date();
      const start = new Date();
      var datetimeStart = start.getFullYear() + '/' + (start.getMonth() + 1) + '/' + start.getDate();
      var datetimeEnd = end.getFullYear() + '/' + (end.getMonth() + 1) + '/' + end.getDate();
      return [datetimeStart, datetimeEnd];
    },
  },
  watch: {
    $route() {
      if (this.$route.fullPath === this.$routeProStr + '/order/list?status=1') {
        this.getPath();
      }
    },
  },
  created() {
    // this.timeVal = this.today;
    // this.orderData.data = this.timeVal ? this.timeVal.join('-') : '';
    if (this.$route.fullPath === this.$routeProStr + '/order/list?status=1') {
      this.getPath();
    }
    // this.getToken();
    this.$parent.$emit('add');
    let searchData = {
      status: this.orderData.status,
      product_id: this.$route.query.product_id || '',
    };
    this.integralGetOrdes(searchData);
  },
  methods: {
    ...mapMutations('integralOrder', ['getOrderStatus', 'getOrderType', 'getOrderTime', 'getOrderNum', 'getfieldKey']),
    integralGetOrdes(searchData) {
      integralGetOrdes(searchData)
        .then((res) => {
          this.$set(this, 'orderChartType', res.data);
        })
        .catch((err) => {});
    },
    getPath() {
      this.orderData.status = this.$route.query.status.toString();
      this.getOrderStatus(this.orderData.status);
      this.$emit('getList', 1);
      this.$emit('order-data', this.orderData);
    },
    // 导出
    // exports(value) {
    //   this.exportListOn = this.exportList.findIndex(
    //     (item) => item.name === value
    //   );
    //   let formValidate = this.orderData;
    //   let data = {
    //     status: formValidate.status,
    //     data: formValidate.data,
    //     real_name: formValidate.real_name,
    //     type: value,
    //   };
    //   storeOrderApi(data)
    //     .then((res) => {
    //       location.href = res.data[0];
    //     })
    //     .catch((res) => {
    //       this.$message.error(res.msg);
    //     });
    // },
    // 数据导出；
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.orderData));
      excelData.page = 1;
      excelData.product_id = this.$route.query.product_id || '';
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData);
        if (!fileName) fileName = lebData.filename;
        if (!filekey.length) {
          filekey = lebData.filekey;
        }
        if (!th.length) th = lebData.header;
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        }
      }
      exportExcel(th, filekey, fileName, data);
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        storeIntegralOrder(excelData).then((res) => {
          return resolve(res.data);
        });
      });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.orderData.data = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
      this.$store.dispatch('integralOrder/getOrderTabs', {
        data: this.orderData.data,
      });
      this.getOrderTime(this.orderData.data);
      this.$emit('getList', 1);
      this.$emit('order-data', this.orderData);
    },
    // 选择时间
    selectChange(tab) {
      this.$store.dispatch('integralOrder/getOrderTabs', { data: tab });
      this.orderData.data = tab;
      this.getOrderTime(this.orderData.data);
      this.timeVal = [];
      this.$emit('getList');
      this.$emit('order-data', this.orderData);
    },
    // 订单选择状态
    selectChange2(tab) {
      this.getOrderStatus(tab);
      this.$emit('getList', 1);
    },
    userSearchs(type) {
      this.getOrderType(type);
      this.$emit('getList', 1);
    },
    // 时间状态
    timeChange(time) {
      this.getOrderTime(time);
      this.$emit('getList');
    },
    // 订单号搜索
    orderSearch() {
      this.getOrderNum(this.orderData.real_name);
      this.getfieldKey(this.orderData.field_key);
      this.$emit('getList', 1);
    },
    // 点击订单类型
    onClickTab() {
      this.$emit('onChangeType', this.currentTab);
    },
    // 批量删除
    delAll() {
      if (this.delIdList.length === 0) {
        this.$message.error('请先选择删除的订单！');
      } else {
        if (this.isDels) {
          this.delIdList.filter((item) => {
            this.isDelIdList.push(item.id);
          });
          let idss = {
            ids: this.isDelIdList,
            all: this.isAll,
            where: this.orderData,
          };
          let delfromData = {
            title: '删除订单',
            url: `/order/dels`,
            method: 'post',
            ids: idss,
          };
          this.$modalSure(delfromData)
            .then((res) => {
              this.$message.success(res.msg);
              this.tabList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          this.$message.error('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单！');
        }
      }
    },
    del(name) {
      // this.orderInfo = ''
      this.modals2 = false;
      this.writeOffFrom.confirm = 0;
      this.$refs[name].resetFields();
    },
    handleSubmit() {
      this.$emit('on-submit', this.data);
    },
    // 刷新
    Refresh() {
      this.$emit('getList');
    },
    //
    handleReset() {
      this.$refs.form.resetFields();
      this.$emit('on-reset');
    },
    // 上传头部token
    // getToken() {
    //   this.uploadHeaders["Authori-zation"] =
    //     "Bearer " + util.cookies.get("token");
    // },
    // beforeUpload(file){
    //     /* Boilerplate to set up FileReader */
    // 	const reader = new FileReader();
    // 	reader.onload = (e) => {
    // 		/* Parse data */
    // 		const bstr = e.target.result;
    // 		const wb = XLSX.read(bstr, {type:'binary'});
    // 		/* Get first worksheet */
    // 		const wsname = wb.SheetNames[0];
    // 		const ws = wb.Sheets[wsname];
    // 		/* Convert array of arrays */
    // 		const data = XLSX.utils.sheet_to_json(ws, {header:1});
    // 		/* Update state */
    // 		this.data5 = data;
    //         this.cols5 = make_cols(ws['!ref']);
    //         this.modal5 = true;
    // 	};
    // 	reader.readAsBinaryString(file);
    // },
    // 上传成功
    uploadSuccess(res, file, fileList) {
      if (res.status === 200) {
        this.$message.success(res.msg);
        this.file = res.data.src;
        this.fileList = fileList;
      } else {
        this.$message.error(res.msg);
      }
    },
    //移除文件
    removeFile(file, fileList) {
      this.file = '';
      this.fileList = fileList;
    },
    // 手动批量发货-确定
    manualModalOk() {
      this.$refs.upload.clearFiles();
      handBatchDelivery({
        file: this.file,
      })
        .then((res) => {
          this.$message.success(res.msg);
          this.fileList = [];
        })
        .catch((err) => {
          this.$message.error(err.msg);
          this.fileList = [];
        });
    },
    // 手动批量发货-取消
    manualModalCancel() {
      this.fileList = [];
      this.$refs.upload.clearFiles();
    },
    // 自动批量发货-取消
    autoModalOk() {
      if (this.isAll == '全部' || this.formSelection.length) {
        this.$refs.send.modals = true;
        this.$refs.send.getList();
        this.$refs.send.getDeliveryList();
      } else {
        this.$message.error('请选择本页订单');
      }
    },
    // 自动批量发货-取消
    autolModalCancel() {},
    submitFail() {
      otherBatchDelivery();
    },
    queuemModal() {
      // this.$router.push({ path: 'queue/list' });
      this.$refs.queue.modal = true;
    },
    onAuto() {
      this.$refs.sends.modals = true;
      this.$refs.sends.getList();
      this.$refs.sends.getDeliveryList();
    },
    // 下载物流公司对照表
    getExpressList() {
      exportExpressList()
        .then((res) => {
          window.open(res.data[0]);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.tab_data ::v-deep .ivu-form-item-content {
  margin-left: 0 !important;
}
.table_box ::v-deep .ivu-divider-horizontal {
  margin-top: 0px !important;
}
.table_box ::v-deep .ivu-form-item {
  margin-bottom: 15px !important;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.order-wrapper {
  margin-top: 10px;
  padding: 10px;
  border: 1px solid #ddd;
  .title {
    font-size: 16px;
  }
  .order-box {
    margin-top: 10px;
    border: 1px solid #ddd;
    .item {
      display: flex;
      align-items: center;
      border-bottom: 1px solid #ddd;
      &:last-child {
        border-bottom: 0;
      }
      .label {
        width: 100px;
        padding: 10px 0 10px 10px;
        border-right: 1px solid #ddd;
      }
      .con {
        flex: 1;
        padding: 10px 0 10px 10px;
      }
    }
  }
}
.manual-modal {
  display: flex;
  align-items: center;
}
.df {
  display: flex;
}
</style>
