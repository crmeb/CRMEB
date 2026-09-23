<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="gradeFrom" inline :label-width="labelWidth" :label-position="labelPosition" @submit.prevent>
          <el-form-item label="批次名称：" label-for="title">
            <el-input clearable v-model="gradeFrom.title" placeholder="请输入批次名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-button type="primary" class="action-row" v-db-click @click="addBatch">添加批次</el-button>
      <el-button class="action-row" v-db-click @click="getMemberScan">卡密使用页面二维码</el-button>
      <el-table class="mt14" :data="tbody" v-loading="loading" highlight-current-row>
        <el-table-column label="编号" width="100">
          <template #default="scope"
            ><span>{{ scope.row.id }}</span></template
          >
        </el-table-column>
        <el-table-column label="批次名称" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.title }}</span></template
          >
        </el-table-column>
        <el-table-column label="体验天数" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.use_day }}</span></template
          >
        </el-table-column>
        <el-table-column label="发卡总数量" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.total_num }}</span></template
          >
        </el-table-column>
        <el-table-column label="使用数量" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.use_num }}</span></template
          >
        </el-table-column>
        <el-table-column label="是否激活" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
            ></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="120">
          <template #default="scope"
            ><span>{{ scope.row.remark }}</span></template
          >
        </el-table-column>
        <el-table-column label="制卡时间" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.add_time }}</span></template
          >
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <el-dropdown @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i></span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="1">编辑批次名</el-dropdown-item>
                  <el-dropdown-item command="2">查看卡列表</el-dropdown-item>
                  <el-dropdown-item command="3">导出</el-dropdown-item>
                </el-dropdown-menu></template
              >
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="gradeFrom.page"
          v-model:limit="gradeFrom.limit"
          @pagination="getMemberBatch"
        />
      </div>
    </el-card>
    <el-dialog v-model="modal" width="540px" :title="`${formValidate.id ? '编辑' : '添加'}批次`">
      <el-form :model="formValidate" label-width="80px" @submit.prevent>
        <el-form-item label="批次名称：">
          <el-input placeholder="请输入批次名称" v-model="formValidate.title" class="w100" />
        </el-form-item>
        <el-form-item label="备注：" v-if="formValidate.id">
          <el-input type="textarea" placeholder="请输入备注" v-model="formValidate.remark" class="w100" />
        </el-form-item>
        <template v-if="!formValidate.id">
          <el-form-item label="制卡数量：">
            <el-input-number
              :controls="false"
              placeholder="请输入制卡数量"
              :precision="0"
              :max="100000"
              :min="1"
              v-model="formValidate.total_num"
              class="perW10"
            />
          </el-form-item>
          <el-form-item label="体验天数：">
            <el-input-number
              :controls="false"
              placeholder="请输入体验天数"
              :precision="0"
              :max="100000"
              :min="1"
              v-model="formValidate.use_day"
              class="perW10"
            />
          </el-form-item>
          <el-form-item label="是否激活：">
            <el-radio-group v-model="formValidate.status">
              <el-radio :label="1" :value="1" class="radio">激活</el-radio>
              <el-radio :label="0" :value="0">冻结</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="备注：">
            <el-input type="textarea" placeholder="请输入备注" v-model="formValidate.remark" class="w100" />
          </el-form-item>
        </template>
      </el-form>
      <div class="acea-row row-right">
        <el-button v-db-click @click="modal = false">取消</el-button>
        <el-button type="primary" v-db-click @click="onSubmit()">提交</el-button>
      </div>
    </el-dialog>
    <el-dialog v-model="cardModal" title="卡列表" width="1000px">
      <cardList v-if="cardModal" :id="id"></cardList>
    </el-dialog>
    <el-dialog v-model="modal3" title="二维码" width="540px">
      <div v-if="qrcode" class="acea-row row-around">
        <div v-if="qrcode && qrcode.wechat_img" class="acea-row row-column-around row-between-wrapper">
          <div v-viewer class="QRpic"><img v-lazy="qrcode.wechat_img" /></div>
          <span class="mt10">公众号二维码</span>
        </div>
        <div v-if="qrcode && qrcode.routine" class="acea-row row-column-around row-between-wrapper">
          <div v-viewer class="QRpic"><img v-lazy="qrcode.routine" /></div>
          <span class="mt10">小程序二维码</span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import cardList from './list.vue';
import { userMemberBatch, memberBatchSave, memberBatchSetValue, userMemberScan } from '../../api';
import exportExcel from '@/utils/newToExcel.js';

defineOptions({ name: 'memberCardBatch' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const cardModal = ref(false);
const id = ref(0);
const tbody = ref([]);
const total = ref(0);
const loading = ref(false);
const modal = ref(false);
const modal3 = ref(false);
const qrcode = ref(null);
const gradeFrom = ref({ title: '', page: 1, limit: 15 });
const formValidate = ref({ id: 0, title: '', total_num: 1, use_day: 1, status: 1, remark: '' });

// created
getMemberBatch();

function getMemberBatch() {
  loading.value = true;
  userMemberBatch(gradeFrom.value)
    .then((res) => {
      loading.value = false;
      tbody.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}

function userSearchs() {
  gradeFrom.value.page = 1;
  getMemberBatch();
}

function onchangeIsShow(row) {
  memberBatchSetValue(row.id, { field: 'status', value: row.status })
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function changeMenu(row, name) {
  switch (name) {
    case '1':
      formValidate.value.id = row.id;
      formValidate.value.title = row.title;
      modal.value = true;
      break;
    case '2':
      id.value = row.id;
      cardModal.value = true;
      break;
    case '3':
      exportCards(row);
      break;
  }
}

async function exportCards(row) {
  let { exportmberCardList } = await import('@/api/export.js');
  let lebData = await exportmberCardList(row.id).then((r) => r.data);
  exportExcel(lebData.header, lebData.fileKey, lebData.filename, lebData.export);
}

function addBatch() {
  modal.value = true;
  formValidate.value.id = 0;
  formValidate.value.title = '';
}

function onSubmit() {
  if (formValidate.value.id) {
    memberBatchSetValue(formValidate.value.id, {
      field: 'title',
      value: formValidate.value.title,
      remark: formValidate.value.remark,
    })
      .then((res) => {
        modal.value = false;
        ElMessage.success(res.msg);
        getMemberBatch();
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  } else {
    memberBatchSave(formValidate.value.id, formValidate.value)
      .then((res) => {
        modal.value = false;
        ElMessage.success(res.msg);
        getMemberBatch();
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  }
}

function getMemberScan() {
  userMemberScan()
    .then((res) => {
      qrcode.value = res.data;
      modal3.value = true;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
</script>

<style lang="scss" scoped>
.QRpic {
  width: 180px;
  height: 180px;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
