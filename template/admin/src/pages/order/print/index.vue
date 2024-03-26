<template>
  <!-- 支付订单 -->
  <div class="order-bgc">
    <div class="putSupplier perpage" v-for="(item, index) in newArrayData" :key="index">
      <div class="header acea-row row-between-wrapper">
        <div class="left acea-row row-middle">
          <!-- <div class="picture" :id="'qrCodeUrl' + index"></div> -->
          <div class="info">
            <div><span class="name">收货人：</span>{{ orderData.user_name }}</div>
            <div><span class="name">收货地址：</span>{{ orderData.user_address }}</div>
            <div>
              <span class="name">手机号：</span><span>{{ orderData.user_phone }}</span>
            </div>
          </div>
        </div>
        <div class="info">
          <div><span class="name">订单编号：</span>{{ orderData.order_id }}</div>
          <div><span class="name">支付时间：</span>{{ orderData.pay_time }}</div>
          <div><span class="name">支付方式：</span>{{ orderData.pay_type }}</div>
        </div>
      </div>
      <div class="mt20">
        <el-table border :columns="columns" :data="item" :disabled-hover="true">
          <el-table-column label="商品编号" align="center">
            <template slot-scope="scope">
              <span class="nickname">{{ scope.row.index }} </span>
            </template>
          </el-table-column>
          <el-table-column label="商品名称">
            <template slot-scope="scope">
              <span class="nickname line2">{{ scope.row.name }} </span>
            </template>
          </el-table-column>
          <el-table-column label="商品规格">
            <template slot-scope="scope">
              <span class="nickname">{{ scope.row.sku }} </span>
            </template>
          </el-table-column>
          <el-table-column label="单价">
            <template slot-scope="scope">
              <span class="nickname">{{ scope.row.price }}</span>
            </template>
          </el-table-column>
          <el-table-column label="数量">
            <template slot-scope="scope">
              <span class="nickname">{{ scope.row.num }} </span>
            </template>
          </el-table-column>
          <el-table-column label="金额">
            <template slot-scope="scope">
              <span class="nickname">{{ scope.row.sum_price }}</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="bottom acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <div class="item"><span class="name">运费：</span>{{ orderData.pay_postage }}</div>
          <div class="item"><span class="name">优惠：</span>{{ orderData.coupon_price }}</div>
          <div class="item"><span class="name">会员折扣：</span>{{ orderData.vip_price }}</div>
          <div class="item"><span class="name">积分抵扣：</span>{{ orderData.deduction_price }}</div>
        </div>
        <div class="pricePay">实付金额：{{ orderData.pay_price }}</div>
      </div>
      <div class="bottom acea-row">
        <div class="name">
          用户备注：<span class="con">{{ orderData.mark || '-' }}</span>
        </div>
      </div>
    </div>
    <!--  注意：后续要是加内容使页面撑大，记得查看下打印是否在同一张,是否会多余一张空白纸  -->
  </div>
