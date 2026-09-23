<template>
  <div>
    <el-form-item label="跳转链接">
      <div v-if="['article', 'goods'].includes(type) && showLinkType">
        <el-radio-group v-model="curComponent.propValue.linkType" @change="onChange" style="margin-bottom: 2px">
          <el-radio label="url" value="url">网址链接</el-radio>
          <el-radio label="detail" value="detail">{{ type === 'article' ? '文章详情' : '商品详情' }}</el-radio>
        </el-radio-group>
      </div>
      <el-input
        v-if="!curComponent.propValue.linkType || curComponent.propValue.linkType === 'url'"
        v-model="curComponent.propValue.link"
        placeholder="请输入链接"
        @change="onChange"
      >
        <template #suffix><i class="el-icon-link" @click="getLink" /></template>
      </el-input>
    </el-form-item>
    <!-- 不是面板 -->
    <el-form-item label="信息类型" v-if="curComponent.component !== 'Panel'">
      <el-select
        v-model="curComponent.propValue.fieldType"
        clearable
        placeholder="请选择信息类型"
        @change="handleFieldTypeChange"
        style="width: 100%"
      >
        <el-option
          v-for="(item, index) in currentFieldList"
          :key="index"
          :label="item.label"
          :value="item.value"
        ></el-option>
      </el-select>
    </el-form-item>
  </div>
</template>

<script setup>
defineOptions({ name: 'ConfigLink' });

const props = defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: 'user',
  },
  currentFieldList: {
    type: Array,
    default: () => [],
  },
  showLinkType: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['change', 'get-link']);

function onChange() {
  emit('change');
}
function getLink() {
  emit('get-link');
}
function handleFieldTypeChange(val) {
  const field = props.currentFieldList.find((item) => item.value === val);
  if (field) {
    props.curComponent.propValue['typeLabel'] = field.label;
  }
  onChange();
}
</script>
