<template>
  <Modal
    v-model="modals"
    scrollable
    title="订单发送货"
    class="order_box"
    :closable="false"
    width="1000"
    @on-visible-change="changeModal"
  >
    <Form
      v-if="modals"
      ref="formItem"
      :model="formItem"
      :label-width="100"
      @submit.native.prevent
    >
      <FormItem label="选择类型：">
        <RadioGroup v-model="formItem.type" @on-change="changeRadio">
          <Radio label="1">发货</Radio>
          <Radio label="2">送货</Radio>
          <Radio label="3">无需配送</Radio>
        </RadioGroup>
      </FormItem>
      <FormItem
        v-if="formItem.type == 1"
        v-show="export_open"
        label="发货类型："
      >
        <RadioGroup
          v-model="formItem.express_record_type"
          @on-change="changeExpress"
        >
          <Radio label="1">手动填写</Radio>
          <Radio label="2">电子面单打印</Radio>
        </RadioGroup>
      </FormItem>
      <div>
        <FormItem label="快递公司：" v-if="formItem.type == 1">
          <Select
            v-model="formItem.delivery_name"
            filterable
            placeholder="请选择快递公司"
            style="width: 80%"
            @on-change="expressChange"
          >
            <Option
              v-for="(item, i) in express"
              :value="item.value"
              :key="item.value"
              >{{ item.value }}</Option
            >
          </Select>
        </FormItem>
        <FormItem
          v-if="formItem.express_record_type === '1' && formItem.type == 1"
          label="快递单号："
        >
          <Input
            v-model="formItem.delivery_id"
            placeholder="请输入快递单号"
            style="width: 80%"
          ></Input>
          <div class="trips" v-if="formItem.delivery_name == '顺丰速运'">
            <p>顺丰请输入单号 :收件人或寄件人手机号后四位，</p>
            <p>例如：SF000000000000:3941</p>
          </div>
        </FormItem>
        <template
          v-if="formItem.express_record_type === '2' && formItem.type == 1"
        >
          <FormItem label="电子面单：" class="express_temp_id">
            <Select
              v-model="formItem.express_temp_id"
              placeholder="请选择电子面单"
              style="width: 80%"
              @on-change="expressTempChange"
            >
              <Option
                v-for="(item, i) in expressTemp"
                :value="item.temp_id"
                :key="i"
                >{{ item.title }}</Option
              >
            </Select>
            <Button v-if="formItem.express_temp_id" type="text" @click="preview"
              >预览</Button
            >
          </FormItem>
          <FormItem label="寄件人姓名：">
            <Input
              v-model="formItem.to_name"
              placeholder="请输入寄件人姓名"
              style="width: 80%"
            ></Input>
          </FormItem>
          <FormItem label="寄件人电话：">
            <Input
              v-model="formItem.to_tel"
              placeholder="请输入寄件人电话"
              style="width: 80%"
            ></Input>
          </FormItem>
          <FormItem label="寄件人地址：">
            <Input
              v-model="formItem.to_addr"
              placeholder="请输入寄件人地址"
              style="width: 80%"
            ></Input>
          </FormItem>
        </template>
      </div>
      <div v-show="formItem.type === '2'">
        <FormItem label="送货人：">
          <Select
            v-model="formItem.sh_delivery"
            placeholder="请选择送货人"
            style="width: 80%"
            @on-change="shDeliveryChange"
          >
            <Option v-for="(item, i) in deliveryList" :value="item.id" :key="i"
              >{{ item.wx_name }}（{{ item.phone }}）</Option
            >
          </Select>
        </FormItem>
      </div>
      <div v-show="formItem.type === '3'">
        <FormItem label="备注：">
          <Input
            v-model="formItem.fictitious_content"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 5 }"
            placeholder="备注"
            style="width: 80%"
          ></Input>
        </FormItem>
      </div>
      <div v-if="total_num > 1">
        <FormItem label="分单发货：">
          <i-switch
            size="large"
            v-model="splitSwitch"
            :disabled="orderStatus === 8 || orderStatus === 11"
            @on-change="changeSplitStatus"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
          <div class="trips">
            <p>
              可选择表格中的商品单独发货，发货后会生成新的订单且不能撤回，请谨慎操作！
            </p>
          </div>
          <i-table
            v-if="splitSwitch && manyFormValidate.length"
            :data="manyFormValidate"
            @on-selection-change="selectOne"
            :columns="header"
          >
            <template slot-scope="{ row, index }" slot="image">
              <div class="product-data">
                <img class="image" :src="row.cart_info.productInfo.image" />
                <div class="line2">
                  {{ row.cart_info.productInfo.store_name }}
                </div>
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="value">
              <div class="product-data">
                <div>{{ row.cart_info.productInfo.attrInfo.suk }}</div>
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="price">
              <div class="product-data">
                <div>{{ row.cart_info.truePrice }}</div>
              </div>
            </template>
          </i-table>
        </FormItem>
      </div>
    </Form>
    <div slot="footer">
      <Button @click="cancel">取消</Button>
      <Button type="primary" @click="putSend">提交</Button>
    </div>
    <!-- <viewer @inited="inited">
            <img :src="temp.pic" style="display:none" />
        </viewer> -->
    <div ref="viewer" v-viewer v-show="temp">
      <img :src="temp.pic" style="display: none" />
    </div>
  </Modal>
