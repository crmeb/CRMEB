<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <el-form :model="levelFrom" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent>
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="状态：" label-for="status1">
              <el-radio-group v-model="levelFrom.status" type="button" @change="userSearchs(levelFrom.status)">
                <el-radio-button label="" value="">全部</el-radio-button>
                <el-radio-button label="0" value="0">待审核</el-radio-button>
                <el-radio-button label="1" value="1">通过</el-radio-button>
                <el-radio-button label="2" value="2">拒绝</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="用户搜索：" label-for="title">
              <el-input
                search
                enter-button
                v-model="levelFrom.keywords"
                placeholder="请输入用户昵称/ID/手机号"
                @on-search="userSearchs"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
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
        <el-table-column label="昵称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="手机号" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.status }}</span>
          </template>
        </el-table-column>
        <el-table-column label="申请时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.up_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.remark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="agree(scope.row)">同意</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="refuse(scope.row)">拒绝</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="remarkFn(scope.row)">备注</a>
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
    <!-- 等级任务-->
    <remark ref="remarkRef" @submitFail="submitFail"></remark>
  </div>
</template>
<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { userCancelList, userCancelSetMark } from '@/api/user';
import remark from '@/components/remark/index';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'user_level' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const id = ref('');
const loading = ref(false);
const levelFrom = ref({
  status: '',
  keywords: '',
  page: 1,
  limit: 15,
});
const levelLists = ref([]);
const total = ref(0);
const remarkRef = ref(null);

// created
getList();

function remarkFn(row) {
  id.value = row.id;
  remarkRef.value.formValidate.remark = row.remark;
  remarkRef.value.modals = true;
}

function agree(row) {
  const delfromData = {
    title: '注销用户',
    url: `/user/cancel/agree/${row.id}`,
    method: 'get',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function refuse(row) {
  const delfromData = {
    title: '拒绝注销用户',
    info: '您确认拒绝注销此用户吗?',
    url: `/user/cancel/refuse/${row.id}`,
    method: 'get',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function submitFail(text) {
  let data = {
    id: id.value,
    mark: text,
  };
  userCancelSetMark(data)
    .then((res) => {
      remarkRef.value.modals = false;
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      remarkRef.value.modals = false;
      ElMessage.error(err.msg);
    });
}

function getList() {
  loading.value = true;
  levelFrom.value.status = levelFrom.value.status || '';
  userCancelList(levelFrom.value)
    .then(async (res) => {
      let data = res.data;
      levelLists.value = data.list;
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
  levelFrom.value.page = 1;
  getList();
}
</script>

<style lang="scss" scoped>
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
</style>
