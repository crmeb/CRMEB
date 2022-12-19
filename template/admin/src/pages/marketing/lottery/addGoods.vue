<template>
  <div>
    <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="90">
      <FormItem label="奖品" prop="type">
        <RadioGroup v-model="formValidate.type">
          <Radio :label="1">未中奖</Radio>
          <Radio :label="5">优惠券</Radio>
          <Radio :label="2">积分</Radio>
          <Radio :label="6">商品</Radio>
          <Radio :label="4">红包</Radio>
          <Radio :label="3">余额</Radio>
        </RadioGroup>
      </FormItem>
      <FormItem label="赠送优惠券：" v-if="formValidate.type == 5">
        <div v-if="couponName.length" class="mb20">
          <Tag closable v-for="(item, index) in couponName" :key="index" @on-close="handleClose(item)">{{
            item.title
          }}</Tag>
        </div>
        <Button type="primary" @click="addCoupon" v-if="!couponName.length">添加优惠券</Button>
      </FormItem>
      <FormItem
        :label="[3, 4].includes(formValidate.type) ? '金额信息' : '积分数量'"
        prop="num"
        v-if="[2, 3, 4].includes(formValidate.type)"
      >
        <InputNumber
          v-model="formValidate.num"
          placeholder="请输入金额数量"
          :max="formValidate.type == 4 ? 999 : 99999"
          :min="1"
          style="width: 300px"
        ></InputNumber>
        <div class="ml100 grey">
          {{
            formValidate.type == 3
              ? '用户领取余额后会自动到账余额账户'
              : formValidate.type == 4
              ? '用户领取红包后会自动到账微信零钱，添加此奖品需开通微信支付,并且账户中金额不能小于1元'
              : ''
          }}
        </div>
      </FormItem>
      <FormItem v-if="formValidate.type == 6" label="商品" prop="goods_image">
        <template v-if="formValidate.goods_image">
          <div class="upload-list">
            <img :src="formValidate.goods_image" />
            <Icon type="ios-close-circle" size="16" @click="removeGoods()" />
          </div>
        </template>
        <div v-else class="upLoad pictrueTab acea-row row-center-wrapper">
          <Icon type="ios-camera-outline" size="26" @click="modals = true" />
        </div>
      </FormItem>
      <FormItem label="奖品名称" prop="name">
        <Input v-model="formValidate.name" :maxlength="10" placeholder="请输入奖品名称" style="width: 300px"></Input>
      </FormItem>
      <FormItem label="奖品图片" prop="image">
        <template v-if="formValidate.image">
          <div class="upload-list">
            <img :src="formValidate.image" />
            <Icon type="ios-close-circle" size="16" @click="remove()" />
          </div>
        </template>
        <div v-else class="upLoad pictrueTab acea-row row-center-wrapper">
          <Icon type="ios-camera-outline" size="26" @click="modalPic = true" />
        </div>
        <!-- <div class="info">选择商品</div> -->
      </FormItem>
      <FormItem label="奖品数量" prop="total">
        <InputNumber
          v-model="formValidate.total"
          placeholder="请输入奖品数量"
          :max="99999"
          :min="0"
          :precision="0"
          style="width: 300px"
        ></InputNumber>
      </FormItem>
      <FormItem label="奖品权重" prop="chance">
        <InputNumber
          v-model="formValidate.chance"
          placeholder="请输入奖品权重"
          :max="100"
          :min="1"
          :precision="0"
          style="width: 300px"
        ></InputNumber>
      </FormItem>
      <FormItem label="提示语" prop="prompt">
        <Input v-model="formValidate.prompt" :maxlength="15" placeholder="请输入提示语" style="width: 300px"></Input>
      </FormItem>
      <FormItem>
        <Button type="primary" @click="handleSubmit('formValidate')">提交</Button>
      </FormItem>
    </Form>
    <!-- 上传图片-->
    <Modal
      v-model="modalPic"
      width="950px"
      scrollable
      footer-hide
      closable
      title="上传图片"
      :mask-closable="false"
      :z-index="1"
    >
      <uploadPictures :isChoice="isChoice" @getPic="getPic" v-if="modalPic"></uploadPictures>
    </Modal>
    <Modal
      v-model="modals"
      title="商品列表"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <goods-list ref="goodslist" v-if="modals" @getProductId="getProductId"></goods-list>
    </Modal>
    <coupon-list ref="couponTemplates" :luckDraw="true" @getCouponId="getCouponId"></coupon-list>
    <!--<coupon-list-->
    <!--ref="couponTemplates"-->
    <!--@nameId="nameId"-->
    <!--:updateIds="updateIds"-->
    <!--:updateName="updateName"-->
    <!--&gt;</coupon-list>-->
  </div>
