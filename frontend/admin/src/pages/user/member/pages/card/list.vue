<template>
  <div>
    <el-form ref="formData" :model="table" :label-width="labelWidth" label-position="right" inline @submit.prevent>
      <el-form-item label="卡号：">
        <el-input v-model="table.card_number" placeholder="请输入卡号" class="form_content_width" />
      </el-form-item>
      <el-form-item label="手机号：">
        <el-input v-model="table.phone" placeholder="请输入手机号" class="form_content_width" />
      </el-form-item>
      <el-form-item label="是否领取：">
        <el-select clearable v-model="table.is_use" class="form_content_width">
          <el-option value="1" label="已领取"></el-option>
          <el-option value="0" label="未领取"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-db-click @click="formSubmit">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table :data="data1" v-loading="loading" highlight-current-row>
      <el-table-column label="编号" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.id }}</span></template
        >
      </el-table-column>
      <el-table-column label="卡号" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.card_number }}</span></template
        >
      </el-table-column>
      <el-table-column label="密码" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.card_password }}</span></template
        >
      </el-table-column>
      <el-table-column label="领取人名称" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.username ? scope.row.username : '-' }}</span></template
        >
      </el-table-column>
      <el-table-column label="领取人电话" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.phone ? scope.row.phone : '-' }}</span></template
        >
      </el-table-column>
      <el-table-column label="领取时间" min-width="100">
        <template #default="scope"
          ><span>{{ scope.row.use_time }}</span></template
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
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        v-model:page="table.page"
        v-model:limit="table.limit"
        @pagination="getMemberCard"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { userMemberCard, memberCardStatus } from '../../api';

defineOptions({ name: 'memberCardList' });

const props = defineProps({ id: { default: 0 } });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const data1 = ref([]);
const loading = ref(false);
const total = ref(0);
const table = ref({ page: 1, limit: 15, card_number: '', phone: '', is_use: '' });

// created
getMemberCard();

function onchangeIsShow(row) {
  memberCardStatus({ card_id: row.id, status: row.status })
    .then((res) => {
      ElMessage.success(res.msg);
      getMemberCard();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      getMemberCard();
    });
}

function getMemberCard() {
  loading.value = true;
  userMemberCard(props.id, table.value)
    .then((res) => {
      loading.value = false;
      data1.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}

function formSubmit() {
  table.value.page = 1;
  getMemberCard();
}
</script>
