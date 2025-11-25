<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑优惠券' : '添加优惠券'"
      :backUrl="$routeProStr + '/marketing/store_coupon_issue/index'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form :model="formData" label-width="160px">
        <el-form-item label="优惠券名称：">
          <el-input
            v-model="formData.coupon_title"
            maxlength="18"
            show-word-limit
            placeholder="请输入优惠券名称"
            class="content_width"
          ></el-input>
        </el-form-item>
        <el-form-item label="优惠券面值：">
          <el-input-number
            :controls="false"
            :min="1"
            :max="9999999999"
            v-model="formData.coupon_price"
            class="content_width input-number-unit-class"
            class-unit="元"
            :disabled="isEdit"
          ></el-input-number>
        </el-form-item>
        <el-form-item label="用户类型：">
          <el-radio-group v-model="formData.user_type" :disabled="isEdit" @input="changeUserType">
            <el-radio :label="1">普通用户</el-radio>
            <el-radio :label="2">付费会员用户</el-radio>
          </el-radio-group>
          <div class="tip">
            普通用户：所有用户都能获取到的优惠券；<br />
            付费会员用户：仅付费会员才能领取的优惠券；
          </div>
        </el-form-item>
        <el-form-item label="发送方式：" v-show="formData.user_type == 1">
          <el-radio-group v-model="formData.receive_type" :disabled="isEdit">
            <el-radio :label="1">用户领取</el-radio>
            <el-radio :label="3">系统赠送</el-radio>
          </el-radio-group>
          <div class="tip">
            用户领取：用户需要手动领取优惠券；<br />
            系统赠送：1.后台发放指定用户。2.添加到商品里面用户购买该商品获得。3.设置新人礼页面新用户注册赠送优惠券；
          </div>
        </el-form-item>
        <el-form-item label="优惠劵类型：">
          <el-radio-group v-model="formData.type" :disabled="isEdit">
            <el-radio :label="0">通用券</el-radio>
            <el-radio :label="1">品类券</el-radio>
            <el-radio :label="2">商品券</el-radio>
            <!--                        <el-radio :label="3">会员券</el-radio>-->
          </el-radio-group>
        </el-form-item>
        <el-form-item v-show="formData.type === 2">
          <template>
            <div class="acea-row">
              <div v-for="(item, index) in productList" :key="index" class="pictrue">
                <img v-lazy="item.image" />
                <i  v-if="formData.type == 2 && !formData.id" class="el-icon-error btndel" v-db-click @click="remove(item.product_id)"></i>
              </div>
              <div v-if="formData.type == 2 && !formData.id" class="upLoad acea-row row-center-wrapper" v-db-click @click="modals = true">
                <i class="el-icon-goods" style="font-size: 24px"></i>
              </div>
            </div>
          </template>
        </el-form-item>
        <el-form-item v-show="formData.type === 1">
          <el-cascader
            v-model="formData.category_id"
            size="small"
            :options="categoryList"
            :props="{ multiple: true, emitPath: false, checkStrictly: true }"
            clearable
            style="width: 320px"
            :disabled="isEdit"
          ></el-cascader>
          <div class="info">选择商品的品类</div>
        </el-form-item>
        <el-form-item label="使用门槛：">
          <el-radio-group v-model="isMinPrice" :disabled="isEdit">
            <el-radio :label="0">无门槛</el-radio>
            <el-radio :label="1">有门槛</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="isMinPrice">
          <el-input-number
            :controls="false"
            :min="0"
            :max="9999999999"
            v-model="formData.use_min_price"
            class="content_width input-number-unit-class"
            :disabled="isEdit"
            class-unit="元"
          ></el-input-number>
          <div class="info">填写优惠券的最低消费金额</div>
        </el-form-item>
        <el-form-item label="有效期：">
          <el-radio-group v-model="isCouponTime" :disabled="isEdit">
            <el-radio :label="1">天数</el-radio>
            <el-radio :label="0">时间段</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-show="isCouponTime" label="">
          <el-input-number
            :controls="false"
            :min="0"
            v-model="formData.coupon_time"
            :precision="0"
            class="content_width input-number-unit-class"
            :disabled="isEdit"
            class-unit="天"
          ></el-input-number>
          <div class="info">领取后多少天内有效</div>
        </el-form-item>
        <el-form-item v-show="!isCouponTime" label="">
          <el-date-picker
            v-model="datetime1"
            :disabled="isEdit"
            clearable
            :editable="false"
            type="datetimerange"
            value-format="yyyy-MM-dd HH:mm:ss"
            style="width: 380px"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            @change="dateChange"
          ></el-date-picker>
        </el-form-item>

        <el-form-item label="领取时间：" v-if="formData.receive_type != 2 && formData.receive_type != 3">
          <el-radio-group v-model="isReceiveTime" :disabled="isEdit">
            <el-radio :label="1">限时</el-radio>
            <el-radio :label="0">不限时</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-show="isReceiveTime" label="">
          <el-date-picker
            clearable
            v-model="datetime2"
            type="datetimerange"
            value-format="yyyy/MM/dd HH:mm:ss"
            style="width: 380px"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            @change="timeChange"
            :disabled="isEdit"
          ></el-date-picker>
        </el-form-item>
        <el-form-item label="优惠券发布数量：" v-show="formData.receive_type == 1">
          <el-radio-group v-model="formData.is_permanent" :disabled="isEdit">
            <el-radio :label="0">限量</el-radio>
            <el-radio :label="1">不限量</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-show="!formData.is_permanent" label="">
          <el-input-number
            :controls="false"
            :min="isEdit ? formData.total_count : 1"
            :max="9999999999"
            v-model="formData.total_count"
            :precision="0"
            class="content_width input-number-unit-class"
            class-unit="张"
          ></el-input-number>
          <div class="info">填写优惠券的发布数量</div>
        </el-form-item>
        <el-form-item label="用户领取数量：" v-if="formData.receive_type != 2 && formData.receive_type != 3">
          <el-input-number
            :controls="false"
            :min="isEdit ? formData.receive_limit : 1"
            :max="9999999999"
            v-model="formData.receive_limit"
            :precision="0"
            class="content_width input-number-unit-class"
            class-unit="张"
          ></el-input-number>
          <div class="info">填写每个用户可以领取多少张</div>
        </el-form-item>
        <el-form-item label="状态：">
          <el-radio-group v-model="formData.status">
            <el-radio :label="1">开启</el-radio>
            <el-radio :label="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="save" :disabled="disabled">{{
            isEdit ? '立即保存' : '立即创建'
          }}</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" v-if="modals" :ischeckbox="true" @getProductId="getProductId"></goods-list>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import { couponSaveApi, couponDetailApi } from '@/api/marketing';
