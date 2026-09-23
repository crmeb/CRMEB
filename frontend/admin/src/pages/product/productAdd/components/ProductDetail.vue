<template>
  <!-- 商品详情 -->
  <el-row>
    <el-col :span="16">
      <el-form-item label="商品详情：">
        <WangEditor
          style="width: 100%"
          :content="contents"
          :ai-default-prompt="aiDefaultPrompt"
          @editorContent="getEditorContent"
        ></WangEditor>
      </el-form-item>
    </el-col>
    <el-col :span="6" style="width: 33%">
      <div class="ifam">
        <div class="content preview-content" v-html="content"></div>
      </div>
    </el-col>
  </el-row>
</template>

<script setup>
import { computed } from 'vue';
import WangEditor from '@/components/wangEditor/index.vue';

defineOptions({ name: 'ProductDetail' });

const props = defineProps({
  contents: {
    type: String,
    required: true,
  },
  content: {
    type: String,
    required: true,
  },
  // 商品名称（用于 AI 写作默认提示词；标题变化后弹窗内未被用户修改过的提示词会跟随刷新）
  storeName: {
    type: String,
    default: '',
  },
});

// AI 写作默认提示词：优先带入当前商品标题
const aiDefaultPrompt = computed(() => {
  const title = (props.storeName || '').trim();
  return title
    ? `请围绕商品《${title}》撰写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。`
    : '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';
});

const emit = defineEmits(['getEditorContent']);

function getEditorContent(val) {
  emit('getEditorContent', val);
}
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
