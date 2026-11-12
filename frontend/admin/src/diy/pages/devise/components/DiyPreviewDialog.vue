<template>
  <el-dialog :model-value="modelValue" width="540px" title="预览" @update:model-value="$emit('update:modelValue', $event)">
    <div>
      <div v-viewer class="acea-row row-around code">
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" ref="qrCodeUrl"></div>
          <span class="mt10">公众号二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic">
            <img v-if="qrcodeImg" v-lazy="qrcodeImg" />
            <span v-else class="qr-empty">小程序二维码无法生成<br />请检查微信小程序配置</span>
          </div>
          <span class="mt10">小程序二维码</span>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  modelValue: { type: Boolean, default: false },
  qrcodeImg: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const qrCodeUrl = ref(null);
defineExpose({ qrCodeUrl });
</script>

<style scoped lang="scss">
.code {
  .QRpic {
    width: 160px;
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    img {
      width: 100%;
      height: 100%;
    }
    .qr-empty {
      font-size: 12px;
      color: #999;
      text-align: center;
      line-height: 1.6;
      padding: 0 8px;
    }
  }
}
</style>
