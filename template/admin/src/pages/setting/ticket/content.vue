<template>
  <div>
    <pages-header ref="pageHeader" title="小票配置" :backUrl="$routeProStr + '/setting/ticket'"></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <div class="flex justify-between warpper">
        <el-form :model="formItem" label-width="120px">
          <el-form-item label="小票头部：">
            <el-checkbox v-model="formItem.header" :true-label="1" :false-label="0">商家名称</el-checkbox>
          </el-form-item>
          <el-form-item label="配送信息：">
            <el-checkbox v-model="formItem.delivery" :true-label="1" :false-label="0">配送信息</el-checkbox>
          </el-form-item>
          <el-form-item label="买家备注：">
            <el-checkbox v-model="formItem.buyer_remarks" :true-label="1" :false-label="0">买家备注</el-checkbox>
          </el-form-item>
          <el-form-item label="商品信息：">
            <el-checkbox-group v-model="formItem.goods">
              <el-checkbox :label="0">商品基础信息</el-checkbox>
              <el-checkbox :label="1">规格编码</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="运费信息：">
            <el-checkbox v-model="formItem.freight" :true-label="1" :false-label="0">运费</el-checkbox>
          </el-form-item>
          <el-form-item label="优惠信息：">
            <el-checkbox v-model="formItem.preferential" :true-label="1" :false-label="0">优惠总计</el-checkbox>
          </el-form-item>
          <el-form-item label="支付信息：">
            <el-checkbox-group v-model="formItem.pay">
              <el-checkbox :label="0">支付方式</el-checkbox>
              <el-checkbox :label="1">实收金额</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="其他订单信息：">
            <el-checkbox-group v-model="formItem.order">
              <el-checkbox :label="0">订单编号</el-checkbox>
              <el-checkbox :label="1">下单时间</el-checkbox>
              <el-checkbox :label="2">支付时间</el-checkbox>
              <el-checkbox :label="3">打印时间</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="推广二维码：">
            <el-checkbox v-model="formItem.code" :true-label="1" :false-label="0">选择系统链接</el-checkbox>
            <div v-if="formItem.code" class="link">
              <div class="select-link">
                链接：{{ formItem.code_url }}
                <span class="change" @click="getLink(index)">{{ formItem.code_url ? '修改' : '选择' }}</span>
              </div>
            </div>
          </el-form-item>
          <el-form-item label="底部公告：">
            <el-checkbox v-model="formItem.show_notice" :true-label="1" :false-label="0">底部公告</el-checkbox>
            <div v-if="formItem.show_notice">
              <el-input
                v-model="formItem.notice_content"
                maxlength="80"
                show-word-limit
                type="textarea"
                placeholder="请输入公告内容"
                style="width: 500px"
              />
            </div>
          </el-form-item>
        </el-form>
        <div class="ticket-preview">
          <div class="out-line"></div>
          <div class="ticket-content">
            <div v-if="formItem.header === 1" class="ticket-header">商家名称</div>
            <!-- 配送方式 -->
            <div class="delivery btn-line" v-if="formItem.delivery === 1">
              <div class="form-box">
                <div class="label">配送方式：</div>
                <div class="content">商家配送</div>
              </div>
              <div class="form-box">
                <div class="label">客户姓名：</div>
                <div class="content">收货人姓名</div>
              </div>
              <div class="form-box">
                <div class="label">客户电话：</div>
                <div class="content">13023354455</div>
              </div>
              <div class="form-box">
                <div class="label">收货地址：</div>
                <div class="content">上海市浦东新区世界大道25号B座309室</div>
              </div>
            </div>
            <!-- 备注 -->
            <div class="buyer-remarks btn-line" v-if="formItem.buyer_remarks === 1">
              <div class="form-box">
                <div class="label">买家备注：</div>
                <div class="content">请在收货时向商家留言，谢谢！</div>
              </div>
            </div>
            <!-- 商品 -->
            <div v-if="formItem.goods.includes(0)">
              <div class="goods btn-line">
                <div class="xing">*************************商品***********************</div>
                <div class="flex justify-between">
                  <span>商品</span>
                  <span>单价</span>
                  <span>数量</span>
                  <span>金额</span>
                </div>
              </div>
              <div class="goods-msg btn-line">
                <div class="flex justify-between">
                  <span>商品1</span>
                  <span>100.0</span>
                  <span>2</span>
                  <span>200.0</span>
                </div>
                <div class="flex justify-between">
                  <span>(规格1)</span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div v-if="formItem.goods.includes(1)" class="flex py-10">
                  <span>规格编码：</span>
                  <span>FKXQW4567vw59</span>
                </div>
              </div>
              <div class="goods-msg pb-10 pt-10">
                <div class="flex justify-between">
                  <span>商品2</span>
                  <span>100.0</span>
                  <span>2</span>
                  <span>200.0</span>
                </div>
                <div class="flex justify-between">
                  <span>(规格2)</span>
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <div v-if="formItem.goods.includes(1)" class="flex py-10">
                  <span>规格编码：</span>
                  <span>FKXQW4567vw50</span>
                </div>
              </div>
              <div class="xing">**************************************************</div>
              <!-- 合计 -->
              <div class="pay flex flex-col align-end btn-line">
                <template>
                  <div class="fw-500">合计：400.00元</div>
                </template>
              </div>
            </div>
            <!-- 运费优惠 -->
            <div
              class="pay flex flex-col align-end btn-line"
              v-if="formItem.freight === 1 || formItem.preferential === 1"
            >
              <template>
                <div v-if="formItem.freight === 1">运费：+30.00元</div>
                <div v-if="formItem.preferential === 1">优惠：-80.00元</div>
                <div v-if="formItem.preferential === 1">抵扣：-20.00元</div>
              </template>
            </div>
            <!-- 支付信息 -->

            <div class="pay flex flex-col align-end btn-line" v-if="formItem.pay.length > 0">
              <div v-if="formItem.pay.includes(0)">支付方式：微信支付</div>
              <div v-if="formItem.pay.includes(1)" class="fw-500">实际支付：330.00元</div>
            </div>
            <!-- 订单信息 -->

            <div class="order pt-10 btn-line" v-if="formItem.order.length > 0">
              <div v-if="formItem.order.includes(0)">订单编号：wx1234567890</div>
              <div v-if="formItem.order.includes(1)">下单时间：2022/06/18 12:00:00</div>
              <div v-if="formItem.order.includes(2)">支付时间：2022/06/18 12:00:00</div>
              <div v-if="formItem.order.includes(3)">打印时间：2022/06/18 14:20:00</div>
            </div>
            <!-- 二维码 -->
            <div class="code">
              <div v-show="formItem.code" id="qrcode"></div>
              <div class="mt-20" v-if="formItem.show_notice">
                {{ formItem.notice_content }}
              </div>
            </div>
          </div>
          <div class="bottom-notice">
            <img class="image" src="@/assets/images/p-btn.png" alt="" />
          </div>
        </div>
      </div>
    </el-card>
    <el-card :bordered="false" dis-hover class="fixed-card">
      <el-button type="primary" class="submission" @click="save">保存</el-button>
    </el-card>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { printContent, printSaveContent } from '@/api/setting';
