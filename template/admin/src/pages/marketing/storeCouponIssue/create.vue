<template>
  <div>
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: '/admin/marketing/store_coupon_issue/index' }"
          ><Button icon="ios-arrow-back" size="small" type="text">返回</Button></router-link
        >
        <Divider type="vertical" />
        <span
          class="ivu-page-header-title mr20"
          style="padding: 0"
          v-text="$route.params.id ? '编辑优惠券' : '添加优惠券'"
        ></span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form :model="formData" :label-width="150">
        <FormItem label="优惠券名称">
          <Input v-model="formData.coupon_title" :maxlength="18" placeholder="请输入优惠券名称"></Input>
        </FormItem>
        <FormItem label="发送方式">
          <RadioGroup v-model="formData.receive_type">
            <Radio :label="1">普通券</Radio>
            <Radio :label="2">新人券</Radio>
            <!-- <Radio :label="3">赠送券</Radio> -->
            <Radio :label="4">会员券</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem label="优惠劵类型">
          <RadioGroup v-model="formData.type">
            <Radio :label="0">通用券</Radio>
            <Radio :label="1">品类券</Radio>
            <Radio :label="2">商品券</Radio>
            <!--                        <Radio :label="3">会员券</Radio>-->
          </RadioGroup>
        </FormItem>
        <FormItem v-show="formData.type === 2">
          <template>
            <div class="upload-list" v-for="item in productList" :key="item.product_id">
              <img :src="item.image" />
              <Icon type="ios-close-circle" size="16" @click="remove(item.product_id)" />
            </div>
          </template>
          <Icon type="ios-camera-outline" size="26" @click="modals = true" />
          <div class="info">选择商品</div>
        </FormItem>
        <FormItem v-show="formData.type === 1">
          <Select v-model="formData.category_id" style="width: 320px" multiple>
            <Option v-for="item in categoryList" :value="item.id" :key="item.id">{{ item.cate_name }}</Option>
          </Select>
          <div class="info">选择商品的品类</div>
        </FormItem>
        <FormItem label="优惠券面值">
          <InputNumber :min="1" :max="99999" v-model="formData.coupon_price"></InputNumber>
        </FormItem>
        <FormItem label="使用门槛">
          <RadioGroup v-model="isMinPrice">
            <Radio :label="0">无门槛</Radio>
            <Radio :label="1">有门槛</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem v-if="isMinPrice">
          <InputNumber :min="1" :max="99999" v-model="formData.use_min_price"></InputNumber>
          <div class="info">填写优惠券的最低消费金额</div>
        </FormItem>
        <FormItem label="使用时间">
          <RadioGroup v-model="isCouponTime">
            <Radio :label="1">天数</Radio>
            <Radio :label="0">时间段</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem v-show="isCouponTime" label="">
          <InputNumber :min="1" v-model="formData.coupon_time" :precision="0"></InputNumber>
          <div class="info">领取后多少天内有效</div>
        </FormItem>
        <FormItem v-show="!isCouponTime" label="">
          <DatePicker
            :value="datetime1"
            :editable="false"
            type="datetimerange"
            placeholder="领取后在这个时间段内可以使用"
            @on-change="dateChange"
          ></DatePicker>
        </FormItem>
        <FormItem label="领取时间">
          <RadioGroup v-model="isReceiveTime">
            <Radio :label="1">限时</Radio>
            <Radio :label="0">不限时</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem v-show="isReceiveTime" label="">
          <DatePicker
            :value="datetime2"
            type="datetimerange"
            placeholder="在这个时间段内可领取"
            @on-change="timeChange"
          ></DatePicker>
        </FormItem>
        <FormItem label="优惠券发布数量" v-if="formData.receive_type != 2 && formData.receive_type != 3">
          <RadioGroup v-model="formData.is_permanent">
            <Radio :label="0">限量</Radio>
            <Radio :label="1">不限量</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem
          v-show="!formData.is_permanent"
          label=""
          v-if="formData.receive_type != 2 && formData.receive_type != 3"
        >
          <InputNumber :min="1" :max="99999" v-model="formData.total_count" :precision="0"></InputNumber>
          <div class="info">填写优惠券的发布数量</div>
        </FormItem>
        <FormItem label="用户领取数量" v-if="formData.receive_type != 2 && formData.receive_type != 3">
          <InputNumber :min="1" :max="99999" v-model="formData.receive_limit" :precision="0"></InputNumber>
          <div class="info">填写每个用户可以领取多少张</div>
        </FormItem>
        <!--                <FormItem label="排序">-->
        <!--                    <InputNumber-->
        <!--                        :min="0"-->
        <!--                        :max="100000000"-->
        <!--                        v-model="formData.sort"-->
        <!--                        :precision="0"-->
        <!--                    ></InputNumber>-->
        <!--                </FormItem>-->
        <FormItem label="状态">
          <RadioGroup v-model="formData.status">
            <Radio :label="1">开启</Radio>
            <Radio :label="0">关闭</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="save" :disabled="disabled">立即创建</Button>
        </FormItem>
      </Form>
    </Card>
    <Modal
      v-model="modals"
      title="商品列表"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <goods-list ref="goodslist" v-if="modals" :ischeckbox="true" @getProductId="getProductId"></goods-list>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import { couponCategoryApi, couponSaveApi, couponDetailApi } from '@/api/marketing';
