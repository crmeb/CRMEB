<template>
  <div>
    <pages-header class="mb16" :title="$route.meta.title"></pages-header>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="formValidate" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent inline>
          <el-form-item label="是否显示：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="getList"
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="add" class="action-row mr20">添加配置</el-button>
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="开启时间(整点)" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="持续时间(整数小时)" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.continued }}</span>
          </template>
        </el-table-column>
        <el-table-column label="幻灯片" min-width="120">
          <template #default="scope">
            <div class="tabBox_img" v-viewer v-if="scope.row.slide && scope.row.slide.length">
              <img v-lazy="scope.row.slide[0]" />
            </div>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" width="80">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
            ></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, scope.$index)">删除</a>
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

    <!-- 添加/编辑对话框 -->
    <el-dialog
      :title="editId ? '编辑配置' : '添加配置'"
      v-model="dialogVisible"
      width="500px"
      :before-close="handleClose"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="140px" @submit.prevent>
        <el-form-item label="开启时间(整点)" prop="time">
          <el-input v-model="form.time" placeholder="请输入0-23之间的整点数字" maxlength="2"></el-input>
          <div class="tips">例如输入 10 表示从 10:00 开始</div>
        </el-form-item>
        <el-form-item label="持续时间(整数小时)" prop="continued">
          <el-input v-model="form.continued" placeholder="请输入1-24之间的整数" maxlength="2"></el-input>
          <div class="tips">例如输入 2 表示持续 2 小时</div>
        </el-form-item>
        <el-form-item label="幻灯片" prop="slide">
          <div class="picBox" v-db-click @click="modalPicTap('单选')">
            <div class="pictrue" v-if="form.slide">
              <img :src="form.slide" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-plus acea-row row-center-wrapper" style="font-size: 24px"></i>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="9999"></el-input-number>
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="1" :value="1">显示</el-radio>
            <el-radio :label="0" :value="0">隐藏</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="handleClose">取消</el-button>
          <el-button type="primary" @click="submitForm" :loading="submitLoading">确定</el-button>
        </div></template
      >
    </el-dialog>

    <!-- 选择图片弹窗 -->
    <el-dialog v-model="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import uploadPictures from '@/components/uploadPictures';
import { seckillConfigListApi, seckillConfigSaveApi, seckillConfigDelApi, seckillConfigStatusApi } from '../../api';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'index' });

const mediaStore = useMediaStore();

const formRef = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const modalPic = ref(false);
const isChoice = ref('单选');
const gridPic = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};
const gridBtn = {
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
};
const formValidate = ref({
  status: '',
  page: 1,
  limit: 15,
});
const total = ref(0);
const tabList = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const editId = ref(0);
const submitLoading = ref(false);
const form = ref({
  time: '',
  continued: '',
  slide: '',
  sort: 0,
  status: 1,
});
const rules = ref({
  time: [
    { required: true, message: '请输入开启时间', trigger: 'blur' },
    { pattern: /^(\d|1\d|2[0-3])$/, message: '请输入0-23之间的整点数', trigger: 'blur' },
  ],
  continued: [
    { required: true, message: '请输入持续时间', trigger: 'blur' },
    { pattern: /^([1-9]|1\d|2[0-4])$/, message: '请输入1-24之间的整数', trigger: 'blur' },
  ],
});

onMounted(() => {
  getList();
});

function getList() {
  loading.value = true;
  const params = { ...formValidate.value };
  if (params.status === '') delete params.status;
  seckillConfigListApi(params)
    .then((res) => {
      tabList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg || '获取列表失败');
    });
}
function add() {
  editId.value = 0;
  form.value = { time: '', continued: '', slide: '', sort: 0, status: 1 };
  dialogVisible.value = true;
  nextTick(() => {
    formRef.value && formRef.value.clearValidate();
  });
}
function edit(row) {
  editId.value = row.id;
  form.value = {
    time: row.time || '',
    continued: row.continued || '',
    slide: row.slide && Array.isArray(row.slide) ? row.slide[0] : row.slide || '',
    sort: row.sort || 0,
    status: row.status,
  };
  dialogVisible.value = true;
  nextTick(() => {
    formRef.value && formRef.value.clearValidate();
  });
}
function submitForm() {
  formRef.value.validate((valid) => {
    if (!valid) return;
    submitLoading.value = true;
    const data = { ...form.value, id: editId.value };
    seckillConfigSaveApi(data)
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        handleClose();
        getList();
      })
      .catch((err) => {
        ElMessage.error(err.msg || '保存失败');
      })
      .finally(() => {
        submitLoading.value = false;
      });
  });
}
function del(row, index) {
  ElMessageBox.confirm(`确定删除该配置吗？`, '提示', {
    type: 'warning',
  })
    .then(() => {
      seckillConfigDelApi(row.id)
        .then((res) => {
          ElMessage.success(res.msg || '删除成功');
          getList();
        })
        .catch((err) => {
          ElMessage.error(err.msg || '删除失败');
        });
    })
    .catch(() => {});
}
function onchangeIsShow(row) {
  seckillConfigStatusApi({ id: row.id, status: row.status })
    .then((res) => {
      ElMessage.success(res.msg || '设置成功');
    })
    .catch((err) => {
      row.status = row.status === 1 ? 0 : 1;
      ElMessage.error(err.msg || '设置失败');
    });
}
// 选择图片
function modalPicTap() {
  modalPic.value = true;
}
// 选中图片
function getPic(pc) {
  form.value.slide = pc.att_dir;
  modalPic.value = false;
}
function handleClose() {
  dialogVisible.value = false;
  editId.value = 0;
}
</script>

<style scoped>
.tips {
  font-size: 12px;
  color: #999;
  line-height: 1.5;
  margin-top: 4px;
}
.picBox {
  width: 60px;
  height: 60px;
  border: 1px dashed #dcdfe6;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.picBox .pictrue {
  width: 100%;
  height: 100%;
}
.picBox .pictrue img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.picBox .upLoad {
  width: 100%;
  height: 100%;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  overflow: hidden;
}
.tabBox_img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
