<template>
  <div>
    <el-form ref="formValidate" :model="formValidate" :rules="ruleInline" inline>
      <el-form-item label="选择类型：" class="form-item" label-position="right" label-width="100px">
        <el-radio-group v-model="formValidate.gender">
          <el-radio :label="item.key" v-for="(item, index) in radioList" :key="index">{{ item.title }}</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item
        v-if="formValidate.gender == 1"
        label="发货类型："
        class="form-item"
        label-position="right"
        label-width="100px"
        :key="'test0'"
      >
        <el-radio-group v-model="formValidate.shipStatus">
          <el-radio :label="item.key" v-for="(item, index) in shipType" :key="index">{{ item.title }}</el-radio>
        </el-radio-group>
      </el-form-item>
      <!--  发货手动填写  -->
      <div v-if="formValidate.gender == 1 && formValidate.shipStatus == 1" :key="'test1'">
        <el-form-item
          label="快递公司："
          prop="logisticsCode"
          class="form-item"
          label-position="right"
          label-width="100px"
        >
          <el-select
            v-model="formValidate.logisticsCode"
            filterable
            placeholder="请选择"
            @change="bindChange"
            :label-in-value="true"
            style="width: 100%"
          >
            <el-option
              :value="item.code"
              v-for="(item, index) in logisticsList"
              :key="index"
              :label="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="快递单号：" prop="number" class="form-item" label-position="right" label-width="100px">
          <el-input v-model="formValidate.number" placeholder="请输入快递单号" style="width: 100%"></el-input>
        </el-form-item>
        <el-form-item label="" class="form-item" label-position="right" label-width="100px">
          <div style="color: #c4c4c4">顺丰请输入单号：收件人或寄件人手机号后四位,</div>
          <div style="color: #c4c4c4">例如：SF000000000000:3941</div>
        </el-form-item>
      </div>
      <!--  电子面单打印  -->
      <div v-if="formValidate.gender == 1 && formValidate.shipStatus == 2" :key="'test2'">
        <el-form-item
          label="快递公司："
          prop="logisticsCode"
          class="form-item"
          label-position="right"
          label-width="100px"
        >
          <el-select
            v-model="formValidate.logisticsCode"
            placeholder="请选择"
            style="width: 100%"
            @change="bindChange"
            filterable
            :label-in-value="true"
          >
            <el-option
              :value="item.code"
              v-for="(item, index) in logisticsList"
              :key="index"
              :label="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item
          label="电子面单："
          class="form-item"
          label-position="right"
          label-width="100px"
          v-if="orderTempList.length > 0"
        >
          <el-select v-model="formValidate.electronic" placeholder="请选择电子面单" style="width: 80%">
            <el-option
              :value="item.temp_id"
              v-for="(item, index) in orderTempList"
              :key="index"
              :label="item.title"
            ></el-option>
          </el-select>
          <el-button style="flex: 1; margin-left: 21px" v-db-click @click="lookImg">预览</el-button>
          <viewer :images="orderTempList" class="viewer" ref="viewer" @inited="inited" style="display: none">
            <img v-for="src in orderTempList" :src="src.pic" :key="src.id" class="image" />
          </viewer>
        </el-form-item>
        <el-form-item label="寄件人姓名：" prop="sendName" class="form-item" label-position="right" label-width="100px">
          <el-input v-model="formValidate.sendName" placeholder="请输入寄件人姓名" style="width: 100%"></el-input>
        </el-form-item>
        <el-form-item
          label="寄件人电话："
          prop="sendPhone"
          class="form-item"
          label-position="right"
          label-width="100px"
        >
          <el-input v-model="formValidate.sendPhone" placeholder="请输入寄件人电话" style="width: 100%"></el-input>
        </el-form-item>
        <el-form-item
          label="寄件人地址："
          prop="sendAddress"
          class="form-item"
          label-position="right"
          label-width="100px"
        >
          <el-input v-model="formValidate.sendAddress" placeholder="请输入寄件人地址" style="width: 100%"></el-input>
        </el-form-item>
      </div>
      <!--  送货  -->
      <div v-if="formValidate.gender == 2" :key="'test3'">
        <el-form-item label="选择送货人：" class="form-item" label-position="right" label-width="100px">
          <el-select v-model="formValidate.postPeople" placeholder="选择送货人" style="width: 100%">
            <el-option
              :value="item.id"
              v-for="(item, index) in deliveryList"
              :key="index"
              :label="item.nickname"
            ></el-option>
          </el-select>
        </el-form-item>
      </div>
      <div v-if="formValidate.gender == 3">
        <el-form-item label="备注：" props="msg" class="form-item" label-position="right" label-width="100px">
          <el-input placeholder="备注" v-model="formValidate.msg" />
        </el-form-item>
      </div>
      <div class="mask-footer">
        <el-button type="primary" v-db-click @click="handleSubmit('formValidate')">提交</el-button>
        <el-button v-db-click @click="close">取消</el-button>
      </div>
    </el-form>
  </div>
</template>

