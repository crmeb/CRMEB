<template>
  <div>
    <el-form ref="formRef" :model="formValidate" :rules="ruleValidate" label-width="90px">
      <el-form-item label="奖品：" prop="type">
        <el-radio-group v-model="formValidate.type">
          <el-radio :label="1" :value="1">未中奖</el-radio>
          <el-radio :label="5" :value="5">优惠券</el-radio>
          <el-radio :label="2" :value="2">积分</el-radio>
          <el-radio :label="6" :value="6">商品</el-radio>
          <el-radio :label="4" :value="4">红包</el-radio>
          <el-radio :label="3" :value="3">余额</el-radio>
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
    <el-dialog v-model="modalPic" :modal="false" width="1024px" title="上传图片" :close-on-click-modal="false">
      <uploadPictures :isChoice="isChoice" @getPic="getPic" v-if="modalPic"></uploadPictures>
    </el-dialog>
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslistRef" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <coupon-list ref="couponTemplatesRef" :luckDraw="true" @getCouponId="getCouponId"></coupon-list>
    <!--<coupon-list-->
    <!--ref="couponTemplates"-->
    <!--@nameId="nameId"-->
    <!--:updateIds="updateIds"-->
    <!--:updateName="updateName"-->
    <!--&gt;</coupon-list>-->
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import couponList from '@/components/couponList';
import uploadPictures from '@/components/uploadPictures';
import goodsList from '@/components/goodsList/index';

defineOptions({ name: 'lotteryAddGoods' });

const props = defineProps({
  editData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['addGoodsData']);

const formRef = ref(null);
const goodslistRef = ref(null);
const couponTemplatesRef = ref(null);

const modalPic = ref(false);
const modals = ref(false);
const isChoice = ref('单选');
const couponName = ref([]);
const formValidate = reactive({
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
});
const ruleValidate = {
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
};

onMounted(() => {
  let keys = Object.keys(props.editData || {});
  keys.forEach((item) => {
    formValidate[item] = props.editData[item];
    if (item === 'coupon_title' && props.editData[item]) {
      couponName.value.push({
        title: props.editData[item],
        id: props.editData.coupon_id,
      });
    }
  });
});

// 选择商品
function changeGoods() {
  modals.value = true;
  nextTick(() => {
    // Element Plus 弹窗内容首次打开才渲染，等 nextTick 后再驱动子组件刷新列表
    if (goodslistRef.value) {
      goodslistRef.value.getList();
      goodslistRef.value.goodsCategory();
    }
  });
}
function getCouponId(e) {
  formValidate.coupon_id = e.id;
  formValidate.coupon_title = e.coupon_title;
  let list = [];
  list.push(e);
  couponName.value = list;
}
function handleSubmit() {
  formRef.value.validate((valid) => {
    if (valid) {
      emit('addGoodsData', formValidate);
      ElMessage.success('添加成功');
    } else {
      ElMessage.warning('请完善数据');
    }
  });
}
// 获取单张图片信息
function getPic(pc) {
  formValidate.image = pc.att_dir;
  modalPic.value = false;
}
// 点击商品图
function modalPicTap() {
  modalPic.value = true;
}
function cancel() {
  modals.value = false;
}
// 选择的商品
function getProductId(productList) {
  // if (productList.length > 1) {
  //   ElMessage.warning("最多添加一个商品");
  //   return;
  // }
  formValidate.product_id = productList.id;
  formValidate.goods_image = productList.image;
  modals.value = false;
  // productList.forEach((value) => {
  //   formValidate.product_id = value.product_id;
  //   formValidate.goods_image = value.image;
  // });
}
function removeGoods() {
  formValidate.product_id = '';
  formValidate.goods_image = '';
}
function remove() {
  formValidate.image = '';
}
// 添加优惠券
function addCoupon() {
  couponTemplatesRef.value.isTemplate = true;
  couponTemplatesRef.value.tableList();
}
function handleClose(name) {
  couponName.value.splice(0, 1);
  formValidate.coupon_id = 0;
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}

// 父页面（create.vue）通过 ref 调用 handleSubmit 触发校验提交
defineExpose({ handleSubmit });
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
