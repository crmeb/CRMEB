<template>
  <div class="customer">
    <el-form :model="formValidate" label-width="80px" inline @submit.prevent>
      <el-form-item label="搜索用户：">
        <el-input
          clearable
          placeholder="请输入用户UID、昵称或手机号"
          v-model="formValidate.nickname"
          class="form_content_width"
        ></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      class="mt15"
      v-loading="loading2"
      highlight-current-row
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
      ref="selection"
      :data="tableList2"
      height="450"
    >
      <el-table-column width="50">
        <template #default="scope">
          <el-radio
            v-model="currentid"
            :disabled="!!scope.row.is_del"
            :label="scope.row.uid"
            :value="scope.row.uid"
            @input="() => currentidRadio(scope.row)"
            >&nbsp;</el-radio
          >
        </template>
      </el-table-column>
      <el-table-column label="UID" width="80">
        <template #default="scope">
          <span>{{ scope.row.uid }}</span>
        </template>
      </el-table-column>
      <el-table-column label="用户头像" min-width="90">
        <template #default="scope">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="scope.row.avatar" />
          </div>
        </template>
      </el-table-column>
      <el-table-column label="用户昵称" min-width="180">
        <template #default="scope">
          <div>{{ scope.row.nickname }}</div>
          <div style="color: red">{{ scope.row.is_del ? '用户已注销' : '' }}</div>
        </template>
      </el-table-column>
      <el-table-column label="手机号" min-width="180">
        <template #default="scope">
          <div>{{ scope.row.phone }}</div>
        </template>
      </el-table-column>
      <el-table-column label="是否关注公众号" min-width="130">
        <template #default="scope">
          <span v-text="scope.row.subscribe === 1 ? '关注' : '未关注'"></span>
        </template>
      </el-table-column>
      <el-table-column label="注册时间" min-width="180">
        <template #default="scope">
          <div>{{ scope.row.add_time }}</div>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total2"
        :total="total2"
        v-model:page="formValidate.page"
        v-model:limit="formValidate.limit"
        @pagination="getListService"
      />
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useRoute } from 'vue-router';
import { userList } from '@/api/user';

defineOptions({ name: 'index' });

const emit = defineEmits(['imageObject']);

const route = useRoute();

const formValidate = reactive({
  page: 1,
  limit: 15,
  data: '',
  nickname: '',
});
const tableList2 = ref([]);
const timeVal = ref([]);
const fromList = {
  title: '选择时间',
  custom: true,
  fromTxt: [
    { text: '全部', val: '' },
    { text: '今天', val: 'today' },
    { text: '昨天', val: 'yesterday' },
    { text: '最近7天', val: 'lately7' },
    { text: '最近30天', val: 'lately30' },
    { text: '本月', val: 'month' },
    { text: '本年', val: 'year' },
  ],
};
const currentid = ref(0);
const productRow = ref({});
const loading2 = ref(false);
const total2 = ref(0);

// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.data = timeVal.value ? timeVal.value.join('-') : '';
  getListService();
}
// 选择时间
function selectChange(tab) {
  formValidate.data = tab;
  timeVal.value = [];
  getListService();
}
// 客服列表
function getListService() {
  loading2.value = true;
  userList(formValidate)
    .then(async (res) => {
      let data = res.data;
      tableList2.value = data.list;
      total2.value = data.count;
      tableList2.value.map((item) => {
        item._isChecked = false;
      });
      loading2.value = false;
    })
    .catch((res) => {
      loading2.value = false;
      ElMessage.error(res.msg);
    });
}
// 搜索
function userSearchs() {
  formValidate.page = 1;
  getListService();
}
function currentidRadio(row) {
  self.currentid = row.uid;
  productRow.value = row;
  if (productRow.value.uid) {
    if (route.query.fodder === 'image') {
      /* eslint-disable */
      let imageObject = {
        image: productRow.value.avatar,
        uid: productRow.value.uid,
      };
      form_create_helper.set('image', imageObject);
      form_create_helper.close('image');
    } else {
      emit('imageObject', {
        image: productRow.value.avatar,
        uid: productRow.value.uid,
      });
    }
  } else {
    ElMessage.warning('请先选择商品');
  }
}

onMounted(() => {
  getListService();
});
</script>

<style lang="scss" scoped>
.customer {
  height: 100%;
  background-color: var(--prev-bg-white);
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
.modelBox {
  :deep(.ivu-table-header) {
    width: 100% !important;
  }
}
.trees-coadd {
  width: 100%;
  height: 385px;
  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}
.scollhide::-webkit-scrollbar {
  display: none;
}
.footer {
  margin: 15px 0;
  padding-right: 20px;
}
:deep(.el-form--inline .el-form-item) {
  margin-bottom: 0;
}
</style>