</template>

<script>
import couponList from '@/components/couponList';
import uploadPictures from '@/components/uploadPictures';
import goodsList from '@/components/goodsList/index';
import freightTemplate from '@/components/freightTemplate';
export default {
  components: { uploadPictures, goodsList, freightTemplate, couponList },
  data() {
    return {
      modalPic: false,
      modals: false,
      isChoice: '单选',
      updateIds: [],
      updateName: [],
      goodsData: {
        pic: '',
        product_id: '',
        img: '',
        coverImg: '',
      },
      formValidate: {
        type: 5, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
        name: '', //活动名称
        num: 0, //奖品数量
        image: '', //奖品图片
        chance: 1, //中奖权重
        product_id: 0, //商品id
        coupon_id: 0, //优惠券id
        total: 0, //奖品数量
        prompt: '', //提示语
        goods_image: '', //自用商品图
        coupon_title: '', //优惠券名称
      },
      ruleValidate: {
        name: [
          {
            required: true,
            message: '商品名称',
            trigger: 'blur',
          },
        ],
        goods_image: [
          {
            required: true,
            message: '请添加商品',
            trigger: 'blur',
          },
        ],
        num: [
          {
            required: true,
            type: 'number',
            message: '请输入金额数量',
            trigger: 'blur',
          },
        ],
        chance: [
          {
            required: true,
            type: 'number',
            message: '请输入商品权重',
            trigger: 'blur',
          },
        ],
        image: [
          {
            required: true,
            message: '请选择奖品图片',
            trigger: 'blur',
          },
        ],
        prompt: [
          {
            required: true,
            message: '请输入提示语',
            trigger: 'blur',
          },
        ],
      },
      couponName: [],
    };
  },
  props: {
    editData: {
      type: Object,
      default: () => {},
    },
  },
  watch: {
    editData(data) {},
  },
  mounted() {
    let keys = Object.keys(this.editData);
    keys.forEach((item) => {
      this.formValidate[item] = this.editData[item];
      if (item === 'coupon_title') {
        this.couponName.push({
          title: this.editData[item],
          id: this.editData.coupon_id,
        });
      }
    });
  },
  methods: {
    getCouponId(e) {
      this.formValidate.coupon_id = e.id;
      this.formValidate.coupon_title = e.coupon_title;
      let couponName = [];
      couponName.push(e);
      this.couponName = couponName;
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.$emit('addGoodsData', this.formValidate);
          this.$Message.success('添加成功');
        } else {
          this.$Message.warning('请完善数据');
        }
      });
    },
    // 获取单张图片信息
    getPic(pc) {
      this.formValidate.image = pc.att_dir;
      this.modalPic = false;
    },
    // 点击商品图
    modalPicTap() {
      this.modalPic = true;
    },
    cancel() {
      this.modals = false;
    },
    // 选择的商品
    getProductId(productList) {
      // if (productList.length > 1) {
      //   this.$Message.warning("最多添加一个商品");
      //   return;
      // }
      this.formValidate.product_id = productList.id;
      this.formValidate.goods_image = productList.image;
      this.modals = false;
      // productList.forEach((value) => {
      //   this.formValidate.product_id = value.product_id;
      //   this.formValidate.goods_image = value.image;
      // });
    },
    removeGoods() {
      this.formValidate.product_id = '';
      this.formValidate.goods_image = '';
    },
    remove() {
      this.formValidate.image = '';
    },
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    handleClose(name) {
      this.couponName.splice(0, 1);
      this.formValidate.coupon_id = 0;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
  },
};
</script>

<style scoped lang="stylus">
.pictrueBox {
  display: inline-block;
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

.upload-list {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
  position: relative;
}

.upload-list img {
  display: block;
  width: 100%;
  height: 100%;
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

.grey {
  color: #999;
}
</style>
