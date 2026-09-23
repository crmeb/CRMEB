<template>
  <div class="product-add">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="tab-container">
        <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
          <el-tab-pane label="基本信息" name="basic">
            <basicInfo
              ref="basicInfoRef"
              :productId="productId"
              :productType="'coupon'"
              @saveSuccess="saveSuccess"
            ></basicInfo>
          </el-tab-pane>
          <el-tab-pane label="优惠券设置" name="coupon">
            <couponSetting ref="couponSettingRef" :productId="productId" @saveSuccess="saveSuccess"></couponSetting>
          </el-tab-pane>
          <el-tab-pane label="价格与佣金" name="price">
            <priceCommission
              ref="priceCommissionRef"
              :productId="productId"
              @saveSuccess="saveSuccess"
            ></priceCommission>
          </el-tab-pane>
          <el-tab-pane label="商品详情" name="detail">
            <productDetail ref="productDetailRef" :productId="productId" @saveSuccess="saveSuccess"></productDetail>
          </el-tab-pane>
          <el-tab-pane label="营销设置" name="marketing">
            <marketingSetting
              ref="marketingSettingRef"
              :productId="productId"
              @saveSuccess="saveSuccess"
            ></marketingSetting>
          </el-tab-pane>
          <el-tab-pane label="其他设置" name="other">
            <otherSetting ref="otherSettingRef" :productId="productId" @saveSuccess="saveSuccess"></otherSetting>
          </el-tab-pane>
        </el-tabs>
      </div>
      <div class="bottom-btn-box">
        <el-button type="primary" class="save-btn" @click="saveProduct">保存商品</el-button>
        <el-button @click="goBack">返回列表</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import basicInfo from '../../../../pages/product/productAdd/components/BasicInfo.vue';
import priceCommission from '../../../../pages/product/productAdd/components/PriceCommission.vue';
import productDetail from '../../../../pages/product/productAdd/components/ProductDetail.vue';
import marketingSetting from '../../../../pages/product/productAdd/components/MarketingSetting.vue';
import otherSetting from '../../../../pages/product/productAdd/components/OtherSetting.vue';
import couponSetting from './components/CouponSetting.vue';
import { couponProductSave, couponProductUpdate } from '@/pages/plugin/coupon/couponProduct';
import setting from '@/setting';

defineOptions({ name: 'couponProductAdd' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;

const basicInfoRef = ref(null);
const couponSettingRef = ref(null);
const priceCommissionRef = ref(null);
const productDetailRef = ref(null);
const marketingSettingRef = ref(null);
const otherSettingRef = ref(null);

const activeName = ref('basic');
const productId = ref(0);

const id = route.params.id;
if (id) {
  productId.value = parseInt(id);
}

function handleClick(tab) {}
async function saveProduct() {
  const basicData = basicInfoRef.value.getFormData();
  const couponData = couponSettingRef.value.getFormData();
  const priceData = priceCommissionRef.value.getFormData();
  const detailData = productDetailRef.value.getFormData();
  const marketingData = marketingSettingRef.value.getFormData();
  const otherData = otherSettingRef.value.getFormData();

  const formData = {
    ...basicData,
    ...couponData,
    ...priceData,
    ...detailData,
    ...marketingData,
    ...otherData,
    product_type: 'coupon',
  };

  try {
    if (productId.value) {
      await couponProductUpdate(productId.value, formData);
      ElMessage.success('修改成功');
    } else {
      await couponProductSave(formData);
      ElMessage.success('添加成功');
    }
    goBack();
  } catch (error) {
    ElMessage.error(error.msg || '操作失败');
  }
}
function saveSuccess() {
  ElMessage.success('保存成功');
}
function goBack() {
  router.push({ path: routePre + '/product/coupon/list' });
}
</script>

<style scoped>
.product-add {
  .bottom-btn-box {
    text-align: center;
    padding: 20px 0;
    border-top: 1px solid #ebf0f5;
    .save-btn {
      width: 120px;
    }
  }
}
</style>
