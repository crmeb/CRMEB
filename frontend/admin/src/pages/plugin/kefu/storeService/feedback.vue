<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          class="tabform"
          @submit.prevent
          inline
        >
          <el-form-item label="留言时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="留言信息：">
            <el-input
              clearable
              placeholder="请输入用户昵称/电话/留言内容搜索"
              v-model="formValidate.title"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table :data="list" v-loading="loading" no-userFrom-text="暂无数据" no-filtered-userFrom-text="暂无筛选结果">
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="昵称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.rela_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="电话" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="内容" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.content }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template #default="scope">
            <div>{{ scope.row.status === 1 ? '已处理' : '未处理' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="remarks(scope.row.id)">{{ scope.row.status === 1 ? '备注' : '处理' }}</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除反馈', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="getList" />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { kefuFeedBack, kefuFeedBackEdit } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'feedback' });

const mediaStore = useMediaStore();

const loading = ref(false);
const list = ref([]);
const page = ref(1);
const limit = ref(15);
const formValidate = reactive({
  time: '',
  title: '',
});
const timeVal = ref([]);
const total = ref(0);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// created
getList();

//备注；
function remarks(id) {
  modalForm(kefuFeedBackEdit(id)).then(() => getList());
}
// 选择
function selChange() {
  page.value = 1;
  getList();
}
// 选择时间
function selectChange(tab) {
  formValidate.time = tab;
  timeVal.value = [];
  page.value = 1;
  getList();
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.time = timeVal.value ? timeVal.value.join('-') : '';
  page.value = 1;
  getList();
}
function getList() {
  kefuFeedBack({
    page: page.value,
    limit: limit.value,
    time: formValidate.time,
    title: formValidate.title,
  }).then((res) => {
    list.value = res.data.data;
    total.value = res.data.count;
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/app/feedback/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      list.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style scoped></style>
