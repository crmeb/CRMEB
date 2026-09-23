<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="levelFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="搜索：" label-for="keyword">
            <el-input clearable v-model="levelFrom.name" placeholder="请输入模板名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" class="action-row" v-db-click @click="freight">添加运费模板</el-button>
      <el-table
        :data="levelLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
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
        <el-table-column label="计费方式" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="指定包邮" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.appoint }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical" v-if="scope.row.id !== 1" />
            <a v-db-click @click="del(scope.row, '删除模版', index)" v-if="scope.row.id !== 1">删除</a>
          </template>
        </el-table-column>
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
    </el-card>
    <!-- 运费模板-->
    <freight-template
      v-if="isTemplate"
      ref="template"
      @addSuccess="getList"
      @close="
        () => {
          isTemplate = false;
        }
      "
    ></freight-template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { templatesApi } from '@/api/freight';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import freightTemplate from '@/components/freightTemplate/index';

defineOptions({ name: 'setting_templates' });

const mediaStore = useMediaStore();

const template = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);

const levelFrom = reactive({
  name: '',
  page: 1,
  limit: 15,
});
const levelLists = ref([]);
const total = ref(0);
const FromData = ref(null);
const isTemplate = ref(false);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 添加运费模板
function freight() {
  isTemplate.value = true;
  nextTick((e) => {
    template.value.id = 0;
    template.value.isTemplate = true;
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `freight/shipping_templates/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      levelLists.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 运费模板列表
function getList() {
  loading.value = true;
  templatesApi(levelFrom)
    .then(async (res) => {
      let data = res.data;
      levelLists.value = data.data;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(id) {
  isTemplate.value = true;
  nextTick((e) => {
    template.value.isTemplate = true;
    template.value.editFrom(id);
  });
}
// 表格搜索
function userSearchs() {
  levelFrom.page = 1;
  getList();
}

// created
getList();
</script>