</template>
<script>
import { distributionOrder } from '@/api/order';
import QRCode from 'qrcodejs2';
import Setting from '@/setting';
export default {
  data() {
    return {
      columns: [
        {
          title: '商品编号',
          key: 'index',
          align: 'center',
          width: 60,
        },
        {
          title: '商品名称',
          slot: 'store_name',
          align: 'center',
          minWidth: 253,
        },
        {
          title: '商品规格',
          slot: 'suk',
          align: 'center',
          width: 219,
        },
        // {
        //     title: '商品条码',
        //     key: 'bar_code',
        //     align: 'center',
        //     width: 109
        // },
        // {
        //     title: '商品编码',
        //     key: 'code',
        //     align: 'center',
        //     width: 109
        // },
        {
          title: '单价',
          key: 'price',
          align: 'center',
          width: 100,
        },
        {
          title: '数量',
          key: 'num',
          align: 'center',
          width: 60,
        },
        {
          title: '金额',
          key: 'subtotal',
          align: 'center',
          width: 100,
        },
      ],
      orderData: {},
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      newArrayData: [],
      site_name: '',
      refund_phone: '',
      refund_address: '',
    };
  },
  created() {
    this.getDistribution();
  },
  mounted() {},
  methods: {
    // 生成二维码
    creatQrCode() {
      let qrcode;
      let url = window.location.origin + '/pages/goods/order_details/index?order_id=' + this.$route.query.id;
      this.newArrayData.forEach((item, index) => {
        let obj = document.getElementById('qrCodeUrl' + index);
        qrcode = new QRCode(obj, {
          text: url, // 需要转换为二维码的内容
          width: 90,
          height: 90,
          colorDark: '#000000',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H,
        });
      });
    },
    getDistribution() {
      distributionOrder(this.$route.query.id)
        .then((res) => {
          this.orderData = res.data;
          const chunkedArrays = [];
          res.data.product_info.forEach((item, index) => {
            const chunkIndex = Math.floor(index / 6);
            if (!chunkedArrays[chunkIndex]) {
              chunkedArrays[chunkIndex] = [];
            }
            item.index = index + 1;
            chunkedArrays[chunkIndex].push(item);
          });
          this.newArrayData = chunkedArrays;
          setTimeout(() => {
            this.creatQrCode();
          }, 200);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
.perpage {
  page-break-after: always !important;
}
.order-bgc {
  position: absolute;
  width: 100%;
  background-color: #fff;
  height: max-content;
  min-height: 100vh;
}
::v-deep .el-table th {
  background-color: #fff !important;
}
::v-deep .el-table-header thead tr th:nth-of-type(1) {
  padding-left: 0 !important;
}
::v-deep .el-table-header thead tr th {
  border-top: 1px solid #333;
}
::v-deep .el-table td:nth-of-type(1) {
  padding-left: 0 !important;
}
::v-deep .el-table-header table {
  //border-top:0!important;
}
::v-deep .el-table-border th,
::v-deep .el-table-border td {
  border-right: 1px solid #333 !important;
}
::v-deep .el-table-border th:nth-of-type(1),
::v-deep .el-table-border td:nth-of-type(1) {
  border-left: 1px solid #333 !important;
}
::v-deep .el-table th,
::v-deep .el-table td {
  border-bottom: 1px solid #333 !important;
  border-right: 1px solid #333 !important;
}
::v-deep .el-table-wrapper-with-border {
  border-color: #333 !important;
  border: unset;
}
::v-deep .el-table-border:after {
  background-color: #333;
  width: 0 !important;
  height: 0 !important;
}
::v-deep .el-table--border {
  border: 1px solid #333 !important;
  border-bottom: 0px !important;
}
::v-deep .el-table:before {
  background-color: #333;
  width: 0 !important;
  height: 0 !important;
}
::v-deep .el-table .cell,
::v-deep .el-table th.el-table__cell > .cell {
  height: 47px !important;
  display: flex;
  align-items: center;
  justify-content: center;
}
::v-deep .el-table {
  color: #000;
}
.pricePay {
  font-weight: bold;
}
.bottom {
  color: rgba(0, 0, 0, 0.85);
  font-size: 12px;
  font-weight: 400;
  margin-top: 20px;
  .item {
    margin-right: 30px;
  }
  .name {
    font-weight: 600;
  }
  .con {
    width: 740px;
    font-weight: unset;
  }
}
.putSupplier {
  width: 794px;
  background-color: #fff;
  margin: 0 auto;
  padding-top: 10px;
  /*padding: 20px 20px 450px 20px;*/
  /*box-sizing: border-box;*/
  .header {
    .info {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.85);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 80px;
      .name {
        font-weight: 600;
      }
    }
    .left {
      width: 500px;
      .picture {
        width: 90px;
        height: 90px;
        margin-right: 20px;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        flex: 1;
      }
    }
  }
}
.delivery {
  display: flex;
  justify-content: center;
  width: 794px;
  padding-top: 14px;
  border-top: 1px solid #dddddd;
  margin: 11px auto;
  font-size: 10px;
  color: #333333;

  div + div {
    margin-left: 30px;
  }
}
</style>
