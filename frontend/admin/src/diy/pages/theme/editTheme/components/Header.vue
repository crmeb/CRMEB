<template>
  <div class="page-header">
    <div class="header-left">
      <span v-if="isMicroPage" class="iconfont iconfanhui" @click="backToMicroPage"></span>
      <span class="label">当前{{ isMicroPage ? '专题' : '主题' }}：</span>
      <span class="theme-name">{{ themeName }}</span>
      <span class="iconfont iconic_edit1 edit-icon" @click="handleEdit"></span>
    </div>

    <div class="header-right">
      <el-button icon="View" @click="$emit('preview')">预览</el-button>
      <el-button v-if="!isMicroPage" @click="$emit('save-template')">另存主题</el-button>
      <el-button @click="$emit('save')">保存</el-button>
      <el-button type="primary" @click="$emit('save-close')">保存并关闭</el-button>
    </div>

    <!-- 修改主题信息弹窗 -->
    <el-dialog :title="`修改${isMicroPage ? '专题' : '主题'}信息`" v-model="dialogVisible" width="500px">
      <!-- 不能给 el-form 挂与数据 form 同名的模板 ref：script setup 下挂载后会覆盖 form，
           预填与 v-model 全部写到组件实例上，输入框无法键入（受控值不更新） -->
      <el-form :model="form" label-width="80px">
        <el-form-item :label="`${isMicroPage ? '专题' : '主题'}名称：`">
          <el-input
            v-model="form.title"
            :maxlength="20"
            show-word-limit
            :placeholder="`请输入${isMicroPage ? '专题' : '主题'}名称`"
          ></el-input>
        </el-form-item>
        <el-form-item :label="`${isMicroPage ? '专题' : '主题'}简介：`">
          <el-input
            type="textarea"
            v-model="form.info"
            :maxlength="200"
            show-word-limit
            :placeholder="`请输入${isMicroPage ? '专题' : '主题'}简介`"
            :rows="4"
          ></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button @click="dialogVisible = false">取 消</el-button>
          <el-button type="primary" @click="handleConfirm">确 定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import { ElMessageBox } from 'element-plus';
import { useRoute, useRouter } from 'vue-router';

defineOptions({ name: 'PageHeader' });

const props = defineProps({
  themeName: {
    type: String,
    default: '',
  },
  themeInfo: {
    type: String,
    default: '',
  },
  isMicroPage: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['preview', 'save', 'save-close', 'save-template', 'update-info']);

const route = useRoute();
const router = useRouter();

const dialogVisible = ref(false);
const form = ref({
  title: '',
  info: '',
});

function handleEdit() {
  form.value.title = props.themeName;
  form.value.info = props.themeInfo;
  dialogVisible.value = true;
}

function handleConfirm() {
  let data = {
    title: form.value.title,
    info: form.value.info,
  };
  if (route.query.page_type === 'micro') data.page_type = 'micro';
  if (route.query.tid) data.tid = route.query.tid;
  emit('update-info', data);
  dialogVisible.value = false;
}

function backToMicroPage() {
  ElMessageBox.confirm('确定离开此页面？系统可能不会保存您所做的更改。', '提示', {
    confirmButtonText: '保存',
    cancelButtonText: '退出',
    type: 'warning',
    distinguishCancelAndClose: true,
    showClose: false,
  })
    .then(() => {
      emit('save');
      nextTick(() => {
        router.push({
          path: `/admin/setting/theme/micro_page`,
        });
      });
    })
    .catch(() => {
      router.push({
        path: `/admin/setting/theme/micro_page`,
      });
    });
}
</script>

<style lang="scss" scoped>
.page-header {
  height: 60px;
  background: #fff;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;

  .header-left {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #333;

    .label {
      font-weight: 500;
    }
    .iconfanhui {
      margin-right: 10px;
    }
    .theme-name {
      font-weight: 500;
      margin-right: 10px;
    }

    .edit-icon {
      cursor: pointer;
      color: #999;

      &:hover {
        color: #1890ff;
      }
    }
  }
}
</style>
