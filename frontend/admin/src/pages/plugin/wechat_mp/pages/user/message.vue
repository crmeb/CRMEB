<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <div class="table_box">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          class="tabform"
          @submit.prevent
        >
          <el-row :gutter="24" justify="end">
            <el-col :span="24" class="ivu-text-left">
              <el-form-item label="时间选择：">
                <el-radio-group
                  v-model="formValidate.data"
                  type="button"
                  @change="selectChange(formValidate.data)"
                  class="mr"
                >
                  <el-radio-button :label="item.val" :value="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{
                    item.text
                  }}</el-radio-button>
                </el-radio-group>
                <date-range-picker
                  clearable
                  :editable="false"
                  @change="onchangeTime"
                  :value="timeVal"
                  value-format="YYYY/MM/DD"
                  placement="bottom-end"
                  range-separator="-"
                ></date-range-picker>
              </el-form-item>
            </el-col>
            <el-col :span="24" class="ivu-text-left">
              <el-col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <el-form-item label="操作名称：">
                  <el-select v-model="formValidate.type" style="width: 90%" clearable>
                    <el-option :value="1" label="男"></el-option>
                    <el-option :value="2" label="女"></el-option>
                    <el-option :value="0" label="保密"></el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <el-form-item label="操作用户：">
                  <el-input placeholder="请输入用户名称" v-model="formValidate.nickname" style="width: 90%"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xl="3" :lg="4" :md="12" :sm="24" :xs="24" class="btn_box">
                <el-form-item>
                  <el-button type="primary" label="default" class="userSearch" v-db-click @click="userSearchs"
                    >搜索</el-button
                  >
                </el-form-item>
              </el-col>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <el-table ref="selection" :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作用户" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.type_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联内容" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.headimgurl }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" min-width="130">
          <template #default="scope">
            <span> {{ $filters.formatDate(scope.row.add_time ? scope.row.add_time : '') }}</span>
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
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { useOrderStore } from '@/store/module/order';
import { ElMessage } from 'element-plus';
import { wechatActionListApi } from '../../api';

defineOptions({ name: 'message' });

const { isMobile } = storeToRefs(useMediaStore());
const { orderChartType } = storeToRefs(useOrderStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const timeVal = ref([]);
const fromList = ref({
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
});
const formValidate = ref({
  limit: 15,
  page: 1,
  nickname: '',
  data: '',
  type: '',
});
const loading = ref(false);
const tabList = ref([]);
const total = ref(0);

const formValidateRef = ref(null);
const selection = ref(null);

// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.data = timeVal.value ? timeVal.value.join('-') : '';
  getList();
}
// 选择时间
function selectChange(tab) {
  formValidate.value.data = tab;
  timeVal.value = [];
  getList();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.value.type = formValidate.value.type ? formValidate.value.type : '';
  wechatActionListApi(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 搜索
function userSearchs() {
  getList();
}
function timeChange() {}
function Refresh() {}

getList();
</script>

<style lang="scss" scoped>
.btn_box :deep(.ivu-form-item-content) {
  margin-left: 0 !important;
}
</style>
