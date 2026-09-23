<template>
  <div class="cardkey-setting">
    <el-form label-width="120px" label-position="right">
      <el-form-item label="卡密类型：" prop="card_type">
        <el-radio-group v-model="formValidate.card_type">
          <el-radio label="single" value="single">单卡密</el-radio>
          <el-radio label="combo" value="combo">组合卡密</el-radio>
        </el-radio-group>
        <div class="tip">单卡密：一个卡密对应一个密码；组合卡密：卡号 + 密码组合</div>
      </el-form-item>
      <el-form-item label="卡密有效期：" prop="valid_days">
        <el-input-number v-model="formValidate.valid_days" :min="0" :precision="0" :controls="false" />
        <span class="tip"> 天（0 表示永久有效）</span>
      </el-form-item>
      <el-form-item label="使用说明：" prop="usage_tip">
        <el-input
          type="textarea"
          :rows="5"
          v-model="formValidate.usage_tip"
          placeholder="填写卡密的使用方法说明，下单后展示给买家"
        ></el-input>
      </el-form-item>
      <el-form-item label="卡密库存：">
        <span class="tip">当前可用卡密 {{ cardCount }} 条，请在商品列表页导入卡密内容</span>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import request from '@/libs/request';

defineOptions({ name: 'cardkeySetting' });

const props = defineProps({
  formValidate: {
    type: Object,
    required: true,
  },
  // 当前商品ID，用于展示卡密库存数量
  productId: {
    type: Number,
    default: 0,
  },
});

const cardCount = ref(0);

// 加载当前商品的卡密库存数量（编辑时展示）
async function loadCardCount() {
  try {
    const res = await request({
      url: `cardkey/product/${props.productId}/card_list`,
      method: 'get',
    });
    cardCount.value = Array.isArray(res.data) ? res.data.length : 0;
  } catch (e) {
    cardCount.value = 0;
  }
}

// created：编辑时加载卡密库存
if (props.productId) {
  loadCardCount();
}
</script>

<style scoped>
.cardkey-setting {
  padding: 20px;
}
</style>