</template>

<script>
import {
  getExpressData,
  putDelivery,
  splitDelivery,
  orderExpressTemp,
  orderDeliveryList,
  orderSheetInfo,
  splitCartInfo,
} from "@/api/order";
export default {
  name: "orderSend",
  props: {
    orderId: Number,
    status: Number,
    // total_num: Number,
    pay_type: String,
  },
  data() {
    return {
      orderStatus: 0,
      total_num: 0,
      splitSwitch: true,
      formItem: {
        type: "1",
        express_record_type: "1",
        delivery_name: "",
        delivery_id: "",
        express_temp_id: "",
        to_name: "",
        to_tel: "",
        to_addr: "",
        sh_delivery: "",
        fictitious_content: "",
      },
      modals: false,
      express: [],
      expressTemp: [],
      deliveryList: [],
      temp: {},
      export_open: true,
      manyFormValidate: [],
      header: [
        {
          type: "selection",
          width: 60,
          align: "center",
        },
        {
          title: "商品信息",
          slot: "image",
          width: 200,
          align: "center",
        },
        {
          title: "规格",
          slot: "value",
          align: "center",
          minWidth: 120,
        },
        {
          title: "价格",
          slot: "price",
          align: "center",
          minWidth: 120,
        },
        {
          title: "总数",
          key: "cart_num",
          align: "center",
          minWidth: 120,
        },
        {
          title: "待发数量",
          key: "surplus_num",
          align: "center",
          width: 180,
          render: (h, params) => {
            return h("div", [
              h("InputNumber", {
                props: {
                  min: 1,
                  max: params.row.surplus_num,
                  value: params.row.surplus_num,
                },
                on: {
                  "on-change": (e) => {
                    params.row.surplus_num = e || 1;
                    this.manyFormValidate[params.index] = params.row;
                    this.selectData.forEach((v, index) => {
                      if (v.cart_id === params.row.cart_id) {
                        this.selectData.splice(index, 1, params.row);
                      }
                    });
                  },
                },
              }),
            ]);
          },
        },
      ],
      selectData: [],
    };
  },
  methods: {
    selectOne(data) {
      // console.log(data);
      this.selectData = data;
    },
    changeModal(status) {
      if (!status) {
        this.cancel();
      }
    },
    changeSplitStatus(status) {
      // this.splitSwitch = status;
      if (status) {
        splitCartInfo(this.orderId).then((res) => {
          this.manyFormValidate = [];
          Object.keys(res.data).forEach((key) => {
            this.manyFormValidate.push(res.data[key]);
          });
        });
      }
    },
    changeRadio(o) {
      this.$refs.formItem.resetFields();
      switch (o) {
        case "1":
          this.formItem.delivery_name = "";
          this.formItem.delivery_id = "";
          this.formItem.express_temp_id = "";
          this.formItem.express_record_type = "1";
          this.expressTemp = [];
          this.getList(1);
          break;
        case "2":
          this.formItem.sh_delivery = "";
          break;
        case "3":
          this.formItem.fictitious_content = "";
          break;
        default:
          // this.formItem = {
          //     type: '3',
          //     express_record_type: '1',
          //     delivery_name: '',
          //     delivery_id: '',
          //     express_temp_id: '',
          //     to_name: '',
          //     to_tel: '',
          //     to_addr: '',
          //     sh_delivery: ''
          // };
          break;
      }
    },
    changeExpress(j) {
      switch (j) {
        case "2":
          this.formItem.delivery_name = "";
          this.formItem.express_temp_id = "";
          this.expressTemp = [];
          this.getList(2);
          break;
        case "1":
          this.formItem.delivery_name = "";
          this.formItem.delivery_id = "";
          this.getList(1);
          break;
        default:
          break;
      }
    },
    reset() {
      this.formItem = {
        type: "1",
        express_record_type: "1",
        delivery_name: "",
        delivery_id: "",
        express_temp_id: "",
        expressTemp: [],
        to_name: "",
        to_tel: "",
        to_addr: "",
        sh_delivery: "",
        fictitious_content: "",
      };
    },
    // 物流公司列表
    getList(type) {
      let status = type === 2 ? 1 : "";
      getExpressData(status)
        .then(async (res) => {
          this.express = res.data;
          this.getSheetInfo();
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 提交
    putSend(name) {
      let data = {
        id: this.orderId,
        datas: this.formItem,
      };
      if (
        this.formItem.type === "1" &&
        this.formItem.express_record_type === "2"
      ) {
        if (this.formItem.delivery_name === "") {
          return this.$Message.error("快递公司不能为空");
        } else if (this.formItem.express_temp_id === "") {
          return this.$Message.error("电子面单不能为空");
        } else if (this.formItem.to_name === "") {
          return this.$Message.error("寄件人姓名不能为空");
        } else if (this.formItem.to_tel === "") {
          return this.$Message.error("寄件人电话不能为空");
        } else if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(this.formItem.to_tel)) {
          return this.$Message.error("请输入正确的手机号码");
        } else if (this.formItem.to_addr === "") {
          return this.$Message.error("寄件人地址不能为空");
        }
      }
      if (
        this.formItem.type === "1" &&
        this.formItem.express_record_type === "1"
      ) {
        if (this.formItem.delivery_name === "") {
          return this.$Message.error("快递公司不能为空");
        } else if (this.formItem.delivery_id === "") {
          return this.$Message.error("快递单号不能为空");
        }
      }
      if (this.formItem.type === "2") {
        if (this.formItem.sh_delivery === "") {
          return this.$Message.error("送货人不能为空");
        }
      }

      if (this.splitSwitch) {
        data.datas.cart_ids = [];
        this.selectData.forEach((v) => {
          data.datas.cart_ids.push({
            cart_id: v.cart_id,
            cart_num: v.surplus_num,
          });
        });
        splitDelivery(data)
          .then((res) => {
            this.modals = false;
            this.$Message.success(res.msg);
            this.$emit("submitFail");
            this.reset();
            this.splitSwitch = false;
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      } else {
        putDelivery(data)
          .then(async (res) => {
            this.modals = false;
            this.$Message.success(res.msg);
            this.splitSwitch = false;
            this.$emit("submitFail");
            this.reset();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    cancel(name) {
      this.modals = false;
      this.orderStatus = 0;
      this.splitSwitch = false;
      this.selectData = [];
      this.reset();
      // this.$refs[name].resetFields();
      // this.formItem.type = '1';
    },
    // 电子面单列表
    expressChange(value) {
      let expressItem = this.express.find((item) => {
        return item.value === value;
      });
      if (expressItem === undefined) {
        return;
      }
      this.formItem.delivery_code = expressItem.code;
      if (this.formItem.express_record_type === "2") {
        this.expressTemp = [];
        this.formItem.express_temp_id = "";
        orderExpressTemp({
          com: this.formItem.delivery_code,
        })
          .then((res) => {
            this.expressTemp = res.data;
            if (!res.data.length) {
              this.$Message.error("请配置你所选快递公司的电子面单");
            }
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      }
    },
    getCartInfo(data, orderid) {
      // console.log("11",data);
      this.$set(this, "orderStatus", data);
      this.$set(this, "splitSwitch", data === 8 || data === 11 ? true : false);
      // if (data === 8) {
      splitCartInfo(this.orderId).then((res) => {
        this.manyFormValidate = [];
        Object.keys(res.data).forEach((key) => {
          this.manyFormValidate.push(res.data[key]);
        });
      });
      // console.log("22",this.manyFormValidate);
      // }
    },
    getDeliveryList() {
      orderDeliveryList()
        .then((res) => {
          this.deliveryList = res.data.list;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    getSheetInfo() {
      orderSheetInfo()
        .then((res) => {
          const data = res.data;
          for (const key in data) {
            if (data.hasOwnProperty(key)) {
              this.formItem[key] = data[key];
            }
          }
          this.export_open =
            data.export_open === undefined ? true : data.export_open;
          if (!this.export_open) {
            this.formItem.express_record_type = "1";
          }
          this.formItem.to_addr = data.to_add;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    shDeliveryChange(value) {
      if (!value) return;
      let deliveryItem = this.deliveryList.find((item) => {
        return item.id === value;
      });
      this.formItem.sh_delivery_name = deliveryItem.wx_name;
      this.formItem.sh_delivery_id = deliveryItem.phone;
      this.formItem.sh_delivery_uid = deliveryItem.uid;
    },
    expressTempChange(tempId) {
      this.temp = this.expressTemp.find((item) => {
        return tempId === item.temp_id;
      });
      if (this.temp === undefined) {
        this.temp = {};
      }
    },
    // inited (viewer) {
    //     this.$viewer = viewer;
    // },
    preview() {
      this.$refs.viewer.$viewer.show();
      // this.$viewer.show();
    },
  },
};
</script>

<style scoped>
.express_temp_id {
  position: relative;
}

.express_temp_id button {
  position: absolute;
  top: 50%;
  right: 110px;
  padding: 0;
  border: none;
  background: none;
  transform: translateY(-50%);
  color: #57a3f3;
}

.ivu-btn-text:focus {
  box-shadow: none;
}
.trips {
  color: #ccc;
}
.product-data {
  display: flex;
  align-items: center;
  justify-content: center;
}
.product-data .image {
  width: 50px !important;
  height: 50px !important;
}
.line2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
