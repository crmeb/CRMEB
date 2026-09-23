<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="订单时间：">
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
          <el-form-item label="交易类型：">
            <el-select v-model="formValidate.trading_type" @change="selChange" class="form_content_width" clearable>
              <el-option
                v-for="(item, index) in typeList"
                :key="index"
                :label="item.title"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-table ref="table" :data="tabList" class="ivu-mt" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="ID" min-width="70">
          <template #default="scope">
            <div>{{ scope.row.id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="关联订单" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.relation }}</div>
          </template>
        </el-table-column>
        <el-table-column label="交易时间" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.add_time }}</div>
          </template>
        </el-table-column>
        <el-table-column label="交易积分" min-width="80">
          <template #default="scope">
            <div v-if="scope.row.pm" class="z-price">+ {{ scope.row.number }}</div>
            <div v-else class="f-price">- {{ scope.row.number }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户" min-width="80">
          <template #default="scope">
            <div>{{ scope.row.nickname }}</div>
          </template>
        </el-table-column>
        <el-table-column label="交易类型" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.type_name }}</div>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.mark }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template #default="scope">
            <a class="item" v-db-click @click="setMark(scope.row)">修改备注</a>
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
    <!-- 备注弹窗 -->
    <el-dialog v-model="modals" title="备注" :close-on-click-modal="false" width="470px">
      <el-input v-model="mark_msg.mark" type="textarea" :rows="4" placeholder="请输入备注" />
      <template #footer
        ><div>
          <el-button type="primary" v-db-click @click="oks">确定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { pointRecordList, pointRecordRemark } from '@/api/marketing';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'integralRecord' });

const mediaStore = useMediaStore();

const formValidateRef = ref(null);

const mark_msg = reactive({ mark: '' });
const modals = ref(false);
const total = ref(0);
const loading = ref(false);
const tabList = ref([]);
const typeList = ref([]);
const formValidate = reactive({
  trading_type: '',
  time: '',
  page: 1,
  limit: 15,
});
const timeVal = ref([]);
const extractId = ref(0);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

onMounted(() => {
  getList();
});

function oks() {
  mark_msg.mark = mark_msg.mark.trim();
  pointRecordRemark(extractId.value, mark_msg)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modals.value = false;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function setMark(row) {
  modals.value = true;
  extractId.value = row.id;
  mark_msg.mark = row.mark;
}
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.time = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.page = 1;
  getList();
}
function selChange(e) {
  formValidate.page = 1;
  formValidate.trading_type = e;
  getList();
}
function getList() {
  loading.value = true;
  pointRecordList(formValidate)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      // 接口返回 { key: 标题 } 对象，转为 el-option 需要的 [{ title, value }] 数组
      const statusMap = data.status || {};
      typeList.value = Object.keys(statusMap).map((key) => ({ title: statusMap[key], value: key }));
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.z-price {
  color: red;
}
.f-price {
  color: green;
}
</style>