export default {
  name: 'storeCouponCreate',
  components: {
    goodsList,
  },
  data() {
    return {
      disabled: false,
      formData: {
        coupon_title: '',
        coupon_price: 0,
        type: 0,
        use_min_price: 0,
        coupon_time: 1,
        start_use_time: 0,
        end_use_time: 0,
        start_time: 0,
        end_time: 0,
        receive_type: 1,
        is_permanent: 1,
        total_count: 1,
        sort: 0,
        status: 1,
        product_id: '',
        category_id: 0,
        receive_limit: 1,
      },
      categoryList: [],
      productList: [],
      isMinPrice: 0,
      isCouponTime: 1,
      isReceiveTime: 0,
      modals: false,
      datetime1: [],
      datetime2: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
  },
  created() {
    this.getCategoryList();
    if (this.$route.params.id) {
      this.getCouponDetail();
    }
  },
  methods: {
    // 品类
    getCategoryList() {
      couponCategoryApi(1).then(async (res) => {
        res.data.forEach((val) => {
          val.cate_name = `${val.html}${val.cate_name}`;
        });
        this.categoryList = res.data;
      });
    },
    // 优惠券
    getCouponDetail() {
      couponDetailApi(this.$route.params.id)
        .then((res) => {
          let data = res.data;
          this.formData.coupon_title = data.coupon_title;
          this.formData.type = data.type;
          this.formData.category_id = data.category_id;
          this.formData.coupon_price = parseFloat(data.coupon_price);
          this.formData.use_min_price = parseFloat(data.use_min_price);
          if (this.formData.use_min_price) {
            this.isMinPrice = 1;
          }
          this.formData.coupon_time = data.coupon_time;
          this.formData.receive_type = data.receive_type;
          this.formData.is_permanent = data.is_permanent;
          this.formData.status = data.status;
          this.formData.product_id = data.product_id;
          this.formData.start_time = data.start_time;
          this.formData.end_time = data.end_time;
          this.formData.total_count = data.total_count;
          this.formData.sort = data.sort;
          this.formData.receive_limit = data.receive_limit;
          if ('productInfo' in data) {
            this.productList = data.productInfo;
          }
          if (!data.coupon_time) {
            this.isCouponTime = 0;
            this.datetime1 = [data.start_use_time * 1000, data.end_use_time * 1000];
            this.formData.start_use_time = this.makeDate(data.start_use_time * 1000);
            this.formData.end_use_time = this.makeDate(data.end_use_time * 1000);
          }
          if (data.start_time) {
            this.isReceiveTime = 1;
            this.datetime2 = [data.start_time * 1000, data.end_time * 1000];
            this.formData.start_time = this.makeDate(data.start_time * 1000);
            this.formData.end_time = this.makeDate(data.end_time * 1000);
          }
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    makeDate(data) {
      let date = new Date(data);
      let YY = date.getFullYear() + '-';
      let MM = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
      let DD = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
      let hh = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
      let mm = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
      let ss = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
      return YY + MM + DD + ' ' + hh + mm + ss;
    },
    // 创建
    save() {
      if (!this.formData.coupon_title) {
        return this.$Message.error('请输入优惠券名称');
      }
      if (this.formData.type === 2) {
        if (!this.formData.product_id) {
          return this.$Message.error('请选择商品');
        }
      }
      if (this.formData.type === 1) {
        if (!this.formData.category_id) {
          return this.$Message.error('请选择品类');
        }
      }
      if (this.formData.coupon_price <= 0) {
        return this.$Message.error('优惠券面值不能小于0');
      }
      if (!this.isMinPrice) {
        this.formData.use_min_price = 0;
      } else {
        if (this.formData.use_min_price < 1) {
          return this.$Message.error('优惠券最低消费不能小于0');
        }
      }
      if (this.isCouponTime) {
        this.formData.start_use_time = 0;
        this.formData.end_use_time = 0;
        if (this.formData.coupon_time < 1) {
          return this.$Message.error('使用有效期限不能小于1天');
        }
      } else {
        this.formData.coupon_time = 0;
        if (!this.formData.start_use_time) {
          return this.$Message.error('请选择使用有效期限');
        }
      }
      if (this.isReceiveTime) {
        if (!this.formData.start_time) {
          return this.$Message.error('请选择领取时间');
        }
      } else {
        this.formData.start_time = 0;
        this.formData.end_time = 0;
      }
      if (this.formData.receive_type == 2 || this.formData.receive_type == 3) {
        this.formData.is_permanent = 1;
      }
      if (this.formData.is_permanent) {
        this.formData.total_count = 0;
      } else {
        if (this.formData.total_count < 1) {
          return this.$Message.error('发布数量不能小于1');
        }
      }
      if (this.formData.receive_limit < 1) {
        return this.$Message.error('每个用户可以领取数量不能小于1');
      }
      if (this.formData.type == 0) {
        this.formData.product_id = '';
        this.formData.category_id = '';
        this.productList = [];
      } else if (this.formData.type == 1) {
        this.formData.product_id = '';
        this.productList = [];
      } else if (this.formData.type == 2) {
        this.formData.category_id = '';
      }
      if (this.disabled) return;
      this.disabled = true;
      couponSaveApi(this.formData)
        .then((res) => {
          this.$Message.success(res.msg);
          setTimeout(() => {
            this.disabled = false;
            this.$router.push({
              path: '/admin/marketing/store_coupon_issue/index',
            });
          }, 1000);
        })
        .catch((err) => {
          this.disabled = false;
          this.$Message.error(err.msg);
        });
    },
    // 使用有效期--时间段
    dateChange(time) {
      this.formData.start_use_time = time[0];
      this.formData.end_use_time = time[1];
    },
    // 限时
    timeChange(time) {
      this.formData.start_time = time[0];
      this.formData.end_time = time[1];
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
    },
    // 选择的商品
    getProductId(productList) {
      this.modals = false;
      this.productList = this.unique(this.productList.concat(productList));
      this.formData.product_id = '';
      this.productList.forEach((value) => {
        if (this.formData.product_id) {
          this.formData.product_id += `,${value.product_id}`;
        } else {
          this.formData.product_id += `${value.product_id}`;
        }
      });
    },
    cancel() {
      this.modals = false;
    },
    // 删除商品
    remove(productId) {
      for (let index = 0; index < this.productList.length; index++) {
        if (this.productList[index].product_id == productId) {
          this.productList.splice(index, 1);
        }
      }
      this.formData.product_id = '';
      this.productList.forEach((value) => {
        if (this.formData.product_id) {
          this.formData.product_id += `,${value.product_id}`;
        } else {
          this.formData.product_id += `${value.product_id}`;
        }
      });
    },
  },
};
</script>

<style scoped>
.info {
  color: #888;
  font-size: 12px;
}
.ivu-form >>> .ivu-form-item-label {
  font-weight: 700;
  font-size: 14px !important;
}

.ivu-input-wrapper {
  width: 320px;
}

.ivu-radio-wrapper {
  margin-right: 30px;
  font-size: 14px !important;
}

.ivu-radio-wrapper >>> .ivu-radio {
  margin-right: 10px;
}

.ivu-input-number {
  width: 160px;
}

.ivu-date-picker {
  width: 320px;
}

.ivu-icon-ios-camera-outline {
  width: 58px;
  height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background-color: rgba(0, 0, 0, 0.02);
  line-height: 58px;
  cursor: pointer;
  vertical-align: middle;
}

.upload-list {
  width: 58px;
  height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;
  vertical-align: middle;
}

.upload-list img {
  display: block;
  width: 100%;
  height: 100%;
}

.ivu-icon-ios-close-circle {
  position: absolute;
  top: 0;
  right: 0;
  transform: translate(50%, -50%);
}
</style>
