<template>
  <div>
    <el-form-item label="文本标题">
      <el-input v-model="curComponent.propValue.text" placeholder="请填写文本" @change="onChange"></el-input>
    </el-form-item>
    <ConfigLink
      :curComponent="curComponent"
      :type="type"
      :currentFieldList="currentFieldList"
      :showLinkType="true"
      @change="onChange"
      @get-link="getLink"
    />
  </div>
</template>

<script setup>
import ConfigLink from './ConfigLink';

defineOptions({ name: 'ConfigTextContent' });

defineProps({
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
});

const emit = defineEmits(['get-link', 'change']);

function getLink() {
  emit('get-link');
}
function onChange() {
  emit('change');
}
</script>
