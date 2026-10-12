<template>
  <div v-if="FromData">
    <el-dialog v-model="modals" :title="FromData.title" width="720px" @closed="cancel">
      <slot name="before-form"></slot>
      <form-create v-model:api="fapi" :option="config" :rule="FromData.rules" @submit="onSubmit" class="formBox" />
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="modals = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="formSubmit">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import formCreateFactory from '@form-create/element-ui';
import request from '@/libs/request';

defineOptions({ name: 'edit' });

const formCreate = formCreateFactory.$form();

const props = defineProps({
  FromData: {
    type: Object,
    default: null,
  },
  update: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['submitFail']);

const modals = ref(false);
const loading = ref(false);
const fapi = ref(null);
const config = {
  form: {
    labelWidth: '100px',
  },
  resetBtn: false,
  submitBtn: false,
  global: {
    upload: {
      props: {
        onSuccess(res, file) {
          if (res.status === 200) {
            file.url = res.data.src;
          } else {
            ElMessage.error(res.msg);
          }
        },
      },
    },
  },
};

watch(
  () => props.FromData,
  () => {
    props.FromData.rules.forEach((e) => {
      e.title += '：';
    });
  },
);

function formSubmit() {
  fapi.value.submit();
}
// 提交表单 group
function onSubmit(formData) {
  let datas = {};
  datas = formData;
  if (loading.value) return;
  loading.value = true;
  request({
    url: props.FromData.action,
    method: props.FromData.method,
    data: datas,
  })
    .then((res) => {
      // 列表刷新走 submitFail 事件（各页面已绑定）；Vue3 下 <script setup> 的绑定不经实例代理暴露，
      // 旧版 proxy.$parent.getList() 必然 TypeError，会把成功的保存误报为错误且弹窗无法关闭
      ElMessage.success(res.msg);
      modals.value = false;
      setTimeout(() => {
        emit('submitFail');
        loading.value = false;
      }, 1000);
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 关闭按钮
function cancel() {
  // emit('onCancel')
}

// 页面通过 $refs.edits.modals 控制弹窗
defineExpose({ modals });
</script>

<style lang="scss" scoped></style>
