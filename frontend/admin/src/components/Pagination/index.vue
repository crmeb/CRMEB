<template>
  <div :class="{ hidden: hidden }" class="pagination-container">
    <el-pagination
      :background="background"
      v-model:current-page="currentPage"
      v-model:page-size="pageSize"
      :layout="layout"
      :page-sizes="pageSizes"
      :pager-count="pageCount"
      :total="total"
      v-bind="$attrs"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    ></el-pagination>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { scrollTo } from '@/utils/scroll-to.js';

defineOptions({ name: 'Pagination' });

const props = defineProps({
  total: {
    required: true,
    type: Number,
  },
  page: {
    type: Number,
    default: 1,
  },
  limit: {
    type: Number,
    default: 15,
  },
  pageSizes: {
    type: Array,
    default() {
      return [15, 20, 30, 50];
    },
  },
  layout: {
    type: String,
    default: 'total, sizes, prev, pager, next, jumper',
  },
  background: {
    type: Boolean,
    default: true,
  },
  autoScroll: {
    type: Boolean,
    default: true,
  },
  hidden: {
    type: Boolean,
    default: false,
  },
  pageCount: {
    type: Number,
    default: 7,
  },
});

const emit = defineEmits(['update:page', 'update:limit', 'pagination']);

const currentPage = computed({
  get() {
    return props.page;
  },
  set(val) {
    emit('update:page', val);
  },
});
const pageSize = computed({
  get() {
    return props.limit;
  },
  set(val) {
    emit('update:limit', val);
  },
});

function handleSizeChange(val) {
  emit('pagination');
  if (props.autoScroll) {
    scrollTo(0, 800);
  }
}
function handleCurrentChange(val) {
  emit('pagination', val);
  if (props.autoScroll) {
    scrollTo(0, 800);
  }
}
</script>

<style scoped>
.pagination-container {
  background: #fff;
}
.pagination-container.hidden {
  display: none;
}
</style>