<script>
import { orderExport, orderTemp, orderDeliveryAll, orderDelivery, getSender } from '@/api/kefu';
export default {
  name: 'delivery',
  props: {
    isShow: {
      type: Boolean,
      default: false,
    },
    orderId: {
      type: String | Number,
      default: '',
    },
    virtualType: {
      type: Number,
      default: 0,
    },
  },
  watch: {
    'formValidate.shipStatus': {
      handler(nVal, oVal) {
        if (nVal == 2 && !this.formValidate.sendName) {
          getSender().then((res) => {
            this.formValidate.sendName = res.data.to_name;
            this.formValidate.sendPhone = res.data.to_tel;
            this.formValidate.sendAddress = res.data.to_add;
          });
        }
        this.$refs['formValidate'].resetFields();
      },
      deep: true,
    },
    'formValidate.gender': {
      handler(nVal, oVal) {
        this.$refs['formValidate'].resetFields();
      },
      deep: true,
    },
    virtualType: {
      handler(nVal, oVal) {
        if (nVal == 3) this.formValidate.gender = 3;
      },
      immediate: true,
    },
  },
  data() {
    return {
      shipType: [
        {
          key: 1,
          title: '手动填写',
        },
        {
          key: 2,
          title: '电子面单打印',
        },
      ],
      radioList: [
        {
          key: 1,
          title: '发货',
        },
        {
          key: 2,
          title: '送货',
        },
        {
          key: 3,
          title: '虚拟',
        },
      ],
      ruleInline: {
        logisticsCode: [{ required: true, message: '请选择快递公司', trigger: 'change' }],
        number: [{ required: true, message: '请填写快递单号', trigger: 'change' }],
        sendName: [{ required: true, message: '请填写寄件人姓名', trigger: 'change' }],
        sendPhone: [
          { required: true, message: '请填写寄件人手机', trigger: 'change' },
          { pattern: /^1[3456789]\d{9}$/, message: '手机号码格式不正确', trigger: 'blur' },
        ],
        sendAddress: [{ required: true, message: '请填写寄件人地址', trigger: 'change' }],
        msg: [{ required: true, message: '请填写备注信息', trigger: 'change' }],
      },
      formValidate: {
        gender: 1,
        shipStatus: 1,
        logisticsCode: '', // 快递公司编号
        logisticsName: '', // 快递公司名称
        number: '', // 快递单号
        electronic: '', //电子面单
        sendName: '', //寄件人姓名
        sendPhone: '', // 寄件人电话
        sendAddress: '', //寄件人地址
        postPeople: '', // 配送员
        msg: '', // 备注
      },
      logisticsList: [],
      orderTempList: [],
      deliveryList: [],
    };
  },
  mounted() {
    this.getOrderExport();
    this.getDelivery();
  },
  methods: {
    // 获取配送人
    getDelivery() {
      orderDeliveryAll().then((res) => {
        this.deliveryList = res.data;
      });
    },
    //查看大图
    inited(viewer) {
      this.$viewer = viewer;
    },
    //物流公司
    getOrderExport() {
      orderExport().then((res) => {
        this.logisticsList = res.data;
      });
    },
    handleSubmit(name) {
      if (this.formValidate.gender == 1) {
        this.$refs[name].validate((valid) => {
          let paramsData = {};
          paramsData.type = this.formValidate.gender;
          paramsData.express_record_type = parseFloat(this.formValidate.shipStatus);
          paramsData.delivery_name = this.formValidate.logisticsName;
          paramsData.delivery_code = this.formValidate.logisticsCode;
          if (valid) {
            // 手动
            if (this.formValidate.gender == 1 && this.formValidate.shipStatus == 1) {
              paramsData.delivery_id = this.formValidate.number;
            }
            // 电子
            if (this.formValidate.gender == 1 && this.formValidate.shipStatus == 2) {
              paramsData.to_name = this.formValidate.sendName;
              paramsData.to_tel = this.formValidate.sendPhone;
              paramsData.to_addr = this.formValidate.sendAddress;
              paramsData.express_temp_id = this.formValidate.electronic;
            }
            orderDelivery(this.orderId, paramsData)
              .then((res) => {
                this.$message.success(res.msg);
                this.$emit('ok');
              })
              .catch((error) => {
                this.$message.error(error.msg);
              });
          } else {
          }
        });
      }
      if (this.formValidate.gender == 2) {
        let people = {};
        this.deliveryList.forEach((el, index) => {
          if (el.id == this.formValidate.postPeople) {
            people = el;
          }
        });
        orderDelivery(this.orderId, {
          type: this.formValidate.gender,
          sh_delivery_name: people.wx_name,
          sh_delivery_id: people.phone,
          sh_delivery_uid: people.id,
        })
          .then((res) => {
            this.$message.success(res.msg);
            this.$emit('ok');
          })
          .catch((error) => {
            this.$message.error(error.msg);
          });
      }
      if (this.formValidate.gender == 3) {
        orderDelivery(this.orderId, {
          type: this.formValidate.gender,
          remark: this.formValidate.msg,
        })
          .then((res) => {
            this.$message.success(res.msg);
            this.$emit('ok');
          })
          .catch((error) => {
            this.$message.error(error.msg);
          });
      }
    },
    close() {
      this.$emit('close');
    },
    // 物流选中
    bindChange(val) {
      let deliveryItem = this.logisticsList.find((item) => {
        return item.code == val;
      });
      this.formValidate.logisticsName = deliveryItem.value;
      if (this.formValidate.shipStatus == 2) {
        orderTemp({
          com: val.value,
        }).then((res) => {
          this.orderTempList = res.data.data;
        });
      }
    },
    lookImg() {
      if (this.formValidate.electronic) {
        this.orderTempList.forEach((el, index) => {
          if (el.temp_id == this.formValidate.electronic) {
            this.$viewer.view(index);
          }
        });
      } else {
        this.$message.error('请选择电子面单');
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.form-item {
  width: 100%;
}
</style>
