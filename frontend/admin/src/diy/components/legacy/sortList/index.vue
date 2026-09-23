<template>
  <div class="goodList">
    <el-form ref="formValidate" :model="formValidate" label-width="120px" label-position="right" class="tabform">
      <el-row :gutter="24">
        <el-col v-bind="grid">
          <el-form-item label="商品分类：" label-for="pid">
            <el-select v-model="formValidate.pid" style="width: 230px" clearable @change="userSearchs">
              <el-option v-for="item in treeSelect" :value="item.id" :key="item.id" :label="item.cate_name"></el-option>
            </el-select>
          </el-form-item>
        </el-col>
        <el-col v-bind="grid">
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
              search
              enter-button
              placeholder="请输入商品分类,id"
              v-model="formValidate.name"
              style="width: 80%"
              @on-search="userSearchs"
            />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <el-table
      ref="table"
      empty-text="暂无数据"
      max-height="400"
      :data="tableList"
      v-loading="loading"
      @select="selectionGood"
    >
      <el-table-column type="selection" width="55"> </el-table-column>
      <el-table-column label="商品ID" width="80">
        <template #default="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="图片" min-width="90">
        <template #default="scope">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="scope.row.pic" />
          </div>
        </template>
      </el-table-column>
      <el-table-column label="商品分类" min-width="130">
        <template #default="scope">
          <span>{{ scope.row.cate_name }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { getByCategory } from '@diy/api';

defineOptions({ name: 'index' });

const emit = defineEmits(['getProductDiy']);

const formValidate = reactive({
  pid: 0,
  name: '',
});
const treeSelect = ref([]);
const loading = ref(false);
const grid = {
  xl: 10,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const tableList = ref([]);
const currentid = ref(0);
const productRow = ref({});
const columns = [
  {
    type: 'selection',
    width: 60,
    align: 'center',
  },
  {
    title: '商品ID',
    key: 'id',
  },
  {
    title: '图片',
    slot: 'image',
  },
  {
    title: '商品分类',
    key: 'cate_name',
    minWidth: 150,
  },
];
const images = ref([]);

function selectionGood(e) {
  emit('getProductDiy', e);
}
// 商品一级分类；
function goodsCategory() {
  getByCategory()
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  getByCategory(formValidate)
    .then(async (res) => {
      tableList.value = res.data;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  getList();
}
// clear() {
//     productRow.id = '';
//     currentid = '';
// }

onMounted(() => {
  goodsCategory();
  getList();
});
</script>

<style lang="scss" scoped>
.footer {
  margin: 15px 0;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.tabform {
  :deep(.ivu-form-item) {
    margin-bottom: 16px !important;
  }
}
.btn {
  margin-top: 20px;
  float: right;
}
.goodList {
  :deep(table) {
    width: 100% !important;
  }
}
</style>
