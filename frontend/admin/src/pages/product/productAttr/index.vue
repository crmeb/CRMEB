<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="artFromRef"
          :model="artFrom"
          label-width="80px"
          label-position="right"
          class="tabform"
          @submit.prevent
          inline
        >
          <el-form-item label="规格搜索：">
            <el-input
              clearable
              v-model="artFrom.rule_name"
              placeholder="请输入规格名称"
              class="form_content_width"
            ></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['product-rule-save']" type="primary" class="action-row" v-db-click @click="openAddAttr"
        >添加商品规格</el-button
      >
      <el-button
        v-auth="['product-product-rule-delete']"
        class="action-row"
        v-db-click
        @click="del(null, '批量删除规格')"
        >批量删除</el-button
      >
      <el-table
        ref="tableRef"
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        :row-key="getRowKey"
        @selection-change="handleSelectRow"
        empty-text="暂无数据"
        class="mt14"
      >
        <el-table-column type="selection" width="60" :reserve-selection="true"> </el-table-column>
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="模板名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.rule_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="规格名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.attr_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品属性" min-width="130">
          <template #default="scope">
            <span
              v-for="(item, index) in scope.row.attr_value"
              :key="index"
              v-text="item"
              style="display: block"
            ></span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除规格', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="artFrom.page"
          v-model:limit="artFrom.limit"
          @pagination="getDataList"
        />
      </div>
    </el-card>
    <add-attr ref="addAttrRef" @getList="userSearchs"></add-attr>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import addAttr from './addAttr';
import { ruleListApi } from '@/api/product';
import { modalSure } from '@/utils/public';
import { useOrderStore } from '@/store/module/order';

defineOptions({ name: 'productAttr' });

const orderStore = useOrderStore();

const artFromRef = ref(null);
const tableRef = ref(null);
const addAttrRef = ref(null);

const loading = ref(false);
const artFrom = reactive({
  page: 1,
  limit: 15,
  rule_name: '',
});
const tableList = ref([]);
const total = ref(0);
const selectedIds = ref(new Set()); //选中合并项的id
const ids = ref([]);
const multipleSelection = ref([]);

const orderChartType = computed(() => orderStore.orderChartType);

// created
getDataList();

function getRowKey(row) {
  return row.id;
}
//全选和取消全选时触发
function handleSelectAll(selection) {
  if (selection.length === 0) {
    //获取table的数据；
    let data = tableRef.value.data;
    data.forEach((item) => {
      if (selectedIds.value.has(item.id)) {
        selectedIds.value.delete(item.id);
      }
    });
  } else {
    selection.forEach((item) => {
      selectedIds.value.add(item.id);
    });
  }
  nextTick(() => {
    //确保dom加载完毕
    setChecked();
  });
}
//  选中某一行
function handleSelectRow(selection) {
  const uniqueArr = [];
  const ids = [];
  for (let i = 0; i < selection.length; i++) {
    const item = selection[i];
    if (!ids.includes(item.id)) {
      uniqueArr.push(item);
      ids.push(item.id);
    }
  }
  selectedIds.value = new Set(ids);
  multipleSelection.value = uniqueArr;
  nextTick((e) => {
    setChecked();
  });
}
function setChecked() {
  //将new Set()转化为数组
  ids.value = [...selectedIds.value].join(',');
}
// 删除
function del(row, tit) {
  let data = {};
  if (tit === '批量删除规格') {
    if (selectedIds.value.size === 0) return ElMessage.warning('请选择要删除的规格！');
    data = {
      ids: ids.value,
    };
  } else {
    data = {
      ids: row.id,
    };
  }
  let delfromData = {
    title: tit,
    num: 0,
    url: `product/product/rule/delete`,
    method: 'DELETE',
    ids: data,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getDataList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function openAddAttr() {
  addAttrRef.value.modal = true;
}
// 编辑
function edit(row) {
  addAttrRef.value.modal = true;
  addAttrRef.value.getIofo(row);
}
// 列表；
function getDataList() {
  loading.value = true;
  ruleListApi(artFrom)
    .then((res) => {
      let data = res.data;
      tableList.value = data.list;
      nextTick(() => {
        //确保dom加载完毕
        setChecked();
      });
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  artFrom.page = 1;
  getDataList();
}
</script>

<style scoped></style>
