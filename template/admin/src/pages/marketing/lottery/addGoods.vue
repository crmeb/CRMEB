<template>
  <div>
    <el-form ref="formValidate" :model="formValidate" :rules="ruleValidate" label-width="90px">
      <el-form-item label="奖品：" prop="type">
        <el-radio-group v-model="formValidate.type">
          <el-radio :label="1">未中奖</el-radio>
          <el-radio :label="5">优惠券</el-radio>
          <el-radio :label="2">积分</el-radio>
          <el-radio :label="6">商品</el-radio>
          <el-radio :label="4">红包</el-radio>
          <el-radio :label="3">余额</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="赠送优惠券：" v-if="formValidate.type == 5">
        <div v-if="couponName.length" class="mb20">
          <el-tag closable v-for="(item, index) in couponName" :key="index" @close="handleClose(item)">{{
            item.title
          }}</el-tag>
        </div>
        <el-button type="primary" v-db-click @click="addCoupon" v-if="!couponName.length">添加优惠券</el-button>
      </el-form-item>
      <el-form-item
        :label="[3, 4].includes(formValidate.type) ? '金额信息' : '积分数量'"
        prop="num"
        v-if="[2, 3, 4].includes(formValidate.type)"
      >
        <el-input-number
          :controls="false"
          v-model="formValidate.num"
          placeholder="请输入金额数量"
          :max="9999999999"
          :min="0.1"
          style="width: 300px"
        ></el-input-number>
        <div class="ml100 grey">
          {{
            formValidate.type == 3
              ? '用户领取余额后会自动到账余额账户'
              : formValidate.type == 4
              ? '用户抽到之后需要在抽奖列表中手动领取，需要开通微信支付的商家转账功能，金额不能小于0.1元'
              : ''
          }}
        </div>
      </el-form-item>
      <el-form-item v-if="formValidate.type == 6" label="商品：" prop="goods_image">
        <template v-if="formValidate.goods_image">
          <div class="upload-list">
            <img :src="formValidate.goods_image" />
            <i class="el-icon-error" v-db-click @click="removeGoods()" style="font-size: 16px"></i>
          </div>
        </template>
        <div v-else class="upLoad pictrueTab acea-row row-center-wrapper" v-db-click @click="changeGoods">
          <i class="el-icon-picture-outline" style="font-size: 24px"></i>
        </div>
      </el-form-item>
      <el-form-item label="奖品名称：" prop="name">
        <el-input
          v-model="formValidate.name"
          :maxlength="10"
          placeholder="请输入奖品名称"
          style="width: 300px"
        ></el-input>
      </el-form-item>
      <el-form-item label="奖品图片：" prop="image">
        <template v-if="formValidate.image">
          <div class="upload-list">
            <img :src="formValidate.image" />
            <i class="el-icon-error" v-db-click @click="remove()" style="font-size: 16px"></i>
          </div>
        </template>
        <div v-else class="upLoad pictrueTab acea-row row-center-wrapper">
          <i class="el-icon-picture-outline" style="font-size: 24px" v-db-click @click="modalPic = true"></i>
        </div>
        <!-- <div class="info">选择商品</div> -->
      </el-form-item>
      <el-form-item label="奖品数量：" prop="total">
        <el-input-number
          :controls="false"
          v-model="formValidate.total"
          placeholder="请输入奖品数量"
          :max="9999999999"
          :min="0"
          :precision="0"
          style="width: 300px"
        ></el-input-number>
      </el-form-item>
      <el-form-item label="奖品概率(%)：" prop="percent">
        <el-input-number
          :controls="false"
          v-model="formValidate.percent"
          placeholder="请输入奖品概率"
          :max="100"
          :min="0"
          :precision="2"
          style="width: 300px"
        ></el-input-number>
      </el-form-item>
      <el-form-item label="提示语：" prop="prompt">
        <el-input
          v-model="formValidate.prompt"
          :maxlength="15"
          placeholder="请输入提示语"
          style="width: 300px"
        ></el-input>
      </el-form-item>
      <!-- <el-form-item>
        <el-button type="primary" v-db-click @click="handleSubmit('formValidate')">提交</el-button>
      </el-form-item> -->
    </el-form>
    <!-- 上传图片-->
    <el-dialog :visible.sync="modalPic" :modal="false" width="1024px" title="上传图片" :close-on-click-modal="false">
      <uploadPictures :isChoice="isChoice" @getPic="getPic" v-if="modalPic"></uploadPictures>
    </el-dialog>
    <el-dialog :visible.sync="modals" :modal="false" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" @getProductId="getProductId"></goods-list>
    </el-dialog>
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
      if (item === 'coupon_title' && this.editData[item]) {
        this.couponName.push({
          title: this.editData[item],
          id: this.editData.coupon_id,
        });
      }
    });
  },
  methods: {
    // 选择商品
    changeGoods() {
      this.modals = true;
      this.$refs.goodslist.getList();
      this.$refs.goodslist.goodsCategory();
    },
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
          this.$message.success('添加成功');
        } else {
          this.$message.warning('请完善数据');
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
      //   this.$message.warning("最多添加一个商品");
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

<style lang="scss" scoped>
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
  .el-icon-error {
    position: absolute;
    right: -8px;
    top: -8px;
  }
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
