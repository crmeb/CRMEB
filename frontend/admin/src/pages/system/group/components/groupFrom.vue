<template>
  <div>
    <el-dialog v-model="modals" width="720px" :title="titleFrom" :close-on-click-modal="false">
      <el-form ref="formValidateRef" :model="formValidate" label-width="100px" :rules="ruleValidate" @submit.prevent>
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="数据组名称：" prop="name">
              <el-input v-model="formValidate.name" placeholder="请输入数据组名称" style="width: 90%"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="数据字段：" prop="config_name">
              <el-input v-model="formValidate.config_name" placeholder="请输入数据字段" style="width: 90%"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="数据简介：" prop="info">
              <el-input v-model="formValidate.info" placeholder="请输入数据简介" style="width: 90%"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="数据类型：" prop="cate_id">
              <el-radio-group v-model="formValidate.cate_id">
                <el-radio :label="0" :value="0">默认</el-radio>
                <el-radio :label="1" :value="1">数据</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-for="(item, index) in formValidate.typelist" :key="index">
            <el-row :gutter="20">
              <el-col v-bind="grid">
                <el-form-item
                  :label="'字段' + (index + 1) + '：'"
                  label-width="90px"
                  :prop="'typelist.' + index + '.name.value'"
                  :rules="{ required: true, message: '请输入字段名称：姓名', trigger: 'blur' }"
                >
                  <el-input v-model="item.name.value" placeholder="字段名称：姓名"></el-input>
                </el-form-item>
              </el-col>
              <el-col v-bind="grid">
                <el-form-item
                  label-width="0"
                  :prop="'typelist.' + index + '.title.value'"
                  :rules="{ required: true, message: '请输入字段配置名', trigger: 'blur' }"
                >
                  <el-input v-model="item.title.value" placeholder="字段配置名：name"></el-input>
                </el-form-item>
              </el-col>
              <el-col v-bind="grid">
                <el-form-item
                  label-width="0"
                  :prop="'typelist.' + index + '.type.value'"
                  :rules="{ required: true, message: '请选择字段类型', trigger: 'change' }"
                >
                  <el-select placeholder="字段类型" v-model="item.type.value">
                    <el-option value="input">文本框</el-option>
                    <el-option value="textarea">多行文本框</el-option>
                    <el-option value="radio">单选框</el-option>
                    <el-option value="checkbox">多选框</el-option>
                    <el-option value="select">下拉选择</el-option>
                    <el-option value="upload">单图</el-option>
                    <el-option value="uploads">多图</el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col v-bind="gridBtn">
                <el-form-item label-width="0">
                  <el-button icon="Delete" v-db-click @click="delGroup(index)"></el-button>
                </el-form-item>
              </el-col>
              <el-col
                v-if="item.type.value === 'radio' || item.type.value === 'checkbox' || item.type.value === 'select'"
                :span="24"
              >
                <el-form-item
                  :prop="'typelist.' + index + '.param.value'"
                  :rules="{ required: true, message: '请输入参数方式', trigger: 'blur' }"
                >
                  <el-input
                    type="textarea"
                    :rows="4"
                    :placeholder="item.param.placeholder"
                    v-model="item.param.value"
                    style="width: 90%"
                  ></el-input>
                </el-form-item>
              </el-col>
            </el-row>
          </el-col>
          <el-col>
            <el-form-item>
              <el-button type="primary" v-db-click @click="addType">添加字段</el-button>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="handleReset">取 消</el-button>
          <el-button type="primary" v-db-click @click="handleSubmit()" :disabled="valids">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { groupAddApi, groupInfoApi } from '@/api/system';

defineOptions({ name: 'menusFrom' });

const props = defineProps({
  groupId: {
    type: Number,
    default: 0,
  },
  titleFrom: {
    type: String,
    default: '',
  },
  addId: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['getList', 'clearFrom']);

const formValidateRef = ref(null);

const iconVal = ref('');
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const gridBtn = {
  xl: 3,
  lg: 3,
  md: 12,
  sm: 24,
  xs: 24,
};
const modals = ref(false);
const modal12 = ref(false);
const ruleValidate = {
  name: [{ required: true, message: '请输入数据组名称', trigger: 'blur' }],
  config_name: [{ required: true, message: '请输入数据字段', trigger: 'blur' }],
  info: [{ required: true, message: '请输入数据简介', trigger: 'blur' }],
  names: [{ required: true, message: '请输入字段名称', trigger: 'blur' }],
};
const FromData = ref([]);
const valids = ref(false);
const list2 = ref([]);
const formValidate = ref({
  name: '',
  config_name: '',
  info: '',
  typelist: [],
  cate_id: 0,
});

watch(
  () => props.addId,
  (n) => {
    if (n === 'addId') {
      formValidate.value.typelist = [];
    }
  },
);

// 点击添加字段
function addType() {
  formValidate.value.typelist.push({
    name: {
      value: '',
    },
    title: {
      value: '',
    },
    type: {
      value: '',
    },
    param: {
      placeholder: '参数方式例如:\n1=>白色\n2=>红色\n3=>黑色',
      value: '',
    },
  });
}
// 删除字段
function delGroup(index) {
  formValidate.value.typelist.splice(index, 1);
}
// 详情
function fromData(id) {
  groupInfoApi(id)
    .then(async (res) => {
      formValidate.value = res.data.info;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 提交
function handleSubmit() {
  let data = {
    url: props.groupId ? `/setting/group/${props.groupId}` : 'setting/group',
    method: props.groupId ? 'put' : 'post',
    datas: formValidate.value,
  };
  formValidateRef.value.validate((valid) => {
    if (valid) {
      if (formValidate.value.typelist.length === 0) return ElMessage.error('请添加字段名称：姓名！');
      groupAddApi(data)
        .then(async (res) => {
          ElMessage.success(res.msg);
          modals.value = false;
          formValidateRef.value.resetFields();
          formValidate.value.typelist = [];
          emit('getList');
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      if (!formValidate.value.name) return ElMessage.error('请添加数据组名称！');
      if (!formValidate.value.config_name) return ElMessage.error('请添加数据字段！');
      if (!formValidate.value.info) return ElMessage.error('请添加数据简介！');
    }
  });
}
function handleReset() {
  modals.value = false;
  formValidateRef.value.resetFields();
  emit('clearFrom');
}

defineExpose({ modals, fromData });
</script>

<style lang="scss" scoped>
.cur {
  cursor: pointer;
}
</style>
