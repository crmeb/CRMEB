<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table class="mt14" :data="tbody" v-loading="loading" highlight-current-row>
        <el-table-column label="ID" width="80">
          <template #default="scope"
            ><span>{{ scope.row.id }}</span></template
          >
        </el-table-column>
        <el-table-column label="会员名" min-width="130">
          <template #default="scope"
            ><span>{{ scope.row.title }}</span></template
          >
        </el-table-column>
        <el-table-column label="有限期（天）" min-width="130">
          <template #default="scope"
            ><span>{{ scope.row.vip_day === -1 ? '永久' : scope.row.vip_day }}</span></template
          >
        </el-table-column>
        <el-table-column label="原价" min-width="90">
          <template #default="scope"
            ><span>{{ scope.row.price }}</span></template
          >
        </el-table-column>
        <el-table-column label="优惠价" min-width="90">
          <template #default="scope"
            ><span>{{ scope.row.pre_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="是否开启" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="0"
              :inactive-value="1"
              v-model="scope.row.is_del"
              :value="scope.row.is_del"
              @change="onchangeIsShow(scope.row)"
              size="large"
            ></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="90">
          <template #default="scope"
            ><span>{{ scope.row.sort }}</span></template
          >
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a href="javascript:" v-db-click @click="editType(scope.row)">编辑</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog v-model="modal" :title="`${rowModelType}${rowEdit && rowEdit.title}会员`" width="540px" @closed="cancel">
      <form-create v-if="modal" v-model="fapi" :rule="rule" :option="options" @submit="onSubmit"></form-create>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { userMemberShip, memberShipSave, memberCard, deleteCard } from '../../api';

defineOptions({ name: 'memberTypeList' });

const tbody = ref([]);
const loading = ref(false);
const total = ref(0);
const modal = ref(false);
const rowEdit = ref({});
const rowModelType = ref('编辑');
const options = ref({ form: { labelWidth: '100px' } });
const rule = ref([
  { type: 'hidden', field: 'id', value: '' },
  { type: 'hidden', field: 'type', value: '' },
  {
    type: 'input',
    field: 'title',
    title: '会员名',
    value: '',
    props: { disabled: false, placeholder: '输入会员名' },
    validate: [{ type: 'string', max: 10, min: 1, message: '请输入长度为1-10的名称', requred: true }],
  },
  {
    type: 'InputNumber',
    field: 'vip_day',
    title: '有限期（天）',
    value: null,
    props: { precision: 0, disabled: false, type: 'text', placeholder: '输入有限期', controls: false },
    style: { width: '100%' },
    validate: [{ type: 'number', max: 1000000, min: 0, message: '最大只能输入1000000,最小为0', requred: true }],
  },
  {
    type: 'InputNumber',
    field: 'price',
    title: '原价',
    value: null,
    props: { min: 0, disabled: false, placeholder: '输入原价', controls: false },
    style: { width: '100%' },
    validate: [{ type: 'number', max: 1000000, min: 0, message: '最大只能输入1000000,最小为0', requred: true }],
  },
  {
    type: 'InputNumber',
    field: 'pre_price',
    title: '优惠价',
    value: null,
    props: { min: 0, disabled: false, placeholder: '输入优惠价', controls: false },
    style: { width: '100%' },
    validate: [{ type: 'number', max: 1000000, min: 0, message: '最大只能输入1000000,最小为0', requred: true }],
  },
  {
    type: 'InputNumber',
    field: 'sort',
    title: '排序',
    value: 0,
    props: { min: 1, max: 1000000, disabled: false, placeholder: '请输入排序', controls: false },
    style: { width: '100%' },
    validate: [{ type: 'number', max: 1000000, min: 0, message: '最大只能输入1000000,最小为0', requred: true }],
  },
]);
const fapi = ref({ id: '', pre_price: null, price: null, sort: null, title: '', type: 'owner', vip_day: null });

// created
getMemberShip();

function onchangeIsShow(row) {
  memberCard({ id: row.id, is_del: row.is_del })
    .then((res) => {
      ElMessage.success(res.msg);
      getMemberShip();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function cancel() {
  fapi.value = { id: '', pre_price: null, price: null, sort: null, title: '', type: 'owner', vip_day: null };
  rule.value.forEach((e) => {
    e.value = null;
  });
}

function getMemberShip() {
  loading.value = true;
  userMemberShip()
    .then((res) => {
      loading.value = false;
      total.value = res.data.count;
      tbody.value = res.data.list;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}

function editType(row) {
  rule.value.forEach((item) => {
    for (const key in row) {
      if (row.hasOwnProperty(key) && item.field === key) {
        if (key === 'vip_day') {
          if (row[key] === -1 || row[key] == '永久') {
            item.type = 'input';
            item.props.disabled = true;
            row[key] = '永久';
            item.validate = [{ type: 'string', message: '', requred: true }];
          } else {
            item.type = 'InputNumber';
            item.props.disabled = false;
            item.props.min = 1;
            // 恢复数字校验：编辑过永久(-1)后 validate 残留 string 规则，会导致数值型 vip_day 校验失败
            item.validate = [
              { type: 'number', max: 1000000, min: 0, message: '最大只能输入1000000,最小为0', requred: true },
            ];
          }
        }
        if (['price'].includes(key)) row[key] = parseFloat(row[key]);
        if (['pre_price'].includes(key)) {
          row[key] = parseFloat(row[key]);
          item.props.disabled = !row[key];
        }
        item.value = row[key];
      }
    }
  });
  rowModelType.value = '编辑';
  rowEdit.value = JSON.parse(JSON.stringify(row));
  // form-create v3 的 v-model 绑定表单数据，挂载时优先于 rule.value，须同步写入才能回显；
  // type 取行数据真实值（free/month/quarter/year/ever...），后端按 type 区分永久/免费等逻辑
  fapi.value = {
    id: rowEdit.value.id,
    type: rowEdit.value.type,
    title: rowEdit.value.title,
    vip_day: rowEdit.value.vip_day,
    price: rowEdit.value.price,
    pre_price: rowEdit.value.pre_price,
    sort: rowEdit.value.sort,
  };
  modal.value = true;
}

function onSubmit(formData) {
  memberShipSave(rowEdit.value.id, formData)
    .then((res) => {
      modal.value = false;
      ElMessage.success(res.msg);
      getMemberShip();
      cancel();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
</script>