import linkaddress from '@/components/linkaddress';
import QRCode from 'qrcodejs2';
import Setting from '@/setting';
export default {
  name: 'content',
  components: { linkaddress },
  data() {
    return {
      formItem: {
        header: 1,
        delivery: 1,
        buyer_remarks: 1,
        goods: [0],
        freight: 1,
        preferential: 1,
        pay: [0, 1],
        order: [0, 1],
        code: 0,
        code_url: '',
        show_notice: 0,
        notice_content: '',
      },
      code: '',
      BaseURL: Setting.apiBaseURL.replace(/\/adminapi/, ''),
      id: this.$route.query.id,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile', 'menuCollapse']),
    labelWidth() {
      return this.isMobile ? undefined : 120;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    labelBottom() {
      return this.isMobile ? undefined : 15;
    },
  },
  created() {
    if (this.id) this.getPrintContent();
  },
  methods: {
    getPrintContent() {
      printContent(this.id).then((res) => {
        if (!Array.isArray(res.data)) this.formItem = res.data;
        if (res.data.code && res.data.code_url) {
          this.code = this.BaseURL + res.data.code_url;
          this.$nextTick((e) => {
            this.drawCode(this.code);
          });
        }
      });
    },
    save() {
      printSaveContent(this.id, this.formItem)
        .then((res) => {
          this.$message.success('保存成功');
        })
        .catch((err) => {
          this.$message.error('保存失败');
        });
    },
    getLink(index) {
      this.$refs.linkaddres.modals = true;
    },
    linkUrl(e) {
      this.formItem.code_url = e;
      let url = this.BaseURL + e;
      this.drawCode(url);
    },
    drawCode(url) {
      let qrcode = '';
      let obj = document.getElementById('qrcode');
      obj.innerHTML = '';
      qrcode = new QRCode(obj, {
        text: url, // 需要转换为二维码的内容
        width: 128,
        height: 128,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
      });
    },
  },
};
</script>
<style lang="scss" scoped>
::v-deep .ivu-checkbox-wrapper {
  font-size: 12px;
  margin-right: 30px;
}
::v-deep .el-checkbox__input {
  margin-right: 6px;
}
::v-deep .el-checkbox__input {
  width: 14px;
  height: 14px;
  font-size: 12px;
}
.warpper {
  max-width: 1200px;
}
.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 265px;
  z-index: 45;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
}
.link {
  background: #f6f7f9;
  border-radius: 2px;
  padding: 15px;
}
.select-link {
  font-size: 12px;
}
.change {
  color: #2d8cf0;
  cursor: pointer;
}
.ticket-content::-webkit-scrollbar {
  display: none;
}
.ticket-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.out-line {
  width: 271px;
  height: 7px;
  background: #eeeeee;
  border-radius: 4px;
}

@-webkit-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-moz-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-ms-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@-o-keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}

@keyframes show {
  0% {
    margin-top: -70vh;
  }
  100% {
    margin-top: 0;
  }
}
.ticket-preview {
  overflow: hidden;
  height: 70vh;
}
.ticket-content {
  position: relative;
  top: -3px;
  animation: show 2s ease-in-out forwards;
  width: 260px;
  max-height: 70vh;
  overflow-y: scroll;
  overflow-x: hidden;
  background-color: #fff;
  padding: 20px 15px 15px 15px;
  box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.1);
  border-radius: 1px 1px 1px 1px;
  font-size: 12px;
  font-weight: 400;
  color: #333;
  line-height: 18px;
  .form-box {
    display: flex;
    .label {
      white-space: nowrap;
    }
  }
  .ticket-header {
    font-weight: 500;
    font-size: 18px;
    text-align: center;
    margin-bottom: 20px;
  }
  .btn-line {
    border-bottom: 1px dashed #eee;
    padding: 10px 0;
    word-break: break-all;
  }
  .fw-500 {
    font-weight: 500;
  }
  .code {
    display: flex;
    flex-direction: column;
    align-items: center;

    text-align: center;
    #qrcode {
      margin: 25px 0 0px;
    }
  }
}
.bottom-notice {
  width: 260px;
  margin-left: 1px;
  height: 13px;
  position: relative;
}
.image {
  width: 100%;
  height: 100%;
  position: absolute;
  top: -6px;
}
.xing {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  white-space: nowrap;
}
</style>