import { cascaderListApi } from '@/api/product';
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
        coupon_time: 0,
        start_use_time: 0,
        end_use_time: 0,
        start_time: 0,
        end_time: 0,
        user_type: 1,
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
      datetime1: ['2023-10-18 00:00:00', '2023-11-22 00:00:00'],
      datetime2: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    isEdit() {
      return !!this.$route.params.edit;
    },
  },
  created() {
    this.getCategoryList();
    if (this.$route.params.id) {
      this.formData.id = (this.isEdit && Number(this.$route.params.id)) || 0;
      this.getCouponDetail();
    }
  },
  methods: {
    changeUserType() {
      if (this.formData.user_type == 2) {
        this.formData.receive_type = 1;
      }
    },
    // 品类
    getCategoryList() {
      cascaderListApi(1).then(async (res) => {
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
          this.formData.user_type = data.user_type;
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
            this.datetime1 = [this.makeDate(data.start_use_time * 1000), this.makeDate(data.end_use_time * 1000)];
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
          this.$message.error(err.msg);
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
        return this.$message.error('请输入优惠券名称');
      }
      if (this.formData.type === 2) {
        if (!this.formData.product_id) {
          return this.$message.error('请选择商品');
        }
      }
      if (this.formData.type === 1) {
        if (!this.formData.category_id) {
          return this.$message.error('请选择品类');
        }
      }
      if (this.formData.coupon_price <= 0) {
        return this.$message.error('优惠券面值不能小于0');
      }
      if (!this.isMinPrice) {
        this.formData.use_min_price = 0;
      } else {
        if (this.formData.use_min_price < 1) {
          return this.$message.error('优惠券最低消费不能小于0');
        }
      }
      if (this.isCouponTime) {
        this.formData.start_use_time = 0;
        this.formData.end_use_time = 0;
        if (this.formData.coupon_time < 1) {
          return this.$message.error('使用有效期限不能小于1天');
        }
      } else {
        this.formData.coupon_time = 0;
        if (!this.formData.start_use_time) {
          return this.$message.error('请选择使用有效期限');
        }
      }
      if (this.isReceiveTime) {
        if (!this.formData.start_time) {
          return this.$message.error('请选择领取时间');
        }
      } else {
        this.formData.start_time = 0;
        this.formData.end_time = 0;
      }
      // if (this.formData.receive_type == 2 || this.formData.receive_type == 3) {
      //   this.formData.is_permanent = 1;
      // }
      if (this.formData.is_permanent) {
        this.formData.total_count = 0;
      } else {
        if (this.formData.total_count < 1) {
          return this.$message.error('发布数量不能小于1');
        }
      }
      if (this.formData.receive_limit < 1) {
        return this.$message.error('每个用户可以领取数量不能小于1');
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
          this.$message.success(res.msg);
          setTimeout(() => {
            this.disabled = false;
            this.$router.push({
              path: this.$routeProStr + '/marketing/store_coupon_issue/index',
            });
          }, 1000);
        })
        .catch((err) => {
          this.disabled = false;
          this.$message.error(err.msg);
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

<style scoped lang="scss">
.content_width {
  width: 414px;
}

.info {
  color: #888;
  font-size: 12px;
}

.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }

  .btndel {
    position: absolute;
    z-index: 1;
    width: 20px !important;
    height: 20px !important;
    left: 46px;
    top: -4px;
  }
}

.upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}

.ivu-icon-ios-close-circle {
  position: absolute;
  top: 0;
  right: 0;
  transform: translate(50%, -50%);
}

.tip {
  color: #888;
  font-size: 12px;
  line-height: 16px;
}
</style>
