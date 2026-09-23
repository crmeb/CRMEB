<template>
  <el-dialog v-model="modals" title="选择商品" :close-on-click-modal="false" width="1000px" @closed="handleReset">
    <el-form
      ref="levelFrom"
      :model="levelFrom"
      :label-width="labelWidth"
      :label-position="labelPosition"
      @submit.prevent
    >
      <el-row :gutter="24">
        <el-col v-bind="grid">
          <el-form-item label="商品名称：" prop="status2" label-for="status2">
            <el-input
              search
              enter-button
              v-model="levelFrom.name"
              placeholder="请输入商品名称"
              @on-search="userSearchs"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <el-divider direction="vertical" dashed />
    <el-table
      :data="levelLists"
      ref="table"
      v-loading="loading"
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
    >
      <template #is_shows="{ row, index }">
        <el-switch
          :active-value="1"
          :inactive-value="0"
          v-model="row.is_show"
          :value="row.is_show"
          size="large"
          @change="onchangeIsShow(row)"
        >
        </el-switch>
      </template>
      <template #is_musts="{ row, index }">
        <el-switch
          :active-value="1"
          :inactive-value="0"
          v-model="row.is_must"
          :value="row.is_must"
          size="large"
          @change="onchangeIsMust(row)"
          active-text="全部完成"
          inactive-text="达成其一"
        >
        </el-switch>
      </template>
      <template #action="{ row, index }">
        <a v-db-click @click="edit(row)">编辑 | </a>
        <a v-db-click @click="del(row, '删除任务')"> 删除</a>
      </template>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        v-model:page="levelFrom.page"
        v-model:limit="levelFrom.limit"
        @pagination="getList"
      />
    </div>
    <!-- 新建 编辑表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail" :titleType="titleType"></edit-from>
  </el-dialog>
</template>

<script setup>
// 注意：本组件自 Vue2 时代起即为死代码（getList 等引用的 taskListApi/levelFrom/levelLists 等从未定义），
// 按等价迁移原则保留原状，仅做 script setup 结构转换。
import { ref } from 'vue';
import { ElMessage } from 'element-plus';

defineOptions({ name: 'relation' });

const modals = ref(false);
const grid = {
  xl: 12,
  lg: 12,
  md: 12,
  sm: 24,
  xs: 24,
};

// 关闭模态框
function handleReset() {
  modals.value = false;
}
// 表格搜索
function userSearchs() {
  getList();
}
// 任务列表（原代码即为未定义引用，保持）
function getList() {
  loading.value = true;
  taskListApi(levelId, levelFrom)
    .then(async (res) => {
      let data = res.data;
      levelLists = data.list;
      total = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange(index) {
  levelFrom.page = index;
  getList();
}
</script>

<style scoped></style>
