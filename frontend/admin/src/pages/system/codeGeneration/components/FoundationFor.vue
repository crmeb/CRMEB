<template>
  <div class="main">
    <el-alert class="mb20" closable>
      <template v-slot:title>crud生成说明</template>
      <template> 不能生成系统自带的表；已经生成过的表还能继续生成 </template>
    </el-alert>
    <el-form :model="foundation" :rules="foundationRules" label-width="100px">
      <el-form-item label="菜单：">
        <el-cascader
          class="form-width"
          v-model="foundation.pid"
          :options="menusList"
          :props="{ checkStrictly: true, multiple: false, emitPath: false }"
          clearable
        ></el-cascader>
        <div class="tip">选项，选择的菜单成功后会自动写入到此菜单下</div>
      </el-form-item>
      <el-form-item label="菜单名称：">
        <el-input class="form-width" v-model="foundation.menuName" placeholder="请输入菜单名称"></el-input>
        <div class="tip">
          生成菜单为可选项，不填写默认生成的菜单名称将为表名；生成后会把自动生成的权限默认加入该菜单下
        </div>
      </el-form-item>
      <el-form-item label="模块名：" prop="modelName">
        <el-input class="form-width" v-model="foundation.modelName" placeholder="请输入模块名"></el-input>
        <div class="tip">模块名称为中文或者英文，用在接口名称前缀、表单头部标题</div>
      </el-form-item>
      <el-form-item label="表名：" prop="tableName">
        <el-input class="form-width" v-model="foundation.tableName" placeholder="请输入表名"></el-input>
        <div class="tip">
          用于生成CRUD指定的表名，不需要携带表前缀；对于生成过的表将不能在进行生成；或者可以删除对应的文件重新生成！对应系统中重要的数据表将不允许生成！
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { crudMenus, crudColumnType, crudFilePath } from '@/api/systemCodeGeneration';

defineProps({
  foundation: {
    type: Object,
    default: () => {
      return {};
    },
  },
});

const foundationForm = ref(null);

const foundationRules = {
  // pid: [{ required: true, message: '请输入菜单', trigger: 'blur' }],
  tableName: [{ required: true, message: '请输入表名', trigger: 'blur' }],
  modelName: [{ required: true, message: '请输入模块名', trigger: 'blur' }],
};
const menusList = ref([]);
const columnTypeList = ref([]);
const fromTypeList = [
  {
    value: '0',
    label: '不生成',
  },
  {
    value: 'input',
    label: 'input',
  },
  {
    value: 'textarea',
    label: 'textarea',
  },
  // {
  //   value: 'select',
  //   label: 'select',
  // },
  {
    value: 'radio',
    label: 'radio',
  },
  {
    value: 'number',
    label: 'number',
  },
  {
    value: 'frameImageOne',
    label: 'frameImageOne',
  },
  {
    value: 'frameImages',
    label: 'frameImages',
  },
];
const loading = ref(false);
const tableField = ref([]);

function disabledInput(index) {
  let fieldInfo = tableField.value[index];
  let res = ['addTimestamps', 'addSoftDelete'].includes(tableField.value[index].field_type);
  if (fieldInfo.primaryKey) {
    res = true;
  }
  if (fieldInfo.field === 'delete_time' && fieldInfo.field_type === 'timestamp') {
    res = true;
  }
  return res;
}
function initfield() {
  tableField.value = [];
}
function changeItemField(e, i) {
  if (e === 'addSoftDelete') {
    tableField.value[i]['comment'] = '伪删除';
  }
  if (e === 'addTimestamps') {
    tableField.value[i]['comment'] = '添加和修改时间';
  }
}
function getCrudMenus() {
  crudMenus().then((res) => {
    menusList.value = res.data;
  });
  crudColumnType().then((res) => {
    columnTypeList.value = res.data.types;
  });
}
function del(index) {
  tableField.value.splice(index, 1);
}

getCrudMenus();

defineExpose({ disabledInput, initfield, changeItemField, del, tableField, fromTypeList });
</script>
<style lang="scss" scoped>
.form-width {
  width: 500px;
}
.item {
  display: flex;
  margin-bottom: 10px;
  .row {
    width: 140px;
    margin-right: 10px;
  }
}
</style>
