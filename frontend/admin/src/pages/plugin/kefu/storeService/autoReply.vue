<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFrom"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="回复类型：" prop="type" label-for="type">
            <el-select
              v-model="formValidate.type"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="text" label="文字消息"></el-option>
              <el-option value="image" label="图片消息"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="关键字：" prop="key" label-for="key">
            <el-input clearable v-model="formValidate.key" placeholder="请输入关键字" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" class="action-row" v-db-click @click="add">添加自动回复</el-button>
      <el-table
        :data="tabList"
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
        <el-table-column label="关键字" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.key }}</span>
          </template>
        </el-table-column>
        <el-table-column label="回复类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.type == 'text' ? '文字消息' : '图片消息' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="回复内容" min-width="130">
          <template #default="scope">
            <span v-if="scope.row.type == 'text'">{{ scope.row.data.content }}</span>
            <div v-else class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.data.src" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="是否开启" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              :active-text="'开启'"
              :inactive-text="'关闭'"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '客服自动回复', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { kefuAutoReplyListApi, kefuAutoReplyForm, keywordsetStatusApi } from '@/api/app';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'keyword' });

const mediaStore = useMediaStore();

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const formValidate = reactive({
  key: '',
  type: '',
  page: 1,
  limit: 15,
});
const tabList = ref([]);
const total = ref(0);
const columns1 = [
  {
    title: 'ID',
    key: 'id',
    width: 80,
  },
  {
    title: '关键字',
    key: 'key',
    minWidth: 120,
  },
  {
    title: '回复类型',
    key: 'type',
    minWidth: 150,
  },
  {
    title: '是否显示',
    slot: 'status',
    minWidth: 120,
  },
  {
    title: '操作',
    slot: 'action',
    fixed: 'right',
    minWidth: 120,
  },
];
const modal = ref(false);
const qrcode = ref('');

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// created
getList();

// 列表
function getList() {
  loading.value = true;
  kefuAutoReplyListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  keywordsetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 添加
function add() {
  modalForm(kefuAutoReplyForm(0)).then(() => getList());
}
// 编辑
function edit(row) {
  modalForm(kefuAutoReplyForm(row.id)).then(() => getList());
}
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `app/kefu/auto_reply/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style scoped>
.QRpic {
  width: 180px;
  height: 180px;
}

.QRpic img {
  width: 100%;
  height: 100%;
}
</style>
