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
          <el-form-item label="模板搜索：">
            <el-input
              clearable
              v-model="artFrom.name"
              placeholder="请输入模板名称"
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
      <el-button type="primary" class="action-row" v-db-click @click="openParamAdd">添加商品参数</el-button>
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
        <!-- <el-table-column type="selection" width="60" :reserve-selection="true"> </el-table-column> -->
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="模板名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除参数', scope.$index)">删除</a>
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
    <param-add ref="paramAddRef" @getList="userSearchs"></param-add>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import paramAdd from './paramAdd';
import { paramListApi } from '@/api/product';
import { modalSure } from '@/utils/public';
import { useOrderStore } from '@/store/module/order';

defineOptions({ name: 'paramList' });

const orderStore = useOrderStore();

const artFromRef = ref(null);
const tableRef = ref(null);
const paramAddRef = ref(null);

const loading = ref(false);
const artFrom = reactive({
  page: 1,
  limit: 15,
  name: '',
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
  selectedIds.value = ids;
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
  let delfromData = {
    title: tit,
    num: 0,
    url: `product/param/del/${row.id}`,
    method: 'DELETE',
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
function openParamAdd() {
  paramAddRef.value.modal = true;
}
// 编辑
function edit(row) {
  paramAddRef.value.modal = true;
  paramAddRef.value.getIofo(row);
}
// 列表；
function getDataList() {
  loading.value = true;
  paramListApi(artFrom)
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
